<?php
/**
 * dbconn.php 后台数据库连接配置
 * 复用与前端相同的受限账号配置：读取 service/config/.env（DB_USER/DB_PASS 等）。
 * 不再使用 root/空密码。
 *
 * 自愈改造：自动部署会清空 public_html，导致 service/config/.env 丢失。
 * 因此额外读取「public_html 之外」的持久凭据文件（如 <domain>/.bxq.env），
 * 该文件不会被部署清空，从而每次部署后后台也能自动连上数据库。
 */
$_bxq_env_candidates = array(
    __DIR__ . '/../../config/.env',                                  // service/config/.env（部署时若在场，优先）
    dirname(dirname(dirname(dirname(__DIR__)))) . '/.bxq.env',       // 域名目录（public_html 之外，存活于部署）
    (isset($_SERVER['HOME']) ? $_SERVER['HOME'] : '') . '/.bxq.env',
    '/home/u906113796/.bxq.env',                                     // Hostinger 共享主机 home 目录（兜底）
);
$_bxq_env_file = null;
foreach ($_bxq_env_candidates as $_c) {
    if ($_c && file_exists($_c)) { $_bxq_env_file = $_c; break; }
}
if ($_bxq_env_file !== null) {
    $raw = (string) @file_get_contents($_bxq_env_file);
    if (substr($raw, 0, 3) === "\xEF\xBB\xBF") {
        $raw = substr($raw, 3);
    }
    foreach (explode("\n", $raw) as $line) {
        $line = trim($line, " \t\r\n\0\x0B");
        if ($line === '' || $line[0] === '#') continue;
        if (strpos($line, '=') === false) continue;
        list($k, $v) = explode('=', $line, 2);
        putenv(trim($k) . '=' . trim($v, " \t\r\n\0\x0B"));
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
