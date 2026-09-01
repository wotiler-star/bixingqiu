import React from 'react';
import ReactDOM, { render } from 'react-dom';
import { connect } from 'react-redux';
import { NavLink, Switch, Route, Redirect } from 'react-router-dom';
import { Input } from 'antd';
import '../static/css/Personal.less';
import init from './personalBox/init';//个人资料
import myfeedback from './personalBox/myfeedback';//我的评论
import myi from './personalBox/myi';//我的文章
import myfavorate from './personalBox/myfavorate';//我的收藏
import mynotice from './personalBox/mynotice';//系统消息
import pwd2 from './personalBox/pwd2';//修改密码
import geni from './personalBox/geni';//发布文章
import realname from './personalBox/RealName';//实名认证
import personal from './personalBox/personal';//个人认证
import enterprise from './personalBox/enterprise';//企业认证
import media from './personalBox/media';//媒体认证
import myguan from './personalBox/myGuan';//我的关注
import TipsPage from './personalBox/TipsPage';
import axios from "axios";

import { localePath } from '../i18n/i18n';
// 与 Header 同一个问题：'../static/media/avatar.png' 是硬编码字面量，
// webpack 从不打包它 -> 线上 404，未设置头像的会员中心是碎图。改为 import 由打包器接管。
import avatarImg from '../static/image/avatar.png';
//申请等待

class Personal extends React.Component {
  constructor(props, context) {
    super(props, context);
    this.state = {
      name: null,
      image: null,
      num_content:null,
      num_favorate:null,
      num_feedback:null
    }
  }

  componentDidMount() {
    let id = '';
    try {
      id = window.localStorage.getItem('HID') || '';
    } catch (e) { id = ''; }
    // 未登录直接进 /personal 时，原实现会请求 hid=null 并对空数组取 res[0].picdir，
    // 抛 TypeError（Promise 静默吞掉）→ 页面停在空白个人中心。改为跳转登录页。
    if (!id) {
      this.props.history.replace(localePath('/login'));
      return;
    }
    axios.get(`${global.constants.winUrl}?c=h&a=ajax_getInfo&hid=${encodeURIComponent(id)}`).then(res => {
      const row = (Array.isArray(res) && res[0]) ? res[0] : null;
      if (!row) {
        // 账号已被注销/封禁：清掉本地登录态，避免一直卡在坏页面
        try { window.localStorage.removeItem('HID'); } catch (e) { }
        this.props.history.replace(localePath('/login'));
        return;
      }
      let nickname = '';
      try { nickname = window.localStorage.getItem('NICKNAME') || ''; } catch (e) { }
      this.setState({
        name: nickname,
        image: row.picdir,
        num_content: row.num_content,
        num_favorate: row.num_favorate,
        num_feedback: row.num_feedback
      });
    }).catch(() => { /* 网络异常时保留默认占位，不阻塞页面 */ });
  }

  componentWillMount() {
    const el = document.getElementById('root');
    if (el && el.scrollIntoView) el.scrollIntoView(true);//为ture返回顶部，false为底部
  }

  render() {
    let textContent = '点击文字进行编辑！';
    // 嵌套子路由必须跟随当前匹配到的前缀（含语种），如 /en/personal。
    // 否则导航已指向 /en/personal/init，而子路由写死 '/personal/init' 匹配不到，
    // 会一直命中末尾的 Redirect，形成无限重定向。
    const pbase = (this.props.match && this.props.match.url) ? this.props.match.url : '/personal';
    return <section className={'personal'}>
      <div className="bg-content">
        <img src={this.state.image || avatarImg} alt="" />
        <div className="name">{this.state.name}</div>
        <div className="tongji">
          <div className="pl-number">
            <span>评论数量</span>
            <p>{this.state.num_feedback}</p>
          </div>
          <div className="wz-number">
            <span>文章数量</span>
            <p>{this.state.num_content}</p>
          </div>
          <div className="sc-number">
            <span>收藏数量</span>
            <p>{this.state.num_favorate}</p>
          </div>
        </div>
      </div>
      <div className="main-content">
        <div className="left-content">
          <div className="title">
            <img src={this.state.image || avatarImg} alt="" />
            <span>{this.state.name}</span>
          </div>
          <h2>
            <img src="/service/konecms/module/content/template/static/h/tb1.png" alt="" />会员中心
          </h2>
          <div className="left-nav">
            <NavLink to={localePath('/personal/init')}>
              <img src="/service/konecms/module/content/template/static/h/tb5.png" alt="" />
              个人资料
            </NavLink>
            <NavLink to={localePath('/personal/myfeedback')}>
              <img src="/service/konecms/module/content/template/static/h/tb2.png" alt="" />
              我的评论
            </NavLink>
            <NavLink to={localePath('/personal/myi')}>
              <img src="/service/konecms/module/content/template/static/h/tb12.png" alt="" />
              我的文章
            </NavLink>
            <NavLink to={localePath('/personal/myguan')}>
              <img src="/service/konecms/module/content/template/static/h/timg.jpg" alt="" />
              我的关注
            </NavLink>
            <NavLink to={localePath('/personal/myfavorate')}>
              <img src="/service/konecms/module/content/template/static/h/tb4.png" alt="" />
              我的收藏
            </NavLink>
            <NavLink to={localePath('/personal/mynotice')}>
              <img src="/service/konecms/module/content/template/static/h/tb14.png" alt="" />
              系统消息
            </NavLink>
            <NavLink to={localePath('/personal/realname')}>
              <img src="/service/konecms/module/content/template/static/h/timg.jpg"
                alt="" style={{ top: '0.14rem' }} />
              实名认证
            </NavLink>
            <a href={'javascript:;'}>
              <img src="/service/konecms/module/content/template/static/h/tb1.png" alt=""
                style={{ height: '48px', width: '39px' }} />账号管理
            </a>
            <NavLink to={localePath('/personal/pwd2')}>
              <img src="/service/konecms/module/content/template/static/h/tb9.png" alt="" />
              修改密码
            </NavLink>
            <NavLink to={localePath('/home')} onClick={() => { 
              window.localStorage.clear();
              window.location.reload(true);
            }}>
              <img src="/service/konecms/module/content/template/static/h/tb10.png" alt="" />
              安全退出
            </NavLink>
          </div>
        </div>
        <Switch>
          <Route path={pbase + "/init"} component={init} />
          <Route path={pbase + "/myfeedback"} component={myfeedback} />
          <Route path={pbase + "/myi"} component={myi} />
          <Route path={pbase + "/myguan"} component={myguan} />
          <Route path={pbase + "/myfavorate"} component={myfavorate} />
          <Route path={pbase + '/mynotice'} component={mynotice} />
          <Route path={pbase + '/pwd2'} component={pwd2} />
          <Route path={pbase + '/geni'} component={geni} />
          <Route path={pbase + '/realname'} component={realname} />
          <Route path={pbase + '/personal'} component={personal} />
          <Route path={pbase + '/enterprise'} component={enterprise} />
          <Route path={pbase + '/media'} component={media} />
          <Route path={pbase + '/tips'} component={TipsPage} />
          <Redirect to={localePath('/personal/init')} />
        </Switch>
      </div>
    </section>
  }

}

export default (Personal);
