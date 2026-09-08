/**
 * copy-service.js
 * 自愈改造（self-heal）：webpack 构建完成后，把仓库根的 service/（后端 PHP + 用户上传真实图片）拷进 build/，
 * 随前端产物一起被 Hostinger Git 自动部署发布到 public_html/service/。
 * 这样即使自动部署清空 public_html，每次部署后后端与用户上传都会自动复活。
 *
 * 关于 konecms_ups（用户上传目录）：
 *  - 历史上仓库里它是 Git LFS 指针（130B），直接拷贝只会得到坏文件；
 *  - 现改为「真实 blob」存储：仓库 .gitattributes 末尾对 `service/konecms_ups/**` 关闭 LFS，
 *    图片以真实字节入库，构建时这里从仓库源码直接拷进 build/service/konecms_ups，随部署自愈。
 *
 * 排除项：仅 .env（含 DB 凭据，绝不进构建产物）；以及 .git/.settings/.buildpath/.project/node_modules 等无关文件。
 */
'use strict';

const fs = require('fs');
const path = require('path');

const SERVICE_SRC = path.resolve(__dirname, '..', '..', 'service'); // 仓库根/service
const BUILD_DEST = path.resolve(__dirname, '..', 'build', 'service'); // html0113/build/service

const EXCLUDE_NAMES = new Set([
  '.env',
  '.git',
  '.settings',
  '.buildpath',
  '.project',
  'node_modules',
  '.bxq-trash',
]);

function copyRecursive(src, dest) {
  const name = path.basename(src);
  if (EXCLUDE_NAMES.has(name)) return;

  const stat = fs.lstatSync(src);
  if (stat.isSymbolicLink()) return; // 不跟随符号链接，避免环路
  if (stat.isDirectory()) {
    if (!fs.existsSync(dest)) fs.mkdirSync(dest, { recursive: true });
    for (const entry of fs.readdirSync(src)) {
      copyRecursive(path.join(src, entry), path.join(dest, entry));
    }
  } else if (stat.isFile()) {
    fs.mkdirSync(path.dirname(dest), { recursive: true });
    fs.copyFileSync(src, dest);
  }
}

let copied = 0;
try {
  if (fs.existsSync(SERVICE_SRC)) {
    copyRecursive(SERVICE_SRC, BUILD_DEST);

    // 统计拷贝文件数
    const walk = (d) => {
      for (const e of fs.readdirSync(d)) {
        const p = path.join(d, e);
        const s = fs.statSync(p);
        if (s.isDirectory()) walk(p);
        else if (s.isFile()) copied++;
      }
    };
    if (fs.existsSync(BUILD_DEST)) walk(BUILD_DEST);
    console.log('[copy-service] ✓ synced service/ -> build/service/ (' + copied + ' files, incl. konecms_ups real uploads)');
  } else {
    console.warn('[copy-service] ✗ source not found: ' + SERVICE_SRC);
  }
} catch (e) {
  // 任何异常都不阻断前端构建
  console.warn('[copy-service] skipped due to error: ' + (e && e.message ? e.message : e));
}
