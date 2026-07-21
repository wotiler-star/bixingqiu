<?php
/**
 * admin.class.php 系统管理员
 * @copyright konecms 2016-2020
 * last update date 2016年3月27日
 */
konecms::load_module_classes("admin_base");
class admin extends admin_base{
    function __construct(){
        parent::__construct();
        $this->conn_admin=konecms::load_model_class("admin"); 
        $this->conn_gteam=konecms::load_model_class("gteam");
    }

    /*
     * 添加管理员
     * 
     */
    function generate(){
        //组选项
        $gteamArr=$this->conn_gteam->select("id,title");

        $ifDialog = true;
        if (isset($_POST["data"])) {
        
            // 1. 账号不允许同名
            $adminname = $_POST["data"]["adminname"];
            $where="adminname='$adminname'";
            $rs = $this->conn_admin->get_one("*", $where);
            $rs ? showmessage(L("添加失败！原因：该账号已经存在"), "?m=admin&c=admin&a=generate") : "";
           
            //2. 获取开关值
            $_POST["data"]["ifip"] = isset($_POST["data"]["ifip"]) ? 0 : 1;

            //3. 加密
            $_POST["data"]["pwd"] = md5($_POST["data"]["pwd"]);
            
            
            
            //4.有效期
            $_POST["data"]["riqi_start"]=$_POST["data"]["riqi_start"]==""?date("Y-m-d"):$_POST["data"]["riqi_start"];
            
            //5. 组名及昵称
            $where="id=".$_POST["data"]["gid"];
            $rs=$this->conn_gteam->get_one("title",$where);
            $gname=$_POST["data"]["gname"]=$rs["title"];
            $_POST["data"]["name"]=$_POST["data"]["name"]==""?$gname:$_POST["data"]["name"];
        
            $this->conn_admin->insert($_POST["data"]);
        
            $this->conn_admin->insert_id() ? showmessage(L("do_ok"), "?c=admin&a=generate") : showmessage(L("do_fail"), "?c=admin&a=generate");
        }
        
        include parent::load_tpl("admin_generate");
    } 

    /*
     * 管理员信息列表
     * 
     */
    function manage(){
         // 1. 初始化
        $where = "1=1"; 
        if(in_array("pwdmod",$this->gteamArr)&&!in_array("adminList",$this->gteamArr)){//修改本人密码
            $where .=" and id=".$_SESSION["ADMINID"];
        }  
        $gteamArr=$this->conn_gteam->select("id,title");

        // 2. 形成条件语句、a链接、page链接
        $c="admin";
        $a="manage";
        $wFieldArr=array("adminname");
        $riqi_field="riqi_login";
        $conn='';
        $dataListArr=dataList($c,$a,$wFieldArr,$riqi_field,$conn);
        $url = $dataListArr["url"];//a链接
        $pageurl = rtrim($dataListArr["pageurl"],"&");//分页字符串中除了page的字段链接
        $mywhere = $dataListArr["mywhere"];//分页字符串中除了page的字段链接
        $where=$where.$mywhere;
        
        // 3. 加载数据  
        $percount = $_SESSION["PERCOUNT"]?$_SESSION["PERCOUNT"]:10;
        $orderby="id desc";
        $cols = "*"; 
        $listArr = $this->conn_admin->i($cols, $where, "id desc", "", 15, "", "",$percount);

        // 4. 当前搜索条件结果展示
        $mArr=array();
        $pi="";
        $conn=$this->conn_gteam;
        $soArr=soArr($pageurl,$mArr,$pi,$conn);
         
        // 5. 分页及统计        
        $amount = $this->conn_admin->count($where); // 总记录数
        $pageCount = ceil($amount / $percount); // 总分页数
        $curpage = isset($_GET["page"]) ? $_GET["page"] : 1;
        $prepage = $curpage == '1' ? 1 : $curpage - 1;
        $nextpage = $curpage == $pageCount ? $pageCount : $curpage + 1;
        
        // 6. 加载模板
        include parent::load_tpl("admin_manage");
    }

    /*
     * 修改管理员信息
     *
     */
    function manage_mod(){
        //组选项
        $gteamArr=$this->conn_gteam->select("id,title"); 
        //账号信息
        ! isset($_GET["id"]) && showmessage(L("错误的ID值"), "?c=admin&a=manage");
        $id= $_GET["id"];
        $where = "id=$id";
        $data=$this->conn_admin->get_one("*",$where);
        $adminname0=$data["adminname"];
        if(isset($_POST["data"])){
            //1. 获取开关值
            $_POST["data"]["ifip"] = isset($_POST["data"]["ifip"]) ? 0 : 1;

          
            //2 组名及昵称
            $where_="id=".$_POST["data"]["gid"];
            $rs=$this->conn_gteam->get_one("title",$where_);
            $gname=$_POST["data"]["gname"]=$rs["title"];
            
            $this->conn_admin->update($_POST["data"],$where); 

            $url=modReturnURL();
            
            $this->conn_admin->affected_rows() ? showmessage(L("do_ok"), "?$url") : showmessage(L("do_fail"), "?$url"); 
        }
         
        include parent::load_tpl("admin_manage_mod");
    }

    /*
     * 修改管理员信息
     *
     */
    function manage_pwd_mod(){
         
        //账号信息
        ! isset($_GET["id"]) && showmessage(L("错误的ID值"), "?c=admin&a=manage");
        $where = "id=" . $_GET["id"];
        $info=$this->conn_admin->get_one("id,pwd,adminname",$where);
    
    
        if(isset($_POST["data"])){
            //1. 加密
            $_POST["data"]["pwd"] = md5($_POST["data"]["pwd"]);
       
            $this->conn_admin->update($_POST["data"],$where);
            $this->conn_admin->affected_rows()?showmessage(L("do_ok"),"?c=admin&a=manage"):showmessage(L("do_fail"),"?c=admin&a=manage");
        }
         
        include parent::load_tpl("admin_manage_mod_pwd");
    }

    /*
     * 添加组信息
     *
     */
    function generate_gteam(){
        if (isset($_POST["title"])) {
            // 1. 组名不允许重复
            $title = $_POST["title"];
            $where="title='$title'";
            $rs = $this->conn_gteam->get_one("*", $where);
            $rs ? showmessage(L("添加失败！原因：组名已经存在"), "?m=admin&c=admin&a=generate_gteam") : "";
             
            //2. 用户组名
            $arr["title"]=$_POST["title"];
            unset($_POST["title"]);
            //3. 权限组
            $right=implode("*",$_POST);
            if($right=="") $right="*";
            $arr["rightset"]=$right;
            $this->conn_gteam->insert($arr);
            $this->conn_gteam->insert_id() ? showmessage(L("do_ok"), "?c=admin&a=generate_gteam") : showmessage(L("do_fail"), "?c=admin&a=generate_gteam");
        }
    
        include parent::load_tpl("admin_generate_gteam");
    }
    
    /*
     * 组管理
     *
     */
    function manage_gteam(){ 
        // 1. 初始化
        $where = "1=1";
        
        $c="admin";
        $a="manage_gteam";
        $dataListArr=dataList($c,$a);
        $url = $dataListArr["url"];//a链接
        $pageurl = rtrim($dataListArr["pageurl"],"&");//分页字符串中除了page的字段链接
        $mywhere = $dataListArr["mywhere"];//分页字符串中除了page的字段链接
        $where=$where.$mywhere;
         
        $percount = $_SESSION["PERCOUNT"]?$_SESSION["PERCOUNT"]:10;
        $cols = "id,title";
        //echo $where;
        $listArr = $this->conn_gteam->i($cols, $where, "id desc", "", 15, "", "",$percount);

        // 4. 当前搜索条件结果展示
        $mArr=array();
        $pi="";
        $conn="";
        $soArr=soArr($pageurl,$mArr,$pi,$conn);
        
        $amount = $this->conn_gteam->count($where); // 总记录数
        $pageCount = ceil($amount / $percount); // 总分页数
        $curpage = isset($_GET["page"]) ? $_GET["page"] : 1;
        $prepage = $curpage == '1' ? 1 : $curpage - 1;
        $nextpage = $curpage == $pageCount ? $pageCount : $curpage + 1;
         
        include parent::load_tpl("admin_manage_gteam");
    }   

    /*
     * 组信息修改
     *
     */
    function manage_gteam_mod(){
    
        //数据信息
        ! isset($_GET["id"]) && showmessage(L("错误的ID值"), "?c=admin&a=manage");
        $id=$_GET["id"];
        $where = "id=$id";
        $data=$this->conn_gteam->get_one("*",$where);
        $title0=$data["title"];
        $rightset=explode("*",$data["rightset"]);
        //print_r($rightset);
        if(isset($_POST["title"])){
    
            // 1. 组名不允许重复
            $title = $_POST["title"];
            if($title0!=$title){
            $where2="title='$title'";
            $rs = $this->conn_gteam->get_one("*", $where2);
            if($rs): 
               showmessage(L("修改失败！原因：组名已经存在"), "?m=admin&c=admin&a=manage_gteam_mod&id=$id");
               exit();
            endif;
            }
            //2. 用户组名
            $arr["title"]=$_POST["title"];
            unset($_POST["title"]);
            //3. 权限组
    
            $right=implode("*",$_POST);
            if($right=="") $right="*";
            $arr["rightset"]=$right;
             
    
            $this->conn_gteam->update($arr,$where);

            $url=modReturnURL("manage_gteam");
            
            $this->conn_admin->affected_rows() ? showmessage(L("do_ok"), "?$url") : showmessage(L("do_fail"), "?$url");
        }
        include parent::load_tpl("admin_gteam_mod");
    }
   
    /*
     * 异步：单个删除
     */
    function ajax_del_gteam()
    {
        $id = $_POST["id"];
        $where = "id=$id";
        $this->conn_gteam->delete($where);
        $arr['success'] = 0;
        echo json_encode($arr);
    }
    /*
     * 异步设置：批量删除
     */
    function ajax_dels_gteam()
    {
        $ids = $_POST["ids"];
        $idsArr = explode(",", $ids);
        foreach ($idsArr as $id) {
            $where = "id=$id";
            $this->conn_gteam->delete($where);
        }
        $arr['success'] = 0;
        echo json_encode($arr);
    }

    /*
     * 异步设置：批量删除
     */
    function ajax_dels()
    {
        $ids = $_POST["ids"];
        $idsArr = explode(",", $ids);
        foreach ($idsArr as $id) {
            $where = "id=$id";
            $this->conn_admin->delete($where);
    
        }
        $arr['success'] = 0;
        echo json_encode($arr);
    }
    
    /*
     * 异步：单个删除
     */
    function ajax_del()
    {
        $id = $_POST["id"];
        $where = "id=$id";
        $this->conn_admin->delete($where);
        $arr['success'] = 0;
        echo json_encode($arr);
    }
    

    /*
     * 异步：删除单个搜索条件
     */
    function ajax_del_so()
    {
        $k = $_POST["k"];
        $pageurl = $_POST["pageurl"];
    
        $tmp_=explode("&",$pageurl);
        if($tmp_){
            $url_="";
            foreach($tmp_ as $a){
                $arr_=explode("=",$a);
                $k_=$arr_[0];
                $v_=isset($arr_[1])?$arr_[1]:'';
                if($k_!=$k){
                    $url_.=$k_."=".$v_."&";
                }
            }
            $url=rtrim($url_,"&");
        }
    
        $arr['url'] = $url_;
        $arr['success'] = 0;
        echo json_encode($arr);
    }

    /*
     * 异步：删除单个搜索条件
     */
    function ajax_del_so_gteam()
    {
        $k = $_POST["k"];
        $pageurl = $_POST["pageurl"];
    
        $tmp_=explode("&",$pageurl);
        if($tmp_){
            $url_="";
            foreach($tmp_ as $a){
                $arr_=explode("=",$a);
                $k_=$arr_[0];
                $v_=isset($arr_[1])?$arr_[1]:'';
                if($k_!=$k){
                    $url_.=$k_."=".$v_."&";
                }
            }
            $url=rtrim($url_,"&");
        }
    
        $arr['url'] = $url_;
        $arr['success'] = 0;
        echo json_encode($arr);
    }
    
    /*
     * 异步设置：有效
     */
     
    function ajax_set_ifok()
    {
        $arr = array(
            "ifok" => $_POST["ifok"]
        );
        $where = "id=" . $_POST["id"];
        $this->conn_admin->update($arr, $where);
    
        $arr['success'] = 0;
        echo json_encode($arr);
    }
    
    
    /*
     * 异步检查目录账号是否可用
     */ 
    function ajax_check_adminname()
    {
       
    
        $adminname=$_POST["adminname"]; 
    
        // 修改目录时如果目录名称没有变化则正常返回
        if (isset($_POST["adminname0"])) {
            $adminname0=$_POST["adminname0"];  
            if($adminname != $adminname0 ){
                $where = "adminname='$adminname' ";
                $rs = $this->conn_admin->get_one("*", $where);
                $arr['success']=$rs ? '1' : '0';
            }else{
                $arr['success']='0';
            }
        }else{
            $where = "adminname='$adminname' ";
            $rs = $this->conn_admin->get_one("*", $where);
            $arr['success']=$rs ? '1' : '0';
        }
        echo json_encode($arr);
    }

    /*
     * 异步检查组名
     */
    function ajax_check_gteam()
    {
    
        $title=$_POST["title"]; 
    
        // 修改目录时如果目录名称没有变化则正常返回
        if (isset($_POST["title0"])) {
            $title0=$_POST["title0"];  
            if($title != $title0 ){
                $where = "title='$title' ";
                $rs = $this->conn_gteam->get_one("*", $where);
                $arr['success']=$rs ? '1' : '0';
            }else{
                $arr['success']='0';
            }
        }else{
            $where = "title='$title' ";
            $rs = $this->conn_gteam->get_one("*", $where);
            $arr['success']=$rs ? '1' : '0';
        }
        echo json_encode($arr);
    }

 
}