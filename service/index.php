<?php
header('Content-Type:text/html;charset=utf-8');
define("WEB_ROOT", dirname(__FILE__) . DIRECTORY_SEPARATOR);
include WEB_ROOT . "konecms" . DIRECTORY_SEPARATOR . "konecms.php";
konecms::build_cms();
?>
