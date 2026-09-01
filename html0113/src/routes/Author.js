import React from 'react';
import ReactDOM, {render} from 'react-dom';
import {NavLink} from 'react-router-dom';
import {Icon} from 'antd';
import '../static/css/Author.less';
import axios from 'axios';


import { localePath } from '../i18n/i18n';
class Author extends React.Component {
  constructor(props, context) {
    super(props, context);
    this.state = {
      // s: zjA,
      data: null,
      gz: '关注',
      gzType: false,
      typeI: null
    };
  }

  componentWillMount() {
    document.getElementById('root').scrollIntoView(true);//为ture返回顶部，false为底部
  }

  componentDidMount() {
    window.localStorage.HID != undefined ? this.setState({typeI: true}) : this.setState({typeI: false});
    axios.get(`${global.constants.winUrl}?c=Content&a=getExpertList`).then(res => {
      console.log(res);
      let a = res.slice(0, 18);
      this.setState({
        data: a
      });
    })
  }

  render() {
    let {data} = this.state;
    return <section className='author'>
      <div className="header-bg">
        <div className="header-bg-content">
          <h1>专栏作者</h1>
          <p>区块链行业最有思想的 KOL 聚集地，作者深度思想引领区块链理论高地</p>
        </div>
      </div>
      <div className="main">
        <div className="container">
          <ul onClick={this.switch}>
            <li className='active'>最有影响力</li>
            <li>最活跃</li>
            <li>最多产</li>
            <li>最争议</li>
          </ul>
        </div>
        <div className="list-content">
        即将上线， 敬请期待...
          {
            /**{data ? data.map((item, index) => {
                        let {picdir, name, short, id} = item;
                        return <div className="listBox" key={index}>
                          <a href="javascript:;" className='listBox-content'>
                            <div className="avatar">
                              <NavLink to={localePath(`/mydetail?id=${id}`)}>
                                <img src={picdir} alt=""/>
                              </NavLink>
                              <span onClick={() => {
                                // [BUG 修复] 原来跳转登录后没有 return，会继续把「已关注」
                                // 写进 state 并发出未登录请求（后端已改为 401），
                                // 造成 UI 显示已关注但实际没关注。
                                if (!this.state.typeI) {
                                  this.props.history.push(localePath("/login"));
                                  return;
                                }
                                let t = !this.state.gzType;
                                axios({
                                  method: 'post',
                                  // hid 由服务端 session 决定，前端不再上报，避免越权
                                  url: `${global.constants.winUrl}?a=carehid`,
                                  data: {
                                    "data": {
                                      "mycarehid": id
                                    }
                                  }
                                }).then(res => {
                                  if (res && res.success === 401) {
                                    this.props.history.push(localePath("/login"));
                                    return;
                                  }
                                  this.setState({
                                    gz: t ? '已关注' : '关注',
                                    gzType: t
                                  });
                                }).catch(() => {});
                              }}>
                                {this.state.gz}
                              </span>
                            </div>
                            <div className="listText">
                              <h2>{name}</h2>
                              <div className="posts">
                              <span>
                                文章数
                                <b>123</b>
                              </span>
                                <span>
                                浏览量
                                <b>456万</b>
                              </span>
                                <span>
                                获赞数
                                <b>789</b>
                              </span>
                              </div>
                              <p>作者简介:{short}</p>
                            </div>
                          </a>
                        </div>
                      }) : null} */
          }

        </div>
        {/*<div className="tabBox">
          <Icon type="left" theme="outlined"/>
          <ul>
            <li className='active'>1</li>
            <li>2</li>
            <li>3</li>
            <li>4</li>
          </ul>
          <Icon type="right" theme="outlined"/>
        </div>*/}
      </div>
    </section>
  }

  switch = (ev) => {
    let target = ev.target,
        tarName = target.tagName,
        tarClass = target.className,
        liAry1 = target.parentNode.childNodes,
        liAry = [...liAry1];
    liAry.forEach(item => tarName === 'LI' ? item.setAttribute('class', '') : null);
    tarName === "LI" ? target.setAttribute('class', 'active') : null;
    switch (tarName === 'LI') {
      case liAry.indexOf(target) === 0:
        // this.setState({data: zjA});
        break;
      case liAry.indexOf(target) === 1:
        // this.setState({data: zjB})
    }
  }
}

export default (Author);