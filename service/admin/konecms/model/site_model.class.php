<?php
konecms::load_lib_class('model', '', 0);
class site_model extends model {
	public function __construct() {
		$this->db_file =konecms::load_config('dbconn');
		$this->db_name = 'default';
		$this->table_name = 'site_tb';
		parent::__construct();
	}
}
?>