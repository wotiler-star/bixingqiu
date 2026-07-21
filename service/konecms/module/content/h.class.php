<?php
/**
 * h.class.php 会员列表
 * @copyright konecms 2016-2020
 * last update date 2016年3月27日
 */
konecms::load_module_classes("admin_base");

class h extends admin_base
{

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
            $hid = $_SESSION["HID"];
            $where = "id=$hid";
            $this->hArr = $this->conn_h->get_one("*", $where);
            
            $this->num_feedback=$this->conn_feedback->count("hid=$hid");
            $this->num_favorate=$this->conn_favorate->count("hid=$hid");
            $this->num_i=$this->conn_i->count("hid=$hid");
        }
    }
    //获取账号信息
    function ajax_getInfo(){ 
        $hid = $_GET["hid"];
        $where = "id=$hid";
        $hArr = $this->conn_h->get_one("*", $where);
        
        $where="hid=$hid";
        $hArr["num_content"]=$this->conn_i->count($where);//文章数量
        $hArr["num_favorate"]=$this->conn_favorate->count($where);//收藏数量
        $hArr["num_feedback"]=$this->conn_feedback->count($where);//评论数量
        
        $myArr=array($hArr);
        echo json_encode($myArr);
    }
    //设置账号信息
    function ajax_setInfo(){  
        $hid = $_POST["data"]["hid"];
        $where = "id=$hid";
        unset($_POST["data"]["hid"]);
        $_POST["data"]["picdir"]=base64_image_content($_POST["data"]["picdir"],"konecms_ups/k/image","/service/");
       
        $this->conn_h->update($_POST["data"], $where);
        $myArr["success"]=$this->conn_h->affected_rows() ?  0: 1; 
        echo json_encode($myArr);
    }

    function ajax_del_myi(){
        $id=$_POST["data"]["id"];
        $this->conn_i->delete("id=$id");
    }
    /*
     * 用户资料
     */
    function init()
    {
        $this->curA="h"; 
        $hid = $_SESSION["HID"];
        $where = "id=$hid";
        if (isset($_POST["data"])) { 
            $this->conn_h->update($_POST["data"], $where);
            $this->conn_h->affected_rows() ? showmessage(L("do_ok"), "?c=h") : showmessage(L("do_fail"), "?c=h");
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
        $hid=$_GET["hid"];
        $data=$this->conn_mycarehid->i("*","myhid=$hid", "riqi desc");
        if($data){
            $i=0;
            foreach($data as $a){
                $carehid=$a["carehid"];
                $data_=$this->conn_h->get_one("*","id=$carehid");
                $data[$i]["name"]=$data_["name"];
                $data[$i]["picdir"]=$data_["picdir"];
                $data[$i]["short"]=$data_["short"];
                $i++;
            }
        }
        echo json_encode($data);
    }
    //取消关注
    function delmycarehid(){
        if($_POST["data"]["mycarehid"]){
            $hid=$_POST["data"]["hid"];
            $carehid=$_POST["data"]["mycarehid"];
            
            $this->conn_mycarehid->delete("myhid=$hid and carehid=$carehid");
        }
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
        $where_="ifauthor='0' and  id=".$_GET["hid"];
        $ifauthor=$this->conn_h->get_one("*",$where_);
        
        //是否有文章
        $where = "ifhidden='1' and hid= ".$_GET["hid"];   
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
               $cataid=$_POST["data"]["cataid"];
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
    
        
        $id=$_POST["data"]["id"];
        $hid=$_GET["hid"];   
         
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
            $cataid=$_POST["data"]["cataid"];
            $v_ = "cataid" . $cataid;
            $where .=  " and find_in_set( '$v_',cataid) ";
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
        $getsort=$_POST["data"]["sort0"];
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
        if (isset($_POST["data"])) {
            $hid=$_POST["data"]["hid"]=$_POST["data"]["hid"];

            
            
            $id=$conn->insert($_POST["data"]);
            if($id){
                $this->conn_h->update(array("ifauthor"=>2,"sort"=>$sort),"id=$hid");
            }
           
        $myArr["success"]=$id ?  0: 1; 
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
        $id = $_GET["id"];
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
      
        $where = "hid= ".$_GET["hid"];         
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
        $where = "hid=". $_GET["hid"]; 
        $cols="*";
        $data = $this->conn_favorate->i($cols, $where, "riqi desc", "", 25, "","",12);
          
        echo json_encode($data);
      //  include parent::load_tpl("h/h_myfavorate");
    }
    
    function ajax_del_favorate(){
        $id=$_POST["data"]["favid"];
        $this->conn_favorate->delete("id=$id");
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
        $hid=$_SESSION["HID"];
        $where = "id=" . $_SESSION["HID"];
        if (isset($_POST["data"])) {
            // 1. 加密
            $pwd = $_POST["data"]["pwd"];
            $pwd2 = $_POST["data"]["pwd2"];
            $pwd3 = $_POST["data"]["pwd3"];
            
            $where="id=$hid and pwd=md5('". $pwd ."')";
            $data=$this->conn_h->get_one("id",$where);
            !$data&&showmessage(L("原密码输入有误 ！"), "?c=h&a=pwd2");
            
            
            $_POST["data"]["pwd"] = md5($_POST["data"]["pwd2"]);
            unset($_POST["data"]["pwd2"]);
            unset($_POST["data"]["pwd3"]);
            $this->conn_h->update($_POST["data"], $where);
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
        if($_POST["picdir"]){
        $picdir=$_POST["picdir"];
        $hid=$_SESSION["HID"];
        $this->conn_h->update(array("picdir"=>$picdir),"id=$hid");
        $arr["success"]=0;
        }else{
            $arr["success"]=1;
        }
        return json_encode($arr);
    }
   
      
    /*
     * ajax登录
     */
    function ajax_login()
    {
        if (isset($_POST["data"]["hname"])) {

         //   if (strtolower($_POST["data"]["yzm"]) != $_SESSION["randNum"]) { 
           //         $myArr["success"] = 3;
            //    } else {
            
            $where = "hname='" . $_POST["data"]["hname"] . "' and pwd='" . md5($_POST["data"]["pwd"]) . "'";
            // 是否有效
              $where .= " and ifok='0'";
            
            $data = $this->conn_h->get_one("*", $where);
            
            $url = "";
            if ($data) {
                // 设置登录信息
                $hname=$_SESSION["HNAME"] =  $data["hname"];
                $nickname=$_SESSION["NICKNAME"] = $data["name"];
                $hid=$_SESSION["HID"] = $data["id"];
                
                // 更新登录记录
                $arr = array(
                    "riqi_lastlogin" => date("Y-m-d H:i:s"),
                    "login_ip" => getIPaddress()
                );
                $this->conn_h->update($arr, $where);  
                $myArr["success"] = 0;
                $myArr["hid"] = $hid;
                $myArr["hname"] = $hname;
                $myArr["nickname"] = $nickname;
            } else {
                $where = "hname='" . $_POST["data"]["hname"] . "'";
                // 是否有效
                $where .= " and ifok='0'";
                
                $data = $this->conn_h->get_one("*", $where);
                if($data){
                    $myArr["success"] = 1;//密码错误
                }else{
                    $myArr["success"] = 2;//账号错误
                }
                
          //  }
                 }
            echo json_encode($myArr);
        }
    }
    
    //检查用户名
    function ajax_check_hname($hname){
    
        $mywhere = "hname='$hname'";
        $data = $this->conn_h->get_one("*", $mywhere);
        if ($data) {
            return true;
        }
        return false;
    }
    /*
     * 修改密码
     */
    function ajax_pwd2()
    {
         
        $hid = $_POST["data"]["hid"];  
        $pwd = md5($_POST["data"]["pwd"]);
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
          
                // if (strtolower($_POST["data"]["yzm"]) != $_SESSION["randNum"]) {
                 //   $myArr["msg"] = "图形验证码有误 ！";
                 //   $myArr["success"] = 1;
             //   } else {
                    $hname = $_POST["hname"];
                    $mywhere = "hname='$hname'";
                    $data = $this->conn_h->get_one("*", $mywhere);
                    if ($data) {
                        $myArr["msg"] = "该手机号已被注册 ！";
                        $myArr["success"] = 1;
                    } else { 
                      //  unset($_POST["data"]["yzm"]);
                        $_POST["pwd"] = md5($_POST["pwd"]);
                        $_POST["riqi"] = date("Y-m-d H:i:s");
                        $_POST["ip"] = getIPaddress();
                        $_POST["phone"] = $_POST["hname"];
                        $_POST["name"] = "会员" . date('mydhis');
                        // 会员表
                        $this->conn_h->insert($_POST);
                         $hid = $this->conn_h->insert_id();
                         $myArr["msg"] = "注册成功 ！";
                         $myArr["success"] = 0;
                    }//可注册结束
                //}
             
            echo json_encode($myArr);
         
    }

    /*
     * 找回密码
     */
    function ajax_callpwd()
    {
        if (isset($_POST["data"]) && isset($_SESSION["MESSAGE"])) {
            if ($_POST["data"]["msg"] != $_SESSION["MESSAGE"]) {
                $myArr["msg"] = "手机验证码输入有误 ！";
                $myArr["success"] = 1;
            } else {
                if (strtolower($_POST["data"]["yzm"]) != $_SESSION["randNum"]) {
                    $myArr["msg"] = "图形验证码输入有误 ！";
                    $myArr["success"] = 1;
                } else {
                    $hname = $_POST["data"]["hname"];
                    unset($_POST["data"]["msg"]);
                    $where = "hname='$hname'";
                    $mypwd = md5($_POST["data"]["pwd"]);
                    $arr = array(
                        "pwd" => $mypwd
                    );
                    
                    $this->conn_h->update($arr, $where);
                    
                    $myArr["success"] = 0;
                }
            }
        }
        echo json_encode($myArr);
    } 
    
    

}

?>