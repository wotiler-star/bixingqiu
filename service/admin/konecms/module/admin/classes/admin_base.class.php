<?php
/**
 * admin.class.php
 * @copyright konecms.com
 * @lastmodify 2016-2-26
*/
session_start();
error_reporting("E_ALL");
define("KONE_ADMIN",true);
define("STATIC_PATH",SITE_PATH."konecms"."/"."module"."/"."admin"."/"."template"."/"."static"."/");
define("CACHE_ROOT",WEB_ROOT.DIRECTORY_SEPARATOR."cache".DIRECTORY_SEPARATOR);
class admin_base{
    protected  $adminArr=null;//管理员信息数组
    protected  $rightsetArr=null;//管理员信息数组
    protected  $usualArr=null;//管理员信息数组
	public function __construct(){
		self::check_login(); 
		if(isset($_SESSION["ADMINID"])&&$_SESSION["ADMINID"]){
		    $adminid=isset($_SESSION["ADMINID"])?$_SESSION["ADMINID"]:$_COOKIE["adminidCookie"];
		    //管理员信息
		     $where="id=$adminid";
		    $db=konecms::load_model_class("admin");
		    $adminArr=$this->adminArr=$db->get_one("*",$where); 

		    //管理员权限
		    $conn_gteam=konecms::load_model_class("gteam");
		    $gteam_data = $conn_gteam->get_one("*","id=".$adminArr['gid']);
		    $rightsetstr = $gteam_data['rightset'];
		    if($rightsetstr!="*"){
		        $rightsetArr = strpos($rightsetstr,"*")?explode("*",$rightsetstr):array($rightsetstr);
		    }else{
		        $rightsetArr = array("*");
		    }
		    $this->gteamArr=$rightsetArr;
		}

		 
	}
	function load_tpl($tpl,$m="admin"){
	    if(file_exists(KONECMS_ROOT."module".DIRECTORY_SEPARATOR.$m.DIRECTORY_SEPARATOR."template".DIRECTORY_SEPARATOR.$tpl.".html")){
	        return KONECMS_ROOT."module".DIRECTORY_SEPARATOR.$m.DIRECTORY_SEPARATOR."template".DIRECTORY_SEPARATOR.$tpl.".html";
	    }else{
	        exit("load template file :".DIRECTORY_SEPARATOR.$tpl .".html"." failed .");
	    }
	}
	private static function check_login(){ 
	    if(ROUTE_A=="login"){
	        return true;
	    }else{
	        //时间过期后自动跳出
           if(!isset($_SESSION["SESSIONTIME"])){ 
               $conn_usual=konecms::load_model_class("usual");
               $usualArr=$conn_usual->get_one("percount,sessiontime","1=1");
               if($usualArr){
                   $_SESSION["SESSIONTIME"]=$usualArr["sessiontime"];
                   $_SESSION["PERCOUNT"]=$usualArr["percount"];
               }else{ 
                   $_SESSION["SESSIONTIME"]=60*30;
                   $_SESSION["PERCOUNT"]=10;
               }
           }
	       if(isset($_SESSION['ACCESSTIME'])&&(time()-$_SESSION['ACCESSTIME'])>$_SESSION["SESSIONTIME"]){
	            if(isset($_SESSION["ADMINID"])) unset($_SESSION["ADMINID"]);
	            if(isset($_SESSION["ADMINNAME"])) unset($_SESSION["ADMINNAME"]);
	            if(isset($_SESSION["SESSIONTIME"])) unset($_SESSION["SESSIONTIME"]);
	            if(isset($_SESSION["ACCESSTIME"])) unset($_SESSION["ACCESSTIME"]);
	            if(isset($_SESSION["PERCOUNT"])) unset($_SESSION["PERCOUNT"]);
	            //echo "<script language=javascript>$(function() {parent.location.reload();})</script>";
	            showmessage(L("login_please"), "?m=admin&a=login",3);
	        }
	        //刷新后重新计时
	        $_SESSION['ACCESSTIME'] = time();
	         
	        
	        if(!isset($_SESSION["ADMINID"])){
	        //    echo "<script language=javascript>$(function() {parent.location.reload();})</script>";
	             showmessage(L("login_please"), "?m=admin&a=login",3);
	        } 
	    }
	    return true;
	}
	/*
	 * cataidSelect($conn,$parentid,$fieldname,$cs,$level)
	 * 给定任一目录ID,以SELLECT表单列出它所有的子孙目录（高级搜索）
	 * @return html select下option选项
	 * @conn 目录表连接资源
	 * @parentid 当前cataid
	 * @fieldname 目录字段名称
	 * @cs cata:查找目录；shux查找属性
	 * @level层级
	 */
	function cataidSelect($conn, $parentid,$fieldname='',$cs='shux',$level='1')
	{
	    $cataidArr = array(); 
	    $where_ = "parentid=$parentid";
	    $where_.=$cs=='shux'?"":" and ifm='1'";
	    $data_ = $conn->select("cataid,sort", $where_);
	    $str = '';
	    $html="";
	    if ($data_) {
	        foreach ($data_ as $a) {
	            $cataid_=$a["cataid"];
	            $sort_=$a["sort"];

	            if ($cataid_ == $parentid) {
	                $level = 0;
	            } else {
	                $flag = "|";
	            }
	            $flag = $flag . str_repeat("——", $level);
	            
	            $html.='<option value='.$fieldname. $cataid_ . ">";
	            
	            $html.=$flag."$sort_"."</option>";
	            $html .= $this->cataidSelect($conn, $a["cataid"],$fieldname,$cs,$level+1);
	        }
	    }
	    return $html;
	}
	

	/*
	 * cataidSelect($conn,$parentid,$fieldname,$level)
	 * 给定任一目录ID,以CHECKBOX表单列出它所有的子孙目录（目录属性）
	 * @return html select下option选项
	 * @conn 目录表连接资源
	 * @parentid 当前cataid
	 * @fieldname 目录字段名称
	 * @cs cata:查找目录；shux查找属性
	 * @data 数据（修改时用于加载当前值）
	 * @level层级
	 */
	function cataidCheckbox($conn, $parentid,$fieldname,$cs='shux',$data=array(),$level='1')
	{ 
	    $where_ = "parentid=$parentid";
	    $where_.=$cs=='shux'?"":" and ifm='1'";
	    $data_ = $conn->select("cataid,sort,parentid", $where_);
	    $str = '';
	    $html="";
	    if ($data_) {
	        foreach ($data_ as $a) {
	            $cataid_=$a["cataid"];
	            $sort_=$a["sort"];
	            $parentid_=$a["parentid"];
	            $myArr_=array();
	            if($data){
	                $myArr_=explode(",",rtrim($data[$fieldname],","));
	            }
	            
	            $flag =   str_repeat("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;", $level);
	            $v_=$fieldname.$cataid_;
	            $html.=$flag. "<input type=checkbox ";
	            if($myArr_){
	                if(in_array($v_,$myArr_)) $html.=" checked ";
	            }
	            $html.=" name=$fieldname"."[] value='".$v_."'> $sort_ &nbsp; &nbsp;";
	            $html.="<br>";
	            $html.= $this->cataidCheckbox($conn, $a["cataid"],$fieldname,$cs,$data,$level+1) ;
	            
	        }
	    }
	    return $html;
	}
	

	/* select_catalog(二维数组，父目录ID,层级);
	 * 列出目录
	 */
	
	function select_catalog($data, $pId=0, $level = 1)
	{
	    $html = '';
	    foreach ($data as $k => $v) {
	        $flag = "";
	        if ($v['parentid'] == $pId) {
	            if ($pId == 0) {
	                $level = 0;
	            } else {
	                $flag = "|";
	            }
	            $flag = $flag . str_repeat("——", $level);
	            $html .= "<option value=".$v['cataid'];
	            //if($v['cataid']==74) $html .=" selected";
	            $html.=">$flag" . $v['sort'];
	            $html = $html . "</option>";
	            $html .= $this->select_catalog($data, $v['cataid'], $level + 1);
	        }
	    }
	    return $html;
	}
 
	/*
	 * listCatalog
	 * 目录列表
	 */
	function listCatalog($id = 0,$c="catalog",$a="manage_mod") {
	    global $str;

	    $this->conn_catalog=konecms::load_model_class("catalog");
	    $sql = "select cataid,parentid,sort,ifm,fieldname from catalog_tb where parentid= $id order by cataid asc ";
	    $result = $this->conn_catalog->query($sql);//查询子类
	    if($result &&$result->num_rows>0){//如果有子类
	        while ($row = $result->fetch_assoc()) { //循环记录集
	
	            $str .= "<li><div>" . $row['sort'];
	            if(!$row["ifm"]) $str.="[属性 ".$row["fieldname"]."]";
	            $str .="&nbsp;[cataid=".$row["cataid"] ."]";
	            $str.="&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp; <a href=?c=$c&a=$a&cataid=".$row["cataid"].">修改</a>";
	            $str.="&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp; <a href=javascript:; class=del delid=".$row["cataid"].">删除</a>";
	            $str.="</div>"; //构建字符串
	            $str .= '<ul>';
	            $this->listCatalog($row['cataid']); //调用get_str()，将记录集中的id参数传入函数中，继续查询下级
	            $str .= '</ul>';
	            $str.="</li>";
	
	        }//while
	    }
	    return $str;
	}
	
	
}




