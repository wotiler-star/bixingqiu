<?php
/**
 * 币星球 采集入库接口（服务器端，token 保护）
 * 由 Python 管线 POST 调用，把二创改写后的英文文章 INSERT 进 i_tb。
 *
 * 部署：上传到 /public_html/service/ingest.php
 * 安全：必须带正确 token；仅接受 POST；做了输入清洗与防注入。
 */
error_reporting(0);
header("Content-Type: application/json; charset=utf-8");

// ⚠️ 必须与 pipeline/config.yaml 的 site.ingest_token 保持一致
define('INGEST_TOKEN', 'BxqCollect2026');

// ---- 建立数据库连接（自包含，不依赖外部 $conn） ----
function bxq_connect() {
    $host = 'srv2062.hstgr.io';
    $port = 3306;
    $db   = 'u906113796_bixingqiu';
    $user = 'u906113796_bixingqiu';
    $pass = 'Bendan666.';
    // 读取 .env（如有）覆盖默认值
    $env = __DIR__ . '/config/.env';
    if (file_exists($env)) {
        $raw = (string) @file_get_contents($env);
        if (substr($raw, 0, 3) === "\xEF\xBB\xBF") $raw = substr($raw, 3);
        foreach (explode("\n", $raw) as $line) {
            $line = trim($line, " \t\r\n\0\x0B");
            if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) continue;
            list($k, $v) = explode('=', $line, 2);
            $k = trim($k); $v = trim($v, " \t\r\n\0\x0B");
            if      ($k === 'DB_HOST') $host = $v;
            elseif  ($k === 'DB_PORT') $port = (int)$v;
            elseif  ($k === 'DB_NAME') $db   = $v;
            elseif  ($k === 'DB_USER') $user = $v;
            elseif  ($k === 'DB_PASS') $pass = $v;
        }
    }
    // Hostinger：主机名在 Web 服务器上常解析到 IPv6（2a02:...），而授权白名单只放行 IPv4，
    // 导致 "Access denied"。强制用 gethostbyname 取 IPv4 字面量再连接。
    $connHost = $host;
    if (function_exists('gethostbyname')) {
        $ipv4 = gethostbyname($host);
        if ($ipv4 && $ipv4 !== $host && filter_var($ipv4, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $connHost = $ipv4;
        }
    }
    return @mysqli_connect($connHost, $user, $pass, $db, $port);
}

$conn = bxq_connect();
if (!$conn) {
    http_response_code(500);
    echo json_encode(array("success" => 1, "msg" => "DB connect failed: " . mysqli_connect_error()));
    exit;
}

// 读 JSON 请求体
$raw = file_get_contents("php://input");
$in = json_decode($raw, true);
if (!is_array($in)) {
    $in = $_POST; // 兼容表单提交
}
if (!is_array($in)) {
    http_response_code(400);
    echo json_encode(array("success" => 1, "msg" => "invalid body"));
    exit;
}

// token 校验
$token = isset($in['token']) ? $in['token'] : '';
if ($token !== INGEST_TOKEN) {
    http_response_code(403);
    echo json_encode(array("success" => 1, "msg" => "bad token"));
    exit;
}

// ---- 清洗 ----
function clean_text($s) { return trim(strip_tags($s === null ? '' : $s)); }
function clean_html($s) {
    $s = $s === null ? '' : (string)$s;
    // 防御：移除 script / iframe / object / embed 及其内容
    $s = preg_replace('#<(script|iframe|object|embed)[^>]*>.*?</\1>#is', '', $s);
    // 移除 on* 事件属性
    $s = preg_replace('#\s+on[a-z]+\s*=\s*("[^"]*"|\'[^\']*\')#i', '', $s);
    return trim($s);
}
function cut_str($s, $n) {
    if (function_exists('mb_substr')) return mb_substr($s, 0, $n, 'utf-8');
    return substr($s, 0, $n);
}

$title    = clean_text($in['title'] ?? '');
$cnt      = clean_html($in['cnt'] ?? '');
$cnt_short= clean_text($in['cnt_short'] ?? '');
// ---- cataid 处理 ----
// ⚠️ 旧实现用 preg_replace('/[^0-9,]/','',$cataid) 把 "cataid159" 洗成 "159"，
// 随后 preg_match('/cataid\d/') 必然失败 -> 所有文章被强制回落成 cataid108（栏目映射失效）。
// 改为：提取数字 -> 拼回 "cataidN" -> 再追加「一级栏目父ID」。
// ⚠️ 首页 i*Arr 列表按一级栏目筛选（LIKE '%cataid2%'），只带子栏目 ID 的文章在首页完全看不到。
$CAT_PARENT = array(
    '95' => '2', '108' => '2', '157' => '2', '158' => '2', '159' => '2',
    '160' => '2', '193' => '2',                 // 数字货币 2
    '54' => '3', '17'  => '3',                  // 宏观经济 3
    '61' => '31', '31' => '31',                 // 区块链+ 31
);
$rawCata = isset($in['cataid']) ? trim((string)$in['cataid']) : '';
preg_match_all('/\d+/', $rawCata, $mm);
$ids = array_values(array_unique($mm[0]));
if (!$ids) $ids = array('108');
$parts = array();
foreach ($ids as $i) { $parts[] = 'cataid' . $i; }
foreach ($ids as $i) {
    if (isset($CAT_PARENT[$i])) {
        $p = 'cataid' . $CAT_PARENT[$i];
        if (!in_array($p, $parts)) $parts[] = $p;
    }
}
// ⚠️ 首页 12 个 i*Arr 板块（11头条/12行情/13研报/14人物/15宏观/17技术/54政策/55评级/
//    57全球/58八卦/59挖矿…）全部要求 ifindex='0'，且 genWhere 会按栏目路径 OR 匹配，
//    每个板块只取 orderid desc 的前 20 条。
// ⚠️ 实测：11头条 与 57全球 各被 23 篇 orderid=999999 的置顶文章占满 20 个坑位，
//    挂到这两个板块的文章在首页永远看不到。故这里改挂「有空位」的板块。
$HOME_BOARD = array(
    '95' => '12', '108' => '12', '157' => '12', '193' => '12',  // 币种/行情 -> 12 行情
    '159' => '15', '160' => '15', '158' => '15',                 // 交易所/项目方/挖矿 -> 15 宏观
    '54' => '54',                                                // 政策 -> 54 政策
    '17' => '17', '61' => '17', '31' => '17',                    // 技术/公链/区块链 -> 17 技术
);
// ⚠️ 8 = 快讯。首页 kuaiArr 的 WHERE 走 genWhere(快讯栏目路径)，且**按 riqi desc 取前 20**，
//    是唯一「按时间倒序」的板块 —— 新文 riqi 最新，只要带 cataid8 就能顶到快讯最前面。
//    之前漏了 8，导致入库的文章在快讯里完全检索不到（用户看到「首页没显示」的主因）。
$add = array('57', '8');                              // 57 全球：语义正确，便于栏目页归类
foreach ($ids as $i) {
    if (isset($HOME_BOARD[$i])) $add[] = $HOME_BOARD[$i];
}
foreach ($add as $hb) {
    $t = 'cataid' . $hb;
    if (!in_array($t, $parts)) $parts[] = $t;
}
$cataid = implode(',', $parts);
$source   = clean_text($in['source'] ?? '');
$riqi     = clean_text($in['riqi'] ?? '');
if ($riqi === '') $riqi = date('Y-m-d H:i:s');
$picdir_list = clean_text($in['picdir_list'] ?? '');
$dataurl  = clean_text($in['link'] ?? ($in['dataurl'] ?? ''));
$short    = clean_text($cnt_short) ?: cut_str(clean_text($cnt), 200);
if ($cnt === '') $cnt = '正文';
if ($cnt_short === '') $cnt_short = cut_str(clean_text($cnt), 200);

// i_tb 必填（NOT NULL 无默认）字段兜底
$webtitle = $title;
$keywords = $title;
// ⚠️ 首页/栏目列表按 "orderid desc" 排序且只取前 20 条。老文章 orderid 分布为
// 999999(23) / 999(16) / 0(44)。若新文章 orderid=0，会排在所有 0 之后被 20 条截断，
// 首页永远看不到。这里取 500：高于绝大多数普通文章(0)，但不超过人工置顶(999/999999)。
$orderid  = 500;
$tcolor   = '';
$wedfew   = ' ';

// 其它标志位：发布 + 可见；不置顶/不头条/不推荐等（'1' = 未标记，与后台一致）
// ⚠️ ifindex：'0'=上首页，'1'=不上首页（反直觉）。首页 index.class.php 每个 i*Arr 的
//    WHERE 都是 "ifindex='0' and ifhidden='1'"，漏设 '0' 的文章在首页完全不可见。
$ifhidden = '1';   // 可见
$ifpublic = '0';   // 发布
$ifindex  = '0';   // 上首页（关键！）
$ifhead = $ifonly = $ifhot = $ifgun = $ifbold = $ifone = '1';
$uploader = 'api';
$hitnum   = 0;

// 28 列：title,webtitle,keywords,short,cnt,cnt_short,cnt_phone,cnt_short_phone,
//        cataid,source,riqi,picdir_list,dataurl,dataurl_fname,ifhidden,ifpublic,
//        ifindex,ifhead,ifonly,ifhot,ifgun,ifbold,ifone,uploader,hitnum,orderid,tcolor,wedfew
// 类型：前 24 个为字符串；hitnum/orderid 为整数；tcolor/wedfew 为字符串
//       => "ssssssssssssssssssssssss" + "iiss" = 28 个字符。
$sql = "INSERT INTO i_tb
 (title, webtitle, keywords, short, cnt, cnt_short, cnt_phone, cnt_short_phone,
  cataid, source, riqi, picdir_list, dataurl, dataurl_fname, ifhidden, ifpublic,
  ifindex, ifhead, ifonly, ifhot, ifgun, ifbold, ifone, uploader, hitnum, orderid, tcolor, wedfew)
 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(array("success" => 1, "msg" => "prepare failed: " . mysqli_error($conn)));
    exit;
}
$cnt_phone = $cnt;
$cnt_short_phone = $cnt_short;
$dataurl_fname = '';
$bf = "ssssssssssssssssssssssssiiss"; // 28 列类型串（24s + i,i,s,s）
mysqli_stmt_bind_param($stmt, $bf,
    $title, $webtitle, $keywords, $short, $cnt, $cnt_short, $cnt_phone, $cnt_short_phone,
    $cataid, $source, $riqi, $picdir_list, $dataurl, $dataurl_fname, $ifhidden, $ifpublic,
    $ifindex, $ifhead, $ifonly, $ifhot, $ifgun, $ifbold, $ifone, $uploader, $hitnum, $orderid, $tcolor, $wedfew);

if (mysqli_stmt_execute($stmt)) {
    $id = mysqli_insert_id($conn);
    echo json_encode(array("success" => 0, "id" => $id));
} else {
    http_response_code(500);
    echo json_encode(array("success" => 1, "msg" => mysqli_stmt_error($stmt)));
}
