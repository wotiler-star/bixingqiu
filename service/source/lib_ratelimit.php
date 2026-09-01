<?php
/**
 * lib_ratelimit.php — 轻量文件级频率限制（按 IP），用于登录/注册/发短信防刷。
 *
 * 存储路径：优先 sys_get_temp_dir()/bxq_rl，回退到 service/konecms_ups/k/rl。
 * 若所有路径均不可写，则放行（不误伤正常用户），仅失去限流能力。
 *
 * 用法：
 *   if (!bxq_rl_check('login', 20, 600)) { /* 超限，拒绝 * / }
 *   bxq_rl_hit('login', 600); // 记录一次尝试（窗口 600 秒）
 */

if (!function_exists('bxq_rl_dir')) {
    function bxq_rl_dir()
    {
        $dirs = array();
        if (function_exists('sys_get_temp_dir')) {
            $dirs[] = rtrim(sys_get_temp_dir(), '/') . '/bxq_rl';
        }
        // 回退：与 source/ 同级的 konecms_ups/k/rl
        $dirs[] = __DIR__ . '/../konecms_ups/k/rl';
        foreach ($dirs as $d) {
            if (@is_dir($d) || @mkdir($d, 0755, true)) {
                if (@is_writable($d)) {
                    return $d;
                }
            }
        }
        return false;
    }
}

if (!function_exists('bxq_rl_check')) {
    function bxq_rl_check($key, $max, $windowSec)
    {
        $dir = bxq_rl_dir();
        if (!$dir) {
            return true; // 无法限流时放行
        }
        $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
        $file = $dir . '/' . md5($key . '_' . $ip) . '.json';
        $now = time();
        $hits = array();
        if (file_exists($file)) {
            $hits = json_decode(@file_get_contents($file), true);
            if (!is_array($hits)) {
                $hits = array();
            }
        }
        $hits = array_filter($hits, function ($t) use ($now, $windowSec) {
            return $t > $now - $windowSec;
        });
        return count($hits) < $max;
    }
}

if (!function_exists('bxq_rl_hit')) {
    function bxq_rl_hit($key, $windowSec)
    {
        $dir = bxq_rl_dir();
        if (!$dir) {
            return;
        }
        $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
        $file = $dir . '/' . md5($key . '_' . $ip) . '.json';
        $now = time();
        $hits = array();
        if (file_exists($file)) {
            $hits = json_decode(@file_get_contents($file), true);
            if (!is_array($hits)) {
                $hits = array();
            }
        }
        $hits = array_filter($hits, function ($t) use ($now, $windowSec) {
            return $t > $now - $windowSec;
        });
        $hits[] = $now;
        @file_put_contents($file, json_encode($hits));
    }
}
