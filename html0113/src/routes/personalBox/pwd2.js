import React from 'react';
import ReactDOM, {render} from 'react-dom';
import axios from "axios";

class pwd2 extends React.Component {
  constructor(props, context) {
    super(props, context);
    this.state = {
      data: null,
      hname: window.localStorage.getItem('HNAME') || ''
    }
  }

  componentDidMount() {
  }

  render() {
    return <div className="right-content-6 right-box">
      <h3>修改密码</h3>
      <ul>
        <li>
          <span>用户名</span>
          {/* [修复] 原硬编码 18073921415，现显示当前登录账号 */}
          <p>{this.state.hname || '—'}</p>
        </li>
        <li>
          <span>原密码</span>
          <input placeholder="请输入原密码！" ref={'a'} type={'password'}/>
        </li>
        <li>
          <span>新密码</span>
          <input placeholder="8-24 位字母、数字或常见符号" ref={'b'} type={'password'}/>
        </li>
        <li>
          <span>确认密码</span>
          <input placeholder="请确认密码！" ref={'c'} type={'password'}/>
        </li>
        <li>
          <button onClick={() => {
            let id = window.localStorage.getItem('HID');
            if (!id) {
              alert('登录状态已失效，请重新登录！');
              return;
            }
            if (this.refs.a.value == '') {
              alert('请输入原密码！');
              return;
            }
            if (this.refs.b.value != this.refs.c.value) {
              alert('两次输入的新密码不一致！');
              return;
            }
            if (!/^[\w_@#$%&*\-]{8,24}$/.test(this.refs.b.value)) {
              alert('新密码需为 8-24 位字母、数字或常见符号！');
              return;
            }
            let obj = {
              // [修复] 不再信任客户端 hid；后端已改为以 session 为准。仅传原/新密码。
              "pwd0": this.refs.a.value,
              "pwd": this.refs.b.value
            };
            axios({
              method: 'post',
              url: `${global.constants.winUrl}?c=h&a=ajax_pwd2`,
              data: {"data": obj}
            }).then(res => {
              if (res.success == 0) {
                alert('密码修改成功！');
                this.refs.a.value = '';
                this.refs.b.value = '';
                this.refs.c.value = '';
              } else {
                // 后端区分「原密码错误」与「新密码格式错误」并给出 msg
                alert(res.msg || '修改失败，请重试！');
              }
            }).catch(() => alert('网络异常，请稍后重试！'));
          }}>确认修改
          </button>
        </li>
      </ul>
    </div>
  }
}

export default (pwd2);