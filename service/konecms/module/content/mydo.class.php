<?php
/**
 * @copyright konecms.com
 * @lastmodify 2016年2月26日
*/
konecms::load_module_classes("admin_base");
class mydo extends admin_base{
    
	public function __construct(){
	     parent::__construct();  
        
        $this->conn_i=konecms::load_model_class("i"); 
        $this->conn_catalog=konecms::load_model_class("catalog"); 
	    //广告
	    $this->conn_ad=konecms::load_model_class("ad");
	    $this->conn_h=konecms::load_model_class("h");
	    $this->conn_site=konecms::load_model_class("site");
	    
	}
	/*
	 * [P0 安全修复] mydo 是 2016 年一次性数据迁移工具（把老站绝对路径批量
	 * 改写为相对路径）。但 admin_base 的构造函数**不做任何登录校验**，
	 * 于是匿名访问 /service/index.php?c=mydo 就会执行 init()：
	 * 对 i_tb / ad_tb / catalog_tb / site_tb 做全表 SELECT + 逐行 UPDATE。
	 * 后果：①任何人（含爬虫）一次请求即可触发数百次写操作，MyISAM 表级锁
	 * 会让整站卡顿甚至 502；②路径被反复改写导致全站图片链接错乱。
	 * genBase64() 更是任意文件读取（file_get_contents + base64 直接回显）。
	 *
	 * 该模块现在已无任何用途，统一按管理员鉴权拦截：非后台管理员一律 403。
	 */
	private function requireAdmin(){
	    if (!isset($_SESSION["ADMINID"]) || !$_SESSION["ADMINID"]) {
	        if (!headers_sent()) {
	            header('HTTP/1.1 403 Forbidden');
	            header('Content-Type: text/plain; charset=utf-8');
	        }
	        exit('403 Forbidden');
	    }
	    return (int)$_SESSION["ADMINID"];
	}
	function init(){
	    $this->requireAdmin();
	    $v0="/kuangit/web/k3_bixignqiu/service/konecms_ups/";
	    $v1="../service/konecms_ups/";
	    $this->chgdir($v0,$v1); //用于将本地地址转化为线上地址
	  // $this->genBase64("base64/konecms.txt");
	}
	function genBase64($file){
	    $this->requireAdmin();
	    echo base64_encode(file_get_contents($file));
	}

	/*
	 * 用于将本地地址转化为线上地址
	 */
	private function chgdir($v0,$v1=""){
	   //文档
	    $data=$this->conn_i->select("*","1=1");
	    
	    foreach($data as $a){
	        $picdir_index=str_replace($v0,$v1,$a["picdir_index"]);
	        $picdir_list=str_replace($v0,$v1,$a["picdir_list"]);
	        $picdir=str_replace($v0,$v1,$a["picdir"]);
	        $dataurl=str_replace($v0,$v1,$a["dataurl"]);
	        $dataurl_fname=str_replace($v0,"",$a["dataurl_fname"]);
	      //  $cnt=str_replace($v0,"",$a["cnt"]);
	        $id=$a["id"];
	        $arr_=array(
	            "picdir_index"=>$picdir_index,
	            "picdir_list"=>$picdir_list,
	            "picdir"=>$picdir,
	            "dataurl"=>$picdir,
	            "dataurl_fname"=>$dataurl_fname,
	           // "cnt"=>$cnt,
	            "picdir"=>$picdir 
	        );
	        $this->conn_i->update($arr_,"id=$id");
	    }

	    

	    //广告
	    $data=$this->conn_ad->select("*","1=1");
	   
	    foreach($data as $a){ 
	        $picdir_wap=str_replace($v0,$v1,$a["picdir_wap"]);
	        $picdir=str_replace($v0,$v1,$a["picdir"]); 
	        $id=$a["id"];
	        $arr_=array(
	            "picdir_wap"=>$picdir_wap, 
	            "picdir"=>$picdir
	        );
	        $this->conn_ad->update($arr_,"id=$id");
	    }

	    //目录
	    $data=$this->conn_catalog->select("*","1=1");
	    
	    foreach($data as $a){ 
	        $picdir=str_replace($v0,$v1,$a["picdir"]);
	        $id=$a["cataid"];
	        $arr_=array( 
	            "picdir"=>$picdir
	        );
	        $this->conn_catalog->update($arr_,"cataid=$id");
	    }

	    //导航
	    $data=$this->conn_site->select("*","1=1");
	     
	    foreach($data as $a){
	        $picdir=str_replace($v0,$v1,$a["picdir"]);
	        $id=$a["id"];
	        $arr_=array(
	            "picdir"=>$picdir
	        );
	        $this->conn_site->update($arr_,"id=$id");
	    }
	    //会员
	    $data=$this->conn_h->select("*","1=1");
	    
	    foreach($data as $a){ 
	        $picdir=str_replace($v0,$v1,$a["picdir"]);
	        $id=$a["id"];
	        $arr_=array( 
	            "picdir"=>$picdir
	        );
	        $this->conn_h->update($arr_,"id=$id");
	    }
	    
	    
	}
	
	
	
	
}