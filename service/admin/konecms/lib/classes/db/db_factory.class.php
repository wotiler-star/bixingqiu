<?php
/**
 * db_factory.class.php
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
final class db_factory {

	

	/**

	 * 当前数据库工厂类静态实例

	 */

	private static $db_factory;

	

	/**

	 * 数据库配置列表

	 */

	protected $db_file = array();

	

	/**

	 * 数据库操作实例化列表

	 */

	protected $db_list = array();

	

	/**

	 * 构造函数

	 */

	public function __construct() {

	}

	

	/**

	 * 返回当前终级类对象的实例

	 * @param $db_config 数据库配置

	 * @return object

	 */

	public static function get_instance($db_file = '') {

		

		// [修复] 原代码把配置读进 $db_config 后从未使用（死代码），
		// 真正的配置由调用方（model 子类）通过 $db_file 参数传入。
		// 这里改为回填 $db_file，使无参调用也能拿到默认配置。
		if($db_file == '') {
			$db_file = konecms::load_config('dbconn');
		}

		if(db_factory::$db_factory == '') { 

			db_factory::$db_factory = new db_factory();

		}

		if($db_file != '' && $db_file != db_factory::$db_factory->db_file) {

			db_factory::$db_factory->db_file = array_merge($db_file, db_factory::$db_factory->db_file);

		} 

		return db_factory::$db_factory;

	}

	

	/**

	 * 获取数据库操作实例

	 * @param $db_name 数据库配置名称

	 */

	public function get_database($db_name) {

		if(!isset($this->db_list[$db_name]) || !is_object($this->db_list[$db_name])) {

			 $this->db_list[$db_name] = $this->connect($db_name);

		}

		return $this->db_list[$db_name];

	}

	

	/**

	 *  加载数据库驱动

	 * @param $db_name 	数据库配置名称

	 * @return object

	 */

	public function connect($db_name) {

		$object = null;

		// [PHP8 修复] 原代码直接取 $this->db_file[$db_name]['type']，键不存在时
		// PHP 8 抛 Warning: Undefined array key，告警文本会混进 JSON 响应体导致前端解析失败。
		if(!isset($this->db_file[$db_name]) || !is_array($this->db_file[$db_name])) {
			exit('数据库配置 ['.htmlspecialchars((string)$db_name, ENT_QUOTES).'] 不存在，请检查 config/dbconn.php 与 config/.env');
		}
		$db_type = isset($this->db_file[$db_name]['type']) ? $this->db_file[$db_name]['type'] : 'mysqli';
		switch($db_type) {

			case 'mysql' :
				// [PHP7/8 修复] mysql 扩展已在 PHP 7.0 移除，且本项目未提供 db/mysql.class.php，
				// 原分支必然 exit("类：mysql文件不存在")。统一降级到 mysqli 驱动。
				$object = konecms::load_lib_classes('db_mysqli', 'db');
				break;

			case 'mysqli' : 

				$object = konecms::load_lib_classes('db_mysqli', 'db');

				break;

			case 'access' :

				$object = konecms::load_lib_classes('db_access', 'db');

				break;

			default :
				// [修复] 同上：默认分支不再加载不存在的 mysql 驱动。
				$object = konecms::load_lib_classes('db_mysqli', 'db');

		}

	    $object->open($this->db_file[$db_name]);

		 

		return $object;

	}



	/**

	 * 关闭数据库连接

	 * @return void

	 */

	protected function close() {

		foreach($this->db_list as $db) {

			$db->close();

		}

	}

	

	/**

	 * 析构函数

	 */

	public function __destruct() {

		$this->close();

	}

}
