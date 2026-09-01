import React from 'react';
import { render } from 'react-dom';
import { BrowserRouter, Switch, Route, Redirect } from 'react-router-dom';
import SeoManager from './SeoManager';
import { setLocale, LOCALES, DEFAULT_LOCALE } from './i18n/i18n';


import { Provider } from 'react-redux';
import store from './store/index';

import './static/css/Header.less';
import './static/css/Footer.less';

import Header from './component/Header';
import Footer from './component/Footer';
import Column from './routes/Column';
import MyDetail from './routes/MyDetail';
import Home from './routes/Home';
import List from './routes/ListPage';
import Detailed from './routes/Detailed';
import Livenews from './routes/Livenews';
import NavPage from './routes/NavPage';
import Author from './routes/Author';
import Login from './routes/Login';
import Register from './routes/Register';
import Personal from './routes/Personal';
import Details from './routes/Details';
import Apply from './routes/Apply';
import Search from './routes/search';

// 百度统计说明（已移除此处的 <GetBaidu> 组件）：
// 原实现把统计脚本包成 <GetBaidu> 组件塞进 <Footer> 的 children，
// 但 Footer.render 从不渲染 this.props.children，所以脚本永远不会被注入 —— 纯死代码；
// 且 `let _hmt = _hmt || []` 在 ES6 里是 TDZ 错误（访问未初始化的 let 变量）。
// public/index.html 已经在 <body> 内联注入了 hm.js，这里不能再注入一份，
// 否则同一次访问会加载两个 hm.js、PV 双计。故直接删除组件，统计由 index.html 单点负责。

// 多语言路由外壳：把 /:locale 前缀注入所有子路由，并驱动全局 currentLocale
class LocaleRouter extends React.Component {
  render() {
    const loc = this.props.match.params.locale;
    const valid = LOCALES.indexOf(loc) !== -1;
    // 关键：非法 locale 前缀（如站内历史链接 /Detailed?cataid=11&id=25，
    // 会被外层 /:locale 把 "Detailed" 误当语种）绝不能用 <Redirect to={字符串}> 跳转，
    // 因为字符串形式会丢弃 query string，详情页就拿不到 id 而白页。
    // 正确做法：按默认语种、以「无前缀 base」渲染同一套路由，query 天然保留。
    // canonical/hreflang 由 seo.js 统一收敛到 /<locale>/小写路径，故 SEO 无重复内容风险。
    setLocale(valid ? loc : DEFAULT_LOCALE); // 渲染前同步，保证 Header/Footer/SeoManager 拿到正确 locale
    const base = valid ? this.props.match.url : ''; // 如 /en；无语种前缀时为 ''
    return (
      <div>
        <SeoManager />
        <Header />
        <Switch>
          <Route path={base + '/home'} exact component={Home} />
          <Route path={base + '/column'} component={Column} />
          <Route path={base + '/mydetail'} component={MyDetail} />
          <Route path={base + '/list'} component={List} />
          <Route path={base + '/detailed'} component={Detailed} />
          <Route path={base + '/livenews'} component={Livenews} />
          <Route path={base + '/navpage'} component={NavPage} />
          <Route path={base + '/author'} component={Author} />
          <Route path={base + '/login'} component={Login} />
          <Route path={base + '/register'} component={Register} />
          <Route path={base + '/personal'} component={Personal} />
          <Route path={base + '/details'} component={Details} />
          <Route path={base + '/apply'} component={Apply} />
          <Route path={base + '/search'} component={Search} />
          <Redirect to={base + '/home'} />
        </Switch>
        <Footer />
      </div>
    );
  }
}

// 原实现直接用裸标识符 `root`，靠浏览器把 id="root" 的元素隐式挂到 window 上才没崩，
// 一旦宿主页面改 id 或在严格模式/非浏览器环境下就是 ReferenceError。这里显式获取并兜底创建。
var rootEl = document.getElementById('root');
if (!rootEl) {
  rootEl = document.createElement('div');
  rootEl.id = 'root';
  document.body.appendChild(rootEl);
}

render(<Provider store={store}>
  <BrowserRouter>
    <Switch>
      <Route path='/:locale' component={LocaleRouter} />
      <Redirect to='/zh-CN' />
    </Switch>
  </BrowserRouter>
</Provider>
  , rootEl);

window.onbeforeunload = function () {
  try {
    window.localStorage.removeItem('SEARCH');
  } catch (e) {
    // 隐私模式 / localStorage 被禁用时 removeItem 会抛异常，不能让它阻塞页面卸载
  }
}

// 注册缓存型 Service Worker：让回访/二次打开文章页「秒开」，绕过慢速 CDN 回源。
// 注意：URL 带 ?v=2 缓存破坏参数 —— 根 .htaccess 把 /service-worker.js 标了 immutable(1年)，
// 裸 URL 会被 CDN 永久缓存，改 SW 逻辑也无法生效；用新版号 URL 让浏览器/CDN 走全新键。
// 同时此 URL 与「竞争部署源」覆盖的裸 /service-worker.js 不同，互不干扰。
if ('serviceWorker' in navigator) {
  window.addEventListener('load', function () {
    navigator.serviceWorker.register('/service-worker.js?v=2')
      .catch(function (err) {
        // 注册失败（隐私模式/浏览器禁用 SW）不影响主站功能
        console.warn('SW register failed:', err);
      });
  });
}