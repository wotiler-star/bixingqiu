import React from 'react';
import ReactDOM, {render} from 'react-dom';
import axios from "axios";

class pwd2 extends React.Component {
  constructor(props, context) {
    super(props, context);
    this.state = {
      data: null,
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
          <p>18073921415</p>
        </li>
        <li>
          <span>原密码</span>
          <input placeholder="请输入原密码！" ref={'a'} type={'password'}/>
        </li>
        <li>
          <span>新密码</span>
          <input placeholder="请输入新密码！" ref={'b'} type={'password'}/>
        </li>
        <li>
          <span>确认密码</span>
          <input placeholder="请确认密码！" ref={'c'} type={'password'}/>
        </li>
        <li>
          <button onClick={() => {
            let id = window.localStorage.getItem('HID');
            if (this.refs.b.value == this.refs.c.value) {
              let obj = {
                "hid": id,
                "pwd": this.refs.b.value,
                "pwd0": this.refs.a.value
              };
              axios({
                method: 'post',
                url: `${global.constants.winUrl}?c=h&a=ajax_pwd2&hid=${id}`,
                data: {"data": obj}
              }).then(res => {
                console.log(res);
                res.success == 0 ? alert('修改成功！') : alert('修改失败！');
              })
            }
          }}>确认修改
          </button>
        </li>
      </ul>
    </div>
  }
}

export default (pwd2);