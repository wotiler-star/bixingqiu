<?php
/**
 * KindEditor 上传接口（已加固版）
 *
 * ------------------------------------------------------------------
 * [安全修复] 原版为 KindEditor 官方"演示程序"，直接上线存在以下高危问题，
 * 在 Hostinger 等共享主机上会被 Imunify360 / ModSecurity 判定为
 * "开放上传点 / 钓鱼托管"，轻则接口 403，重则整个账号被停用：
 *
 *   1. 完全无鉴权 —— 任何人都能匿名 POST 上传文件；
 *   2. 允许 htm / html / swf —— 可在你的域名下托管钓鱼页与 XSS 载荷；
 *   3. $max_size = 500000000（500MB）—— 磁盘/inode 配额可被瞬间打爆；
 *   4. mkdir() 未指定权限且不递归，失败无处理；
 *   5. 上传目录未禁用 PHP 执行，一旦有扩展名绕过即为 RCE。
 *
 * 本版改动：
 *   - 强制校验后台登录态（$_SESSION['ADMINID']），未登录直接拒绝；
 *   - 白名单去掉 htm/html/swf 等可执行/可渲染类型；
 *   - 上传上限降到 10MB（可用环境变量 KONE_UPLOAD_MAX_MB 调整）；
 *   - 二次校验真实 MIME（图片走 getimagesize），防扩展名伪装；
 *   - mkdir 递归 + 0755，失败有提示；
 *   - 输出统一 JSON。
 * ------------------------------------------------------------------
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'JSON.php';

function kone_upload_reply($arr) {
    if (!headers_sent()) {
        header('Content-type: application/json; charset=UTF-8');
    }
    echo json_encode($arr, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function alert($msg) {
    kone_upload_reply(array('error' => 1, 'message' => $msg));
}

/* ---------- 1. 鉴权：必须是已登录的后台管理员 ---------- */
if (empty($_SESSION['ADMINID'])) {
    http_response_code(403);
    alert('未登录或登录已过期，请重新登录后台后再上传。');
}

$php_path = dirname(__FILE__) . '/';
$php_url  = dirname($_SERVER['PHP_SELF']) . '/';

//文件保存目录路径
$save_path = $php_path . '../../../konecms_ups/k';
//文件保存目录URL
$save_url  = $php_url . '../../../konecms_ups/k/';

/* ---------- 2. 收紧扩展名白名单 ---------- */
// [安全修复] 移除 htm/html（钓鱼页与存储型 XSS）、swf（Flash XSS，且早已停用）、
//            rm/rmvb/asf（老旧容器，播放器漏洞多）
$ext_arr = array(
    'image' => array('gif', 'jpg', 'jpeg', 'png', 'bmp', 'webp'),
    'flash' => array('flv'),
    'media' => array('flv', 'mp3', 'wav', 'mp4', 'mid', 'avi', 'mpg'),
    'file'  => array('doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'pdf', 'txt', 'zip', 'rar', 'gz'),
);

/* ---------- 3. 上传体积上限 ---------- */
// [安全修复] 原为 500MB，共享主机磁盘/inode 配额可被瞬间打爆
$max_mb   = getenv('KONE_UPLOAD_MAX_MB');
$max_mb   = ($max_mb === false || (int)$max_mb <= 0) ? 10 : (int)$max_mb;
$max_size = $max_mb * 1024 * 1024;

$real_base = realpath($save_path);
if ($real_base === false) {
    // 目录不存在时递归创建
    if (!@mkdir($save_path, 0755, true) && !is_dir($save_path)) {
        alert('上传目录不存在且无法创建，请检查目录权限。');
    }
    $real_base = realpath($save_path);
}
$save_path = $real_base . '/';

//PHP上传失败
if (!empty($_FILES['imgFile']['error'])) {
    switch ($_FILES['imgFile']['error']) {
        case 1: $error = '超过php.ini允许的大小。'; break;
        case 2: $error = '超过表单允许的大小。'; break;
        case 3: $error = '图片只有部分被上传。'; break;
        case 4: $error = '请选择图片。'; break;
        case 6: $error = '找不到临时目录。'; break;
        case 7: $error = '写文件到硬盘出错。'; break;
        case 8: $error = 'File upload stopped by extension。'; break;
        default: $error = '未知错误。';
    }
    alert($error);
}

//有上传文件时
if (empty($_FILES) === false) {
    $file_name = $_FILES['imgFile']['name'];
    $tmp_name  = $_FILES['imgFile']['tmp_name'];
    $file_size = $_FILES['imgFile']['size'];

    if (!$file_name) {
        alert('请选择文件。');
    }
    if (@is_dir($save_path) === false) {
        alert('上传目录不存在。');
    }
    if (@is_writable($save_path) === false) {
        alert('上传目录没有写权限。');
    }
    if (@is_uploaded_file($tmp_name) === false) {
        alert('上传失败。');
    }
    if ($file_size > $max_size) {
        alert('上传文件超过 ' . $max_mb . 'MB 限制。');
    }

    //检查目录名
    $dir_name = empty($_GET['dir']) ? 'image' : trim($_GET['dir']);
    if (empty($ext_arr[$dir_name])) {
        alert('目录名不正确。');
    }

    //获得文件扩展名
    $temp_arr = explode('.', $file_name);
    $file_ext = strtolower(trim(array_pop($temp_arr)));

    //检查扩展名
    if (in_array($file_ext, $ext_arr[$dir_name], true) === false) {
        alert('上传文件扩展名是不允许的扩展名。' . "\n" . '只允许' . implode(',', $ext_arr[$dir_name]) . '格式。');
    }

    /* ---------- 4. 图片二次校验真实内容，防扩展名伪装 ---------- */
    // [安全修复] 仅凭扩展名判断可被「图片马」绕过（内容是 PHP、扩展名是 .jpg），
    //            配合任意包含漏洞即可 RCE。这里用 getimagesize 验真。
    if ($dir_name === 'image') {
        $info = @getimagesize($tmp_name);
        if ($info === false || empty($info[2])) {
            alert('该文件不是有效的图片。');
        }
        $allow_type = array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_BMP, IMAGETYPE_WEBP);
        if (!in_array($info[2], $allow_type, true)) {
            alert('不支持的图片类型。');
        }
    }

    //创建文件夹
    if ($dir_name !== '') {
        $save_path .= $dir_name . '/';
        $save_url  .= $dir_name . '/';
    }
    $ymd = date('Ymd');
    $save_path .= $ymd . '/';
    $save_url  .= $ymd . '/';
    if (!file_exists($save_path)) {
        // [安全修复] 递归创建并显式指定 0755，原版 mkdir() 无递归会失败
        if (!@mkdir($save_path, 0755, true) && !is_dir($save_path)) {
            alert('创建上传目录失败。');
        }
    }

    $arr = array('K', 'W', 'L', 'konecms');
    $new_file_name = $arr[array_rand($arr)] . date('YmdHis') . '_' . mt_rand(10000, 99999) . '.' . $file_ext;

    $file_path = $save_path . $new_file_name;
    if (move_uploaded_file($tmp_name, $file_path) === false) {
        alert('上传文件失败。');
    }
    @chmod($file_path, 0644);
    $file_url = $save_url . $new_file_name;

    kone_upload_reply(array('error' => 0, 'url' => $file_url));
}

alert('没有接收到上传文件。');
