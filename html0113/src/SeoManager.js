import React from 'react';
import { withRouter } from 'react-router-dom';
import { setSEO } from './util/seo';
import { t, normalizeBase } from './i18n/i18n';

// 各路由对应的 SEO 元信息（翻译 key，按当前 locale 由 t() 取译文）。
// base path 去 locale 前缀后匹配（/en/home -> /home）。新增页面时在此补充即可。
const PAGE_META = {
  '/home': { title: 'page.home.title', description: 'page.home.desc' },
  '/column': { title: 'page.column.title', description: 'page.column.desc' },
  '/author': { title: 'page.author.title', description: 'page.author.desc' },
  '/list': { title: 'page.list.title', description: 'page.list.desc' },
  '/detailed': { title: 'page.detailed.title', description: 'page.detailed.desc' },
  '/details': { title: 'page.details.title', description: 'page.details.desc' },
  '/livenews': { title: 'page.livenews.title', description: 'page.livenews.desc' },
  '/navpage': { title: 'page.navpage.title', description: 'page.navpage.desc' },
  '/apply': { title: 'page.apply.title', description: 'page.apply.desc' },
  '/search': { title: 'page.search.title', description: 'page.search.desc' },
  '/login': { title: 'page.login.title', description: 'page.login.desc' },
  '/register': { title: 'page.register.title', description: 'page.register.desc' },
  '/personal': { title: 'page.personal.title', description: 'page.personal.desc' },
  '/mydetail': { title: 'page.mydetail.title', description: 'page.mydetail.desc' },
};

class SeoManager extends React.Component {
  componentDidMount() {
    this.apply();
  }

  componentDidUpdate(prevProps) {
    // 路径或 query 变化都要重算（详情页 /detailed?id=1 -> ?id=2 仅 query 变）
    const a = (this.props.location.pathname || '') + (this.props.location.search || '');
    const b = (prevProps.location.pathname || '') + (prevProps.location.search || '');
    if (a !== b) {
      this.apply();
    }
  }

  apply() {
    // 保留 query string：详情页 /en/detailed?id=123 的 canonical/hreflang 必须带 id
    const full = (this.props.location.pathname || '/') + (this.props.location.search || '');
    // 查 PAGE_META 必须用「不含 query」的纯路径。
    // 原实现直接用带 query 的 full 去查表，而表里的键（/detailed、/list…）都不带 query，
    // 于是 /detailed?id=1、/list?cataid=11、/search?w=btc 等全部 miss，
    // 统统回落到 '/home' 的 title/description —— 全站内容页 <title>/meta 重复，SEO 事故。
    // normalizeBase 只吃 pathname（不含 query），返回去 locale 前缀 + 全小写的 base path，
    // 与 canonical/hreflang 用的归一化规则完全一致，避免大小写混用链接（/Detailed）查不到 meta。
    const key = normalizeBase(this.props.location.pathname || '/');
    const meta = PAGE_META[key] || PAGE_META['/home'];
    setSEO({ title: t(meta.title), description: t(meta.description), path: full });
  }

  render() {
    return null;
  }
}

export default withRouter(SeoManager);
