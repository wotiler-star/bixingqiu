import React from 'react';
import { connect } from 'react-redux';
import { withRouter, NavLink } from 'react-router-dom';
import { Icon } from 'antd';
import axios from 'axios';

class Footer extends React.Component {
  constructor(props, context) {
    super(props, context);
    this.state = {
      linkD: null,
      data: null
    }
  }

  componentDidMount() {
    axios.get(`${global.constants.winUrl}?a=link`).then(res => {
      this.setState({
        linkD: res
      })
    });
    axios.get(`${global.constants.winUrl}?a=usual`).then(res => {
      this.setState({
        data: res
      })
    })
  }

  render() {
    return <section className='footer'>
      <div className='main'>
        <div className='topBox'>
          <div className='friendship-link'>
            <h3>友情链接</h3>
            <div>
              {this.state.linkD ? this.state.linkD.map((item, index) => {
                return <a href={'http://' + item.url} key={index}>
                  {item.title}
                  <span></span>
                </a>
              }) : null}
            </div>
          </div>

          {this.state.data ? this.state.data.map((item, index) => {
            return <div className='cooperation'>
              <h3>广告合作</h3>
              <p>QQ：{item.qq}(同微信) &nbsp;&nbsp;手机：{item.mobile}</p>
              <p>商务邮箱：{item.email}</p>
              <p>投稿邮箱：{item.email}
                友情链接
              </p>
            </div>
          }) : null}

        </div>
        {this.state.data ? this.state.data.map((item, index) => {
          return <div className='bottomBox'>
            <div className='footerLog'>
              <img src={item.picdir_logo} alt="" />
              <p>
                {item.ensitename}
                <br />
                <a href="/#">{item.enicp}</a>
              </p>
            </div>
            <div className='footer-right'>
              <div className='footer-share'>
                <NavLink to='/#'>
                  <Icon type="wechat" theme="filled" />

                  <div className='footer-wechat'>
                    <img src="http://www.huoxing24.com/img/hx-ewm-c6929e3815.png" alt="" />
                    <span>{item.sitename}</span>
                    <div></div>
                  </div>
                </NavLink>
                <NavLink to='/#'>
                  <Icon type="dingding" theme="outlined" />
                </NavLink>
                <NavLink to='/#'>
                  <Icon type="weibo" theme="outlined" />
                </NavLink>
              </div>
              <div className='footer-right-b'>
                <NavLink to='/#'>关于我们</NavLink>
                <span></span>
                <NavLink to='/#'>版权声明</NavLink>
              </div>
            </div>

          </div>
        }) : null}
        
      </div>
    </section>
  }
}

export default withRouter(connect()(Footer));