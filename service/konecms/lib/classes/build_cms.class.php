<?php
/**
 * application.class.php
 * @copyright konecms.com
 * @lastmodify 2016年2月26日
*/
class build_cms{
	function __construct(){
		$route=konecms::load_lib_class("route");
		define("ROUTE_M",$route->route_m());
		define("ROUTE_C",$route->route_c());
		define("ROUTE_A",$route->route_a());
	    self::init();	
	}
	private static function init(){
	     
		 $konecms=konecms::load_module_class(ROUTE_C,ROUTE_M); 
		 if(method_exists($konecms, ROUTE_A)){
		 	$foo = new ReflectionMethod(ROUTE_C, ROUTE_A);
		 	  // 用 HTTP_HOST（随客户端 Host 头变化）而非 SERVER_NAME（=服务器绑定地址，不随域名变化），
		 	  // 否则绑定 IP 部署时 SERVER_NAME 为 IP，原“含字母 o”的校验必然失败。
		 	  $s = isset($_SERVER["HTTP_HOST"]) ? $_SERVER["HTTP_HOST"] : $_SERVER["SERVER_NAME"];
		 	$ifPrivateArr=Reflection::getModifierNames($foo->getModifiers());
		 	// 原代码用 !strpos($s,"o") 要求 SERVER_NAME 必须包含字母 o，导致无法部署到其它主机。
		 	// 改为：仅当设置了 BXQ_ALLOWED_HOST 时才校验主机名，未设置则放行任意主机（便于部署到任意 IP/域名）。
		 	// 注意 SERVER_NAME 可能带端口（如 localhost:8092），比较前先去掉端口只留主机名。
		 	$hostOnly = preg_replace('/:\d+$/', '', $s);
		 	$allowedHost = getenv('BXQ_ALLOWED_HOST');
		 	$hostBlocked = ($allowedHost !== false && $allowedHost !== '' && $hostOnly !== $allowedHost);
		 	if(preg_match('/^private_/i', ROUTE_A) || $hostBlocked || $ifPrivateArr[0]=="private" || ROUTE_A=="__construct"){
		 		exit("");
		 	}else{
		 		call_user_func(array($konecms,ROUTE_A));
		 	}
		 }else{
		 	exit("找不到对应的方法。");
		 }
	}
}