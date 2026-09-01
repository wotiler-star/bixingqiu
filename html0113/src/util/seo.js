// 轻量 SEO 助手：在客户端动态更新 <title>、描述、Open Graph、canonical、hreflang。
// 不依赖 react-helmet，避免新增依赖与构建风险。
import { LOCALES, X_DEFAULT_LOCALE, normalizeBase, getLocale } from '../i18n/i18n';

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

// 注入成对互引的 hreflang（Google 多语言 SEO 必需）。每个页面声明所有语种版本 + x-default。
function upsertHreflang(path) {
  if (typeof document === 'undefined') return;
  const origin = (window.location && window.location.origin) ? window.location.origin : '';
  const base = normalizeBase(path || window.location.pathname); // /home（已小写、已去语种前缀）
  // 先清掉旧的，避免重复叠加
  const old = document.head.querySelectorAll('link[rel="alternate"][hreflang]');
  for (let i = 0; i < old.length; i++) old[i].parentNode.removeChild(old[i]);
  const mk = (hreflang, href) => {
    const l = document.createElement('link');
    l.setAttribute('rel', 'alternate');
    l.setAttribute('hreflang', hreflang);
    l.setAttribute('href', href);
    document.head.appendChild(l);
  };
  LOCALES.forEach((loc) => mk(loc, origin + '/' + loc + base));
  mk('x-default', origin + '/' + X_DEFAULT_LOCALE + base);
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

  // canonical / og:url 跟随当前域名自动拼接路径（含 locale 前缀），部署到任意域名都正确
  const origin = (typeof window !== 'undefined' && window.location && window.location.origin)
    ? window.location.origin
    : '';
  // canonical 强制收敛为「/<locale> + 小写 base + query」的唯一形式：
  // 即使用户从 /Detailed?id=1（无语种前缀、大写）进入，canonical 也指向 /zh-CN/detailed?id=1
  const rawPath = path || (typeof window !== 'undefined' && window.location ? window.location.pathname : '/');
  const url = origin + '/' + getLocale() + normalizeBase(rawPath);
  let can = document.head.querySelector('link[rel="canonical"]');
  if (!can) {
    can = document.createElement('link');
    can.setAttribute('rel', 'canonical');
    document.head.appendChild(can);
  }
  can.setAttribute('href', url);
  upsertMeta('meta[property="og:url"]', 'property', 'og:url', url);

  // 多语言 hreflang 互链
  upsertHreflang(path);
}

export default setSEO;

