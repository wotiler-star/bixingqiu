<?php
/**
 * dbconn.php 数据库连接配置
 * @copyright konecms.com
 * @lastmodify 2016年2月26日
*/
return array (
	'default' => array (
		'hostname'=>'localhost',//服务器地址
		'port'=>3306,//端口号
		'database'=>'k_k3_bixingqiu',//数据库名称
		'username'=>'root',//用户名
		'password'=>'',//密码
		'tablepre'=>'',
		'charset' => 'utf8',
		'type' => 'mysqli',
		'debug' => true,
		'pconnect' => 0,
		'autoconnect' => 0
		)
);

?>