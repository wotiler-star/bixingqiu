import React from 'react';
import ReactDOM, { render } from 'react-dom';
import { NavLink } from 'react-router-dom';
import '../static/css/Register.less';
import axios from 'axios';

import { localePath } from '../i18n/i18n';
class ForgotPwd extends React.Component {
  constructor(props, context) {
    super(props, context);
    const capBase = (typeof global !== 'undefined' && global.constants && global.constants.winUrl) || '';
    this.state = {
      warning: '',
      warningIf: false,
      captchaSrc: capBase + 'yzm.php?t=' + Date.now(),
      smsText: '获取验证码',
      smsDisabled: false,
    }
  }

  componentWillMount() {
    const el = document.getElementById('root');
    if (el && el.scrollIntoView) el.scrollIntoView(true);
  }

  refreshCaptcha = () => {
    const capBase = (typeof global !== 'undefined' && global.constants && global.constants.winUrl) || '';
    this.setState({ captchaSrc: capBase + 'yzm.php?t=' + Date.now() });
  };

  showTip = (msg, ok) => {
    this.setState({ warning: msg, warningIf: true });
    setTimeout(() => this.setState({ warningIf: false }), ok ? 2500 : 3500);
  };

  // 发送短信验证码（与 Register 同一接口，需图形验证码保护）
  sendSms = () => {
    if (this.state.smsDisabled) return;
    const phone = this.refs.a.value;
    const yzm = this.refs.d.value;
    if (!(/^1[3-9]\d{9}$/.test(phone))) {
      this.showTip('请先填写正确的手机号！');
      return;
    }
    if (!yzm) {
      this.showTip('请先填写图形验证码！');
      return;
    }
    axios({
      method: 'post',
      url: `${global.constants.winUrl}?c=h&a=ajax_send_sms`,
      data: { hname: phone, yzm: yzm }
    }).then((res) => {
      if (res.success == 0) {
        let tip = '验证码已发送';
        if (res.dev_code) tip += '（联调码：' + res.dev_code + '，生产环境不会显示）';
        this.showTip(tip, true);
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
        this.showTip(res.msg || '发送失败');
        this.refreshCaptcha();
      }
    }).catch(() => this.showTip('网络异常，请稍后重试'));
  };

  submit = () => {
    const phone = this.refs.a.value;
    const yzm = this.refs.d.value;
    const sms = this.refs.e.value;
    const pwd = this.refs.b.value;
    const pwd2 = this.refs.c.value;

    if (!(/^1[3-9]\d{9}$/.test(phone))) { this.showTip('手机号格式错误'); return; }
    if (!yzm) { this.showTip('请填写图形验证码'); return; }
    if (!sms) { this.showTip('请填写短信验证码'); return; }
    if (pwd !== pwd2) { this.showTip('两次密码不一致'); return; }
    if (!(/^[\w_@#$%&*\-]{8,24}$/.test(pwd))) { this.showTip('密码需为 8-24 位字母、数字或常见符号'); return; }

    axios({
      method: 'post',
      url: `${global.constants.winUrl}?c=h&a=ajax_callpwd`,
      data: {
        data: { hname: phone, pwd: pwd, msg: sms },
        yzm: yzm
      }
    }).then((res) => {
      if (res.success == 0) {
        this.showTip('密码已重置，请使用新密码登录', true);
        setTimeout(() => this.props.history.push(localePath('/login')), 1500);
      } else if (res.success == 1) {
        this.showTip(res.msg || '重置失败');
        this.refreshCaptcha();
      } else if (res.success == 3) {
        this.showTip('图形验证码错误');
        this.refreshCaptcha();
      } else {
        this.showTip(res.msg || '重置失败');
        this.refreshCaptcha();
      }
    }).catch(() => this.showTip('网络异常，请稍后重试'));
  };

  render() {
    return <section className='register'>
      <div className="login-box">
        <div className='warning' style={{ display: this.state.warningIf ? 'block' : 'none' }}>
          {this.state.warning}
        </div>
        <div className="title">
          <span>找回密码</span>
          <NavLink to={localePath('/login')}>返回登录</NavLink>
        </div>
        <div className="account-number">
          <span>
            +86
            <i />
          </span>
          <input type="tel" title='手机号码' placeholder='注册手机号'
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
          <input type="password" placeholder="输入新密码（8-24位）" title='新密码'
            onFocus={(ev) => ev.target.style.borderColor = '#f29700'}
            onBlur={(ev) => ev.target.style.borderColor = '#E1E1E1'} ref={'b'} />
        </div>
        <div className="password">
          <input type="password" placeholder="确认新密码" title='确认新密码'
            onFocus={(ev) => ev.target.style.borderColor = '#f29700'}
            onBlur={(ev) => ev.target.style.borderColor = '#E1E1E1'} ref={'c'} />
        </div>
        <div className="sign-in">
          <input type="button" value="重置密码" title="重置密码"
            onClick={this.submit} />
        </div>
      </div>
    </section>
  }
}

export default (ForgotPwd);
