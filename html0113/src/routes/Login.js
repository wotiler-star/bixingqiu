import React from 'react';
import ReactDOM, { render } from 'react-dom';
import { NavLink } from 'react-router-dom';
import { Icon } from 'antd';
import '../static/css/Login.less';
import axios from "axios";
import Qs from "qs";

import { localePath } from '../i18n/i18n';
class Login extends React.Component {
  constructor(props, context) {
    super(props, context);
    const capBase = (typeof global !== 'undefined' && global.constants && global.constants.winUrl) || '';
    this.state = {
      warning: "手机号码格式错误",
      warningIf: false,
      captchaSrc: capBase + 'yzm.php?t=' + Date.now(),
    }
  }

  componentWillMount() {
    document.getElementById('root').scrollIntoView(true);//为ture返回顶部，false为底部
  }

  refreshCaptcha = () => {
    const capBase = (typeof global !== 'undefined' && global.constants && global.constants.winUrl) || '';
    this.setState({ captchaSrc: capBase + 'yzm.php?t=' + Date.now() });
  };

  render() {
    return <section className='login'>
      <div className="login-box">
        <div className='warning' style={{ display: this.state.warningIf ? 'block' : 'none' }}>{this.state.warning}</div>
        <div className="title">
          <span>账号密码登录</span>
          <NavLink to={localePath('/register')}>注册</NavLink>
        </div>
        <div className="account-number">
          <span>
            +86
            <i />
          </span>
          <input type="tel" title='手机号码' placeholder='手机号码'
            onFocus={(ev) => ev.target.parentNode.setAttribute('id', 'account-active')}
            onBlur={(ev) => ev.target.parentNode.setAttribute('id', '')} ref={'a'} />
        </div>
        <div className="password">
          <input type="password" placeholder="输入8-24位密码" title='输入8-24位密码'
            onFocus={(ev) => ev.target.style.borderColor = '#f29700'}
            onBlur={(ev) => ev.target.style.borderColor = '#E1E1E1'} ref={'b'} />
        </div>
        <div className="captcha" style={{ display: 'flex', alignItems: 'center', margin: '0 0 14px' }}>
          <input type="text" placeholder="图形验证码" title='图形验证码' maxLength={4}
            style={{ flex: 1, height: '44px', padding: '0 12px', border: '1px solid #E1E1E1', borderRadius: '4px' }}
            ref={'c'} />
          <img src={this.state.captchaSrc} alt="验证码" onClick={this.refreshCaptcha}
            title="点击刷新" style={{ width: '110px', height: '44px', marginLeft: '10px', cursor: 'pointer', borderRadius: '4px' }} />
        </div>
        <div className="sign-in">
          <input type="button" value="登录" title="登录"
            onMouseDown={(ev) => ev.target.setAttribute('id', 'btn')}
            onMouseUp={(ev) => ev.target.setAttribute('id', '')}
            onMouseLeave={(ev) => ev.target.setAttribute('id', '')}
            onClick={this.verification}
          />
        </div>
      </div>
    </section>
  }

  verification = () => {
    let { location: { search } } = this.props,
      { id = 0 } = Qs.parse(search.substr(1)) || {},
      { cataid = 0 } = Qs.parse(search.substr(1)) || {};
    id = parseFloat(id);
    let url = !id ? 'personal' : `detailed?cataid=${cataid}&id=${id}`;
    console.log(this.props);
    let a = () => setTimeout(() => this.setState({ warningIf: false }), 3000),
      b = () => this.props.history.push(localePath(`/${url}`));

    if (!(/^[\w_-]{8,24}$/.test(this.refs.b.value))) {
      this.setState({
        warning: '密码格式错误,请输入8-24个字符！',
        warningIf: true
      });
      a()
      return;
    }

    if (!(/^1[3-9]\d{9}$/.test(this.refs.a.value))) {
      this.setState({
        warning: '手机号码错误,请重新输入！',
        warningIf: true
      });
      a()
      return;
    }


    let data = { "hname": this.refs.a.value, "pwd": this.refs.b.value };
    axios({
      method: "post",
      url: `${global.constants.winUrl}?c=h&a=ajax_login`,
      data: { "data": data, "yzm": this.refs.c.value }
    }).then((res) => {
      if (res.success == 0) {
        this.setState({
          warning: '登录成功！',
          warningIf: true
        });
        a();
        if (window.localStorage) {
          let Storage = window.localStorage;
          Storage.NICKNAME = res.nickname;
          Storage.HID = res.hid;
          Storage.HNAME = res.hname;
        }
        setTimeout(b, 3000);

      }
      if (res.success == 1) {
        this.setState({
          warning: '登录失败，密码错误！',
          warningIf: true
        });
        a()
        this.refreshCaptcha();
        alert('登录失败，密码错误！');
      }
      if (res.success == 2) {
        this.setState({
          warning: '账号不存在！',
          warningIf: true
        });
        a()
        this.refreshCaptcha();
        alert('账号不存在！');
      }
      if (res.success == 3) {
        this.setState({
          warning: '图形验证码错误！',
          warningIf: true
        });
        a()
        this.refreshCaptcha();
        alert('图形验证码错误！');
      }
      if (res.success == 9) {
        this.setState({
          warning: '尝试过于频繁，请稍后再试',
          warningIf: true
        });
        a()
        this.refreshCaptcha();
        alert('尝试过于频繁，请稍后再试');
      }
    });
  }
}

export default (Login);
