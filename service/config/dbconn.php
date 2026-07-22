<?php
/**
 * dbconn.php 数据库连接配置
 * 支持通过环境变量（或同目录 .env 文件）覆盖，便于在不同服务器部署。
 * 可用变量：DB_HOST / DB_PORT / DB_NAME / DB_USER / DB_PASS / BXQ_DEBUG
 * 未设置时回退到下面的默认值。
 */
if (file_exists(__DIR__ . '/.env')) {
    foreach (file(__DIR__ . '/.env') as $line) {
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
        'username' => $env('DB_USER', 'root'),
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
