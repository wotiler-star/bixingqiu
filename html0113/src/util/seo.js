// 轻量 SEO 助手：在客户端动态更新 <title>、描述、Open Graph、canonical。
// 不依赖 react-helmet，避免新增依赖与构建风险。

function upsertMeta(selector, attr, key, value) {
  let el = document.head.querySelector(selector);
  if (!el) {
    el = document.createElement('meta');
    if (attr === 'name') el.setAttribute('name', key);
    else el.setAttribute('property', key);
    document.head.appendChild(el);
  }
  el.setAttribute('content', value);
}

export function setSEO({ title, description, path } = {}) {
  if (title) {
    document.title = title;
    upsertMeta('meta[property="og:title"]', 'property', 'og:title', title);
    upsertMeta('meta[name="twitter:title"]', 'name', 'twitter:title', title);
  }

  if (description) {
    upsertMeta('meta[name="description"]', 'name', 'description', description);
    upsertMeta('meta[property="og:description"]', 'property', 'og:description', description);
    upsertMeta('meta[name="twitter:description"]', 'name', 'twitter:description', description);
  }

  // canonical / og:url 跟随当前域名自动拼接路径，部署到任意域名都正确
  const origin = (typeof window !== 'undefined' && window.location && window.location.origin)
    ? window.location.origin
    : '';
  const url = origin + (path || (typeof window !== 'undefined' && window.location ? window.location.pathname : '/'));
  let can = document.head.querySelector('link[rel="canonical"]');
  if (!can) {
    can = document.createElement('link');
    can.setAttribute('rel', 'canonical');
    document.head.appendChild(can);
  }
  can.setAttribute('href', url);
  upsertMeta('meta[property="og:url"]', 'property', 'og:url', url);
}

export default setSEO;
