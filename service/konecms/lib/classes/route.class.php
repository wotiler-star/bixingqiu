<?php
/**
 * route.class.php 路由解析 + 入参过滤
 * @copyright konecms.com
 *
 * [部署优化说明]
 * 原文件为 「动态代码执行 + Base64 解码」的混淆形式，存在三个问题：
 *   1. 无法审计与修复，隐藏了下面的 PHP 8 兼容性缺陷；
 *   2. 部分虚拟主机（含 Hostinger 的 ModSecurity / imunify360 规则）会把
 *      "动态代码执行+Base64解码" 判定为 webshell 特征而拦截或隔离文件；
 *   3. eval 每次请求都要额外解码编译，无谓开销。
 * 现已还原为等价明文实现，逻辑保持一致，并修复了下列 bug。
 */

class route
{
    private $routeArr = array();

    public function __construct()
    {
        $this->routeArr = konecms::load_config("route");

        // ---------------- GET 参数过滤 ----------------
        if (isset($_GET) && $_GET) {
            foreach ($_GET as $k => $v) {
                // [BUG 修复] 原代码为 get_check(add_slashes(trim($v)))，未判断 $v 是否为数组。
                // 当请求形如 ?a[]=1 时，trim() 收到数组：PHP 7 报 Warning 并返回 null，
                // PHP 8 直接抛 TypeError → 整站 500。此处与下方 POST 分支保持一致的数组处理。
                if (is_array($v)) {
                    $_GET[$k] = $this->filterArray($v, 'get');
                } else {
                    $_GET[$k] = get_check(add_slashes(trim((string) $v)));
                }
            }
        }

        // ---------------- POST 参数过滤 ----------------
        if (isset($_POST) && $_POST) {
            foreach ($_POST as $k => $v) {
                // pwd / password 表示密码，原样保留不做转义，避免改变口令内容
                if ($k != "pwd" && $k != "password" && !is_array($v)) {
                    $_POST[$k] = post_check(add_slashes(trim((string) $v)));
                } else {
                    $_POST[$k] = $v;
                }
            }
        }
    }

    /**
     * 递归过滤数组型入参
     * @param array  $arr
     * @param string $mode get|post
     * @return array
     */
    private function filterArray($arr, $mode = 'get')
    {
        $out = array();
        foreach ($arr as $k => $v) {
            if (is_array($v)) {
                $out[$k] = $this->filterArray($v, $mode);
            } else {
                $val = add_slashes(trim((string) $v));
                $out[$k] = ($mode === 'get') ? get_check($val) : post_check($val);
            }
        }
        return $out;
    }

    public function route_m()
    {
        $m = isset($_GET["m"]) && !empty($_GET["m"]) ? $_GET["m"] : (isset($_POST["m"]) && !empty($_POST["m"]) ? $_POST["m"] : "");
        if (empty($m)) {
            $m = $this->routeArr["route_m"];
        }
        // [安全修复] m/c/a 会被直接拼进类文件路径（见 konecms::_load_class），
        // 原实现未做白名单校验，存在路径穿越（../）风险。此处限制为安全字符集。
        return $this->sanitizeRouteToken($m, $this->routeArr["route_m"]);
    }

    public function route_c()
    {
        $c = isset($_GET["c"]) && !empty($_GET["c"]) ? $_GET["c"] : (isset($_POST["c"]) && !empty($_POST["c"]) ? $_POST["c"] : "");
        if (empty($c)) {
            $c = $this->routeArr["route_c"];
        }
        return $this->sanitizeRouteToken($c, $this->routeArr["route_c"]);
    }

    public function route_a()
    {
        $a = isset($_GET["a"]) && !empty($_GET["a"]) ? $_GET["a"] : (isset($_POST["a"]) && !empty($_POST["a"]) ? $_POST["a"] : "");
        if (empty($a)) {
            $a = $this->routeArr["route_a"];
        }
        return $this->sanitizeRouteToken($a, $this->routeArr["route_a"]);
    }

    /**
     * 路由片段安全校验：只允许字母、数字、下划线
     * 命中非法字符时回退到默认值，避免 ../ 造成的本地文件包含
     */
    private function sanitizeRouteToken($value, $default)
    {
        if (!is_string($value) || $value === '' || !preg_match('/^[A-Za-z0-9_]{1,50}$/', $value)) {
            return $default;
        }
        return $value;
    }
}
