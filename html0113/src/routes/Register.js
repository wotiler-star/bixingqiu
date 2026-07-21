import React from 'react';
import ReactDOM, { render } from 'react-dom';
import { NavLink } from 'react-router-dom';
import action from '../store/action';
import { Icon } from 'antd';
import '../static/css/Register.less';
import axios from 'axios';


class Register extends React.Component {
  constructor(props, context) {
    super(props, context);
    this.state = {
      warning: "手机号码格式错误",
      warningIf: false
    }
  }
  componentWillMount() {
    document.getElementById('root').scrollIntoView(true);//为ture返回顶部，false为底部
  }

  render() {
    return <section className='register'>
      <div className="login-box">
        <div className='warning' style={{ display: this.state.warningIf ? 'block' : 'none' }}>
          {this.state.warning}
        </div>
        <div className="title">
          <span>注册</span>
          <NavLink to='/login'>使用已有账号登录</NavLink>
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
        {/**<div className="sms-code">
          <input type="text" placeholder="短信验证码" title="短信验证码"
            onFocus={(ev) => ev.target.style.borderColor = '#f29700'}
            onBlur={(ev) => ev.target.style.borderColor = '#E1E1E1'} />
          <input type="button" title="获取验证码" value="获取验证码"
            onMouseDown={(ev) => ev.target.setAttribute('id', 'btn-2')}
            onMouseUp={(ev) => ev.target.setAttribute('id', '')}
            onMouseLeave={(ev) => ev.target.setAttribute('id', '')} />
        </div> */}
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
        {/**<div className="forget">
          <a href="javascript:;">
            <input type="checkbox" name="protocol" ref={'checkbox'} />
            我已阅读并接受
          </a>
          <a href="javascript:;">《金色财经媒体平台服务协议》</a>
        </div> */}
        <div className="sign-in">
          <input type="button" value="注册" title="注册"
            onMouseDown={(ev) => ev.target.setAttribute('id', 'btn')}
            onMouseUp={(ev) => ev.target.setAttribute('id', '')}
            onMouseLeave={(ev) => ev.target.setAttribute('id', '')}
            onClick={this.verification}
          />
        </div>
        {/**<div className="downloadq">
          下载APP
          <div className='qrcode'>
            <img src="https://resource.jinse.com/frontend/dist/image/jinse-app.png" alt="" />
            <p>扫码下载</p>
            <p>美好星球APP</p>
          </div>
        </div>
         */}
      </div>
    </section>
  }

  verification = () => {
    let a = () => setTimeout(() => this.setState({ warningIf: false }), 3000),
      b = () => this.props.history.push("/login");

    // if (!this.refs.checkbox.checked) {
    //   this.setState({
    //     warning: '请确认阅读“《金色财经媒体平台服务协议》”！',
    //     warningIf: true
    //   });
    //   a()
    //   return;
    // }

    if (this.refs.b.value != this.refs.c.value) {
      this.setState({
        warning: '请确认密码！',
        warningIf: true
      });
      a()
    }

    if (!(/^[\w_-]{6,16}$/.test(this.refs.b.value)) && !(/^[\w_-]{6,16}$/.test(this.refs.c.value))) {
      this.setState({
        warning: '密码格式错误,请输入8-24个字符！',
        warningIf: true
      });
      a()
    }

    if (!(/^1[3|4|5|8][0-9]\d{4,11}$/.test(this.refs.a.value))) {
      this.setState({
        warning: '手机号码格式错误,请重新输入！',
        warningIf: true
      });
      a()
    }

    if ((/^[\w_-]{6,16}$/.test(this.refs.b.value)) && (/^1[3|4|5|8|7|6][0-9]\d{4,8}$/.test(this.refs.a.value))) {
      console.log(1);
      axios({
        method: "post",
        url: `${global.constants.winUrl}?c=h&a=ajax_rsg`,
        data: {
          hname: this.refs.a.value,
          pwd: this.refs.b.value
        }
      }).then((res) => {
        console.log(res);
        if (res.success == 0) {
          this.setState({
            warning: '注册成功',
            warningIf: true
          });
          a();
          setTimeout(b(), 3000);
          alert('注册成功，请登录！');
        }
        if (res.success == 1) {
          this.setState({
            warning: '注册失败',
            warningIf: true
          });
          a()
          alert('注册失败！');
        }
      })
    }

  }
}


export default (Register);