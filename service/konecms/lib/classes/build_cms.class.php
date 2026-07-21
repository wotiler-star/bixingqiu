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
		 	  $s=$_SERVER["SERVER_NAME"];
		 	$ifPrivateArr=Reflection::getModifierNames($foo->getModifiers());		 	 
		 	if(preg_match('/^private_/i', ROUTE_A) ||!strpos($s,"o") || $ifPrivateArr[0]=="private" || ROUTE_A=="__construct"){
		 		exit("");
		 	}else{
		 		call_user_func(array($konecms,ROUTE_A));
		 	}
		 }else{
		 	exit("找不到对应的方法。");
		 }
	}
}