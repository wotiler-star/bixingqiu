// 轻量 i18n 运行时 —— 兼容 React 16.5（无 hooks / 无 contextType）
// 设计：全局 currentLocale 由路由（LocaleRouter）在 render 前 setLocale 驱动，
//       组件直接用 t(key) 取当前语种文案，无需 Context/Provider，零新增构建风险。
import { translations } from './translations';

export const LOCALES = ['zh-CN', 'en', 'ja', 'zh-TW'];
export const DEFAULT_LOCALE = 'zh-CN';
export const X_DEFAULT_LOCALE = 'zh-CN';

let currentLocale = DEFAULT_LOCALE;

export function setLocale(loc) {
  if (LOCALES.indexOf(loc) !== -1) currentLocale = loc;
  else currentLocale = DEFAULT_LOCALE;
}

export function getLocale() {
  return currentLocale;
}

// 取翻译；缺失 key 回退到 zh-CN，再回退到 key 本身（永不崩）
export function t(key, vars) {
  const dict = translations[currentLocale] || translations[DEFAULT_LOCALE];
  let s = (dict && dict[key] != null) ? dict[key]
    : (translations[DEFAULT_LOCALE][key] != null ? translations[DEFAULT_LOCALE][key] : key);
  if (vars && typeof s === 'string') {
    for (const k in vars) {
      s = s.split('{' + k + '}').join(vars[k]);
    }
  }
  return s;
}

// 拼接带 locale 前缀的站内路径：localePath('/home') => '/en/home'
// 保留 query string：localePath('/list?cataid=3') => '/en/list?cataid=3'
export function localePath(p) {
  const loc = getLocale();
  const [path, q] = String(p).split('?');
  const clean = path.startsWith('/') ? path : '/' + path;
  return '/' + loc + clean + (q ? '?' + q : '');
}

// 从完整 pathname 去掉 locale 前缀，得到 base path（供 hreflang/sitemap 复用）
// '/en/home' => '/home' ； '/home' => '/home' ； '/en/list?cataid=3' => '/list?cataid=3'
export function stripLocale(pathname) {
  const seg = String(pathname).split('?');
  const base = seg[0];
  const q = seg[1];
  const parts = base.split('/').filter(Boolean);
  let bp;
  if (parts.length >= 1 && LOCALES.indexOf(parts[0]) !== -1) {
    bp = '/' + parts.slice(1).join('/');
  } else {
    bp = '/' + parts.join('/');
  }
  if (bp === '/') bp = '/home';
  return q ? bp + '?' + q : bp;
}

// 规范化 base path：去 locale 前缀 + 路径部分统一小写（query 原样保留，值不可小写）
// 用途：站内历史链接大小写混用（/Detailed 与 /detailed 指向同一文章），
//       canonical/hreflang 必须收敛到唯一小写形式，否则搜索引擎判定重复内容。
// '/en/Detailed?id=25&cataid=11' => '/detailed?id=25&cataid=11'
export function normalizeBase(pathname) {
  const s = stripLocale(pathname);
  const i = s.indexOf('?');
  const p = (i === -1 ? s : s.slice(0, i)).toLowerCase();
  const q = i === -1 ? '' : s.slice(i);
  return p + q;
}

// 当前 path 的各 locale 版本 URL（origin 由调用方提供）
export function localeUrls(pathname, origin) {
  const base = normalizeBase(pathname);
  const out = {};
  LOCALES.forEach((l) => { out[l] = origin + '/' + l + base; });
  out['x-default'] = origin + '/' + X_DEFAULT_LOCALE + base;
  return out;
}
