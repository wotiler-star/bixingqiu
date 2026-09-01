import React from 'react';
import ReactDOM, {render} from 'react-dom';
import {NavLink} from 'react-router-dom';
import axios from "axios";

import { localePath } from '../../i18n/i18n';
import stepImg from '../../static/image/step1.jpg'; // 修复：原为硬编码 static/media 字面量，webpack 未打包该文件 -> 线上 404
class RealName extends React.Component {
  constructor(props, context) {
    super(props, context);
    this.state = {
      data: null,
    }
  }

  render() {
    return <div className="right-content-8 right-box">
      <h3>实名认证</h3>
      <img src={stepImg} alt=""/>
      <NavLink className="btn" to={localePath("/personal/personal")}>
        <button>立即申请</button>
      </NavLink>
      <NavLink className="btn" to={localePath("/personal/enterprise")}>
        <button>立即申请</button>
      </NavLink>
      <NavLink className="btn" to={localePath("/personal/media")}>
        <button>立即申请</button>
      </NavLink>
    </div>
  }
}

export default (RealName);