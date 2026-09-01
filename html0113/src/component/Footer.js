import React from 'react';
import { connect } from 'react-redux';
import { withRouter } from 'react-router-dom';
import { Icon } from 'antd';
import axios from 'axios';
import { t } from '../i18n/i18n';
// 原代码把微信二维码指向 https://www.huoxing24.com/img/hx-ewm-c6929e3815.png ——
// 盗链第三方站点资源，对方一改路径就 404，且把访客 referer 泄露给外站。
// 本仓库 src/static/image/ 下就有同名文件，改为本地打包引用。
import wechatQr from '../static/image/hx-ewm-c6929e3815.png';

// 友情链接的 url 字段可能已带协议（http:// 或 https://），
// 原实现无条件前缀 'http://' 会拼出 http://https://xxx 这种废链接；
// 且强制 http 在 HTTPS 站点上是混合内容。统一归一化为 https。
function normalizeExternalUrl(url) {
  const s = String(url || '').trim();
  if (!s) return '';
  if (/^https:\/\//i.test(s)) return s;
  if (/^http:\/\//i.test(s)) return s.replace(/^http:\/\//i, 'https://');
  if (/^\/\//.test(s)) return 'https:' + s;
  return 'https://' + s;
}

class Footer extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      linkD: null,
      data: null
    };
  }

  componentDidMount() {
    axios.get(`${global.constants.winUrl}?a=link`).then(res => {
      this.setState({ linkD: Array.isArray(res) ? res : [] });
    }).catch(() => this.setState({ linkD: [] }));
    axios.get(`${global.constants.winUrl}?a=usual`).then(res => {
      this.setState({ data: Array.isArray(res) ? res : [] });
    }).catch(() => this.setState({ data: [] }));
  }

  render() {
    const { linkD, data } = this.state;
    const site = (data && data.length) ? data[0] : null;
    return <section className='footer'>
      <div className='main'>
        <div className='topBox'>
          <div className='friendship-link'>
            <h3>{t('footer.friendlinks')}</h3>
            <div>
              {linkD ? linkD.map((item, index) => {
                const href = normalizeExternalUrl(item.url);
                if (!href) return null;
                return <a
                  href={href}
                  key={item.id || index}
                  target="_blank"
                  rel="noopener noreferrer nofollow"
                >
                  {item.title}
                  <span></span>
                </a>;
              }) : null}
            </div>
          </div>

          {/* 站点信息（usual_tb）只有一行，原实现用 map 渲染整块且没有 key，
              一旦后台多加一行就会出现重复页脚。这里显式取第一行。 */}
          {site ? <div className='cooperation'>
            <h3>{t('footer.ad')}</h3>
            <p>QQ：{site.qq}(同微信) &nbsp;&nbsp;手机：{site.mobile}</p>
            <p>商务邮箱：{site.email}</p>
            <p>投稿邮箱：{site.email}</p>
          </div> : null}

        </div>

        {site ? <div className='bottomBox'>
          <div className='footerLog'>
            <img src={site.picdir_logo} alt={site.sitename || ''} />
            <p>
              {site.ensitename}
              <br />
              {/* 原为 <a href="/#">：备案号点击后跳到无意义的锚点。
                  按工信部要求，备案号应链接到备案查询系统。 */}
              <a href="https://beian.miit.gov.cn/" target="_blank" rel="noopener noreferrer nofollow">
                {site.enicp}
              </a>
            </p>
          </div>
          <div className='footer-right'>
            <div className='footer-share'>
              {/* 原为 <NavLink to={localePath('/#')}>：点击会跳到 /<locale>/ 再被重定向回首页，
                  纯属误导。这三个只是 hover 展示入口，改为非导航元素。 */}
              <span className='footer-share-item'>
                <Icon type="wechat" theme="filled" />
                <div className='footer-wechat'>
                  <img src={wechatQr} alt={site.sitename || ''} />
                  <span>{site.sitename}</span>
                  <div></div>
                </div>
              </span>
              <span className='footer-share-item'>
                <Icon type="dingding" theme="outlined" />
              </span>
              <span className='footer-share-item'>
                <Icon type="weibo" theme="outlined" />
              </span>
            </div>
            <div className='footer-right-b'>
              {/* 站内没有「关于我们 / 版权声明」路由，原先都指向死锚点 /#。
                  暂以纯文本呈现，避免产生指向首页的无效爬取路径与误点击。 */}
              <span className='footer-text'>{t('footer.about')}</span>
              <span></span>
              <span className='footer-text'>{t('footer.copyright')}</span>
              <span></span>
            </div>
          </div>
        </div> : null}

      </div>
    </section>;
  }
}

export default withRouter(connect()(Footer));
