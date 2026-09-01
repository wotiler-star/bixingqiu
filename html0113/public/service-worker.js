/* 币星球 · 缓存型 Service Worker（替代原先的自毁版）
 * 目标：让回访/二次打开文章页「秒开」，绕过慢速 CDN 回源。
 *
 * 策略：
 *  - /static/ 哈希静态资源（js/css/字体/图片）：cache-first。文件名带内容哈希、服务端标了
 *    immutable，命中即返回（毫秒级），未命中再网络并写入缓存。永远不会serve到过期版本。
 *  - 导航/HTML 等：stale-while-revalidate。先返回缓存中的壳子（瞬时渲染），后台静默更新缓存，
 *    保证发版后最终能拿到新版；离线或边缘慢时也能用缓存壳子。
 *  - /service/ 后端接口：始终走网络（network-only），保证文章/评论/收藏等数据实时、不串旧数据。
 *  - 跨域请求（广告、百度统计等）与非 GET 请求：一律不拦截，保持原行为。
 */
const CACHE = 'bxq-cache-v2';
const STATIC_RE = /\/static\//;   // 哈希静态资源，长期缓存
const API_RE = /\/service\//;     // 后端接口，不缓存

self.addEventListener('install', (event) => {
  self.skipWaiting(); // 新版本立即生效，无需等旧页面关闭
  // [优化] 安装阶段预缓存导航壳（index.html 及各语种首页），首次访问后即可秒开/弱网可用
  event.waitUntil(
    caches.open(CACHE).then(c =>
      c.addAll(['/', '/index.html', '/zh-CN/']).catch(() => {})
    )
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys()
      .then(keys => Promise.all(
        keys.filter(k => k !== CACHE).map(k => caches.delete(k)) // 清掉旧缓存（含历史自毁版残留）
      ))
      .then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', (event) => {
  const req = event.request;
  if (req.method !== 'GET') return;            // 非 GET（POST 登录/收藏等）不碰
  const url = new URL(req.url);
  if (url.origin !== self.location.origin) return; // 跨域（广告/百度）不碰

  // 后端接口：网络优先，数据永远最新
  if (API_RE.test(url.pathname)) {
    event.respondWith(fetch(req));
    return;
  }

  // 哈希静态资源：cache-first
  if (STATIC_RE.test(url.pathname)) {
    event.respondWith(
      caches.match(req).then(cached =>
        cached || fetch(req).then(res => {
          if (res && res.status === 200) {
            const copy = res.clone();
            caches.open(CACHE).then(c => c.put(req, copy));
          }
          return res;
        }).catch(() => cached)
      )
    );
    return;
  }

  // 其余（HTML 导航等）：stale-while-revalidate
  event.respondWith(
    caches.match(req).then(cached => {
      const network = fetch(req).then(res => {
        if (res && res.status === 200) {
          const copy = res.clone();
          caches.open(CACHE).then(c => c.put(req, copy));
        }
        return res;
      }).catch(() => cached);
      return cached || network;
    })
  );
});
