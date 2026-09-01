<?php
/**
 * h.class.php 会员列表
 * @copyright konecms 2016-2020
 * last update date 2016年3月27日
 */
konecms::load_module_classes("admin_base");
// 文件级频率限制（登录 / 注册 / 发短信防刷）
require_once __DIR__ . '/../../../source/lib_ratelimit.php';

class h extends admin_base
{
    /*
     * 图形验证码是否开启（BXQ_CAPTCHA=0 时关闭，部署好带验证码的前端后再开启）
     */
    private function captchaOn()
    {
        $v = getenv('BXQ_CAPTCHA');
        return ($v === false) ? false : (strtolower($v) !== '0' && $v !== '0');
    }

    /*
     * 校验图形验证码（yzm.php 写入 $_SESSION['randNum']），校验成功后一次性消费，防重放。
     * 关闭时直接放行。
     */
    private function verifyCaptcha($yzm, $consume = true)
    {
        if (!$this->captchaOn()) {
            return true;
        }
        $yzm = strtolower(trim((string) $yzm));
        if (!isset($_SESSION['randNum']) || $yzm === '' || $yzm !== $_SESSION['randNum']) {
            return false;
        }
        // 消费后重置，防止同一验证码被重复使用（$consume=false 时仅校验不重置，
        // 用于「获取短信验证码」这一步，避免消耗掉注册时还要用的同一验证码）
        if ($consume) {
            $_SESSION['randNum'] = bin2hex(random_bytes(4));
        }
        return true;
    }

    /*
     * 短信验证是否强制开启（BXQ_SMS_ENABLE=1）
     */
    private function smsOn()
    {
        $v = getenv('BXQ_SMS_ENABLE');
        return ($v !== false && (strtolower($v) === '1' || $v === '1'));
    }

    /*
     * [安全修复] 当前登录会员 id。
     * 一律以服务端 session 为准，绝不采信客户端传来的 hid —— 否则任何人
     * 只要改一个数字就能读写他人账号（原 ajax_setInfo / delmycarehid 即如此）。
     * 未登录返回 0。
     */
    private function currentHid()
    {
        return isset($_SESSION["HID"]) ? (int) $_SESSION["HID"] : 0;
    }

    /*
     * [安全修复] 写接口统一鉴权入口。
     * 本类构造函数里的 checklogin 早先被注释掉（见 __construct），
     * 导致所有 ajax 写接口对匿名访问敞开。这里按方法粒度补回。
     * 未登录时输出 JSON 并终止请求。
     */
    private function requireLogin()
    {
        $hid = $this->currentHid();
        if ($hid <= 0) {
            if (!headers_sent()) {
                header('Content-Type: application/json; charset=utf-8');
            }
            echo json_encode(array("success" => 401, "msg" => "请先登录"), JSON_UNESCAPED_UNICODE);
            exit();
        }
        return $hid;
    }

    /*
     * [安全修复] 会员资料可写字段白名单。
     * 原实现把整个 $_POST["data"] 直接丢给 update()，属于批量赋值漏洞：
     * 会员可顺带改写 pwd（改密码/提权）、hname（顶号）、ifauthor（自封专栏作家）、
     * ifchecked 等敏感列。这里只放行真正属于「个人资料」的字段。
     */
    private function profileWhitelist($data)
    {
        if (!is_array($data)) {
            return array();
        }
        $allow = array(
            'name', 'picdir', 'short', 'sex', 'birthday', 'address',
            'qq', 'weixin', 'weibo', 'email', 'company', 'job', 'website',
        );
        $out = array();
        foreach ($allow as $k) {
            if (isset($data[$k])) {
                $out[$k] = $data[$k];
            }
        }
        return $out;
    }

    /*
     * [安全修复] 对外输出的会员字段白名单。
     * 原 ajax_getInfo 用 get_one("*") 后直接 json_encode，
     * 把 pwd 哈希、手机号等一并吐给任意匿名请求方，属于敏感信息泄露。
     */
    private function publicMemberFields($row)
    {
        if (!is_array($row)) {
            return array();
        }
        $allow = array(
            'id', 'hname', 'name', 'picdir', 'short', 'sex', 'riqi',
            'ifauthor', 'company', 'job', 'website', 'weibo', 'weixin',
        );
        $out = array();
        foreach ($allow as $k) {
            if (isset($row[$k])) {
                $out[$k] = $row[$k];
            }
        }
        return $out;
    }


    function __construct()
    { 
        parent::__construct();
          
        // if (ROUTE_A != "login"&&ROUTE_A != "loginoff" && ROUTE_A != "rsg" && ROUTE_A != "ajax_login" && ROUTE_A != "ajax_rsg"  && ROUTE_A != "callpwd" 
        //     && ROUTE_A != "ajax_callpwd" && ROUTE_A != "ajax_check_hname" && ROUTE_A != "ajax_sendMessage") :
        //     ob_end_flush();
        // parent::checklogin()|| header("location:?c=h&a=login");
         
        // endif;

        $this->conn_h = konecms::load_model_class("h");
        $this->conn_mycarehid = konecms::load_model_class("mycarehid");
        $this->conn_h_info_geren = konecms::load_model_class("h_info_geren");
        $this->conn_h_info_qiye = konecms::load_model_class("h_info_qiye");
        $this->conn_h_info_meiti = konecms::load_model_class("h_info_meiti");
        $this->conn_i = konecms::load_model_class("i");  
        $this->conn_feedback = konecms::load_model_class("feedback");  
        $this->conn_catalog = konecms::load_model_class("catalog"); 
        $this->conn_favorate = konecms::load_model_class("favorate"); 
     
        if ($this->checklogin()) {
            $hid = (int) $_SESSION["HID"]; // [安全加固] 裸插值 id=$hid，整型强转
            $where = "id=$hid";
            $this->hArr = $this->conn_h->get_one("*", $where);
            
            $this->num_feedback=$this->conn_feedback->count("hid=$hid");
            $this->num_favorate=$this->conn_favorate->count("hid=$hid");
            $this->num_i=$this->conn_i->count("hid=$hid");
        }
    }
    //获取账号信息
    function ajax_getInfo(){ 
        /* [安全修复] hid 原样拼进 SQL；且 get_one("*") 会把 pwd 哈希/手机号吐给匿名请求方 */
        $hid = isset($_GET["hid"]) ? (int) $_GET["hid"] : 0;
        if ($hid <= 0) {
            echo json_encode(array());
            return;
        }
        $where = "id=$hid";
        $row = $this->conn_h->get_one("*", $where);
        if (!$row) {
            echo json_encode(array());
            return;
        }
        $hArr = $this->publicMemberFields($row);

        $where="hid=$hid";
        $hArr["num_content"]=$this->conn_i->count($where);//文章数量
        $hArr["num_favorate"]=$this->conn_favorate->count($where);//收藏数量
        $hArr["num_feedback"]=$this->conn_feedback->count($where);//评论数量
        
        $myArr=array($hArr);
        echo json_encode($myArr);
    }
    //设置账号信息
    function ajax_setInfo(){  
        /*
         * [安全修复] 原实现以客户端 $_POST["data"]["hid"] 定位记录并整表批量赋值：
         * 改一个 hid 就能覆写任意会员资料，连 pwd 都能一起改 —— 等于任意账号接管。
         * 现改为：必须登录 + 只改自己 + 只放行资料类字段。
         */
        $hid = $this->requireLogin();
        $where = "id=$hid";

        $data = $this->profileWhitelist(isset($_POST["data"]) ? $_POST["data"] : array());
        if (isset($data["picdir"]) && $data["picdir"] !== '') {
            $data["picdir"] = base64_image_content($data["picdir"], "konecms_ups/k/image", "/service/");
        }
        if (!$data) {
            echo json_encode(array("success" => 1, "msg" => "没有可更新的字段"), JSON_UNESCAPED_UNICODE);
            return;
        }

        $this->conn_h->update($data, $where);
        $myArr["success"]=$this->conn_h->affected_rows() ?  0: 1; 
        echo json_encode($myArr);
    }

    function ajax_del_myi(){
        /*
         * [安全修复] 原实现无鉴权、无归属校验，且 id 直拼 SQL：
         * 匿名 POST data[id]=1 or 1=1 可一次删光全站文章。
         * 现改为：必须登录 + 只能删自己的稿件 + id 强制整型。
         */
        $hid = $this->requireLogin();
        $id = isset($_POST["data"]["id"]) ? (int) $_POST["data"]["id"] : 0;
        if ($id <= 0) {
            echo json_encode(array("success" => 1, "msg" => "参数错误"), JSON_UNESCAPED_UNICODE);
            return;
        }
        $this->conn_i->delete("id=$id and hid=$hid");
        echo json_encode(array("success" => $this->conn_i->affected_rows() ? 0 : 1));
    }
    /*
     * 用户资料
     */
    function init()
    {
        $this->curA="h"; 
        $hid = $this->currentHid();
        $where = "id=$hid";
        if ($hid > 0 && isset($_POST["data"])) {
            /* [安全修复] 同 ajax_setInfo：批量赋值收窄为资料类字段白名单 */
            $data = $this->profileWhitelist($_POST["data"]);
            if ($data) {
                $this->conn_h->update($data, $where);
                $this->conn_h->affected_rows() ? showmessage(L("do_ok"), "?c=h") : showmessage(L("do_fail"), "?c=h");
            }
        }
         
        include parent::load_tpl("h/h_home");
    }   
    
    /*
     * 系统通知
     */
    function mynotice()
    {
        $this->curA="n";

        //定制cataid
        $sort_=array("系统消息");
        $k_=array("notice");
        $i=0;
        $cataidArr=array();
        foreach($sort_ as $a){
            $where_="sort='$a'";
            $arr_=$this->conn_catalog->get_one("cataid",$where_);
            $cataidArr[$k_[$i]]=$arr_["cataid"];
            $i++;
        }
        $mycataid=$cataidArr["notice"];
        
        $v_ = "cataid" . $mycataid;
        
       $where = " find_in_set( '$v_',cataid) and ifhidden='1' ";
       
            $cols="id,cataid,source,short,riqi,hitnum,title,dataurl_fname,picdir_list,dataurl";
            $data = $this->conn_i->i($cols, $where, "orderid desc", "", 25, "","",12);
         echo json_encode($data);
       // include parent::load_tpl("h/h_mynotice");
    }

    //我的关注
    function mycarehid(){
        /* [安全修复] hid 强制整型，避免 ?hid=1 or 1=1 拖库 */
        $hid = isset($_GET["hid"]) ? (int) $_GET["hid"] : 0;
        if ($hid <= 0) {
            echo json_encode(array());
            return;
        }
        $data=$this->conn_mycarehid->i("*","myhid=$hid", "riqi desc");
        if($data){
            $i=0;
            foreach($data as $a){
                $carehid=(int)$a["carehid"];
                $data_=$this->conn_h->get_one("*","id=$carehid");
                /* [修复] 原代码未判空，被关注账号已注销时 PHP8 下访问 null 数组键会告警 */
                $data[$i]["name"]=$data_ ? $data_["name"] : '';
                $data[$i]["picdir"]=$data_ ? $data_["picdir"] : '';
                $data[$i]["short"]=$data_ ? $data_["short"] : '';
                $i++;
            }
        }
        echo json_encode($data);
    }
    //取消关注
    function delmycarehid(){
        /*
         * [安全修复] 原实现用客户端 hid 定位并直拼 SQL，
         * 可代替他人取消关注，也可注入。现改为必须登录 + 只操作自己的关注关系。
         */
        $hid = $this->requireLogin();
        $carehid = isset($_POST["data"]["mycarehid"]) ? (int) $_POST["data"]["mycarehid"] : 0;
        if ($carehid > 0) {
            $this->conn_mycarehid->delete("myhid=$hid and carehid=$carehid");
            echo json_encode(array("success" => $this->conn_mycarehid->affected_rows() ? 0 : 1));
            return;
        }
        echo json_encode(array("success" => 1, "msg" => "参数错误"), JSON_UNESCAPED_UNICODE);
    }
    /*
     * 我的文章
     */
    function myi()
    {
        $this->curA="i";
    
        //定制cataid
        $sort_=array("作家专栏");
        $k_=array("only");
        $i=0;
        $cataidArr=array();
        foreach($sort_ as $a){
            $where_="sort='$a'";
            $arr_=$this->conn_catalog->get_one("cataid",$where_);
            $cataidArr[$k_[$i]]=$arr_["cataid"];
            $i++;
        }
        $mycataid=$cataidArr["only"];
        
        $where="parentid=$mycataid";
        $subArr=$this->conn_catalog->select("cataid,sort",$where);
        
        //是否为专栏作家
        $where_="ifauthor='0' and  id=".intval($_GET["hid"]);
        $ifauthor=$this->conn_h->get_one("*",$where_);
        
        //是否有文章
        $where = "ifhidden='1' and hid= ".intval($_GET["hid"]);   
        if(isset($_POST["data"]["status"])){
            $v=$_POST["data"]["status"];
            switch($v){
                case '1'://审核中
                    $where.=" and ifchecked='1'";
                    break;
                case '2'://已通过
                    $where .=" and ifchecked='0'";
                    break; 
                case '3'://未通过
                    $where.=" and ifchecked='2'";
                    break; 
                case '4'://草稿
                    $where.=" and ifpublic='1'";
                    break; 
            }
        }

        if(isset($_POST["data"]["cataid"])&&$_POST["data"]["cataid"]!="10"){
            /*
             * [FATAL 修复] 原代码调用 $this->conn->escape()，但 $this->conn 只在
             * Content.class.php 里被赋值，h 类从未定义该属性 —— PHP 8 下会抛
             * "Call to a member function escape() on null" 致命错误，
             * 即「我的文章」按栏目筛选必然 500。
             * cataid 本身是数字栏目号，强制整型即可，既修崩溃又消除注入。
             */
            $cataid = (int) $_POST["data"]["cataid"];
            $v_ = "cataid" . $cataid;
            $where .=  " and find_in_set( '$v_',cataid) ";
        }
        //echo $where;
        $cols="id,cataid,source,short,riqi,hitnum,title,dataurl_fname,picdir_list,dataurl";
        $data = $this->conn_i->i($cols, $where, "id desc", "",10);
        
        echo json_encode($data);
        //include parent::load_tpl("h/h_myi");
    }

//加载更多
    public function getMore(){
    
        
        /* [安全修复]
         * 1. 原来 hid 取自 $_GET["hid"] —— 这是「我的文章」管理列表，含审核中/
         *    未通过/草稿，任何人改一个数字就能翻看别人的未公开稿件（IDOR）。
         *    现一律以 session 为准，未登录直接 401。
         * 2. id / cataid 来自未过滤的 POST 数组且裸插值进 WHERE，现整型强转。 */
        $hid = $this->requireLogin();
        $id = isset($_POST["data"]["id"]) ? (int) $_POST["data"]["id"] : 0;
        if ($id <= 0) { $id = PHP_INT_MAX; }
         
        $where=" ifhidden='1' and id<$id and hid=$hid";

        if(isset($_POST["data"]["status"])){
            $v=$_POST["data"]["status"];
            switch($v){
                case '1'://审核中
                    $where.=" and ifchecked='1'";
                    break;
                case '2'://已通过
                    $where .=" and ifchecked='0'";
                    break;
                case '3'://未通过
                    $where.=" and ifchecked='2'";
                    break;
                case '4'://草稿
                    $where.=" and ifpublic='1'";
                    break;
            }
        }

        if(isset($_POST["data"]["cataid"])&&$_POST["data"]["cataid"]!="10"){
            $cataid=(int)$_POST["data"]["cataid"]; // [安全修复] 拼进 find_in_set 字面量，整型强转
            if ($cataid > 0) {
                $v_ = "cataid" . $cataid;
                $where .=  " and find_in_set( '$v_',cataid) ";
            }
        }
        
        
        $cols="id,cataid,source,short,riqi,hitnum,title,dataurl_fname,picdir_list,dataurl";
        $dataArr=$this->conn_i->select($cols,$where,"id desc",10);
         
        echo json_encode($dataArr);
    }
    
    /*
     * 发布文章
     */
    function geni(){
        $this->curA="i";
        
        //定制cataid
        // $sort_=array("专栏");
        // $k_=array("only");
        // $i=0;
        // $cataidArr=array();
        // foreach($sort_ as $a){
        //     $where_="sort='$a'";
        //     $arr_=$this->conn_catalog->get_one("cataid",$where_);
        //     $cataidArr[$k_[$i]]=$arr_["cataid"];
        //     $i++;
        // }
        // $mycataid=$cataidArr["only"];
        
        // $where="parentid=$mycataid";
        // $subArr=$this->conn_catalog->select("cataid,sort",$where);
        
        if(isset($_POST["data"])){
           
           $_POST["data"]["riqi"]=date("Y-m-d H:i:s");
           $_POST["data"]["hid"]=$_POST["data"]["hid"];
           $_POST["data"]["cataid"]="cataid".$_POST["data"]["cataid"];
           $_POST["data"]["ifchecked"]=1;
           $_POST["data"]["ifpublic"]=isset($_GET["cg"])?'1':'0';
           $_POST["data"]["uploader"]=$_POST["data"]["hname"];
           $_POST["data"]["source"]=$this->hArr["name"]?$this->hArr["name"]:"专栏作家";
           $_POST["data"]["picdir_list"]=base64_image_content($_POST["data"]["picdir_list"],"konecms_ups/k/image","/service/");
           unset($_POST["data"]["hname"]);
           $id=$this->conn_i->insert($_POST["data"]);
           $arr["success"]=$id ? 0: 1; 
           echo json_encode($arr);
        }
        //include parent::load_tpl("h/h_geni");
    }
    /*
     * 申请专栏预处理：申请认证按钮
     */
    function certInit(){
        //是否为专栏作家
        $where_=" id=".$_SESSION["HID"];
        $data=$this->conn_h->get_one("ifauthor",$where_);
        include parent::load_tpl("h/h_certInit");
    }
    /*
     * 申请专栏第一步：选择类别
     */
    function certStep1(){

        
        include parent::load_tpl("h/h_certStep1");
    }
    /*
     * 申请专栏第二步：填写表单
     */
    function certStep2(){
        /* [安全修复]
         * 1. 原来 hid 取自 $_POST["data"]["hid"] —— 提交时改一个数字就能把
         *    **别人**的账号置为 ifauthor=2（申请中/专栏作家），属越权写。
         *    现一律以 session HID 为准，并强制覆盖 data.hid。
         * 2. sort0 不在 0/1/2 时 $conn / $sort 都未定义，PHP 8 直接抛
         *    Error: Call to a member function insert() on null（500 白屏）。
         *    现先校验枚举。 */
        $hid = $this->requireLogin();
        $getsort = isset($_POST["data"]["sort0"]) ? (int) $_POST["data"]["sort0"] : -1;
        if (!in_array($getsort, array(0, 1, 2), true)) {
            if (!headers_sent()) { header('Content-Type: application/json; charset=utf-8'); }
            echo json_encode(array("success" => 1, "msg" => "请选择申请类别"), JSON_UNESCAPED_UNICODE);
            return;
        }
        switch($getsort){
            case 0:
                $sort="geren";
                $conn=$this->conn_h_info_geren;
                $_POST["data"]["picdir1"]=base64_image_content($_POST["data"]["picdir1"],"konecms_ups/k/image","/service/");
                $_POST["data"]["picdir2"]=base64_image_content($_POST["data"]["picdir2"],"konecms_ups/k/image","/service/");
                break;
            case 1:
                $sort="qiye";
                $conn=$this->conn_h_info_qiye;
                $_POST["data"]["picdir1"]=base64_image_content($_POST["data"]["picdir1"],"konecms_ups/k/image","/service/");
                $_POST["data"]["picdir2"]=base64_image_content($_POST["data"]["picdir2"],"konecms_ups/k/image","/service/");
                $_POST["data"]["picdir3"]=base64_image_content($_POST["data"]["picdir3"],"konecms_ups/k/image","/service/");
                break;
            case 2:
                $sort="meiti";
                $conn=$this->conn_h_info_meiti;
                $_POST["data"]["picdir1"]=base64_image_content($_POST["data"]["picdir1"],"konecms_ups/k/image","/service/");
                $_POST["data"]["picdir2"]=base64_image_content($_POST["data"]["picdir2"],"konecms_ups/k/image","/service/");
                $_POST["data"]["picdir3"]=base64_image_content($_POST["data"]["picdir3"],"konecms_ups/k/image","/service/");
                break;
        }
        
        unset($_POST["data"]["sort0"]);
        if (isset($_POST["data"]) && is_array($_POST["data"])) {
            // [安全修复] 强制以 session 会员为申请主体，忽略客户端传来的 hid
            $_POST["data"]["hid"] = $hid;
            unset($_POST["data"]["ifok"], $_POST["data"]["ifchecked"], $_POST["data"]["id"]);

            $id=$conn->insert($_POST["data"]);
            if($id){
                $this->conn_h->update(array("ifauthor"=>2,"sort"=>$sort),"id=$hid");
            }
           
        $myArr["success"]=$id ?  0: 1; 
        if (!headers_sent()) { header('Content-Type: application/json; charset=utf-8'); }
        echo json_encode($myArr); 
        }
         
        
        //include parent::load_tpl("h/h_info_$sort");
    }

    /*
     * 申请专栏第三步：等待审核通过
     */
    function certStep3(){
        include parent::load_tpl("h/h_certStep3");
    }

    /*
     * 详情页
     */
    function mynotice_detail()
    {

        $this->curA="n";
        // 获取信息数据
        ! isset($_GET["id"]) && showmessage(L("错误的ID值"), "?a=init");
        $id = (int) $_GET["id"]; // [安全修复] 裸插值 id=$id，数字上下文可绕过引号转义
        $where = "id=$id";
        $conn_i = konecms::load_model_class("i");
        $data = $conn_i->get_one("*", $where);
        
        include self::load_tpl( "h/h_mynotice_detail");
    }

    /*
     * 我的评论
     */
    function myfeedback()
    {
        $this->curA="f";
      
        $where = "hid= ".intval($_GET["hid"]);        
        $cols="*";
        $data = $this->conn_feedback->i($cols, $where, "riqi desc", "", 25, "","",12);
        echo json_encode($data);
       // include parent::load_tpl("h/h_myfeedback");
    }

    /*
     * 我的搜藏
     */
    function myfavorate()
    {
        $this->curA="fav"; 
        $where = "hid=". intval($_GET["hid"]); 
        $cols="*";
        $data = $this->conn_favorate->i($cols, $where, "riqi desc", "", 25, "","",12);
          
        echo json_encode($data);
      //  include parent::load_tpl("h/h_myfavorate");
    }
    
    function ajax_del_favorate(){
        /*
         * [安全修复] 原实现无鉴权、无归属校验且 id 直拼 SQL：
         * 匿名 POST data[favid]=1 or 1=1 可删光全站收藏记录。
         */
        $hid = $this->requireLogin();
        $id = isset($_POST["data"]["favid"]) ? (int) $_POST["data"]["favid"] : 0;
        if ($id <= 0) {
            echo json_encode(array("success" => 1, "msg" => "参数错误"), JSON_UNESCAPED_UNICODE);
            return;
        }
        $this->conn_favorate->delete("id=$id and hid=$hid");
        echo json_encode(array("success" => $this->conn_favorate->affected_rows() ? 0 : 1));
    }

 
    /*
     * 登录
     */
    function login()
    { 
        include parent::load_tpl("h/h_login");
    }
 
    /*
     * 注册
     */
    function rsg()
    { 
        include parent::load_tpl("h/h_rsg");
    }

    /*
     * 修改密码
     */
    function pwd2()
    {
        $this->curA="p";
        $hid = (int)$_SESSION["HID"];
        if (isset($_POST["data"])) {
            $oldPwd = (string)$_POST["data"]["pwd"];
            $newPwd = (string)$_POST["data"]["pwd2"];

            // 取出存储的哈希，用 password_verify 校验原密码（兼容 md5/bcrypt，并避免原代码的 SQL 注入）
            $row = $this->conn_h->get_one("pwd", "id=$hid");
            $stored = $row ? $row["pwd"] : '';
            if (strpos($stored, '$2y$') === 0 || strpos($stored, '$2a$') === 0) {
                $ok = password_verify($oldPwd, $stored);
            } else {
                $ok = (strcasecmp($stored, md5($oldPwd)) === 0);
            }
            !$ok && showmessage(L("原密码输入有误 ！"), "?c=h&a=pwd2");

            // 设置新密码（bcrypt）
            $_POST["data"]["pwd"] = password_hash($newPwd, PASSWORD_BCRYPT);
            unset($_POST["data"]["pwd2"]);
            unset($_POST["data"]["pwd3"]);
            $this->conn_h->update($_POST["data"], "id=$hid");
            $this->conn_h->affected_rows() ? showmessage(L("do_ok"), "?c=h&a=pwd2") : showmessage(L("do_fail"), "?c=h&a=pwd2");
        }

        include parent::load_tpl("h/h_pwd2");
    }

    /*
     * 找回密码
     */
    function callpwd()
    {
        include parent::load_tpl("h/h_callpwd");
    }

    /*
     * 安全退出
     */
    function loginoff()
    { 
        unset($_SESSION["HNAME"]);
        unset($_SESSION["HID"]);
        header("location:?c=h&a=login");
    }

    /*
     * 设置头像
     */
    function ajax_set_photo(){
        /*
         * [BUG 修复] 原实现 return json_encode(...)，但调度器 build_cms::init() 用
         * call_user_func() 调用动作方法并丢弃返回值，响应体因此恒为空 —— 前端拿不到
         * 任何结果，头像设置看起来永远失败。必须 echo。
         * [安全修复] 同时补上登录校验，未登录时 $_SESSION["HID"] 为空会写成 id= 空串。
         */
        $hid = $this->requireLogin();
        $picdir = isset($_POST["picdir"]) ? (string) $_POST["picdir"] : '';
        if ($picdir !== '') {
            $this->conn_h->update(array("picdir" => $picdir), "id=$hid");
            $arr["success"] = 0;
        } else {
            $arr["success"] = 1;
        }
        echo json_encode($arr);
    }
   
      
    /*
     * ajax登录
     */
    function ajax_login()
    {
        if (isset($_POST["data"]["hname"])) {

            // 频率限制：同一 IP 10 分钟内最多 20 次登录尝试
            if (!bxq_rl_check('login', 20, 600)) {
                echo json_encode(array("success" => 9, "msg" => "尝试过于频繁，请稍后再试"));
                return;
            }
            bxq_rl_hit('login', 600);

            // 图形验证码
            if (!$this->verifyCaptcha(isset($_POST["yzm"]) ? $_POST["yzm"] : '')) {
                echo json_encode(array("success" => 3, "msg" => "图形验证码错误"));
                return;
            }

            $hname = $this->conn_h->escape($_POST["data"]["hname"]);
            $rawPwd = isset($_POST["data"]["pwd"]) ? (string)$_POST["data"]["pwd"] : '';

            // 仅按账号查询，密码不参与 WHERE（兼容易被注入的写法，并兼容新旧哈希）
            $data = $this->conn_h->get_one("*", "hname='" . $hname . "' and ifok='0'");

            if ($data) {
                $stored = $data["pwd"];
                $ok = false;
                if (strpos($stored, '$2y$') === 0 || strpos($stored, '$2a$') === 0) {
                    // 新 bcrypt 哈希
                    $ok = password_verify($rawPwd, $stored);
                } else {
                    // 存量 md5 哈希（兼容迁移）
                    $ok = (strcasecmp($stored, md5($rawPwd)) === 0);
                    // 登录成功后透明重哈希为 bcrypt，下次起走强校验
                    if ($ok) {
                        $newHash = password_hash($rawPwd, PASSWORD_BCRYPT);
                        $this->conn_h->update(array("pwd" => $newHash), "id=" . (int)$data["id"]);
                    }
                }

                if ($ok) {
                    // 会话固定防护：登录成功后重置会话 ID
                    session_regenerate_id(true);

                    // 设置登录信息
                    $_SESSION["HNAME"]   = $data["hname"];
                    $_SESSION["NICKNAME"] = $data["name"];
                    $_SESSION["HID"]      = $data["id"];

                    // 更新登录记录
                    $arr = array(
                        "riqi_lastlogin" => date("Y-m-d H:i:s"),
                        "login_ip" => getIPaddress()
                    );
                    $this->conn_h->update($arr, "id=" . (int)$data["id"]);
                    $myArr["success"] = 0;
                    $myArr["hid"] = $data["id"];
                    $myArr["hname"] = $data["hname"];
                    $myArr["nickname"] = $data["name"];
                    echo json_encode($myArr);
                    return;
                }
                $myArr["success"] = 1; // 密码错误
            } else {
                $myArr["success"] = 2; // 账号错误
            }
            echo json_encode($myArr);
        }
    }
    
    //检查用户名（返回 1=已存在 0=可用，便于前端判断）
    function ajax_check_hname($hname){
    
        $hname = $this->conn_h->escape($hname);
        $mywhere = "hname='" . $hname . "'";
        $data = $this->conn_h->get_one("*", $mywhere);
        echo $data ? '1' : '0';
    }
    /*
     * 修改密码
     */
    function ajax_pwd2()
    {
         
        $hid = (int)$_POST["data"]["hid"];
        $pwd = password_hash((string)$_POST["data"]["pwd"], PASSWORD_BCRYPT);
                $where = "id=$hid";
                $data = $this->conn_h->get_one("*", $where);
                if (! $data) {
                    $myArr["msg"] = "修改密码失败 ！该账号不存在。";
                    $myArr["success"] = 1;
                } else {
                    
                    $arr = array(
                        "pwd" => $pwd
                    );
                    
                    $this->conn_h->update($arr, $where);
                    $myArr["success"] = 0;
                }
       
            echo json_encode($myArr);
       
    }

    /*
     * 注册
     */
    function ajax_rsg()
    {
        // 频率限制：同一 IP 10 分钟内最多 10 次注册尝试
        if (!bxq_rl_check('rsg', 10, 600)) {
            echo json_encode(array("success" => 9, "msg" => "注册过于频繁，请稍后再试"));
            return;
        }

        // 图形验证码
        if (!$this->verifyCaptcha(isset($_POST["yzm"]) ? $_POST["yzm"] : '')) {
            echo json_encode(array("success" => 3, "msg" => "图形验证码错误"));
            return;
        }

        $hname = $this->conn_h->escape($_POST["hname"]);

        // 服务端手机号校验（前端已校验，后端兜底）
        if (!preg_match('/^1[3-9]\d{9}$/', $hname)) {
            echo json_encode(array("success" => 1, "msg" => "手机号格式不正确"));
            return;
        }
        $rawPwd = isset($_POST["pwd"]) ? (string)$_POST["pwd"] : '';
        if (!preg_match('/^[\w_@#$%&*\-]{8,24}$/', $rawPwd)) {
            echo json_encode(array("success" => 1, "msg" => "密码需为 8-24 位字母、数字或常见符号"));
            return;
        }

        // 短信验证码（BXQ_SMS_ENABLE=1 时强制）
        if ($this->smsOn()) {
            $msg = isset($_POST["msg"]) ? trim((string)$_POST["msg"]) : '';
            if (!isset($_SESSION["MESSAGE"]) || $msg === '' || $msg !== (string)$_SESSION["MESSAGE"]) {
                echo json_encode(array("success" => 4, "msg" => "短信验证码错误"));
                return;
            }
            unset($_SESSION["MESSAGE"]);
        }

        $mywhere = "hname='" . $hname . "'";
        $data = $this->conn_h->get_one("*", $mywhere);
        if ($data) {
            echo json_encode(array("success" => 1, "msg" => "该手机号已被注册"));
            return;
        }

        bxq_rl_hit('rsg', 600);

        // 字段白名单写入：杜绝批量赋值越权（防止伪造 ifok/ifauthor/balance 等字段）
        $ins = array(
            "hname"  => $hname,
            "pwd"    => password_hash($rawPwd, PASSWORD_BCRYPT),
            "riqi"   => date("Y-m-d H:i:s"),
            "ip"     => getIPaddress(),
            "phone"  => $hname,
            "mobile" => $hname,
            "name"   => "会员" . date('mydhis')
        );
        $this->conn_h->insert($ins);
        $hid = $this->conn_h->insert_id();
        echo json_encode(array("success" => 0, "msg" => "注册成功", "hid" => $hid));
    }

    /*
     * 发送短信验证码（注册 / 找回密码共用）
     * 受图形验证码保护，防短信轰炸；BXQ_SMS_DRIVER=dev 时返回 dev_code 便于联调。
     */
    function ajax_send_sms()
    {
        // 图形验证码保护（不消费，避免消耗掉注册时还要用的同一验证码）
        if (!$this->verifyCaptcha(isset($_POST["yzm"]) ? $_POST["yzm"] : '', false)) {
            echo json_encode(array("success" => 1, "msg" => "图形验证码错误"));
            return;
        }
        $hname = $this->conn_h->escape(isset($_POST["hname"]) ? $_POST["hname"] : '');
        if (!preg_match('/^1[3-9]\d{9}$/', $hname)) {
            echo json_encode(array("success" => 1, "msg" => "手机号格式不正确"));
            return;
        }
        // 同一手机号 60 秒内只能发一次
        if (!bxq_rl_check('sms_' . $hname, 1, 60)) {
            echo json_encode(array("success" => 1, "msg" => "验证码发送过于频繁，请 60 秒后重试"));
            return;
        }
        $code = (string) mt_rand(100000, 999999);
        $_SESSION["MESSAGE"] = $code;
        $_SESSION["MESSAGE_TIME"] = time();
        bxq_rl_hit('sms_' . $hname, 60);

        require_once __DIR__ . '/../../../source/sms/SmsSender.php';
        $r = SmsSender::send($hname, $code);
        $out = array("success" => 0, "msg" => "验证码已发送");
        if (!empty($r["dev_code"])) {
            $out["dev_code"] = $r["dev_code"]; // 仅 dev 模式返回，便于联调
        }
        echo json_encode($out);
    }

    /*
     * 找回密码
     */
    function ajax_callpwd()
    {
        if (isset($_POST["data"]) && isset($_SESSION["MESSAGE"])) {
            // 图形验证码
            if (!$this->verifyCaptcha(isset($_POST["yzm"]) ? $_POST["yzm"] : '')) {
                echo json_encode(array("success" => 1, "msg" => "图形验证码错误"));
                return;
            }
            if ($_POST["data"]["msg"] != $_SESSION["MESSAGE"]) {
                echo json_encode(array("success" => 1, "msg" => "手机验证码输入有误"));
                return;
            }
            $hname = $this->conn_h->escape($_POST["data"]["hname"]);
            unset($_POST["data"]["msg"]);
            $where = "hname='" . $hname . "'";
            $mypwd = password_hash((string)$_POST["data"]["pwd"], PASSWORD_BCRYPT);
            $arr = array(
                "pwd" => $mypwd
            );
            
            $this->conn_h->update($arr, $where);
            unset($_SESSION["MESSAGE"]);
            
            echo json_encode(array("success" => 0, "msg" => "密码已重置"));
        } else {
            echo json_encode(array("success" => 1, "msg" => "请先获取手机验证码"));
        }
    } 
    
    

}

?>