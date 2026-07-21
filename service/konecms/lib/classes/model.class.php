<?php
/*
 * model.class.php
 * @copyright konecms 2016-2020
 * last update date 2016年4月3日
 */
konecms::load_lib_classes ( 'db_factory', 'db', 0 );
class model {
	
	// 数据库配置文件
	protected $db_file = '';
	// 数据库连接
	protected $db = '';
	// 数据库
	protected $db_name = 'default';
	// 数据表名
	protected $table_name = '';
	// 表前缀
	public $db_tablepre = '';
	// 分页字符串
	public $pagestr = ""; 
	public function __construct() {
		if (! isset ( $this->db_file [$this->db_name] )) {
			$this->db_name = 'default';
		}
		$this->table_name = $this->db_file [$this->db_name] ['tablepre'] . $this->table_name;
		$this->db_tablepre = $this->db_file [$this->db_name] ['tablepre'];
		$this->db = db_factory::get_instance ( $this->db_file )->get_database ( $this->db_name ); 		 
	}
	/*
	 * 获取执行的SQL语句
	 */
	final public function sql(){
	    return $this->db->sql;
	}
	
	/**
	 * 执行sql查询
	 *
	 * @param $field 需要查询的字段        	
	 * @param $where 查询条件.可使用数组        	
	 * @param $limit 返回结果范围        	
	 * @param $order 排序方式        	
	 * @param $group 分组方式        	
	 * @param $key 返回数组按键名排序        	
	 * @return array
	 */
	final public function select($field = '*', $where = '',$order = '', $limit = '',  $title2num = false, $short2num = false, $group = '', $key = '') {
		if (is_array ( $where ))
			$where = $this->sqls ( $where );
		return $this->db->select ( $field, $this->table_name, $where,$order, $limit,  $group, $key, $title2num, $short2num );
	}
	
	/**
	 * i(查询字段，查询条件，排序方式,记录总数，标题长度，描述长度，每页条数，分页标识字符，分页样式，其它参数数组)
	 * i 数据列表，包含分页、标题/描述字数控制等
	 *
	 * @param unknown $field
	 *        	查询字段
	 * @param unknown $where
	 *        	查询条件
	 * @param unknown $order
	 *        	排序方式
	 * @param unknown $title2num
	 *        	截取后的标题长度
	 * @param unknown $short2num
	 *        	截取后的描述长度
	 * @param unknown $queryArr
	 *        	分页字符串其它参数，如 array("sortid"=>1);
	 * @param unknown $pagesize
	 *        	每页展示数量
	 * @param unknown $setpage
	 *        	分页字符 [page],实现一个页面多个分页字符串
	 * @param unknown $pageStyle
	 *        	分页字符串样式
	 */                 
	final public function i($field = '*', $where = '', $order = '', $limit = false,$title2num = false, $short2num = false, $queryArr = array(),  $pagesize = 20, $setpage = "page", $pageStyle = 4) {
		  $where = to_sqls ( $where ); 
		 $setpage = $setpage ? "page" : $setpage;
		$page = isset ( $_GET [$setpage] ) ? safe_replace ( $_GET [$setpage] ) : 1;
		$this->number = $this->count ( $where );
		$page = max ( intval ( $page ), 1 );
		$offset = $pagesize * ($page - 1);
		$array = array ();
		if ($this->number > 0) {
			if (empty ( $limit )) {
				konecms::load_lib_classes ( "page", "", false );
				$page = new page ( $this->number, $pagesize, $setpage, $queryArr );
				$this->pagestr = $page->show ( 4 );
				return $this->select ( $field, $where, $order, "$offset, $pagesize", $title2num, $short2num );
			} else {
				return $this->select ( $field, $where, $limit,$title2num, $short2num );
			}
		} else {
			return array ();
		}
	}
	
	/**
	 * 获取单条记录查询
	 *
	 * @param $where 查询条件        	
	 * @param $data 需要查询的字段值[例`name`,`gender`,`birthday`]        	
	 * @param $order 排序方式        	
	 * @param $group 分组方式        	
	 * @return array/null
	 */
	final public function get_one($field = '*', $where = '', $order = '', $group = '') {
		if (is_array ( $where ))
			$where = $this->sqls ( $where );
		return $this->db->get_one ( $field, $this->table_name, $where, $order, $group );
	}
	
	/**
	 * 直接执行sql查询
	 *
	 * @param $sql 查询sql语句        	
	 * @return boolean/query resource 如果为查询语句，返回资源句柄，否则返回true/false
	 */
	final public function query($sql) {
		// $sql = str_replace('konecms_', $this->db_tablepre, $sql);
		return $this->db->query ( $sql );
	}
	
	/**
	 * 执行添加记录操作
	 *
	 * @param $fieldArr 要增加的数据，参数为数组。数组key为字段值，数组值为数据取值        	
	 * @param $return_insert_id 是否返回新建ID号,默认不返回        	
	 * @param $replace 是否采用
	 *        	replace into的方式添加数据，默认为insert into
	 * @return 插入数据对应的ID号或插入操作结果（1：插入成功）
	 */
	final public function insert($fieldArr, $return_insert_id = false, $replace = false) {
		return $this->db->insert ( $fieldArr, $this->table_name, $return_insert_id, $replace );
	}
	
	/**
	 * 获取最后一次添加记录的主键号
	 *
	 * @return int
	 */
	final public function insert_id() {
		return $this->db->insert_id ();
	}
	
	/**
	 * 执行更新记录操作
	 *
	 * @param $field 要更新的数据内容，参数可以为数组也可以为字符串，建议数组。
	 *        	为数组时数组key为字段值，数组值为数据取值
	 *        	为字符串时[例：`name`='konecms',`hits`=`hits`+1]。
	 *        	为数组时[例: array('name'=>'konecms','password'=>'123456')]
	 *        	数组的另一种使用array('name'=>'+=1', 'base'=>'-=1','break'=>'+=aaa');程序会自动解析为`name` = `name` + 1, `base` = `base` - 1 (break非数字跳过执行)
	 * @param $where 更新数据时的条件,可为数组或字符串        	
	 * @return boolean
	 */
	final public function update($field, $where = '') {
		if (is_array ( $where ))
			$where = $this->sqls ( $where );
		return $this->db->update ( $field, $this->table_name, $where );
	}
	
	/**
	 * 执行删除记录操作
	 *
	 * @param $where 删除数据条件,不充许为空。        	
	 * @return boolean
	 */
	final public function delete($where) {
		if (is_array ( $where ))
			$where = $this->sqls ( $where );
		return $this->db->delete ( $this->table_name, $where );
	}
	
	/**
	 * 计算记录数
	 *
	 * @param string/array $where
	 *        	查询条件
	 */
	final public function count($where = '') {
		$r = $this->get_one ( "COUNT(*) AS num", $where );
		return $r ['num'];
	}
	
	/**
	 * 将数组转换为SQL语句
	 *
	 * @param array $where
	 *        	要生成的数组
	 * @param string $font
	 *        	连接串。
	 */
	final public function sqls($where, $font = ' AND ') {
		if (is_array ( $where )) {
			$sql = '';
			foreach ( $where as $key => $val ) {
				$sql .= $sql ? " $font `$key` = '$val' " : " `$key` = '$val'";
			}
			return $sql;
		} else {
			return $where;
		}
	}
	
	/**
	 * 获取最后数据库操作影响到的条数
	 *
	 * @return int
	 */
	final public function affected_rows() {
		return $this->db->affected_rows ();
	}
	
	/**
	 * 获取数据表主键
	 *
	 * @return array
	 */
	final public function get_primary() {
		return $this->db->get_primary ( $this->table_name );
	}
	
	/**
	 * 获取表字段
	 *
	 * @param string $table_name
	 *        	表名
	 * @return array
	 */
	final public function get_fields($table_name = '') {
		if (empty ( $table_name )) {
			$table_name = $this->table_name;
		} else {
			$table_name = $this->table_name;
		}
		return $this->db->get_fields ( $table_name );
	}
	/**
	 * check_fields 给定数组，检查其中哪些不存在于表字段中。
	 *
	 * @param array $myFieldArr        	
	 * @param string $table_name        	
	 * @return boolean
	 */
	final public function check_fields($myFieldArr, $table_name = "") {
		if (is_array ( $myFieldArr ) && count ( $myFieldArr ) > 0) {
			if (empty ( $table_name )) {
				$table_name = $this->table_name;
			} else {
				$table_name = $this->table_name;
			}
			return $this->db->check_fields ( $myFieldArr, $table_name );
		}
		return false;
	}
	/**
	 * 检查表是否存在
	 *
	 * @param $table 表名        	
	 * @return boolean
	 */
	final public function table_exists($table) {
		return $this->db->table_exists ( $table );
	}
	
	/**
	 * 检查字段是否存在
	 *
	 * @param $field 字段名        	
	 * @return boolean
	 */
	public function field_exists($field) {
		$fields = $this->db->get_fields ( $this->table_name );
		return array_key_exists ( $field, $fields );
	}
	final public function list_tables() {
		return $this->db->list_tables ();
	}
	/**
	 * 返回数据结果集
	 *
	 * @param $query （mysql_query返回值）        	
	 * @return array
	 */
	final public function fetch_array() {
		$data = array ();
		while ( $r = $this->db->fetch_next () ) {
			$data [] = $r;
		}
		return $data;
	}
	
	/**
	 * 返回数据库版本号
	 */
	final public function version() {
		return $this->db->version ();
	}
	
	/**
	 * 返回数据库版本号
	 */
	final public function error() { 
		return $this->db->error ();
	}
}