#!/usr/bin/env bash
#
# Vultr 自动开通 + 一键部署（需 Vultr API Token 与本地部署私钥）
# 依赖：curl / ssh / rsync / grep（Windows 用 Git Bash 运行）
#
# 用法：
#   export VULTR_TOKEN=你的Vultr_API_Token
#   ./vultr-create.sh
#
# 流程：加 SSH key -> 创建洛杉矶 $5 Ubuntu22.04 实例 -> 等待就绪 ->
#       上传代码+导库 -> 跑 server-init.sh 初始化 -> 输出访问地址
#
set -euo pipefail

TOKEN="${VULTR_TOKEN:?请先设置环境变量 VULTR_TOKEN（export VULTR_TOKEN=xxxx）}"
APP_ROOT=/var/www/bixingqiu
REGION=los-angeles          # 洛杉矶，对国内延迟最优
PLAN=vc2-1c-1gb             # $5/月，1 vCPU / 1 GB
OS_ID=1743                  # Ubuntu 22.04 LTS x64
SSH_PUB=~/.ssh/bxq_deploy_ed25519.pub
SSH_PRIV=~/.ssh/bxq_deploy_ed25519
API=https://api.vultr.com/v2

SSH_KEY="$(cat "$SSH_PUB")"

echo "==> [1] 添加 SSH key 到 Vultr"
KEY_RESP=$(curl -s -X POST "$API/ssh-keys" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d "{\"name\":\"bxq-deploy\",\"ssh_key\":\"$SSH_KEY\"}")
KEY_ID=$(echo "$KEY_RESP" | grep -o '"id":"[^"]*"' | head -1 | sed 's/"id":"//;s/"//')
[ -n "$KEY_ID" ] || { echo "SSH key 添加失败: $KEY_RESP"; exit 1; }
echo "    key_id=$KEY_ID"

echo "==> [2] 创建实例 ($REGION / $PLAN / Ubuntu22.04)"
INST_RESP=$(curl -s -X POST "$API/instances" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d "{\"region\":\"$REGION\",\"plan\":\"$PLAN\",\"os_id\":$OS_ID,\"label\":\"bixingqiu\",\"sshkey_id\":[\"$KEY_ID\"],\"backups\":\"disabled\",\"enable_ipv6\":true}")
INST_ID=$(echo "$INST_RESP" | grep -o '"id":"[^"]*"' | head -1 | sed 's/"id":"//;s/"//')
[ -n "$INST_ID" ] || { echo "实例创建失败: $INST_RESP"; exit 1; }
echo "    instance_id=$INST_ID"

echo "==> [3] 等待实例就绪（轮询 main_ip）..."
IP=""
for i in $(seq 1 40); do
  sleep 10
  INFO=$(curl -s "$API/instances/$INST_ID" -H "Authorization: Bearer $TOKEN")
  IP=$(echo "$INFO" | grep -o '"main_ip":"[^"]*"' | head -1 | sed 's/"main_ip":"//;s/"//')
  if echo "$INFO" | grep -q '"status":"active"'; then break; fi
  echo "    尝试 $i: ip='$IP'"
done
[ -n "$IP" ] || { echo "未能获取 IP"; exit 1; }
echo "    实例就绪 IP=$IP"

echo "==> [4] 等待 SSH 可用"
for i in $(seq 1 30); do
  if ssh -i "$SSH_PRIV" -o StrictHostKeyChecking=no -o ConnectTimeout=5 root@$IP "true" 2>/dev/null; then
    echo "    SSH 已就绪"; break
  fi
  sleep 10
done

echo "==> [5] 上传代码并初始化"
"$(dirname "$0")/push.sh" "root@$IP"
ssh -i "$SSH_PRIV" -o StrictHostKeyChecking=no root@$IP "bash ${APP_ROOT}/deploy/server-init.sh"

echo "=================================================="
echo " 部署完成！访问 http://$IP/"
echo " 实例ID: $INST_ID"
echo " 计费: 按小时，不用时删除以免持续收费："
echo "   curl -X DELETE $API/instances/$INST_ID -H 'Authorization: Bearer \$VULTR_TOKEN'"
echo "=================================================="
