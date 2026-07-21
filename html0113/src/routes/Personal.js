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
    let id = window.localStorage.getItem('HID');
    axios.get(`${global.constants.winUrl}?c=h&a=ajax_getInfo&hid=${id}`).then(res => {
      console.log(res, res[0].picdir);
      this.setState({
        name: window.localStorage.getItem('NICKNAME'),
        image: res[0].picdir,
        num_content: res[0].num_content,
        num_favorate: res[0].num_favorate,
        num_feedback: res[0].num_feedback
      });
    })

  }

  componentWillMount() {
    document.getElementById('root').scrollIntoView(true);//为ture返回顶部，false为底部
  }

  render() {
    let textContent = '点击文字进行编辑！';
    return <section className={'personal'}>
      <div className="bg-content">
        <img src={this.state.image == undefined ? '../static/media/avatar.png' : this.state.image} alt="" />
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
            <img src={this.state.image == undefined ? '../static/media/avatar.png' : this.state.image} alt="" />
            <span>{this.state.name}</span>
          </div>
          <h2>
            <img src="/service/konecms/module/content/template/static/h/tb1.png" alt="" />会员中心
          </h2>
          <div className="left-nav">
            <NavLink to={'/personal/init'}>
              <img src="/service/konecms/module/content/template/static/h/tb5.png" alt="" />
              个人资料
            </NavLink>
            <NavLink to={'/personal/myfeedback'}>
              <img src="/service/konecms/module/content/template/static/h/tb2.png" alt="" />
              我的评论
            </NavLink>
            <NavLink to={'/personal/myi'}>
              <img src="/service/konecms/module/content/template/static/h/tb12.png" alt="" />
              我的文章
            </NavLink>
            <NavLink to={'/personal/myguan'}>
              <img src="/service/konecms/module/content/template/static/h/timg.jpg" alt="" />
              我的关注
            </NavLink>
            <NavLink to={'/personal/myfavorate'}>
              <img src="/service/konecms/module/content/template/static/h/tb4.png" alt="" />
              我的收藏
            </NavLink>
            <NavLink to={'/personal/mynotice'}>
              <img src="/service/konecms/module/content/template/static/h/tb14.png" alt="" />
              系统消息
            </NavLink>
            <NavLink to={'/personal/realname'}>
              <img src="/service/konecms/module/content/template/static/h/timg.jpg"
                alt="" style={{ top: '0.14rem' }} />
              实名认证
            </NavLink>
            <a href={'javascript:;'}>
              <img src="/service/konecms/module/content/template/static/h/tb1.png" alt=""
                style={{ height: '48px', width: '39px' }} />账号管理
            </a>
            <NavLink to={'/personal/pwd2'}>
              <img src="/service/konecms/module/content/template/static/h/tb9.png" alt="" />
              修改密码
            </NavLink>
            <NavLink to={'/home'} onClick={() => { 
              window.localStorage.clear();
              window.location.reload(true);
            }}>
              <img src="/service/konecms/module/content/template/static/h/tb10.png" alt="" />
              安全退出
            </NavLink>
          </div>
        </div>
        <Switch>
          <Route path={"/personal/init"} component={init} />
          <Route path={"/personal/myfeedback"} component={myfeedback} />
          <Route path={"/personal/myi"} component={myi} />
          <Route path={"/personal/myguan"} component={myguan} />
          <Route path={"/personal/myfavorate"} component={myfavorate} />
          <Route path={'/personal/mynotice'} component={mynotice} />
          <Route path={'/personal/pwd2'} component={pwd2} />
          <Route path={'/personal/geni'} component={geni} />
          <Route path={'/personal/realname'} component={realname} />
          <Route path={'/personal/personal'} component={personal} />
          <Route path={'/personal/enterprise'} component={enterprise} />
          <Route path={'/personal/media'} component={media} />
          <Route path={'/personal/tips'} component={TipsPage} />
          <Redirect to={'/personal/init'} />
        </Switch>
      </div>
    </section>
  }

}

export default (Personal);
