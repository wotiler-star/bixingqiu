/**
 * copy-service.js
 * 自愈改造（self-heal）：在 webpack 构建完成后，把仓库根目录的 service/（后端 PHP）
 * 拷贝进 build/，使其随前端产物一起被 Hostinger Git 自动部署发布到 public_html/service/。
 *
 * 这样即使自动部署会清空 public_html，每次部署后后端也会自动复活，不再需要手动 FTP 补传。
 *
 * 排除项（不进构建产物）：
 *  - .env            含数据库凭据，绝不进仓库/构建产物
 *  - konecms_ups/    用户上传目录（体积大，且应通过 FTP/持久存储单独维护）
 *  - .git/.settings/.buildpath/.project/node_modules 等无关文件
 */
'use strict';

const fs = require('fs');
const path = require('path');

const SERVICE_SRC = path.resolve(__dirname, '..', '..', 'service'); // 仓库根/service
const BUILD_DEST = path.resolve(__dirname, '..', 'build', 'service'); // html0113/build/service

const EXCLUDE_NAMES = new Set([
  '.env',
  'konecms_ups',
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
    console.log('[copy-service] ✓ synced service/ -> build/service/ (' + copied + ' files)');
  } else {
    console.warn('[copy-service] ✗ source not found: ' + SERVICE_SRC);
  }
} catch (e) {
  // 任何异常都不阻断前端构建
  console.warn('[copy-service] skipped due to error: ' + (e && e.message ? e.message : e));
}
