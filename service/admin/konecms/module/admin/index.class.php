<?php
/**
 * @copyright konecms.com
 * @lastmodify 2016年2月26日
*/
konecms::load_module_classes("admin_base");
class index extends admin_base{
    
    /*
     * 外层框架
     */
    
	function init(){
	    $adminArr=$this->adminArr;
	    
	    include self::load_tpl("welcome");
	}
	
	/*
	 * 登录
	 */
	
	function login(){
	    if(isset($_POST["name"])){
	        strtolower($_POST["yzm"])!=$_SESSION["randNum"]?showmessage(L('wrong_yzm'),"?m=admin&a=login"):"";
	        $db=konecms::load_model_class("admin");
	        $adminname = $db->escape($_POST["name"]);
	        $rawPwd = (string)$_POST["pwd"];
	        // 仅按账号查询，密码不参与 WHERE
	        $data=$db->get_one("*","adminname='".$adminname."' and ifok='0'");

	        if($data){
	            $stored = $data["pwd"];
	            if (strpos($stored, '$2y$') === 0 || strpos($stored, '$2a$') === 0) {
	                $ok = password_verify($rawPwd, $stored);
	            } else {
	                // 存量 md5 兼容，登录成功后透明重哈希
	                $ok = (strcasecmp($stored, md5($rawPwd)) === 0);
	                if ($ok) {
	                    $db->update(array("pwd" => password_hash($rawPwd, PASSWORD_BCRYPT)), "id=".(int)$data["id"]);
	                }
	            }
	            if (!$ok) {
	                showmessage(L('login_fail'),"?m=admin&a=login");
	            }
	             $ifip=$data["ifip"];
	             $ip=$data["ip"];
	             !$ifip&&$ip!=getIPaddress()?showmessage(L('登录失败！原因：您的账号指定了登录IP'),"?m=admin&a=login"):"";//绑定IP验证
	            $riqi_start=$data["riqi_start"];
	            $riqi_expire=$data["riqi_expire"];
	            $riqi_start&&$riqi_start>date("Y-m-d")?showmessage(L('登录失败！原因：您的账号暂未生效。生效日期为：'.$riqi_start),"?m=admin&a=login"):"";
	            $riqi_expire&&$riqi_expire!="0000-00-00"&&$riqi_expire<date("Y-m-d")?showmessage(L('登录失败！原因：您的账号已过期。过期日期为：'.$riqi_expire),"?m=admin&a=login"):"";
	            $_SESSION["ADMINID"]=$data["id"];
	            $_SESSION["ADMINNAME"]=$data["adminname"];
	            $_SESSION['ACCESSTIME'] = time();//进入系统时间

	            //设置登录信息
	             $arr=array("riqi_login"=>date("Y-m-d H:i:s"),"login_ip"=>getIPaddress());
	             $db->update($arr,"id=".(int)$data["id"]);
	             showmessage(L('login_success'),"?m=admin");
	        }else{
	             showmessage(L('login_fail'),"?m=admin&a=login");
	        }
	    }else{
	         
	        include parent::load_tpl("login");
	    }
	}
	/*
	 * 登录成功后的欢迎页
	 */
	function sysinfo(){
	    include parent::load_tpl("index");
	} 
    /*
     * 异步检查原密码是否正确以及修改密码
     */
	function ajax_check_pwd()
    {
	    $adminid=(int)$_SESSION["ADMINID"];
        $db=konecms::load_model_class("admin");
        // 取出存储的哈希，用 password_verify 校验原密码（兼容 md5/bcrypt）
        $row = $db->get_one("pwd", "id=$adminid");
        $stored = $row ? $row["pwd"] : '';
        if (strpos($stored, '$2y$') === 0 || strpos($stored, '$2a$') === 0) {
            $ok = password_verify((string)$_POST["pwd"], $stored);
        } else {
            $ok = (strcasecmp($stored, md5((string)$_POST["pwd"])) === 0);
        }
        if($ok){
            $newpwd=(string)$_POST["newpwd"];
            $arr=array("pwd"=>password_hash($newpwd, PASSWORD_BCRYPT));
            $where="id=$adminid";
            $db->update($arr,$where);
            $arr["msg"]=$db->affected_rows()?0:1;//0修改密码成功，1修改密码失败
        }else{
            $arr["msg"]=2;//原密码输入有误
        }
        echo json_encode($arr);
    }
     
	function loginoff(){ 
	            if(isset($_SESSION["ADMINID"])) unset($_SESSION["ADMINID"]);
	            if(isset($_SESSION["ADMINNAME"])) unset($_SESSION["ADMINNAME"]);
	            if(isset($_SESSION["NAME"])) unset($_SESSION["NAME"]);
	            if(isset($_SESSION["SESSIONTIME"])) unset($_SESSION["SESSIONTIME"]);
	            if(isset($_SESSION["ACCESSTIME"])) unset($_SESSION["ACCESSTIME"]);
	            if(isset($_SESSION["PERCOUNT"])) unset($_SESSION["PERCOUNT"]); 
		 header("location:index.php?a=login");
	}
	 
	
	


}





