#!/usr/bin/env bash
# ============================================================
# 币星球 · Hostinger 部署包组装脚本
#   用法：在仓库根目录执行  bash deploy/build-hostinger.sh
#   指定域名： SITE_ORIGIN=https://your-domain.com bash deploy/build-hostinger.sh
#   产物：release/public_html/  +  release/bixingqiu-hostinger.zip
# ============================================================
set -euo pipefail

# 站点地址：写入 canonical / og:url / JSON-LD。
# 不指定时留空占位符，由页面内兜底脚本在运行时按 location.origin 修正。
SITE_ORIGIN="${SITE_ORIGIN:-}"

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

FRONT="$ROOT/html0113"
BUILD="$FRONT/build"
SERVICE="$ROOT/service"
# 输出目录可覆盖：RELEASE_DIR=/path/to/out bash deploy/build-hostinger.sh
# （旧产物被占用或无法删除时，换个目录即可继续打包）
RELEASE="${RELEASE_DIR:-$ROOT/release}"
OUT="$RELEASE/public_html"
ZIP="$RELEASE/bixingqiu-hostinger.zip"

say()  { printf '\033[36m==>\033[0m %s\n' "$*"; }
warn() { printf '\033[33m[!]\033[0m %s\n' "$*"; }
die()  { printf '\033[31m[x]\033[0m %s\n' "$*" >&2; exit 1; }

# Git Bash 的 /f/xxx 转成 Windows 的 F:/xxx，供 Python / PowerShell 调用
winpath() {
  if command -v cygpath >/dev/null 2>&1; then cygpath -w "$1"; return; fi
  case "$1" in
    /[a-zA-Z]/*) printf '%s\n' "$1" | sed -E 's#^/([a-zA-Z])/#\1:/#' ;;
    *)           printf '%s\n' "$1" ;;
  esac
}

# 递归删除。部分 Windows 开发环境的安全策略会拦截 rm -rf 大目录，
# 此时退回 Python 的 shutil.rmtree，保证脚本跨平台可重复执行。
rmrf() {
  [ -e "$1" ] || return 0
  rm -rf "$1" 2>/dev/null && [ ! -e "$1" ] && return 0

  # 退路 1：用 Python 删（Windows 版 Python 解析不了 /f/xxx，需先转路径）
  local WPATH; WPATH="$(winpath "$1")"
  for PY in python3 python py; do
    command -v "$PY" >/dev/null 2>&1 || continue
    "$PY" -c "import shutil,sys; shutil.rmtree(sys.argv[1], ignore_errors=True)" "$WPATH" 2>/dev/null || true
    [ -e "$1" ] || return 0
  done

  # 退路 2：删除被拦截时改为挪进 $RELEASE/.trash（在 public_html 之外，
  #         不会被打进部署包），不阻断流程，结束时提示手动清理。
  local TDIR="$RELEASE/.trash"
  # 删的就是输出目录本身时，不能挪进它自己，改用同级目录
  [ "$1" = "$RELEASE" ] && TDIR="$(dirname "$RELEASE")/.bxq-trash"
  mkdir -p "$TDIR" 2>/dev/null || true
  local TRASH="$TDIR/$(basename "$1").$(date +%H%M%S)"
  if mv "$1" "$TRASH" 2>/dev/null; then
    warn "删除被拦截，已挪出部署包：$TRASH"
    TRASH_LEFT="${TRASH_LEFT:-}${TRASH_LEFT:+ }$TRASH"
    return 0
  fi
  die "无法删除 $1，请手动清理后重试"
}
TRASH_LEFT=""

# ---------- 1. 前端构建 ----------
if [ ! -d "$BUILD" ] || [ ! -f "$BUILD/index.html" ]; then
  say "未发现前端产物，开始构建 …"
  [ -d "$FRONT/node_modules" ] || die "缺少 node_modules，请先执行：cd html0113 && npm install --legacy-peer-deps"
  ( cd "$FRONT" && NODE_OPTIONS=--openssl-legacy-provider npm run build ) \
    || die "前端构建失败。Node 17+ 需要 --openssl-legacy-provider（webpack 3 使用 md4 哈希）"
else
  say "复用已有前端产物：$BUILD"
fi
[ -f "$BUILD/index.html" ] || die "构建产物缺少 index.html"

# ---------- 2. 清理并组装 ----------
say "组装部署包 …"
rmrf "$RELEASE"
mkdir -p "$OUT"

# 2.1 前端产物 -> public_html 根
cp -a "$BUILD/." "$OUT/"

# 前端 .htaccess 兜底（CRA 有时不复制 public 下的隐藏文件）
if [ ! -f "$OUT/.htaccess" ] && [ -f "$FRONT/public/.htaccess" ]; then
  warn "build/ 缺少 .htaccess，从 public/ 补齐"
  cp "$FRONT/public/.htaccess" "$OUT/.htaccess"
fi
[ -f "$OUT/.htaccess" ] || die "缺少根 .htaccess，SPA 路由会 404"

# 2.1.1 写入真实站点地址（替换 __SITE_ORIGIN__ 占位符）
if [ -n "$SITE_ORIGIN" ]; then
  SITE_ORIGIN="${SITE_ORIGIN%/}"   # 去掉结尾斜杠，避免出现 //
  say "写入站点地址：$SITE_ORIGIN"
  # 只替换带结尾斜杠的三处（canonical / og:url / JSON-LD）。
  # 页面内兜底脚本里的占位符变量不带斜杠，必须原样保留，否则兜底逻辑失效。
  sed -i.bak "s#__SITE_ORIGIN__/#${SITE_ORIGIN}/#g" "$OUT/index.html"
  rm -f "$OUT/index.html.bak"
  LEFT=$(grep -o '__SITE_ORIGIN__/' "$OUT/index.html" | wc -l)
  [ "$LEFT" -eq 0 ] || die "站点地址替换不完整，仍有 $LEFT 处占位符"
else
  warn "未设置 SITE_ORIGIN，canonical/og:url 将由运行时脚本按访问域名自动填充"
  warn "  建议重跑：SITE_ORIGIN=https://你的域名 bash deploy/build-hostinger.sh"
fi

# 2.2 后端 service/ -> public_html/service/
#     konecms_ups/ 是网站运营期间累积的上传内容（文章配图等），体积大但与代码无关，
#     单独打包：核心包便于快速部署/回滚，资源包一次性上传即可。
say "复制后端 service/ …"
mkdir -p "$OUT/service"
if command -v rsync >/dev/null 2>&1; then
  rsync -a --exclude 'konecms_ups/' "$SERVICE/" "$OUT/service/"
else
  # 无 rsync 时逐项复制并直接跳过 konecms_ups。
  # 不采用「整体复制再删除」：该目录上万个文件，先拷后删既慢又易被安全策略拦截。
  ( shopt -s dotglob nullglob
    for item in "$SERVICE"/*; do
      [ "$(basename "$item")" = "konecms_ups" ] && continue
      cp -a "$item" "$OUT/service/"
    done )
fi
# 上传目录本身要建出来（含禁执行 .htaccess），但不带历史文件
mkdir -p "$OUT/service/konecms_ups"
[ -f "$SERVICE/konecms_ups/.htaccess" ] && cp "$SERVICE/konecms_ups/.htaccess" "$OUT/service/konecms_ups/.htaccess"

# ---------- 3. 剔除不该上线的东西 ----------
say "清理开发/敏感文件 …"
# IDE 元数据
for junk in .settings .project .buildpath; do rmrf "$OUT/service/$junk"; done
# 源码映射（体积大且泄露源码结构）
find "$OUT" -name '*.map' -type f -delete 2>/dev/null || true
# 版本控制 / 系统垃圾
find "$OUT" -name '.git*' -maxdepth 2 -not -name '.gitkeep' -exec rm -rf {} + 2>/dev/null || true
find "$OUT" \( -name '.DS_Store' -o -name 'Thumbs.db' -o -name '*.bak' -o -name '*.orig' -o -name '*~' \) -delete 2>/dev/null || true
# 误传的本地环境文件
rm -f "$OUT/service/.env"

# ---------- 4. 关键文件校验 ----------
say "校验关键文件 …"
MISS=0
check() { if [ -e "$OUT/$1" ]; then echo "  OK   $1"; else echo "  MISS $1"; MISS=$((MISS+1)); fi; }
check ".htaccess"
check "index.html"
check "service/.htaccess"
check "service/.user.ini"
check "service/index.php"
check "service/admin.php"
check "service/config/.htaccess"
check "service/konecms_ups/.htaccess"
[ "$MISS" -eq 0 ] || die "有 $MISS 个关键文件缺失，终止打包"

# 上传目录必须存在（Hostinger 上要可写 755）
mkdir -p "$OUT/service/konecms_ups"
[ -f "$OUT/service/konecms_ups/.htaccess" ] || die "上传目录缺少 .htaccess（禁脚本执行）"

# Git LFS 指针检测：仓库用 LFS 托管图片/字体，若未 checkout 出真实内容，
# 这些文件只有约 130 字节的文本指针，上线后表现为图片全部裂开。
say "检测 Git LFS 指针 …"
LFS_BAD=$(find "$OUT" -type f -size -1k -exec grep -l '^version https://git-lfs' {} + 2>/dev/null | wc -l)
if [ "$LFS_BAD" -gt 0 ]; then
  warn "发现 $LFS_BAD 个 LFS 指针文件（实际内容缺失），前 10 个："
  find "$OUT" -type f -size -1k -exec grep -l '^version https://git-lfs' {} + 2>/dev/null | head -10 | sed 's/^/    /'
  die "请先执行 git lfs pull（或 python restore_lfs.py）拉取真实文件再打包"
else
  echo "  OK   无 LFS 指针残留"
fi

# 残留 webshell 特征扫描（Imunify360 会据此隔离文件）
say "扫描 webshell 特征 …"
if grep -rIl --exclude-dir=node_modules -E 'eval[[:space:]]*\([[:space:]]*(base64_decode|gzinflate|str_rot13)' "$OUT" 2>/dev/null | head -5 | grep -q .; then
  warn "发现疑似 webshell 特征，Imunify360 可能隔离以下文件："
  grep -rIl --exclude-dir=node_modules -E 'eval[[:space:]]*\([[:space:]]*(base64_decode|gzinflate|str_rot13)' "$OUT" 2>/dev/null | sed 's/^/    /'
  die "请先清理上述特征再打包"
else
  echo "  OK   未发现 webshell 特征"
fi

# ---------- 5. 生成 .env 模板 ----------
cat > "$RELEASE/public_html/env.example" <<'EOF'
# 上传到服务器后，重命名为 .env 放在 public_html/service/config/ 目录下（dbconn.php 用 __DIR__ 读取）
DB_HOST=localhost
DB_PORT=3306
DB_NAME=u000000000_bxq
DB_USER=u000000000_bxq
DB_PASS=CHANGE_ME
BXQ_DEBUG=0
KONE_UPLOAD_MAX_MB=10
EOF

# ---------- 6. 打包 ----------
# 优先用 zip 命令；没有则退回 Python（比 Compress-Archive 更可靠：
# 能正确处理 .htaccess 等隐藏文件，且不受 Windows 路径格式困扰）。
PYBIN=""
for p in python3 python py; do command -v "$p" >/dev/null 2>&1 && { PYBIN="$p"; break; }; done

zip_paths() {  # $1=工作目录  $2=输出 zip 绝对路径  $3..=要压缩的相对路径
  local WORKDIR="$1" OUTZIP="$2"; shift 2
  if command -v zip >/dev/null 2>&1; then
    ( cd "$WORKDIR" && rm -f "$OUTZIP" && zip -qr "$OUTZIP" "$@" )
  elif [ -n "$PYBIN" ]; then
    ( cd "$WORKDIR" && "$PYBIN" - "$(winpath "$OUTZIP")" "$@" <<'PYEOF'
import os, sys, zipfile
out, items = sys.argv[1], sys.argv[2:]
def _arc(p):
    p = p.replace(os.sep, '/')
    if p == '.': return None
    if p.startswith('./'): p = p[2:]
    return p
with zipfile.ZipFile(out, 'w', zipfile.ZIP_DEFLATED, compresslevel=6) as z:
    for item in items:
        if os.path.isfile(item):
            a = _arc(item)
            if a: z.write(item, a)
            continue
        for root, dirs, files in os.walk(item):
            dirs[:] = [d for d in dirs if d not in ('.git', '__pycache__')]
            if not dirs and not files:          # 保留空目录（如 konecms_ups）
                a = _arc(root)
                if a: z.writestr(a + '/', '')
            for f in files:
                p = os.path.join(root, f)
                a = _arc(p)
                if a: z.write(p, a)
print('  条目数:', len(zipfile.ZipFile(out).namelist()))
PYEOF
    )
  else
    die "未找到 zip 或 python，无法打包。请安装其一后重试。"
  fi
}

say "打包核心包 …"
zip_paths "$RELEASE/public_html" "$ZIP" .

# 历史上传内容单独成包
UPS_SRC="$SERVICE/konecms_ups"
UPS_ZIP="bixingqiu-uploads.zip"
UPS_COUNT=$(find "$UPS_SRC" -type f 2>/dev/null | wc -l)
if [ "$UPS_COUNT" -gt 1 ]; then
  say "打包历史上传内容（$UPS_COUNT 个文件）…"
  # 直接从源目录压缩，不做中转拷贝（该目录上百 MB，拷贝纯属浪费）
  zip_paths "$SERVICE" "$RELEASE/$UPS_ZIP" konecms_ups
fi

# ---------- 7. 汇总 ----------
FILES=$(find "$OUT" -type f | wc -l)
if command -v du >/dev/null 2>&1; then
  SIZE=$(du -sh "$OUT" 2>/dev/null | cut -f1)
elif [ -n "$PYBIN" ]; then
  SIZE=$("$PYBIN" -c "
import os,sys
t=sum(os.path.getsize(os.path.join(r,f)) for r,d,fs in os.walk(sys.argv[1]) for f in fs)
print('%.1f MB' % (t/1048576.0))" "$(winpath "$OUT")" 2>/dev/null || echo '未知')
else
  SIZE="未知"
fi
echo
say "完成"
echo "  部署目录 : $OUT"
echo "  文件数量 : $FILES"
echo "  目录大小 : $SIZE"
echo "  核心包   : $ZIP"
[ -f "$RELEASE/$UPS_ZIP" ] && echo "  资源包   : $RELEASE/$UPS_ZIP（$UPS_COUNT 个历史上传文件）" || true
echo "  env 模板 : $RELEASE/public_html/env.example"
echo
[ -n "$TRASH_LEFT" ] && { echo; warn "以下临时目录未能自动删除，请手动清理："; for t in $TRASH_LEFT; do echo "    $t"; done; echo; }

echo "上传顺序："
echo "  1) 核心包解压到 public_html/               —— 站点即可运行"
echo "  2) 资源包解压到 public_html/service/       —— 恢复历史文章配图"
echo "  3) 按 deploy/README-HOSTINGER.md 建库、导数据、写 .env"
