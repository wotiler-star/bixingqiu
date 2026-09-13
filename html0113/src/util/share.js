// 社交分享链接构造工具
// 微信无网页分享入口，公众号二维码由站点静态资源（wechatQr）提供，故这里只处理微博 / QQ。
// 所有链接均在新窗口打开，避免离开当前文章页。

export function socialShareUrl(platform, url, title) {
  const u = encodeURIComponent(url || '');
  const t = encodeURIComponent(title || '');
  if (platform === 'weibo') {
    return `https://service.weibo.com/share/share.php?url=${u}&title=${t}`;
  }
  if (platform === 'qq') {
    return `https://connect.qq.com/widget/shareqq/index.html?url=${u}&title=${t}`;
  }
  // 未知平台返回原 URL，调用方自行处理（如微信二维码弹层）
  return url || '';
}

export function openShare(platform, url, title) {
  const target = socialShareUrl(platform, url, title);
  if (target) {
    window.open(target, '_blank', 'noopener,noreferrer');
  }
}

// 取当前页面用于分享的 URL 与标题（SPA 路由页，window.location.href 已含 locale 前缀）
export function currentShareContext(titleFallback) {
  const url = (typeof window !== 'undefined' && window.location && window.location.href) || '';
  const title = (typeof document !== 'undefined' && (document.title || '')) || titleFallback || '';
  return { url, title };
}
