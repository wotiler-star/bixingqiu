import React from 'react';
import ReactDOM, { render } from 'react-dom';
import { NavLink } from 'react-router-dom';
import action from '../store/action';
import { Icon } from 'antd';
import '../static/css/Register.less';
import axios from 'axios';


import { localePath } from '../i18n/i18n';
class Register extends React.Component {
  constructor(props, context) {
    super(props, context);
    const capBase = (typeof global !== 'undefined' && global.constants && global.constants.winUrl) || '';
    this.state = {
      warning: "手机号码格式错误",
      warningIf: false,
      captchaSrc: capBase + 'yzm.php?t=' + Date.now(),
      smsText: "获取验证码",
      smsDisabled: false,
    }
  }
  componentWillMount() {
    document.getElementById('root').scrollIntoView(true);//为ture返回顶部，false为底部
  }

  refreshCaptcha = () => {
    const capBase = (typeof global !== 'undefined' && global.constants && global.constants.winUrl) || '';
    this.setState({ captchaSrc: capBase + 'yzm.php?t=' + Date.now() });
  };

  // 发送短信验证码
  sendSms = () => {
    if (this.state.smsDisabled) return;
    if (!(/^1[3-9]\d{9}$/.test(this.refs.a.value))) {
      this.setState({ warning: '请先填写正确的手机号！', warningIf: true });
      setTimeout(() => this.setState({ warningIf: false }), 3000);
      return;
    }
    if (!this.refs.d || !this.refs.d.value) {
      this.setState({ warning: '请先填写图形验证码！', warningIf: true });
      setTimeout(() => this.setState({ warningIf: false }), 3000);
      return;
    }
    axios({
      method: "post",
      url: `${global.constants.winUrl}?c=h&a=ajax_send_sms`,
      data: { "hname": this.refs.a.value, "yzm": this.refs.d.value }
    }).then((res) => {
      if (res.success == 0) {
        let tip = '验证码已发送';
        if (res.dev_code) tip += '（联调码：' + res.dev_code + '，生产环境不会显示）';
        this.setState({ warning: tip, warningIf: true });
        setTimeout(() => this.setState({ warningIf: false }), 5000);
        // 60 秒倒计时
        let left = 60;
        this.setState({ smsDisabled: true, smsText: left + 's' });
        this._smsTimer = setInterval(() => {
          left--;
          if (left <= 0) {
            clearInterval(this._smsTimer);
            this.setState({ smsDisabled: false, smsText: '获取验证码' });
          } else {
            this.setState({ smsText: left + 's' });
          }
        }, 1000);
      } else {
        this.setState({ warning: res.msg || '发送失败', warningIf: true });
        setTimeout(() => this.setState({ warningIf: false }), 3000);
        this.refreshCaptcha();
      }
    });
  };

  render() {
    return <section className='register'>
      <div className="login-box">
        <div className='warning' style={{ display: this.state.warningIf ? 'block' : 'none' }}>
          {this.state.warning}
        </div>
        <div className="title">
          <span>注册</span>
          <NavLink to={localePath('/login')}>使用已有账号登录</NavLink>
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
        <div className="captcha" style={{ display: 'flex', alignItems: 'center', margin: '0 0 14px' }}>
          <input type="text" placeholder="图形验证码" title='图形验证码' maxLength={4}
            style={{ flex: 1, height: '44px', padding: '0 12px', border: '1px solid #E1E1E1', borderRadius: '4px' }}
            ref={'d'} />
          <img src={this.state.captchaSrc} alt="验证码" onClick={this.refreshCaptcha}
            title="点击刷新" style={{ width: '110px', height: '44px', marginLeft: '10px', cursor: 'pointer', borderRadius: '4px' }} />
        </div>
        <div className="sms-code" style={{ display: 'flex', alignItems: 'center', margin: '0 0 14px' }}>
          <input type="text" placeholder="短信验证码" title="短信验证码"
            style={{ flex: 1, height: '44px', padding: '0 12px', border: '1px solid #E1E1E1', borderRadius: '4px' }}
            ref={'e'} />
          <input type="button" title="获取验证码" value={this.state.smsText}
            disabled={this.state.smsDisabled}
            onClick={this.sendSms}
            style={{ width: '120px', height: '44px', marginLeft: '10px', border: 'none', borderRadius: '4px', background: '#f29700', color: '#fff', cursor: 'pointer' }} />
        </div>
        <div className="password">
          <input type="password" placeholder="输入8-24位密码" title='输入8-24位密码'
            onFocus={(ev) => ev.target.style.borderColor = '#f29700'}
            onBlur={(ev) => ev.target.style.borderColor = '#E1E1E1'} ref={'b'} />
        </div>
        <div className="password">
          <input type="password" placeholder="确认密码" title='确认密码'
            onFocus={(ev) => ev.target.style.borderColor = '#f29700'}
            onBlur={(ev) => ev.target.style.borderColor = '#E1E1E1'} ref={'c'} />
        </div>
        <div className="sign-in">
          <input type="button" value="注册" title="注册"
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
    let a = () => setTimeout(() => this.setState({ warningIf: false }), 3000),
      b = () => this.props.history.push(localePath("/login"));

    if (this.refs.b.value != this.refs.c.value) {
      this.setState({
        warning: '两次密码不一致，请确认！',
        warningIf: true
      });
      a()
      return; // 修复：不一致时直接拦截，不再提交
    }

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
        warning: '手机号码格式错误,请重新输入！',
        warningIf: true
      });
      a()
      return;
    }

    if ((/^[\w_-]{8,24}$/.test(this.refs.b.value)) && (/^1[3-9]\d{9}$/.test(this.refs.a.value))) {
      axios({
        method: "post",
        url: `${global.constants.winUrl}?c=h&a=ajax_rsg`,
        data: {
          hname: this.refs.a.value,
          pwd: this.refs.b.value,
          yzm: this.refs.d.value,
          msg: this.refs.e.value
        }
      }).then((res) => {
        if (res.success == 0) {
          this.setState({
            warning: '注册成功',
            warningIf: true
          });
          a();
          setTimeout(b, 3000);
          alert('注册成功，请登录！');
        }
        if (res.success == 1) {
          this.setState({
            warning: res.msg || '注册失败',
            warningIf: true
          });
          a()
          this.refreshCaptcha();
          alert(res.msg || '注册失败！');
        }
        if (res.success == 3) {
          this.setState({ warning: '图形验证码错误！', warningIf: true });
          a()
          this.refreshCaptcha();
          alert('图形验证码错误！');
        }
        if (res.success == 4) {
          this.setState({ warning: '短信验证码错误！', warningIf: true });
          a()
          alert('短信验证码错误！');
        }
        if (res.success == 9) {
          this.setState({ warning: '注册过于频繁，请稍后再试', warningIf: true });
          a()
          this.refreshCaptcha();
          alert('注册过于频繁，请稍后再试');
        }
      })
    }

  }
}


export default (Register);
