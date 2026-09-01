<?php
/**
 * dbconn.php 数据库连接配置
 * 支持通过环境变量（或同目录 .env 文件）覆盖，便于在不同服务器部署。
 * 可用变量：DB_HOST / DB_PORT / DB_NAME / DB_USER / DB_PASS / BXQ_DEBUG
 * 未设置时回退到下面的默认值。
 */
if (file_exists(__DIR__ . '/.env')) {
    // 兼容 Git/记事本导致的 UTF-8 BOM 头：BOM 会出现在首行键名前，
    // 使 "﻿DB_HOST" 不匹配 "DB_HOST"，从而回退 localhost 并连接失败（空 500）。
    // 这里在解析前一次性剥掉文件开头的 BOM（\xEF\xBB\xBF）。
    $raw = (string) @file_get_contents(__DIR__ . '/.env');
    if (substr($raw, 0, 3) === "\xEF\xBB\xBF") {
        $raw = substr($raw, 3);
    }
    foreach (explode("\n", $raw) as $line) {
        // 兼容 Windows CRLF：去掉行尾 \r，避免值里混入回车
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
