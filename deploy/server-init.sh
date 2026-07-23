#!/usr/bin/env bash
#
# 币星球 一键服务器初始化脚本（Ubuntu 22.04 LTS）
# 适用：Vultr / 任意 KVM VPS（Ubuntu 22.04）
# 作用：安装 Nginx + PHP7.4-FPM + MariaDB，建库、建受限账号、写 .env、配 Nginx、开防火墙
# 用法：
#   1) 把本项目传到服务器（见 push.sh）
#   2) ssh root@服务器IP  'bash /var/www/bixingqiu/deploy/server-init.sh'
# 注意：本脚本会生成数据库受限账号 bxq_app 的随机口令并写入 service/config/.env
#
set -euo pipefail

APP_ROOT=/var/www/bixingqiu
DB_NAME=k_k3_bixingqiu
DB_USER=bxq_app
# 生成 32 位随机强口令
DB_PASS="$(openssl rand -base64 24 | tr -dc 'A-Za-z0-9' | head -c 32)"
PHP_VER=7.4

echo "==> [1/9] 更新系统"
export DEBIAN_FRONTEND=noninteractive
apt-get update -y
apt-get upgrade -y

echo "==> [2/9] 添加 ondrej PHP PPA（用于安装 PHP ${PHP_VER}）"
apt-get install -y software-properties-common ca-certificates lsb-release apt-transport-https
add-apt-repository -y ppa:ondrej/php
apt-get update -y

echo "==> [3/9] 安装 Nginx / PHP / MariaDB"
apt-get install -y nginx mariadb-server \
  php${PHP_VER}-fpm php${PHP_VER}-cli php${PHP_VER}-common \
  php${PHP_VER}-mysqli php${PHP_VER}-gd php${PHP_VER}-curl \
  php${PHP_VER}-mbstring php${PHP_VER}-xml php${PHP_VER}-zip \
  unzip openssl

echo "==> [4/9] 启动服务"
systemctl enable --now nginx mariadb php${PHP_VER}-fpm

echo "==> [5/9] 初始化数据库（沿用原库 root 空密码约定）"
# 项目原始环境 root 为空密码；这里保持一致，并建业务库
mysql -e "ALTER USER 'root'@'localhost' IDENTIFIED VIA mysql_native_password USING '';" 2>/dev/null || true
mysql -e "FLUSH PRIVILEGES;" 2>/dev/null || true
mysql -e "CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"
mysql -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';"
mysql -e "GRANT SELECT, INSERT, UPDATE, DELETE, CREATE TEMPORARY TABLES, EXECUTE ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';"
mysql -e "FLUSH PRIVILEGES;"

echo "==> [6/9] 写入后端 .env（凭据不进 git）"
mkdir -p "${APP_ROOT}/service/config"
cat > "${APP_ROOT}/service/config/.env" <<EOF
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=${DB_NAME}
DB_USER=${DB_USER}
DB_PASS=${DB_PASS}
DB_DEBUG=false
EOF
chown www-data:www-data "${APP_ROOT}/service/config/.env"
chmod 600 "${APP_ROOT}/service/config/.env"

echo "==> [7/9] 目录权限（上传目录可写）"
mkdir -p "${APP_ROOT}/service/konecms_ups"
chown -R www-data:www-data "${APP_ROOT}/service/konecms_ups"
chmod -R 755 "${APP_ROOT}/service/konecms_ups"
chown -R www-data:www-data "${APP_ROOT}/html0113/build"
chmod -R 755 "${APP_ROOT}/html0113/build"

echo "==> [8/9] 部署 Nginx 配置"
cp "${APP_ROOT}/deploy/nginx-bixingqiu.conf" /etc/nginx/sites-available/bixingqiu.conf
ln -sf /etc/nginx/sites-available/bixingqiu.conf /etc/nginx/sites-enabled/bixingqiu.conf
rm -f /etc/nginx/sites-enabled/default
nginx -t
systemctl reload nginx

echo "==> [9/9] 防火墙"
ufw --force enable
ufw allow 22/tcp
ufw allow 80/tcp
ufw allow 443/tcp

echo "==> 导入数据库（若已上传到 /tmp/bixingqiu.sql）"
if [ -f /tmp/bixingqiu.sql ]; then
  mysql "${DB_NAME}" < /tmp/bixingqiu.sql && echo "数据库已导入"
else
  echo "未找到 /tmp/bixingqiu.sql，稍后可用: mysql ${DB_NAME} < /tmp/bixingqiu.sql"
fi

echo ""
echo "=================================================="
echo " 初始化完成！"
echo " 前端根:  ${APP_ROOT}/html0113/build"
echo " 后端根:  ${APP_ROOT}/service"
echo " 访问:    http://<服务器IP>/"
echo " .env 中的 bxq_app 口令已随机生成（仅存于服务器）"
echo "=================================================="
