import React from 'react';
import ReactDOM, {render} from 'react-dom';
import {Icon, Input, Button} from 'antd';
import {NavLink} from 'react-router-dom';
import axios from 'axios';
import '../static/css/Detailed.less';
import Qs from 'qs';
import sanitizeHtml from '../util/sanitize';
import { setSEO } from '../util/seo';

import { AVATAR_PLACEHOLDER, COVER_PLACEHOLDER } from '../static/placeholder';
import { localePath } from '../i18n/i18n';
import wechatQr from '../static/image/hx-ewm-c6929e3815.png'; // 修复：原为硬编码 static/media 字面量，webpack 未打包该文件 -> 线上 404
const {TextArea} = Input;

class Detailed extends React.Component {
  constructor(props, context) {
    super(props, context);
    this.state = {
      tabSwitch: false,
      nextActive: false,
      goTop: false,
      aboutArr: null,
      hotArr: null,
      nextArr: null,
      feedArr: null,
      index: null,
      id: null,
      cataid: null,
      title: null,
      hid: null,
      thid: null,
      data: null,
      i: null,
      favorate: null,
      feednum: null,
      favid:null,
      loading: true       // [修复] 新增加载状态，数据返回前显示占位、避免底部内容先闪出
    }
  }

  async componentWillMount() {
    document.getElementById('root').scrollIntoView(true);//为ture返回顶部，false为底部
  }

  async componentDidMount() {
    // [修复] 首屏 loading=true 时正文分支未渲染，this.refs.top 为 undefined，
    // 直接读 .offsetHeight 会抛 TypeError，导致 fetchArticle() 与兜底 setTimeout 都不执行 ->
    // loading 永远为 true、永久「加载中」。改为安全取值，并在滚动时实时重算文章高度。
    const getTop = () => (this.refs.top ? this.refs.top.offsetHeight : 0);
    window.onscroll = () => {
      let scr = document.documentElement.scrollTop || document.body.scrollTop;
      let top = getTop();
      scr > 1200 ? this.setState({nextActive: true}) : this.setState({nextActive: false});
      scr > top ? this.setState({goTop: true}) : this.setState({goTop: false});
    };
    this.fetchArticle();
    // [修复] 兜底：请求若挂起（网络异常/CDN 拦截）15 秒后强制结束加载态，
    // 避免用户永远停在「加载中」。此时若 data 仍为空会走「文章不存在或加载失败」分支；
    // 若请求随后才返回，setState 会补上数据并正常渲染正文。
    this._loadTimer = setTimeout(() => {
      if (this.state.loading) this.setState({ loading: false });
    }, 15000);
  }

  componentWillUnmount() {
    if (this._loadTimer) clearTimeout(this._loadTimer);
  }

  // 文章之间跳转（/detailed?id=1 -> ?id=2）只变 query，不会重新挂载组件。
  // 原代码靠链接上的 window.location.reload(true) 硬刷新换文章，但该 reload 已被
  // index.html 里的 shim 中和（避免白闪），因此必须在此监听 query 变化重新拉取，
  // 否则会出现「URL 变了、正文还是上一篇」。
  componentDidUpdate(prevProps) {
    if (prevProps.location.search !== this.props.location.search) {
      const el = document.getElementById('root');
      if (el) el.scrollIntoView(true);
      this.fetchArticle();
    }
  }

  fetchArticle() {
    let hid = window.localStorage.getItem('HID'),
        {location: {search}} = this.props,
        {id = 0} = Qs.parse(search.substr(1)) || {},
        {cataid = 0} = Qs.parse(search.substr(1)) || {};
    id = parseFloat(id);

    axios.get(`${global.constants.winUrl}?c=Content&cataid=${cataid}&id=${id}&hid=${hid == undefined ? 0 : hid}`).then(res => {
      console.log(res);

      const _ad = (res.data && res.data.length) ? res.data[0] : null;
      const _title = _ad ? _ad.title : '';
      const _short = _ad ? _ad.short : '';
      // 用真实文章标题/摘要覆盖通用 SEO，并把带 id 的完整路径传入以保证 canonical/hreflang 正确
      if (_title) {
        setSEO({
          title: _title,
          description: _short,
          path: this.props.location.pathname + (this.props.location.search || '')
        });
      }

      this.setState({
        aboutArr: res.aboutArr,
        feedArr: res.feedArr,
        hotArr: res.hotArr,
        nextArr: res.nextArr,
        index: res.feedArr ? res.feedArr.length : 0,
        thid: hid ? true : false,
        hid: hid,
        id: id,
        cataid: cataid,
        data: res.data,
        title: _title,
        i: (res.nextArr && res.nextArr.length) ? res.nextArr[0] : null,
        favorate: (res.data && res.data.length) ? res.data[0].favorate : 0,
        feednum: (res.data && res.data.length) ? res.data[0].feednum : 0,
        favid: (res.data && res.data.length) ? res.data[0].favid : 0,
        loading: false
      });
    }).catch(() => {
      // [修复] 请求失败也要关闭 loading，避免用户一直看白屏
      this.setState({loading: false});
    });
  }

  render() {
    return <section className='detailed'>

      {this.state.loading ? (
        <div className='detail-loading'>
          <Icon type="loading" theme="outlined" />
          <p>加载中...</p>
        </div>
      ) : (this.state.data && this.state.data.length) ? (
      <React.Fragment>
      <div className='main' ref={'top'}>
        {/* <div className='activity'>
        <img src={COVER_PLACEHOLDER}
          alt="" />
      </div>*/}
        <div className='left-content'>
          {this.state.data ? this.state.data.map((item, index) => {
            let {cnt, cnt_phone, cnt_short, cnt_short_phone, hitnum, source, riqi, title, short, favorate} = item;

            return <div className='detailBox' key={index}>
              <div className='text-header'>
                <h1>
                  {title}
                </h1>
                <div className="issue-box">
                  <span>{source}</span>
                  <span>·</span>
                  <span>{riqi}</span>
                  <span>热度: <i>{hitnum}</i></span>
                </div>
              </div>
              <div className={'synopsisBox'}>
                <div className="synopsis" dangerouslySetInnerHTML={{__html: sanitizeHtml(cnt_short)}}>
                </div>
              </div>
              <div className="detail-text" dangerouslySetInnerHTML={{__html: sanitizeHtml(cnt)}}/>
            </div>
          }) : null}


          {
            /*<div className="keyword">
                        关键字：
                        <NavLink to={localePath("/list")}>火星晨报</NavLink>
                        <NavLink to={localePath("/list")}>赵长鹏</NavLink>
                        <NavLink to={localePath("/list")}>日本金融厅</NavLink>
                        <NavLink to={localePath("/list")}>火币</NavLink>
                        <NavLink to={localePath("/list")}>BitTrade</NavLink>
                        <NavLink to={localePath("/list")}>SPoS</NavLink>
                        <NavLink to={localePath("/list")}>共识机制</NavLink>
                      </div> */
          }
          <div className={this.state.goTop ? 'news-share' : 'news-share news-active'}>
            {
              /**<div className='author-left'>
               <img
               src={AVATAR_PLACEHOLDER}
               alt="" />
               <b>火星财经</b>
               <span>关注</span>
               </div> */
            }
            <div className="author-right">
              <div className="back-top" onClick={this.goTop}></div>
              <div className="share-box">
                <a href="/#" className='icon-wechat'></a>
                <a href="/#" className='icon-weibo'>
                  <div className="wechat-qrcode">
                    <h4>微博扫一扫：分享</h4>
                    <div className="qrcode">
                      <img src={wechatQr} alt=''/>
                    </div>
                    <div className="help">
                      <p>微博里点“发现”，扫一下</p>
                      {/*<p>二维码便可将本文分享至朋友圈。</p> */}
                    </div>
                  </div>
                </a>
                <a href="/#" className='icon-qq'></a>
                <p>分享</p>
              </div>
              <div className="comment-btn">
                <p>{this.state.feednum}</p>
                <span></span>
              </div>
              {/* [BUG 修复] 原实现直接改 ev.target.className，绕开 React 状态：
                  1) 请求失败/未登录时按钮照样变成「已收藏」，与真实状态脱节；
                  2) 收藏成功后没有回写 favid，紧接着点「取消收藏」会带 favid=0 打空；
                  3) 组件重渲染后 className 又被 state 覆盖，出现闪回。
                  现统一走 setState，并根据后端返回的 success/favid 更新。 */}
              <div className={this.state.favorate == 0 ? 'collect-img on' : 'collect-img'} onClick={() => {
                if (!this.state.thid) {
                  alert('请先登录后再收藏');
                  return;
                }
                if (this.state.favorate === 1 || this.state.favorate === '1') {
                  axios({
                    method: 'post',
                    url: `${global.constants.winUrl}?c=Content&a=ajax_favorate`,
                    data: {
                      "data": {
                        pid: this.state.id,
                        cataid: this.state.cataid
                      }
                    }
                  }).then(res => {
                    if (res && res.success === 401) { alert('请先登录后再收藏'); return; }
                    if (res && res.success === 0) {
                      this.setState({favorate: 0, favid: res.favid || 0});
                    }
                  }).catch(() => {});
                } else {
                  axios({
                    method: 'post',
                    url: `${global.constants.winUrl}?c=h&a=ajax_del_favorate`,
                    data: {
                      "data": {
                        favid: this.state.favid
                      }
                    }
                  }).then(res => {
                    if (res && res.success === 401) { alert('请先登录'); return; }
                    this.setState({favorate: 1, favid: 0});
                  }).catch(() => {});
                }
              }}></div>
            </div>
          </div>
          <div className="new-interest">
            <h5>相关新闻</h5>
            <div className="interest-box">
              {this.state.aboutArr ? this.state.aboutArr.map((item, index) => {
                let {location: {search}} = this.props,
                    {cataid = 8} = Qs.parse(search.substr(1)) || {}, a;
                let {picdir_list, short, title, id} = item;
                // [修复] 后端 short 字段被 Mars AI 占位文案（"摘要由 Mars AI 生成..."）污染，
                // 导致所有相关新闻卡片文字一模一样。检测到占位符时回退到真实 title。
                let aboutText = (short && !/摘要由|Mars AI|生成内容的准确性/.test(short)) ? short : title;
                return <NavLink to={localePath(`/detailed?cataid=${cataid}&id=${id}`)}
                                key={index}>
                  <img src={picdir_list} alt=""/>
                  <p>{aboutText}</p>
                </NavLink>
              }) : null}
            </div>
          </div>
        </div>
        <div className='right-content'>

          <div className='recomend'>
            <h3>热门新闻</h3>
            {this.state.hotArr ? this.state.hotArr.map((item, index) => {
              let {location: {search}} = this.props,
                  {cataid = 8} = Qs.parse(search.substr(1)) || {}, a;
              let {picdir_list, title, riqi, id} = item;
              return <div className='listBox' key={index}>
                <NavLink to={localePath(`/detailed?cataid=${cataid}&id=${id}`)}>
                  <img src={picdir_list} alt=""/>
                  <span>{title}</span>
                  <p>{riqi}</p>
                </NavLink>
              </div>
            }) : null}
          </div>
          {this.state.i ? this.state.nextArr.map((item, index) => {
            let {location: {search}} = this.props,
                {cataid = 8} = Qs.parse(search.substr(1)) || {}, a;
            return <div className={this.state.nextActive ? 'next-page next-active' : 'next-page'} key={index}>
              <NavLink to={localePath(`/detailed?cataid=${cataid}&id=${item.id}`)}>
                <h5>下一篇</h5>
                <img src={item.picdir_list} alt=""/>
                <p>{item.title ? item.title : ''}</p>
              </NavLink>
            </div>
          }) : null}

        </div>
      </div>


      <div className="footer">
        <div className="footer-content">
          <div className="reply-issue">
            <div className="prompt-not-login" style={{display: !this.state.thid ? 'block' : 'none'}}>
              <p>请
                <span className="reply-login-button" onClick={() => {
                  this.props.history.push(localePath(`/login?cataid=${this.state.cataid}&id=${this.state.id}`))
                }}>登录
                </span> 后输入评论…
              </p>
            </div>

            <div className={'form_box'} style={{display: this.state.thid ? 'block' : 'none'}}>
              <TextArea placeholder="评论内容" autosize={{minRows: 2, maxRows: 6}}/>
              <Button type="primary" style={{marginTop: '0.2rem'}} onClick={(ev) => {
                let t = ev.target,
                    ta = t.previousSibling,
                    v = ta && typeof ta.value === 'string' ? ta.value.trim() : '',
                    {cataid, id} = this.state,
                    // [安全/BUG 修复] pname / hid / hname 不再由前端上报：
                    // 后端已改为从会员表与 session 取值，前端传了也会被忽略。
                    obj = {
                      pid: id,
                      cataid: cataid,
                      content: v
                    };
                if (v === '') return;
                axios({
                  method: 'post',
                  url: `${global.constants.winUrl}?c=Content&a=ajax_feedback`,
                  data: {"data": obj}
                }).then(res => {
                  if (!res) return;
                  if (res.success === 401) { alert('请先登录后再评论'); return; }
                  if (res.success === 429) { alert(res.msg || '评论过于频繁，请稍后再试'); return; }
                  if (res.success === 0) { window.location.reload(true); return; }
                  alert(res.msg || '评论提交失败');
                }).catch(() => {});
              }}>提交</Button>
            </div>
          </div>
          <div className="reply-module">
            <ul>
              <h3>评论（{this.state.index}条）</h3>
              {this.state.feedArr ? this.state.feedArr.map((item, index) => {
                let {picdir, name, content, riqi} = item;
                return <li key={index} style={{display: !this.state.index ? 'none' : 'block'}}>
                  <img src={picdir} alt=""/>
                  <div className="reply-detail">
                    <span>{name}</span>
                    <p>{content}</p>
                  </div>
                  <div className="reply-info">
                    <span>{riqi}</span>
                    <div className="reply-info-item">
                      {/*<Icon type="message" theme="outlined"/>*/}
                      {/*<p>回复</p>*/}
                    </div>
                  </div>
                </li>
              }) : null}
            </ul>
            <span className='all-reply-btn' style={{display: !this.state.index ? 'block' : 'none'}}>暂无数据！</span>
          </div>
        </div>
      </div>
      </React.Fragment>
      ) : (
      <div className='detail-empty'>
        <p>文章不存在或加载失败</p>
        <NavLink to={localePath('/home')}>返回首页</NavLink>
      </div>
      )}
    </section>
  }

  goTop = () => {
    let timer = setInterval(function () {
      let osTop = document.documentElement.scrollTop || document.body.scrollTop,
          isSpeed = Math.floor(-osTop / 6);
      document.documentElement.scrollTop = document.body.scrollTop = osTop + isSpeed;
      if (osTop == 0) clearInterval(timer);
    }, 17);
  }
}

export default (Detailed);