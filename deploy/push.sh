#!/usr/bin/env bash
#
# 币星球 一键上传脚本（本地运行）
# 依赖：rsync + ssh（Windows 请用 Git Bash / WSL）
#
# 用法：
#   ./push.sh user@服务器IP          # 例如 ./push.sh root@45.76.123.45
#   ./push.sh root@45.76.123.45      # 首次部署
#
# Windows 用户：在 Git Bash 中运行，本地路径用 /f/bixingqiu
#
set -euo pipefail

REMOTE="${1:?用法: ./push.sh user@服务器IP}"

# 本地源码根（Windows Git Bash 下 F:/bixingqiu => /f/bixingqiu）
LOCAL="/f/bixingqiu"
APP_ROOT=/var/www/bixingqiu

echo "==> [1/5] 上传前端构建产物"
rsync -avz --delete \
  --exclude 'static/js/*.map' \
  "$LOCAL/html0113/build/" "${REMOTE}:${APP_ROOT}/html0113/build/"

echo "==> [2/5] 上传后端（排除 .git 与 .env，保留服务器上传目录 konecms_ups）"
rsync -avz --delete \
  --exclude '.git' \
  --exclude 'config/.env' \
  --exclude 'konecms_ups' \
  "$LOCAL/service/" "${REMOTE}:${APP_ROOT}/service/"

echo "==> [3/5] 上传部署工具包（server-init.sh / nginx 配置等）"
rsync -avz \
  "$LOCAL/deploy/" "${REMOTE}:${APP_ROOT}/deploy/"

echo "==> [4/5] 上传数据库 dump 并导入"
rsync -avz "$LOCAL/deploy/db/bixingqiu.sql" "${REMOTE}:/tmp/bixingqiu.sql"
ssh "${REMOTE}" "mysql ${DB_NAME:-k_k3_bixingqiu} < /tmp/bixingqiu.sql && echo imported"

echo "==> [5/5] 完成"
echo "访问 http://${REMOTE#*@}/"
