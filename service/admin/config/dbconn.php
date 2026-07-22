<?php
/**
 * dbconn.php 后台数据库连接配置
 * 复用与前端相同的受限账号配置：读取 service/config/.env（DB_USER/DB_PASS 等）。
 * 不再使用 root/空密码。
 */
$envFile = __DIR__ . '/../../config/.env';
if (file_exists($envFile)) {
    foreach (file($envFile) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') continue;
        if (strpos($line, '=') === false) continue;
        list($k, $v) = explode('=', $line, 2);
        putenv(trim($k) . '=' . trim($v));
    }
}

$env = function ($k, $d) { $v = getenv($k); return ($v === false) ? $d : $v; };
$debugEnv = getenv('BXQ_DEBUG');
$debug = ($debugEnv === false) ? false : (strtolower($debugEnv) !== '0' && $debugEnv !== 'false');

return array (
	'default' => array (
		'hostname' => $env('DB_HOST', 'localhost'),
		'port'     => (int)$env('DB_PORT', 3306),
		'database' => $env('DB_NAME', 'k_k3_bixingqiu'),
		'username' => $env('DB_USER', 'bxq_app'),
		'password' => $env('DB_PASS', ''),
		'tablepre' => '',
		'charset'  => 'utf8',
		'type'     => 'mysqli',
		'debug'    => $debug,
		'pconnect' => 0,
		'autoconnect' => 0
		)
);

?>
