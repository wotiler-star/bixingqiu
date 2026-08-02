<?php
/**
 * 后台入口跳转。
 *
 * [修复] 原实现为 header("location:index.php?m=admin");
 * 但前台应用的模块目录 konecms/module/ 下只有 content 一个模块，
 * 后台是位于 service/admin/ 的独立应用（有自己的 konecms/ 与 config/）。
 * 因此原跳转会落到前台路由的 admin 模块上并直接报错 / 白屏，
 * 等于后台入口是坏的。这里改为跳到真正的后台入口。
 */
header('Location: admin/index.php', true, 302);
exit;
