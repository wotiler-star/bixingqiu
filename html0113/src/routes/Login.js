import React from 'react';
import ReactDOM, { render } from 'react-dom';
import { NavLink } from 'react-router-dom';
import { Icon } from 'antd';
import '../static/css/Login.less';
import axios from "axios";
import Qs from "qs";


class Login extends React.Component {
  constructor(props, context) {
    super(props, context);
    this.state = {
      warning: "手机号码格式错误",
      warningIf: false,
    }
  }

  componentWillMount() {
    document.getElementById('root').scrollIntoView(true);//为ture返回顶部，false为底部
  }

  render() {
    return <section className='login'>
      <div className="login-box">
        <div className='warning' style={{ display: this.state.warningIf ? 'block' : 'none' }}>{this.state.warning}</div>
        <div className="title">
          <span>账号密码登录</span>
          <NavLink to='/register'>注册</NavLink>
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
        {/**<div className="forget">
          <a href="javascript:;">手机验证码登录</a>
          <a href="javascript:;">忘记密码</a>
        </div> */}
        <div className="sign-in">
          <input type="button" value="登录" title="登录"
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
    let { location: { search } } = this.props,
      { id = 0 } = Qs.parse(search.substr(1)) || {},
      { cataid = 0 } = Qs.parse(search.substr(1)) || {};
    id = parseFloat(id);
    let url = !id ? 'personal' : `Detailed?cataid=${cataid}&id=${id}`;
    console.log(this.props);
    let a = () => setTimeout(() => this.setState({ warningIf: false }), 3000),
      b = () => this.props.history.push(`/${url}`);

    if (!(/^[\w_-]{6,16}$/.test(this.refs.b.value))) {
      this.setState({
        warning: '密码格式错误,请输入8-24个字符！',
        warningIf: true
      });
      a()
    }

    if (!(/^1[3|4|5|8|6][0-9]\d{4,8}$/.test(this.refs.a.value))) {
      this.setState({
        warning: '手机号码错误,请重新输入！',
        warningIf: true
      });
      a()
    }


    let data = { "hname": this.refs.a.value, "pwd": this.refs.b.value };
    axios({
      method: "post",
      url: `${global.constants.winUrl}?c=h&a=ajax_login`,
      data: { "data": data }
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
        window.location.reload(true);
        setTimeout(b(), 3000);

      }
      if (res.success == 1) {
        this.setState({
          warning: '登录失败，请重新登录！',
          warningIf: true
        });
        a()
        alert('登录失败！');
      }
    });
  }
}

export default (Login);