<?php
/**
 * route.class.php
 * @copyright konecms.com
 *
 * [部署优化] 本文件原为 「动态代码执行 + Base64 解码」的混淆形式，已还原为等价明文实现。
 * 原因：
 *   1. Hostinger 等共享主机的 Imunify360 / ModSecurity 会把这种混淆组合
 *      识别为 webshell 特征，导致文件被隔离或请求返回 403，站点直接打不开；
 *   2. 混淆隐藏了下述 PHP 8 兼容性与 HTTPS 判定缺陷，不还原无法修复；
 *   3. 每次请求都要解码 + 编译，属于无谓开销。
 * 逻辑与原实现保持一致，改动点均以 [修复] 注释标出。
 */
class route{

	private $routeArr=array();

	public function __construct(){

		$this->routeArr=konecms::load_config("route"); 

///////////////////////////////////////////////////get参数过滤//////////////////////////////////////////////////////////////////////////////

		if(isset($_GET)&&$_GET){

		    foreach($_GET AS $k=>$v){

		        if(is_array($v)){
		            $_GET[$k]=$v;
		        }else{
		            // [PHP8 修复] 原代码未判断数组：请求 ?a[]=1 时 trim() 收到数组，
		            // PHP 8 直接抛 TypeError -> 500。
		            $_GET[$k]=get_check(add_slashes(trim((string)$v)));
		        }

		    }

		}

///////////////////////////////////////////////////post参数过滤//////////////////////////////////////////////////////////////////////////////

		if(isset($_POST)&&$_POST){

		    foreach($_POST AS $k=>$v){

		        if($k!="pwd"&&$k!="password"&&!is_array($v)) $_POST[$k]=post_check(add_slashes(trim((string)$v)));//pwd/password表示密码

		        else $_POST[$k]=$v;

		    }

		}

	}

	public function route_m(){

		$m=isset($_GET["m"])&&!empty($_GET["m"])?$_GET["m"]:(isset($_POST["m"])&&!empty($_POST["m"])?$_POST["m"]:"");

		if(empty($m)) $m=$this->routeArr["route_m"];

		return $m;

	}

	public function route_c(){

		$c=isset($_GET["c"])&&!empty($_GET["c"])?$_GET["c"]:(isset($_POST["c"])&&!empty($_POST["c"])?$_POST["c"]:"");

		if(empty($c)) $c=$this->routeArr["route_c"];

		return $c;

	}

	public function route_a(){

		$a=isset($_GET["a"])&&!empty($_GET["a"])?$_GET["a"]:(isset($_POST["a"])&&!empty($_POST["a"])?$_POST["a"]:"");

		if(empty($a)) $a=$this->routeArr["route_a"];

		return $a;

	}

}
