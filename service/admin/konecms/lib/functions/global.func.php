<?php

/**
 * global.func.php
 * @copyright konecms.com
 * @lastmodify 2016年2月26日
 * 
 * 包含函数：
 * _addslashes                 给字符/数组增加转义字符\
 * smarty_set_template_dir     设置SMARTY模板文件根路径
 * smarty_display              设置SMARTY使用的模板
 * to_sqls                     将数组转化为sql语句
 * safe_replace                替换字符中的特定字符
 * L                           语言调用
 * cutStr                      截取字符
 */
/**
 * _addslashes
 *
 * @param unknown $var            
 * @return string
 */
function add_slashes($string, $force = 0)
{
    if (! get_magic_quotes_gpc() || $force) {
        if (is_array($string)) {
            foreach ($string as $key => $val) {
                $string[$key] = addslashes($val, $force);
            }
        } else {
            $string = addslashes($string);
        }
    }
    
    return $string;
}

function get_check($Sql_Str)
{ // 自动过滤Sql的注入语句。
    $check = preg_match('/select|insert|update|delete|\'|\\*|\*|\.\.\/|\.\/|union|into|load_file|outfile/i', $Sql_Str, $matchArr);
    if ($check) {
        print_r($matchArr);
        echo '<script language="JavaScript">alert("系统警告：\n\n请不要尝试在参数中包含非法字符尝试注入！");</script>';
        exit();
    } else {
        return $Sql_Str;
    }
}

function post_check($Sql_Str)
{ // 自动过滤Sql的注入语句。
    $check = preg_match('/select|update|delete|outfile/i', $Sql_Str, $matchArr);
    if ($check) {
        print_r($matchArr);
        echo '<script language="JavaScript">alert("系统警告：\n\n请不要尝试在参数中包含非法字符尝试注入！");</script>';
        exit();
    } else {
        return $Sql_Str;
    }
}

/*
 * 获取字符串中指定字符在字符串中出现的位置
 * @param str String 字符串
 * @param char String 指定字符
 * @return Array 表示所在位置的数组
 *
 */
function getCharpos($str, $char)
{
    $j = 0;
    
    $arr = array();
    
    $count = substr_count($str, $char);
    
    for ($i = 0; $i < $count; $i ++) {
        
        $j = strpos($str, $char, $j);
        
        $arr[] = $j;
        
        $j = $j + 1;
    }
    return $arr;
}

function sql_replace($str)
{
    $str = str_replace("and", "", $str);
    $str = str_replace("execute", "", $str);
    $str = str_replace("chr", "", $str);
    $str = str_replace("mid", "", $str);
    $str = str_replace("master", "", $str);
    $str = str_replace("truncate", "", $str);
    $str = str_replace("char", "", $str);
    $str = str_replace("declare", "", $str);
    $str = str_replace("select", "", $str);
    $str = str_replace("create", "", $str);
    $str = str_replace("delete", "", $str);
    $str = str_replace("insert", "", $str);
    $str = str_replace("update", "", $str);
    $str = str_replace("modify", "", $str);
    $str = str_replace("delete", "", $str);
    $str = str_replace("iframe", "", $str);
    $str = str_replace("or", "", $str);
    $str = str_replace('*', '', $str);
    $str = str_replace("{", '', $str);
    $str = str_replace('}', '', $str);
    return $str;
}

/**
 * 设置smarty模板文件根路径
 */
function smarty_set_template_dir()
{
    $arr = konecms::load_config("system");
    $dir = $arr["default_smarty_template_dir"];
    $default_smarty_template_dir = WEB_ROOT . $dir;
    if (file_exists(($default_smarty_template_dir))) {
        $GLOBALS["smarty"]->setTemplateDir($default_smarty_template_dir);
    } else {
        $default_smarty_template_dir = WEB_ROOT . "template" . DIRECTORY_SEPARATOR . "default";
        $GLOBALS["smarty"]->setTemplateDir($default_smarty_template_dir);
    }
    $GLOBALS["smarty"]->assign("STATIC_PATH", SITE_PATH . $dir . "/" . "static" . "/");
}

/**
 * 设置smarty使用的模板
 *
 * @param unknown $filename            
 */
function smarty_display($filename)
{
    $GLOBALS["smarty"]->display(ROUTE_M . DIRECTORY_SEPARATOR . $filename);
}

/*
 * 操作信息提示
 * @param string $msg 提示信息
 * @param string $tourl 跳转至页面网址
 * @intime int $intime 跳转发生时间
 * @fresh_parent 是否刷新父框架
 * @ifDialog 是否显示对话框
 */
function showmessage($msg, $tourl, $intime = 3, $fresh_parent = false, $ifDialog = false)
{
    include KONECMS_ROOT . "lib" . DIRECTORY_SEPARATOR . "classes" . DIRECTORY_SEPARATOR . 'showmessage.php';
    exit();
}

/*
 * 格式化输出文本
 */
function format_output_string($string)
{
    $chars = 'utf-8';
    if (CHARSET == 'gbk')
        $chars = 'gb2312';
    return nl2br(str_replace(' ', '&nbsp;', htmlspecialchars($string, ENT_COMPAT, $chars)));
}

/**
 * 生成sql语句，如果传入$in_cloumn 生成格式为 IN('a', 'b', 'c')
 *
 * @param $data 条件数组或者字符串            
 * @param $front 连接符            
 * @param $in_column 字段名称            
 * @return string
 */
function to_sqls($data, $front = ' AND ', $in_column = false)
{
    if ($in_column && is_array($data)) {
        $ids = '\'' . implode('\',\'', $data) . '\'';
        $sql = "$in_column IN ($ids)";
        return $sql;
    } else {
        if ($front == '') {
            $front = ' AND ';
        }
        if (is_array($data) && count($data) > 0) {
            $sql = '';
            foreach ($data as $key => $val) {
                $sql .= $sql ? " $front `$key` = '$val' " : " `$key` = '$val' ";
            }
            return $sql;
        } else {
            return $data;
        }
    }
}

/**
 * 安全过滤函数
 *
 * @param
 *            $string
 * @return string
 */
function safe_replace($string)
{
    $string = str_replace('%20', '', $string);
    $string = str_replace('%27', '', $string);
    $string = str_replace('%2527', '', $string);
    $string = str_replace('*', '', $string);
    $string = str_replace('"', '&quot;', $string);
    $string = str_replace("'", '', $string);
    $string = str_replace('"', '', $string);
    $string = str_replace(';', '', $string);
    $string = str_replace('<', '&lt;', $string);
    $string = str_replace('>', '&gt;', $string);
    $string = str_replace("{", '', $string);
    $string = str_replace('}', '', $string);
    $string = str_replace('\\', '', $string);
    return $string;
}

/**
 * 语言文件处理
 *
 * @param string $language            
 * @param array $pars            
 * @param string $modules            
 * @return string
 */
function L($language = 'no_language', $pars = array(), $modules = '')
{
    static $LANG = array();
    static $LANG_MODULES = array();
    static $lang = '';
    
    if (defined('IN_ADMIN')) {
        $lang = SYS_STYLE ? SYS_STYLE : 'zh-cn';
    } else {
        $lang = konecms::load_config('system', 'lang');
    }
    if (! $LANG) {
        require_once KONECMS_ROOT . 'languages' . DIRECTORY_SEPARATOR . $lang . DIRECTORY_SEPARATOR . 'system.lang.php';
        if (defined('IN_ADMIN'))
            require_once KONECMS_ROOT . 'languages' . DIRECTORY_SEPARATOR . $lang . DIRECTORY_SEPARATOR . 'system_menu.lang.php';
        if (file_exists(KONECMS_ROOT . 'languages' . DIRECTORY_SEPARATOR . $lang . DIRECTORY_SEPARATOR . ROUTE_M . '.lang.php'))
            require_once KONECMS_ROOT . 'languages' . DIRECTORY_SEPARATOR . $lang . DIRECTORY_SEPARATOR . ROUTE_M . '.lang.php';
    }
    if (! empty($modules)) {
        $modules = explode(',', $modules);
        foreach ($modules as $m) {
            if (! isset($LANG_MODULES[$m]))
                require_once KONECMS_ROOT . 'languages' . DIRECTORY_SEPARATOR . $lang . DIRECTORY_SEPARATOR . $m . '.lang.php';
        }
    }
    if (! array_key_exists($language, $LANG)) {
        return $language;
    } else {
        $language = $LANG[$language];
        if ($pars) {
            foreach ($pars as $_k => $_v) {
                $language = str_replace('{' . $_k . '}', $_v, $language);
            }
        }
        return $language;
    }
}

function mfile($file)
{
    if (file_exists($file)) {
        return md5(file_get_contents($file));
    } else {
        exit(0);
    }
}

function rmfile($file, $url = "")
{
    return file_get_contents($url . $file);
}

/**
 * cutStr(输入字符，截取长度，开始位置，输出字符编码);
 *
 * @param unknown $string            
 * @param unknown $sublen            
 * @param number $start            
 * @param string $code            
 * @return string
 */
function cutStr($string, $sublen, $start = 0, $code = 'UTF-8')
{
    if ($code == 'UTF-8') {
        $pa = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/";
        preg_match_all($pa, $string, $t_string);
        if (count($t_string[0]) - $start > $sublen)
            return join('', array_slice($t_string[0], $start, $sublen));
        return join('', array_slice($t_string[0], $start, $sublen));
    } else {
        $start = $start * 2;
        $sublen = $sublen * 2;
        $strlen = strlen($string);
        $tmpstr = '';
        for ($i = 0; $i < $strlen; $i ++) {
            if ($i >= $start && $i < ($start + $sublen)) {
                if (ord(substr($string, $i, 1)) > 129) {
                    $tmpstr .= substr($string, $i, 2);
                } else {
                    $tmpstr .= substr($string, $i, 1);
                }
            }
            if (ord(substr($string, $i, 1)) > 129)
                $i ++;
        }
        if (strlen($tmpstr) < $strlen)
            $tmpstr .= "";
        return $tmpstr;
    }
}

/**
 * 获取真实IP
 *
 * @return String IPaddress
 */
function getIPaddress()

{
    $IPaddress = '';
    
    if (isset($_SERVER)) {
        
        if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $IPaddress = $_SERVER["HTTP_X_FORWARDED_FOR"];
        } else 
            if (isset($_SERVER["HTTP_CLIENT_IP"])) {
                $IPaddress = $_SERVER["HTTP_CLIENT_IP"];
            } else {
                $IPaddress = $_SERVER["REMOTE_ADDR"];
            }
    } else {
        
        if (getenv("HTTP_X_FORWARDED_FOR")) {
            
            $IPaddress = getenv("HTTP_X_FORWARDED_FOR");
        } else 
            if (getenv("HTTP_CLIENT_IP")) {
                
                $IPaddress = getenv("HTTP_CLIENT_IP");
            } else {
                
                $IPaddress = getenv("REMOTE_addR");
            }
    }
    
    return $IPaddress;
}

/**
 * unicode_decode(unicode字符（串）);
 *
 * @param unicode $string            
 * @return string
 */
function unicode_decode($string)
{
    $pattern = '/([\w]+)|(\\\u([\w]{4})|[{}:,\"])/i';
    preg_match_all($pattern, $string, $matches);
    if (! empty($matches)) {
        
        $string = '';
        for ($j = 0; $j < count($matches[0]); $j ++) {
            $str = $matches[0][$j];
            if (strpos($str, '\\u') === 0) {
                $code = base_convert(substr($str, 2, 2), 16, 10);
                $code2 = base_convert(substr($str, 4), 16, 10);
                $c = chr($code) . chr($code2);
                $c = iconv('UCS-2', 'UTF-8', $c);
                $string .= $c;
            } else {
                $string .= $str;
            }
        }
    }
    return json_decode($string, true);
}

function unicode_decode_string($name)
{
    $pattern = '/([\w]+)|(\\\u([\w]{4})|[{}:,\"])/i';
    preg_match_all($pattern, $name, $matches);
    if (! empty($matches)) {
        
        $name = '';
        for ($j = 0; $j < count($matches[0]); $j ++) {
            $str = $matches[0][$j];
            if (strpos($str, '\\u') === 0) {
                $code = base_convert(substr($str, 2, 2), 16, 10);
                $code2 = base_convert(substr($str, 4), 16, 10);
                $c = chr($code) . chr($code2);
                $c = iconv('UCS-2', 'UTF-8', $c);
                $name .= $c;
            } else {
                $name .= $str;
            }
        }
    }
    return $name;
}

/*
 * Cataid($conn,$parentid)
 * @return cataidArr 自身cataid+子目录cataid
 * @conn 目录表连接资源
 * $parentid 当前cataid
 *
 */
function Cataid($conn, $parentid)
{
    $cataidArr = array();
    $where_ = "parentid=$parentid";
    $data_ = $conn->select("cataid", $where_);
    $str = '';
    $html = $parentid . ","; 
    if ($data_) {
        foreach ($data_ as $a) {
             $html .= Cataid($conn, $a["cataid"]);
        }
    }
    
    return $html;
}

/*
 * pCataid($conn,$cataid)
 * @return cataidArr 自身cataid+父目录cataid
 * @conn 目录表连接资源
 * $parentid 当前cataid
 *
 */
function  pCataid($conn, $cataid)
{
    $cataidArr = array();
    $where_ = "cataid=$cataid";
    $data_ = $conn->select("parentid", $where_);
    $str = '';
    $html = $cataid . ",";
    if ($data_) {
        foreach ($data_ as $a) {
            $html .= pCataid($conn, $a["parentid"]);
        }
    }

    return $html;
}
/*
 * list_post($mypost, $wFieldArr = array(), $riqi_field = "riqi",$conn='')
 * @return array(mywhere,my)
 * $mypost:提交的POST系统变量
 * $wFieldArr:搜索输入字段需要匹配的字段
 *
 *
 */
function list_post($mypost, $wFieldArr = array(), $riqi_field = "riqi", $conn = '')
{
    $where = ""; // 返回where子句
    $urlArr = array(); // url地址除去page部分 -用于进入编辑页面，可操作返回至搜索结果页
                       // 搜索关键词
    $_POST = $mypost;
    if (isset($_POST["w"]) && $_POST["w"] && $wFieldArr) {
        $w = $_POST["w"];
        $wArr = preg_split("/[\s,，]+/u", $w);
        
        foreach ($wArr as $a) {
            foreach ($wFieldArr as $field) {
                $where .= $field . " like '%$a%'  or ";
            }
        }
        $where = " and (" . rtrim($where, "or ") . ") ";
        $urlArr["w"] = urlencode($w);
    }
    // 日期
     
    if (isset($_POST["riqi1"]) && $_POST["riqi1"]) {
        $riqi1 = $_POST["riqi1"];
        $where .= " and  $riqi_field >='$riqi1'";
        $urlArr["riqi1"] = urlencode($riqi1);
    }
    if (isset($_POST["riqi2"]) && $_POST["riqi2"]) {
        $riqi2 = $_POST["riqi2"];
        $riqi_=date("Y-m-d",strtotime("+1 day",strtotime($riqi2)));
        $where .= " and  $riqi_field <'$riqi_'";
        $urlArr["riqi2"] = urlencode($riqi2);
    }
    // 目录搜索：包含子目录
    if (isset($_POST["cataid"]) && $_POST["cataid"] != '_') {
           $cataid = str_replace("cataid","",$_POST["cataid"]);
        $cataidArr = explode(",", rtrim(Cataid($conn, $cataid), ","));
          $where .= " and (";
        $str_ = "";
        foreach ($cataidArr as $a) {
            $v_="cataid".$a;
            $str_.="find_in_set('$v_',cataid) or ";
         //   $str_ .= " cataid like '%$v_%' or ";
        }
        $where .= rtrim($str_, "or ") . ")";
        $urlArr["cataid"] = urlencode($cataid);
    }
    
    if (isset($_POST["w"]))
        unset($mypost["w"]);
    if (isset($_POST["riqi1"]))
        unset($mypost["riqi1"]);
    if (isset($_POST["riqi2"]))
        unset($mypost["riqi2"]);
    if (isset($_POST["cataid"]))
        unset($mypost["cataid"]);
        // 其它
    if ($mypost) {
        foreach ($mypost as $k => $v) {
            if ($v != "" && $v != "_") {
                $where .= " and $k like '%$v%'";
                $urlArr["$k"] = urlencode($v);
            }
        }
    }
   
    return array(
        "mywhere" => $where,
        "myurlArr" => $urlArr
    );
}

/*
 * 列表页GET 部分
 * $wFieldArr:搜索输入字段需要匹配的字段
 */
function list_get($wFieldArr = array(), $riqi_field = "riqi", $conn = '')
{
    $pageArr = explode("&", rtrim($_SERVER["QUERY_STRING"], "&"));
    $url = "";
    $pageurl = "";
    $where = "";
    $myArr = array();
    foreach ($pageArr as $k => $v) {
        $arr_ = explode("=", $v);
        $k_ = $arr_[0];
        $v_ = isset($arr_[1]) ? $arr_[1] : '';
        if ($k_ != "page" && $k_ != "c" && $k_ != "a" && $k_ != "m" && $v_ != "")
            $myArr[$k_] = urldecode($v_);
    }
    $sql = "";
    foreach ($myArr as $k => $v) {
         
        switch ($k) {
            case "riqi1":
                $where .= " and  $riqi_field>='$v'";
                break;
            case "riqi2":
                $where .= " and  $riqi_field<='$v'";
                break;
            case "w":
                if ($wFieldArr) :
                    $wArr = preg_split("/[\s,，]+/", $v);
                    $where2 = "";
                    foreach ($wArr as $a) {
                        foreach ($wFieldArr as $field) {
                            $where2 .= $field . " like '%$a%'  or ";
                        }
                    }
                    $where .= " and (" . rtrim($where2, "or ") . ") ";
                
                    endif;
                break;
            case "cataid":
 
                $cataidArr = explode(",", rtrim(Cataid($conn, $v), ","));
                $where .= " and (";
                $str_ = "";
                foreach ($cataidArr as $a) {
                    $v_="cataid".$a;
                 //   $str_ .= " cataid like '%$v_%' or ";
            $str_.="find_in_set('$v_',cataid) or ";
                }
                $where .= rtrim($str_, "or ") . ")";
                break;
            default:
                $where .= " and $k like '%$v%'";
        }
        $v = urlencode($v);
        $pageurl .= "$k=$v" . "&";
        $url .= "$k=$v" . "&";
    }
    $url = rtrim($url, "&");
    $pageurl = rtrim($pageurl, "&");
    return array(
        "mywhere" => $where,
        "myurl" => $url,
        "mypageurl" => $pageurl
    );
}

/*
 * dataList($c,$a,$wFieldArr,$riqiField)
 * 返回数据列表页搜索及分页后需要的where条件语句、url链接字符串、pageurl分页字符串
 * @c c控制器
 * @a a方法
 * @wFieldArr 搜索关键词需要匹配的字段数组
 * @riqiField 搜索日期对应的真实字段名称
 *
 */
function dataList($c, $a = "manage", $wFieldArr = array("title"), $riqiField = "riqi", $conn = '')
{
    $url = "c=$c&a=$a" . "_mod&"; // a链接
     $pageurl = "c=$c&a=$a&"; // 分页字符串中除了page的字段链接
                             // 2.post
    if (isset($_POST) && $_POST) {
        $myArr = list_post($_POST, $wFieldArr, $riqiField, $conn);
        $mywhere = $myArr["mywhere"];
        $myurlArr = $myArr["myurlArr"]; // 搜索产生的字段
        
        foreach ($myurlArr as $k => $v) {
            $pageurl .= "$k=$v" . "&";
            $url .= "$k=$v" . "&";
        }
        $url = rtrim($url, "&");
        $pageurl = rtrim($pageurl, "&");
    }
    // 3. get
    $pageArr = explode("&", rtrim($_SERVER["QUERY_STRING"], "&"));
    
    if (count($pageArr) >= 3) { // c、a +page
        $myArr = list_get($wFieldArr, $riqiField,$conn);
        $mywhere = $myArr["mywhere"];
        $myurl = $myArr["myurl"];
        $mypageurl = $myArr["mypageurl"];
        
        $url = $url . $myurl;
        $pageurl = $pageurl . $mypageurl;
    }
    $mywhere.=($c=="admin"&&$a=="manage")?" and adminname!='".L("SAFEADMIN")."'":"";
    return array(
        "mywhere" => $mywhere,
        "url" => $url,
        "pageurl" => $pageurl
    );
}

/*
 * modReturnURL($a="manage")
 * @return 修改后返回跳转至的页面
 * @a 地址栏中a对应的需要被替换的值
 */
function modReturnURL($a = "manage")
{
    $url = $_SERVER["QUERY_STRING"]; // 地址栏参数
    if (strpos($url, "&")) {
        $urlArr = explode("&", $url);
        $myurl = "";
        foreach ($urlArr as $k => $v) {
            $arr = explode("=", $v);
            if ($arr[0] != "id" && isset($arr[1]) && $arr[1] != "") {
                $myurl .= $v . "&";
            }
        }
        $url = str_replace($a . "_mod", $a, rtrim($myurl, "&"));
    } else {
        $url = "";
    }
    return $url;
}

/*
 * soArr($pageurl,$pi='',$conn='')
 * @return 展示当前搜索条件
 * @pageurl page字符串
 * @mArr 自定义属性搜索条件集
 * @pi 自定义属性 （商品或文档）
 * @conn 数据库链接 （目录表连接资源）
 */
function soArr($pageurl, $mArr = array(), $pi = '', $conn = '')
{
    $tmp_ = explode("&", $pageurl);
    $soArr = array();
    if ($tmp_) {
        $i = 0;
        $fieldnameArr_ = array();
        if ($mArr) {
            foreach ($mArr as $a) {
                $fieldnameArr_[] = $a["fieldname"];
            }
        }
        
        foreach ($tmp_ as $a) {
            $arr_ = explode("=", $a);
            $mykArr_[] = $k_ = $arr_[0];
            $v_ = isset($arr_[1]) ? $arr_[1] : '';
            $mykvArr_[$k_] = $v_;
            $str_ = "";
            if ($k_ != 'm' && $k_ != 'c' && $k_ != 'a' && $k_ != 'page' && $v_ != '' && $v_ != '_') {
                $v_ = urldecode($v_);
                switch ($k_) {
                    case "riqi1":
                        $mytitle = "开始日期";
                        $myv_ = $v_;
                        break;
                    case "riqi2":
                        $mytitle = "截止日期";
                        $myv_ = $v_;
                        break;
                    
                    case "cataid":
                        $mytitle = "所属目录";
                        $where_ = "cataid=$v_";
                        $data_ = $conn->get_one("sort", $where_);
                        $myv_ = $data_["sort"];
                        break;
                    case "w":
                        $mytitle = "搜索关键词";
                        $myv_ = $v_;
                        break;
                    
                    case "ifhot":
                        $mytitle = "是否推荐";
                        $myv_ = $v_ == "0" ? '推荐' : '否';
                        break;
                    case "ifone":
                        $mytitle = "是否头条";
                        $myv_ = $v_ == "0" ? '头条' : '否';
                        break;
                    
                    case "ifhead":
                        $mytitle = "是否置顶";
                        $myv_ = $v_ == "0" ? '置顶' : '否';
                        break;
                    case "ifindex":
                        $mytitle = "是否首页";
                        $myv_ = $v_ == "0" ? '首页' : '否';
                        break;
                    case "ifchecked":
                        $mytitle = "是否审核";
                        $myv_ = $v_ == "0" ? '已审核' : '未审核';
                        break;
                    case "ifhidden":
                        $mytitle = "是否隐藏";
                        $myv_ = $v_ == "0" ? '隐藏' : '显示';
                        break;
                    case "ifip":
                        $mytitle = "绑定IP";
                        $myv_ = $v_ == "0" ? '是' : '否';
                        break;
                    case "ifok":
                        $mytitle = "生效状态";
                        $myv_ = $v_ == "0" ? '有效' : '失效';
                        break;
                    case "gid":
                        $mytitle = "角色选择";
                        $where_ = "id=$v_";
                        $data_ = $conn->get_one("title", $where_);
                        $myv_ = $data_["title"];
                        break;
                    case "sort":
                        $mytitle = "位置";
                        $myv_ = $v_ ;
                        break;
 
                    default:
                        break;
                }
                // 消除自定义属性搜索
                if (! $mArr || ! in_array($k_, $fieldnameArr_)) {
                    $soArr[$i]["mytitle"] = $mytitle;
                    $soArr[$i]["myv"] = $myv_;
                    $soArr[$i]["myk"] = $k_;
                    $i ++;
                }
            }
        }
        
        // 存在自定义属性搜索
        if ($mArr) {
            
            $arr_ = array_intersect($fieldnameArr_, $mykArr_);
            
            if ($arr_) {
                foreach ($arr_ as $a) {
                    $v_ = str_replace($a, "", $mykvArr_[$a]);
                    $where_ = "cataid=$v_";
                    $dataV_ = $conn->get_one("sort", $where_);
                    $where_ = "fieldname='$a' and pi='$pi' and ifm='0'";
                    $dataField_ = $conn->get_one("sort", $where_);
                    $soArr[$i]["mytitle"] = $dataField_["sort"];
                    $soArr[$i]["myv"] = $dataV_["sort"];
                    $soArr[$i]["myk"] = $a;
                    
                    $i ++;
                }
            }
        } // if mArr
    } // iftmp_
    return $soArr;
}


