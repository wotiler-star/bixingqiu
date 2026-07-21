<?php
/**
 * admin.class.php
 * @copyright konecms.com
 * @lastmodify 2016-2-26
*/ 
error_reporting("E_ALL");
session_start();
define("KONE_ADMIN",true);
define("STATIC_PATH",SITE_PATH."konecms"."/"."module"."/"."content"."/"."template"."/"."static"."/");
define("CACHE_ROOT",WEB_ROOT.DIRECTORY_SEPARATOR."cache".DIRECTORY_SEPARATOR);
class admin_base{
    
	public function __construct(){
	    
	   //配置
	    $this->conn_usual=konecms::load_model_class("usual"); 
	    $this->usualArr=$this->conn_usual->get_one("*");
	    
	    //主导航
	    $this->conn_catalog=konecms::load_model_class("catalog"); 
	    $where_="parentid='0' and ifnav='0' and ifm='1'";
	    $data_=$this->conn_catalog->select("cataid,sort,ensort,sort_mobile,pi",$where_,"orderid asc");
	     
	    if($data_){
	        $navArr=array();
	        $i=0;
	        foreach($data_ as $a){
	            $pcataid_=$navArr[$i]["pcataid"]=$a["cataid"];
	            $navArr[$i]["psort"]=$a["sort"];
	            $navArr[$i]["ensort"]=$a["ensort"];
	            $navArr[$i]["psort_mobile"]=$a["sort_mobile"];
	            $navArr[$i]["pi"]=$a["pi"];
	            $where="parentid='$pcataid_' and ifm='1'  ";
	            $navArr["subnav"][$pcataid_]=$this->conn_catalog->select("cataid,parentid,sort",$where,"orderid asc" );
	            $i++;
	        }
	        $this->navArr=$navArr;
	        //echo "<pre>";
	        // print_r($this->navArr);
	    }
	     
	    define("IS_PHONE",is_mobile_request()?'phone/':'');
	}
	   
	/*
	 * 判断是否已会员登录
	 */
	 
	public function checklogin(){
	    if(isset($_SESSION["HID"])&&$_SESSION["HID"]){
	        return true;
	    }else{
	         $_SESSION["SESSID"]=session_id();
	    }
	    return false;
	}
	
 
	
	/*
	 * 返回购物车数量
	 */
	public function get_gwc_num(){

	    $conn_gwc = konecms::load_model_class("gwc");
	    $conn_gwc_tmp = konecms::load_model_class("gwc_tmp"); // 临时购物车
	    if($this->checklogin()){ 
	        $where = "ifover='1' and hid=" . $_SESSION["HID"];
	        $num = $conn_gwc->count($where);
	    }else{
	        $sessid=session_id();
	        $where = "sessid='$sessid'";
	        $num = $conn_gwc_tmp->count($where);
	    }
	    return $num;
	}
	/*
	 * 加载视图
	 */
	function load_tpl($tpl,$m="content"){
	    if(file_exists(KONECMS_ROOT."module".DIRECTORY_SEPARATOR.$m.DIRECTORY_SEPARATOR."template".DIRECTORY_SEPARATOR.$tpl.".html")){
	        return KONECMS_ROOT."module".DIRECTORY_SEPARATOR.$m.DIRECTORY_SEPARATOR."template".DIRECTORY_SEPARATOR.$tpl.".html";
	    }else{
	        exit("load template file :".DIRECTORY_SEPARATOR.$tpl .".html"." failed .");
	    }
	}
	 

  
	
}




