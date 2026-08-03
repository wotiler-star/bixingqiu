# 币星球 · Hostinger 共享主机部署指南

> 本文档是**主部署方案**。原 `README.md`（Vultr VPS + Nginx + PHP7.4）为备选方案，
> 与 Hostinger 环境**不适用**，请勿混用其中的 nginx 配置与 shell 脚本。

---

## 一、目标环境

| 项目 | Hostinger 共享主机实际情况 |
|---|---|
| Web 服务器 | **LiteSpeed**（兼容 Apache `.htaccess`，**不读 nginx.conf**） |
| PHP | 8.0 / 8.1 / 8.2（hPanel 可切换，**推荐 8.1 或 8.2**） |
| PHP 配置 | 用 **`.user.ini`**（PERDIR 级），**不能改 php.ini** |
| 数据库 | MariaDB 10.x（hPanel → 数据库 → MySQL 数据库） |
| 安全扫描 | **Imunify360 + ClamAV** 全盘扫描，命中 webshell 特征会**直接隔离文件甚至封站** |
| SSL | hPanel 免费 Let's Encrypt，**反向代理回源**（所以必须靠 `X-Forwarded-Proto` 判断 https） |
| 站点根 | `~/public_html/`（或 `~/domains/<域名>/public_html/`） |

---

## 二、目录结构（上传后 public_html 应长这样）

```
public_html/
├── .htaccess              ← SPA 回退 + 强制 HTTPS + 压缩缓存 + 安全头（来自 html0113/public/.htaccess）
├── index.html             ← 前端构建产物
├── favicon.svg
├── robots.txt
├── sitemap.xml
├── static/                ← 前端 JS/CSS/媒体
│   ├── css/
│   ├── js/
│   └── media/
└── service/               ← 后端 KoneCMS（整个 service/ 目录原样上传）
    ├── .htaccess          ← 后端访问控制
    ├── .user.ini          ← PHP 运行时配置
    ├── index.php
    ├── admin.php
    ├── admin/
    ├── config/            ← 有独立 .htaccess（Require all denied）；**.env 放这里**（见第四步）
    ├── konecms/
    ├── konecms_ups/       ← 上传目录，有独立 .htaccess（禁脚本执行）
    └── source/
```

> **关键**：前端产物放 `public_html/` 根，后端 `service/` 作为子目录。
> 前端 `axios.defaults.baseURL` 已同源化为 `<当前域名>/service`，**无需重新构建即可换域名**。

---

## 三、部署步骤

### 步骤 1：构建前端

```bash
cd html0113
npm install --legacy-peer-deps
# webpack 3 在 Node 17+ 需要 legacy OpenSSL
NODE_OPTIONS=--openssl-legacy-provider npm run build
```
产物在 `html0113/build/`。`public/.htaccess` 会被自动复制进 `build/`。

> 若 Node 版本 ≥ 22 仍报 `digital envelope routines::unsupported`，
> 确认 `NODE_OPTIONS=--openssl-legacy-provider` 已生效；或改用 Node 16。

### 步骤 2：组装部署包

```bash
# 在仓库根执行
bash deploy/build-hostinger.sh
```
生成 `release/public_html/` 和 `release/bixingqiu-hostinger.zip`。

### 步骤 3：上传

**方式 A — hPanel 文件管理器（推荐，最省事）**
1. hPanel → 文件 → 文件管理器 → 进入 `public_html`
2. **先清空**原有默认页（`default.php` / `index.php` 等）
3. 上传 `bixingqiu-hostinger.zip` → 在 `public_html` **内**右键 **Extract**
   - ⚠️ 本包**已去除 `public_html/` 前缀**，务必解压到 `public_html` 目录内部；
     若解压到 home 根目录会把文件散落到 public_html 之外，站点无法访问。
4. 解压后确认 `.htaccess` / `.user.ini` 存在（文件管理器需勾选"显示隐藏文件"）
5. 同样上传 `bixingqiu-uploads.zip`，在 `public_html/service/` **内**解压
   （包内顶层是 `konecms_ups/`，解压后落在 `public_html/service/konecms_ups/`）

**方式 B — FTP**
```bash
# hPanel → 文件 → FTP 账户 获取主机/用户名/密码
lftp -u <user>,<pass> ftp://<ftp_host> -e "mirror -R --delete release/public_html/ /public_html/; bye"
```
> ⚠️ FTP 客户端默认**不传隐藏文件**，务必开启"显示/传输隐藏文件"，
> 否则 `.htaccess` / `.user.ini` 漏传会导致 SPA 路由 404、PHP 报错外泄。

### 步骤 4：建库 + 导数据 + 写 .env

1. hPanel → 数据库 → **MySQL 数据库** → 新建数据库和用户（记下 `库名/用户名/密码`）
   - Hostinger 的库名/用户名会自动带前缀，如 `u123456789_bxq`
2. hPanel → 数据库 → **phpMyAdmin** → 选中该库 → 导入 → 上传 **`deploy/db/bixingqiu.sql`**（7.33 MB）

   > 🔒 **该 SQL 不在 GitHub 仓库中，只随本地交付件提供**（`.gitignore` 已屏蔽 `deploy/db/`）。
   > 原因：dump 内含 **2206 个真实手机号、182 个密码哈希**，本仓库为 **public**，
   > 一旦提交会永久留在 git 历史里无法彻底清除，违反个人信息保护要求。
   > 若在别的机器上找不到它，可从本机 `F:\bixingqiu\deploy\db\bixingqiu.sql` 或
   > MySQL 数据目录 `D:\mysql57\MySQL5.7\data\k_k3_bixingqiu` 重新导出。
   - ✅ 该备份导出于 2026-07-23，含 **18 张表**：`i_tb`(资讯 441 条) `catalog_tb`(栏目 195 条) `feedback_tb`(269 条) `h_tb`(项目 26 条) `admin_tb`(后台账号) 等，与代码完全匹配。
   - ✅ **无 `CREATE DATABASE` / `USE` 语句**，可直接导入任意库名（适配 Hostinger 的 `u123456789_` 前缀）。
   - ✅ 无 DEFINER / 视图 / 存储过程，MyISAM + utf8，MariaDB 10.x 完全兼容；含 `DROP TABLE`，可重复导入。
   - 若 phpMyAdmin 提示超出上传限制，改用同目录的 **`bixingqiu.sql.gz`**（1.76 MB），phpMyAdmin 支持直接导入 gz。

   > ⚠️ **导入后必须立即改后台密码**：备份里 `admin_tb` 有两个超管账号，其中 `hao` 的密码是弱口令 `123456`（裸 MD5 无加盐）。后台 `admin.php` 暴露在公网，不改极易被爆破。登录后台后第一时间修改，或在 phpMyAdmin 里直接更新 `admin_tb.password` 字段为新密码的 MD5。
3. 在 `public_html/service/config/` 下**新建 `.env`**（文件管理器 → 新建文件）：
   - ⚠️ 是 `service/config/.env`，不是 `service/.env`——`dbconn.php` 用 `__DIR__` 读取 config 目录下的 `.env`。

```ini
DB_HOST=localhost
DB_PORT=3306
DB_NAME=u123456789_bxq
DB_USER=u123456789_bxq
DB_PASS=你的数据库密码
BXQ_DEBUG=0
KONE_UPLOAD_MAX_MB=10
```

> `service/config/dbconn.php` 会优先读 `.env`，读不到才回退硬编码值。
> `.env` 已被 `.gitignore` 忽略，**不会进 git**。

### 步骤 5：设置 PHP 版本与权限

- hPanel → 高级 → **PHP 配置** → 选择 **PHP 8.1**（或 8.2）
- 扩展确认勾选：`mysqli`、`gd`、`mbstring`、`curl`、`json`、`fileinfo`、`zip`
- 目录权限（文件管理器右键 → Permissions）：
  - 目录 `755`，文件 `644`
  - `service/konecms_ups/` 需 `755`（可写）

### 步骤 6：开启 SSL

hPanel → 安全 → **SSL** → 为域名安装免费 SSL → 等待签发（约 5–15 分钟）。
根 `.htaccess` 已内置强制 HTTPS 跳转（用 `X-Forwarded-Proto` 判断，**不会死循环**）。

---

## 四、验证清单

| # | 检查项 | 预期 |
|---|---|---|
| 1 | `https://域名/` | 首页加载，无白屏，无混合内容警告 |
| 2 | 浏览器直接访问 `https://域名/article/123` | **不 404**（SPA 回退生效） |
| 3 | `https://域名/service/index.php` | 返回后端响应（非 500） |
| 4 | F12 Network 看 API 请求 | 指向 `https://域名/service/...`，非 localhost |
| 5 | `https://域名/service/config/dbconn.php` | **403 Forbidden** |
| 6 | `https://域名/service/config/.env` | **403 Forbidden** |
| 7 | `https://域名/service/konecms/konecms.php` | **403 Forbidden** |
| 8 | `https://域名/service/admin.php` | 302 跳转到 `admin/index.php` 登录页 |
| 9 | 未登录直接 POST `service/source/kindeditor-4.1.7/php/upload_json.php` | 返回 `{"error":1,"message":"未授权"}` |
| 10 | 页面响应头 | 含 `X-Content-Type-Options: nosniff`、`X-Frame-Options: SAMEORIGIN` |

---

## 五、本次已修复的部署阻断项

| 类别 | 问题 | 修复 |
|---|---|---|
| **致命** | `admin.php` 跳 `index.php?m=admin`，前台无 admin 模块 → 后台永远打不开 | 改为 `Location: admin/index.php` |
| **致命** | `SITE_PROTOCOL` 只看 `SERVER_PORT==443`，Hostinger SSL 是反代回源 → 全站资源被判 http，HTTPS 下混合内容/跳转异常 | 新增 `kone_is_https()`，识别 `X-Forwarded-Proto`/`X-Forwarded-SSL`/`CF-Visitor` 等 6 种反代头 |
| **致命** | PHP 8 语法：`=& new`（reader.class.php ×3）、`$chrs{++$c}`（JSON.php）、PHP4 风格构造函数 ×10、花括号字符串偏移 ×39 | 全部改为 PHP 8 合法写法；`php -l` 校验 **118 文件 / 0 错误** |
| **致命** | `get_magic_quotes_gpc()`（PHP 8 已移除）、`addslashes()` 多参调用 | 移除 / 改为自定义 `add_slashes()` |
| **致命** | mysqli 在 PHP 8.1+ 默认抛异常、MariaDB 版本字符串比较失败 | `mysqli_report(MYSQLI_REPORT_OFF)` + 版本判定修正（14 处） |
| **封站风险** | `service/base64/a.txt` 含 `eval(base64_decode(...))` 字面量，Imunify360 会判为 webshell | 清空为占位说明文本 |
| **高危** | kindeditor `upload_json.php` **零鉴权公开上传**，允许 `htm/html/swf`，上限 500MB | 加 `$_SESSION['ADMINID']` 鉴权、白名单收紧、10MB 上限、`getimagesize()` 真实性校验 |
| **高危** | 上传目录可执行 PHP | `konecms_ups/.htaccess`：`RemoveHandler` + `FilesMatch` 拒脚本 + `php_flag engine off` + CSP |
| **高危** | 数据库凭据、后台配置可被 HTTP 直接下载 | `config/.htaccess`、`admin/config/.htaccess`：`Require all denied` |
| **兼容** | PHP 8.2 动态属性弃用（`page.class.php` 的 `$url`） | 显式声明属性 |
| **配置** | 无 Apache 层配置，SPA 路由直接 404 | 新增根 `.htaccess`（SPA 回退 + HTTPS + gzip + 缓存 + 安全头） |
| **配置** | 无 PHP 运行时配置，报错会直接吐到页面 | 新增 `service/.user.ini`：`display_errors=Off`、`log_errors=On`、session 加固、时区 |

---

## 六、常见故障排查

**① 首页 500 / 白屏**
`service/.user.ini` 里 `display_errors=Off`，报错不显示。临时排查：
hPanel → 高级 → PHP 配置 → 打开 `display_errors`，或看 `~/public_html/error_log`。
排查完**务必关回去**。

**② SPA 子路由 404**
`.htaccess` 没传上去（隐藏文件）。文件管理器开启"显示隐藏文件"确认根目录有 `.htaccess`。

**③ HTTPS 无限重定向**
说明 `.htaccess` 里用了 `%{HTTPS} !=on` 单条判断。本项目已用
`RewriteCond %{HTTP:X-Forwarded-Proto} !=https` + `RewriteCond %{HTTPS} !=on` 双条件，正常不会触发。
若仍出现，检查 hPanel 是否同时开了"强制 HTTPS"，二选一即可。

**④ 数据库连接失败**
Hostinger 的 `DB_HOST` 通常是 `localhost`；若 hPanel 显示了独立主机名（如 `mysql.hostinger.xx`），按显示的填。
库名/用户名必须带 `u数字_` 前缀。

**⑤ 文件被 Imunify360 隔离**
hPanel → 安全 → Malware Scanner 查看隔离列表，误报可点 Restore 并加白名单。
本项目已清理 webshell 特征字面量，正常不会触发。

**⑥ 上传图片失败**
检查 `konecms_ups/` 权限 755；`.user.ini` 的 `upload_max_filesize=10M`；
若需更大，同时改 `upload_max_filesize` 和 `post_max_size`（后者要更大）。

---

## 七、上线后建议

1. **改后台默认口令**，后台地址可考虑改名（`admin/` → 自定义目录名）。
2. hPanel 开启 **自动备份**（每周）。
3. `robots.txt` / `sitemap.xml` 提交搜索引擎；`sitemap.xml` 里的域名记得替换为实际域名。
4. 定期在 hPanel → 安全 → Malware Scanner 跑一次全盘扫描。
