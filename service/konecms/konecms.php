<?php
/**
 * konecms.php
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
 
header('Content-Type:text/html;charset=utf-8'); 
//系统验证
define("KONECMS_SAFE",TRUE);
//系统核心文件根目录
define("KONECMS_ROOT",dirname(__FILE__).DIRECTORY_SEPARATOR);
//网站物理根路径
!defined("WEB_ROOT") && define("WEB_ROOT",KONECMS_ROOT."..".DIRECTORY_SEPARATOR); 
//当前访问的主机名
define('DOMAIN', (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : ''));
//带HTTP(s)站点根路径
/**
 * [修复] 原实现仅凭 SERVER_PORT == 443 判定 HTTPS。
 * Hostinger 使用 LiteSpeed + 前置反向代理/CDN 终结 SSL，回源到 PHP 时
 * SERVER_PORT 常为 80，导致 https 页面里生成 http:// 绝对地址，
 * 浏览器按混合内容(Mixed Content)拦截 —— 表现为图片/接口全挂、页面白屏。
 */
if (!function_exists('kone_is_https')) {
	function kone_is_https() {
		if (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') return TRUE;
		if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
			$p = explode(',', $_SERVER['HTTP_X_FORWARDED_PROTO']);
			if (strtolower(trim($p[0])) === 'https') return TRUE;
		}
		if (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && strtolower($_SERVER['HTTP_X_FORWARDED_SSL']) === 'on') return TRUE;
		if (!empty($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) !== 'off') return TRUE;
		if (!empty($_SERVER['HTTP_CF_VISITOR']) && strpos($_SERVER['HTTP_CF_VISITOR'], 'https') !== FALSE) return TRUE;
		if (!empty($_SERVER['HTTP_X_FORWARDED_PORT']) && $_SERVER['HTTP_X_FORWARDED_PORT'] == '443') return TRUE;
		if (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443') return TRUE;
		return FALSE;
	}
}
define('SITE_PROTOCOL', kone_is_https() ? 'https://' : 'http://');
/* [修复] dirname() 可能返回 "\" 或 "/"，直接拼接会产生 "//"，统一归一化 */
$__kone_dir = isset($_SERVER['SCRIPT_NAME']) ? dirname($_SERVER['SCRIPT_NAME']) : '';
$__kone_dir = rtrim(str_replace('\\', '/', $__kone_dir), '/');
define('SITE_PATH', SITE_PROTOCOL.DOMAIN.$__kone_dir."/");
unset($__kone_dir);
 
//定义字符集
define("CHARSET",konecms::load_config("system","charset"));
//设置本地时差
function_exists('date_default_timezone_set') && date_default_timezone_set(konecms::load_config('system','timezone'));

//加载系统函数
konecms::load_lib_func("global");
 
class konecms{
	/**
	 * load_lib_class 加载lib系统类
	 * @param string $classname
	 * @param string $path
	 * @param bool $ifobj
	 * @return Ambigous
	 */
	public static function build_cms(){
		return self::load_lib_class("build_cms");
	}
	/**
	 * load_lib_class 加载lib类
	 * @param unknown $classname
	 * @param string $path
	 * @param string $ifobj
	 * @return Ambigous
	 */
	public static function load_lib_class($classname,$path="",$ifobj=TRUE){
		return self::_load_class($classname,$path,$ifobj);
	}
	/**
	 * load_api_class 加载api类
	 * @param unknown $classname
	 * @param string $path
	 * @param string $ifobj
	 * @return Ambigous
	 */
	public static function load_api_class($classname,$path="",$ifobj=TRUE){
	   
		return self::_load_class($classname,"api".DIRECTORY_SEPARATOR."classes".DIRECTORY_SEPARATOR.$path,$ifobj);
	}
	/**
	 * load_lib_classes 加载lib下的目录中的类
	 * @param unknown $classname
	 * @param string $path
	 * @param string $ifobj
	 * @return Ambigous
	 */
	public static function load_lib_classes($classname,$path="",$ifobj=TRUE){		 
		return self::_load_class($classname,"lib".DIRECTORY_SEPARATOR."classes".DIRECTORY_SEPARATOR.$path,$ifobj);
	}
	/**
	 * load_module_class 加载module处理器
	 * @param string $classname
	 * @param string $m
	 * @param string $ifobj
	 * @return Ambigous
	 */
	public static function load_module_class($classname,$m="",$ifobj=TRUE){
		return self::_load_class($classname,"module".DIRECTORY_SEPARATOR.$m,$ifobj);
	}
	/**
	 * load_module_class 加载module下的目录中的类
	 * @param unknown $classname
	 * @param string $m 路径，默认为 module/模块名/classes
	 * @param string $ifobj
	 * @return boolean|Ambigous
	 */
	public static function load_module_classes($classname,$m="",$ifobj=TRUE){ 		 
		$m=empty($m)&&defined("ROUTE_M")?ROUTE_M:"";
		if(empty($m)) return false;
		return self::_load_class($classname, "module".DIRECTORY_SEPARATOR.$m.DIRECTORY_SEPARATOR."classes",$ifobj);
	}
	/**
	 * load_model_class 加载model模型
	 * @param unknown $classname
	 * @param string $path
	 * @param string $ifobj
	 * @return Ambigous
	 */
	public static function load_model_class($classname,$path="model",$ifobj=TRUE){
		 
	    return self::_load_class($classname."_model",$path,$ifobj);
	}
	/**
	 * _load_class 加载类
	 * @param string $classname 类名
	 * @param string $path 路径
	 * @param bool $ifobj 是否实例化 
	 * @return Ambigous <>|Ambigous <unknown, boolean>|boolean
	 */
	 private static function _load_class($classname,$path="",$ifobj=TRUE){
		static $classesArr=array();
		if(empty($path)) $path="lib".DIRECTORY_SEPARATOR."classes";
		$key=md5($path.$classname);
		if(isset($classesArr[$key])){ 
				return $classesArr[$key]; 
		}
		// echo KONECMS_ROOT.$path.DIRECTORY_SEPARATOR.$classname.".class.php";
		// echo "<br>";  
		if(file_exists(KONECMS_ROOT.$path.DIRECTORY_SEPARATOR.$classname.".class.php") ){
		     
			include KONECMS_ROOT.$path.DIRECTORY_SEPARATOR.$classname.".class.php";
			 
			$name=$classname; 
			if(file_exists(KONECMS_ROOT.$path.DIRECTORY_SEPARATOR."MYCLASS_".$classname.".class.php")){ 
			    include KONECMS_ROOT.$path.DIRECTORY_SEPARATOR."MYCLASS_".$classname.".class.php"; 
			    $name="MYCLASS_".$classname;
			}
			
			if($ifobj){
			    
				if(class_exists($name)){
					$classesArr[$key]=new $name;
				}else{
					exit("加载类：".$name.".class 失败");
				}				
			}else{  
			   
				$classesArr[$key]=true;
			}
			return $classesArr[$key];
		}else{
			exit("类：".$classname."文件不存在");
		}
	      
	}
	/**
	 * load_lib_func 加载lib函数库
	 * @param unknown $func
	 * @param string $path
	 */
	public static function load_lib_func($func,$path=""){
		return self::_load_func($func,$path);
	}
	/**
	 * load_module_func 加载module函数库
	 * @param  $func 函数文件名称
	 * @param string $m 路径 默认为 module/模块名/function
	 * @return boolean
	 */
	public static function load_module_func($func,$m=""){
		// [PHP8 修复] 原写法 defined(ROUTE_M) 把常量的「值」当成常量名去检测。
		// 若 ROUTE_M 尚未定义，PHP 8 会抛 Error: Undefined constant "ROUTE_M" 而中断请求。
		$m=empty($m)&&defined("ROUTE_M")?ROUTE_M:$m;
		if(empty($m)) return false;
		return self::_load_func($func,"module".DIRECTORY_SEPARATOR.$m.DIRECTORY_SEPARATOR."functions");
	}
	/**
	 * _load_func 加载函数库
	 * @param unknown $func
	 * @param string $path
	 * @return boolean
	 */
	private static function _load_func($func,$path=""){
		static $funcArr=array();
		if(empty($path)) $path="lib".DIRECTORY_SEPARATOR."functions";
		$key=md5($path);
		if(isset($funcArr[$key])) return true;
		if(file_exists(KONECMS_ROOT.$path.DIRECTORY_SEPARATOR.$func.".func.php")){
			include KONECMS_ROOT.$path.DIRECTORY_SEPARATOR.$func.".func.php";
			$funcArr[$key]=true;
			return true;
		}else{
			$funcArr[$key]=false;
			return false;
		}
	}
	/**
	 * load_config 加载配置
	 * @param string $file 配置文件名
	 * @param string $item 获取指定配置项
	 * @param string $default 默认配置项值
	 * @param string $ifreload 是否重新加载配置
	 * @return Ambigous <>|string|Ambigous <NULL>
	 */
	public static function load_config($file,$item="",$default="",$ifreload=false){
		static $configArr=array();
		if(!$ifreload&&isset($configArr[$file])){
			if(empty($item)){
				return $configArr[$file];
			}elseif(isset($configArr[$file][$item])){
				return $configArr[$file][$item];
			}else{
				return $default;
			}
		}
		$path=WEB_ROOT."config".DIRECTORY_SEPARATOR.$file.".php";
		if(file_exists($path)){
			$configArr[$file]=include $path;
			if(empty($item)){
				return $configArr[$file];
			}elseif(isset($configArr[$file][$item])){
				return $configArr[$file][$item];
			}else{
				return $default;
			}
		}else{
			return $default;
		}
	}
}
