// 轻量 HTML 净化器：在 dangerouslySetInnerHTML 渲染后端富文本（文章正文）之前，
// 过滤 <script>/<iframe> 等危险标签、on* 事件处理器以及 javascript:/vbscript:/data:text/html 危险协议，
// 降低存储型 XSS 风险，同时保留常见排版标签（p/br/img/a/b/ul/li/h1-h6/span/div 等）。
const DANGER_TAGS = [
  'script', 'iframe', 'object', 'embed', 'style', 'link', 'meta', 'base',
  'form', 'input', 'button', 'textarea', 'select', 'option', 'svg', 'math',
  'frame', 'frameset', 'applet'
];

export function sanitizeHtml(html) {
  if (html === null || html === undefined) return '';
  if (typeof window === 'undefined' || typeof window.DOMParser === 'undefined') {
    // 非浏览器环境（如 SSR/测试）直接按字符串返回，避免报错
    return String(html);
  }
  const doc = new window.DOMParser().parseFromString(String(html), 'text/html');

  // 1) 移除危险标签
  DANGER_TAGS.forEach(tag => {
    const nodes = doc.querySelectorAll(tag);
    for (let i = 0; i < nodes.length; i++) {
      if (nodes[i].parentNode) nodes[i].parentNode.removeChild(nodes[i]);
    }
  });

  // 2) 清理所有元素上的危险属性
  const all = doc.querySelectorAll('*');
  for (let i = 0; i < all.length; i++) {
    const el = all[i];
    const attrs = el.attributes;
    for (let j = attrs.length - 1; j >= 0; j--) {
      const name = attrs[j].name.toLowerCase();
      const value = (attrs[j].value || '').toLowerCase().trim();
      if (name.indexOf('on') === 0) {
        el.removeAttribute(attrs[j].name);
      } else if (
        (name === 'href' || name === 'src' || name === 'xlink:href') &&
        (value.indexOf('javascript:') === 0 || value.indexOf('vbscript:') === 0 ||
          value.indexOf('data:text/html') === 0)
      ) {
        el.removeAttribute(attrs[j].name);
      }
    }
    // 3) 清理 style 中的 expression / 动态脚本
    if (el.hasAttribute('style')) {
      const style = el.getAttribute('style') || '';
      if (/javascript:/i.test(style) || /expression\s*\(/i.test(style)) {
        el.removeAttribute('style');
      }
    }
  }

  return doc.body ? doc.body.innerHTML : '';
}

export default sanitizeHtml;
