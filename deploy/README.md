# 币星球 · 部署工具包

把修复后的全站源码一键部署到**海外 VPS**（推荐 Vultr 洛杉矶）。

## 一、为什么选 Vultr（2026-07 行情）

| 服务商 | 配置 | 价格 | 优势 | 注意 |
|---|---|---|---|---|
| **Vultr 洛杉矶** | 1 vCPU / 1 GB / 25 GB | **$5–6/月** | 32 节点、按小时计费、API 完善、洛杉矶对国内延迟 150–180ms | 综合最均衡，推荐 |
| Hetzner (美东/德国) | 2 vCPU / 4 GB | €3.79–4.59/月 | 纯性价比最高 | 对国内延迟偏高 |
| RackNerd | 1 vCPU / 1 GB | ~$1–2/月(年付) | 极致便宜 | 无 API、仅年付、不够"好用" |

> 结论：**Vultr 洛杉矶 $6/月** 是"便宜 + 好用"的最佳平衡点。1 GB 内存跑 Nginx + PHP7.4 + MariaDB 足够（本站数据量小）。

## 二、Vultr 开通步骤

1. 注册 https://www.vultr.com ，充值（信用卡/支付宝/PayPal）。
2. Deploy New Server：
   - Location：**Los Angeles (LAX)** 或 Silicon Valley
   - Type：**Cloud Compute** / Intel Regular，1 vCPU / 1024 MB
   - OS：**Ubuntu 22.04 LTS**
   - 勾选 **Enable SSH Keys**（把你本机公钥加进去，或用 root 密码）
3. 拿到 **Public IP**（如 `45.76.x.x`）。
4. （可选）域名 A 记录指向该 IP，Nginx 配置里把 `server_name _` 改成你的域名，再用 `certbot` 开 HTTPS。

## 三、部署（两种方式二选一）

### 方式 A：已有服务器（给 IP + SSH）
```bash
# 1. 本地把代码上传 + 建库 + 导数据
./push.sh root@45.76.x.x

# 2. SSH 进服务器执行初始化（装环境、写 .env、配 Nginx、开防火墙）
ssh root@45.76.x.x 'bash /var/www/bixingqiu/deploy/server-init.sh'
```
> push.sh 已负责上传并导入数据库；server-init.sh 负责装运行环境。顺序可颠倒，
> 但 server-init.sh 最后一步会尝试导入 `/tmp/bixingqiu.sql`（由 push.sh 上传）。

### 方式 B：想让我用 Vultr API 自动开通
需要你提供 **Vultr API Token**（https://my.vultr.com/settings/#api），
以及你本机的 **SSH 公钥**。我可用脚本自动创建实例并部署。
> ⚠️ API Token 权限较高，建议仅在需要时生成、用后删除。

## 四、文件说明

| 文件 | 作用 |
|---|---|
| `server-init.sh` | 服务器初始化：装 Nginx/PHP7.4/MariaDB、建库、建受限账号 `bxq_app`、写 `.env`、配 Nginx、开防火墙 |
| `nginx-bixingqiu.conf` | Nginx 站点配置（前端 SPA 回退 + `/service/` 指向后端入口） |
| `push.sh` | 本地一键上传（rsync）+ 导入数据库 |
| `db/bixingqiu.sql` | 全量数据库导出（结构+数据，无系统账号/密码） |

## 五、关键设计

- **前端已「同源化」**：不再写死 `localhost:8090`，浏览器访问哪个域名/IP，API 就自动指向该地址的 `/service/`。**无需为不同环境重新构建**。
- **数据库凭据不进 git**：`.env` 已被 `.gitignore` 忽略；服务器上由 `server-init.sh` 生成随机口令。
- **最小权限账号**：后端用 `bxq_app`（仅 SELECT/INSERT/UPDATE/DELETE/CREATE TEMPORARY TABLES/EXECUTE），不用 root。

## 六、验证

部署后访问 `http://<服务器IP>/`：
- 首页正常加载（含 SEO 元信息）
- 打开任意文章详情页不白屏
- 文章内容经 XSS 净化渲染
