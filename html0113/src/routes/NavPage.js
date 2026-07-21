import React from 'react';
import ReactDOM, { render } from 'react-dom';
import { NavLink } from 'react-router-dom';
import { Icon } from 'antd';
import '../static/css/NavPage.less';
import { height } from 'window-size';
import axios from 'axios';

class NavPage extends React.Component {
  constructor(props, content) {
    super(props, content);
    this.state = {
      data: null,
      tab: false,
      g55: null,
      g56: null
    }
  }
  componentWillMount() {
    document.getElementById('root').scrollIntoView(true);//为ture返回顶部，false为底部
  }

  componentDidMount() {
    axios.get(`${global.constants.winUrl}?c=Content&a=getSiteList`).then(res => {
      console.log(res);
      this.setState({
        data: res.a54.b55,
        g55: res.a54.b55,
        g56: res.a54.b56
      })
    })
  }
  render() {
    let { data, g55, g56 } = this.state;
    return <section className={'navPage'}>
      <div className="main">
        <div className="title">
          <h3>网址导航</h3>
          <div className='icon'>
            <span><i className={this.state.tab ? '' : 'active'} onClick={() => this.setState({
              tab: false
            })}> </i></span>
            <span><i className={this.state.tab ? 'active' : ''} onClick={() => this.setState({
              tab: true
            })}> </i></span>
          </div>
          <NavLink to='/apply' className="post_websit">
            <i></i>
            <span>提交网址</span>
          </NavLink>
        </div>

        <div className="tabnav-contents" style={{ display: this.state.tab ? 'block' : 'none' }}>
          <div className="tabnav-title">
            <span className='mechanism'></span>
            <h4>投资机构</h4>
          </div>
          <div className={'listBox'} ref={'guo1'}>
            <span className='s' ref='A'>
              国内机构
              <img src="https://resource.jinse.com/phenix/img/0001.svg?v=1154" alt="" />
            </span>
            <div className='tabnav-mousemove'>
              {g55 ? g55.map((item, index) => {
                let { picdir, short, sitename } = item;
                return <a href="/#" key={index}>
                  <img src={picdir} alt="" />
                  <span>{short}</span>
                  <div className='nameBox'>
                    <p>{sitename}</p>
                    <i></i>
                  </div>
                </a>
              }) : null}
            </div>
            <i className='tabnav-drop-down' onClick={() => {
              let g1 = this.refs.guo1;
              g1.getAttribute('id') != 'active' ? g1.setAttribute('id', 'active') : g1.setAttribute('id', '');
            }}>
              <img src="https://resource.jinse.com/phenix/img/0001.svg?v=1154" alt="" />
            </i>
          </div>
          <div className={'listBox'} ref={'guo2'}>
            <span className='s' ref='A'>
              国内机构
              <img src="https://resource.jinse.com/phenix/img/0001.svg?v=1154" alt="" />
            </span>
            <div className='tabnav-mousemove'>
              {g56 ? g56.map((item, index) => {
                let { picdir, short, sitename } = item;
                return <a href="/#" key={index}>
                  <img src={picdir} alt="" />
                  <span>{short}</span>
                  <div className='nameBox'>
                    <p>{sitename}</p>
                    <i></i>
                  </div>
                </a>
              }) : null}
            </div>
            <i className='tabnav-drop-down' onClick={() => {
              let g1 = this.refs.guo2;
              g1.getAttribute('id') != 'active' ? g1.setAttribute('id', 'active') : g1.setAttribute('id', '');
            }}>
              <img src="https://resource.jinse.com/phenix/img/0001.svg?v=1154" alt="" />
            </i>
          </div>
        </div>

        <div className="tabnav-content" style={{ display: this.state.tab ? 'none' : 'block' }}>
          <div className="list-content">
            <div className="listBox">
              <ul className="tabnav-title" onClick={this.switch}>
                <li className='active'>
                  <i></i>投资机构
                </li>
                <li>国内机构</li>
                <li>国内结构</li>
              </ul>
              <div className="main-content" ref={'content'}>
                {data ? data.map((item, index) => {
                  let { picdir, short, sitename } = item;
                  return <a href="javascript:;" key={index}>
                    <img src={picdir} alt="" />
                    <p>{short}</p>
                    <p>{sitename}</p>
                  </a>
                }) : null}
              </div>
              <div className="footer" style={{ display: data != undefined ? (data.length > 12 ? 'block' : 'none') : null }} onClick={(ev) => {
                let t = ev.target,
                  idT = t.getAttribute('id');
                this.refs.content.style.maxHeight = idT == 'true' ? "100%" : '4.14rem';
                t.innerHTML = idT == 'false' ? '显示全部' : '隐藏全部';
                idT == 'true' ? t.setAttribute('id', 'false') : t.setAttribute('id', 'true');
              }} id='true'>
                显示全部
              </div>
            </div>
          </div>
        </div>
      </div>
    </section >
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
        this.setState({
          data: this.state.g55
        });
        break;
      case liAry.indexOf(target) === 1:
        this.setState({
          data: this.state.g55
        });
        break;
      case liAry.indexOf(target) === 2:
        this.setState({
          data: this.state.g56
        });
        break;
    }
  }
}

export default (NavPage);