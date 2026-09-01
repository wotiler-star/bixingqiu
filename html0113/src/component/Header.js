import React from 'react';
import { connect } from 'react-redux';
import { withRouter, NavLink } from 'react-router-dom';
import { Icon } from 'antd';
import axios from "axios";
import { t, localePath, getLocale, LOCALES, normalizeBase } from '../i18n/i18n';

// 关键修复：原代码写死字符串 "static\media\logo.png" / "static/media/avatar.png"。
// 这两个文件从未被任何模块 import，webpack 根本不会把它们输出到 build/static/media/，
// 实测构建产物里只有 5 个哈希图片，logo 与头像在线上是 404（站点 LOGO 直接不显示）；
// 而且相对路径在 /en/home 这种带语种前缀的 URL 下会解析成 /en/static/... 二次 404。
// 改为 ES import：由 url-loader 决定内联 base64（<10KB）或输出带哈希的绝对路径，永不 404。
import logoImg from '../static/image/logo.png';
import avatarImg from '../static/image/avatar.png';

// 语种显示名（与 i18n LOCALES 对齐）
const LOCALE_LABEL = {
  'zh-CN': '简体',
  'en': 'EN',
  'ja': '日本語',
  'zh-TW': '繁體'
};

// 主导航（数据驱动，避免手写重复 JSX 且保证每项都有 key）
const PRIMARY_NAV = [
  { key: 'nav.home', to: '/home', exact: true },
  { key: 'nav.livenews', to: '/livenews?cataid=1' },
  { key: 'nav.macro', to: '/list?cataid=3' },
  { key: 'nav.invest', to: '/list?cataid=87' },
  { key: 'nav.column', to: '/column?cataid=25' },
  { key: 'nav.stocks', to: '/list?cataid=88' },
  { key: 'nav.blockchain', to: '/list?cataid=31' },
  { key: 'nav.coin', to: '/list?cataid=2' }
];

const MORE_NAV = [
  { key: 'nav.industry', to: '/list?cataid=102' },
  { key: 'nav.elite', to: '/list?cataid=103' },
  { key: 'nav.innovation', to: '/list?cataid=104' },
  { key: 'nav.lifestyle', to: '/list?cataid=105' },
  { key: 'nav.fun', to: '/list?cataid=106' },
  { key: 'nav.promo', to: '/list?cataid=107' }
];

function readHid() {
  try {
    return window.localStorage.getItem('HID') || '';
  } catch (e) {
    return '';
  }
}

class Header extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      search: false,
      typeI: !!readHid(),
      image: null,
      langOpen: false
    };
    this.inputRef = null;
  }

  componentDidMount() {
    const id = readHid();
    this.setState({ typeI: !!id });
    // 原实现无论是否登录都请求 ajax_getInfo&hid=null，且直接读 res[0].picdir，
    // 未登录时 res 为空数组 -> TypeError（被 Promise 静默吞掉，但请求纯属浪费）。
    if (!id) return;
    axios.get(`${global.constants.winUrl}?c=h&a=ajax_getInfo&hid=${encodeURIComponent(id)}`)
      .then(res => {
        if (Array.isArray(res) && res[0] && res[0].picdir) {
          this.setState({ image: res[0].picdir });
        }
      })
      .catch(() => { /* 头像拉取失败保持默认头像即可，不应影响导航渲染 */ });
  }

  // 切换语种：保持当前 base path 与 query 不变，只换前缀，避免语言切换后回到首页
  switchLocale = (loc) => {
    const { location, history } = this.props;
    const base = normalizeBase(location.pathname || '/');
    const search = location.search || '';
    this.setState({ langOpen: false });
    history.push('/' + loc + base + search);
  };

  submitSearch = () => {
    const el = this.inputRef;
    if (!el) return;
    const value = String(el.value || '').trim();
    if (!value) return;
    try {
      window.localStorage.setItem('SEARCH', value);
    } catch (e) { /* storage 不可用不影响搜索 */ }
    el.value = '';
    this.setState({ search: false });
    // 原实现 push('/search') 丢掉了语种前缀 —— 在 /en/* 下搜索会跳回默认中文站；
    // 且关键词只写 localStorage、URL 里没有 w，结果页无法分享/刷新。
    this.props.history.push(localePath('/search?w=' + encodeURIComponent(value)));
  };

  render() {
    const { search, typeI, image, langOpen } = this.state;
    const cur = getLocale();
    return <section className='navBox'>
      <div className='interlayer'>
        <div className='content'>

          <div className='logBox'>
            <NavLink to={localePath('/home')}>
              <img src={logoImg} alt={t('page.home.title')} />
            </NavLink>
          </div>

          <div className="middle" style={{ display: search ? 'none' : 'block' }}>
            {PRIMARY_NAV.map(it => (
              <NavLink key={it.key} to={localePath(it.to)} exact={!!it.exact}>{t(it.key)}</NavLink>
            ))}
          </div>

          <div className='navFinally' style={{ display: search ? 'none' : 'block' }}>
            <span>...</span>
            <div>
              {MORE_NAV.map(it => (
                <NavLink key={it.key} to={localePath(it.to)}>{t(it.key)}</NavLink>
              ))}
            </div>
          </div>

          <div className='navRight'>
            <NavLink to={localePath(typeI ? '/personal' : '/login')}>
              <img src={image || avatarImg} alt={t(typeI ? 'nav.account' : 'nav.login')} />
            </NavLink>

            <div className='form'>
              <Icon type="form" theme="outlined" />
              <span>{t('nav.contribute')}</span>
              <div className='contributeBox'>
                <NavLink to={localePath(typeI ? '/personal/myi' : '/login')}>
                  {t('nav.article')}
                </NavLink>
              </div>
            </div>

            {/* 语言切换器：多语言站必须有可点击入口，否则 /en /ja /zh-TW 只能靠手改地址栏访问 */}
            <div className='bxq-lang'>
              <span className='bxq-lang-cur' onClick={() => this.setState({ langOpen: !langOpen })}>
                <Icon type="global" theme="outlined" />
                <em>{LOCALE_LABEL[cur] || cur}</em>
              </span>
              <div className='bxq-lang-menu' style={{ display: langOpen ? 'block' : 'none' }}>
                {LOCALES.map(loc => (
                  <a
                    key={loc}
                    className={loc === cur ? 'active' : ''}
                    href={'/' + loc + normalizeBase(this.props.location.pathname || '/') + (this.props.location.search || '')}
                    onClick={(ev) => { ev.preventDefault(); this.switchLocale(loc); }}
                  >{LOCALE_LABEL[loc] || loc}</a>
                ))}
              </div>
            </div>

            <div className='search'>
              <Icon type="search" theme="outlined" style={{ display: search ? 'none' : 'block' }}
                onClick={() => this.setState({ search: true })} />
              <div className='search-pop' style={{ display: search ? 'block' : 'none' }}>
                <Icon type="search" theme="outlined" onClick={this.submitSearch} />
                <input
                  type="text"
                  placeholder={t('nav.search')}
                  ref={(el) => { this.inputRef = el; }}
                  onKeyDown={(ev) => { if (ev.keyCode === 13) this.submitSearch(); }}
                />
                <Icon type="close" theme="outlined" onClick={() => this.setState({ search: false })} />
              </div>
            </div>

          </div>
        </div>
      </div>
    </section>;
  }
}
export default withRouter(connect()(Header));
