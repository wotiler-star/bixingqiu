import React from 'react';
import ReactDOM, {render} from 'react-dom';
import {NavLink} from 'react-router-dom';
import axios from "axios";

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
      <img src="static/media/step1.jpg" alt=""/>
      <NavLink className="btn" to={"/personal/personal"}>
        <button>立即申请</button>
      </NavLink>
      <NavLink className="btn" to={"/personal/enterprise"}>
        <button>立即申请</button>
      </NavLink>
      <NavLink className="btn" to={"/personal/media"}>
        <button>立即申请</button>
      </NavLink>
    </div>
  }
}

export default (RealName);