import React from 'react';
import ReactDOM, {render} from 'react-dom';
import {Icon, Input, Button} from 'antd';
import {NavLink} from 'react-router-dom';
import axios from 'axios';
import '../static/css/Detailed.less';
import Qs from 'qs';

const {TextArea} = Input;

class Detailed extends React.Component {
  constructor(props, context) {
    super(props, context);
    this.state = {
      tabSwitch: false,
      nextActive: false,
      goTop: false,
      aboutArr: null,
      hotArr: null,
      nextArr: null,
      feedArr: null,
      index: null,
      id: null,
      cataid: null,
      title: null,
      hid: null,
      thid: null,
      data: null,
      i: null,
      favorate: null,
      feednum: null,
      favid:null
    }
  }

  async componentWillMount() {
    document.getElementById('root').scrollIntoView(true);//为ture返回顶部，false为底部
  }

  async componentDidMount() {
    let top = this.refs.top.offsetHeight,
        hid = window.localStorage.getItem('HID'),
        {location: {search}} = this.props,
        {id = 0} = Qs.parse(search.substr(1)) || {},
        {cataid = 0} = Qs.parse(search.substr(1)) || {};
    id = parseFloat(id);
    window.onscroll = () => {
      let scr = document.documentElement.scrollTop || document.body.scrollTop;
      scr > 1200 ? this.setState({nextActive: true}) : this.setState({nextActive: false});
      scr > top ? this.setState({goTop: true}) : this.setState({goTop: false});
    };


    axios.get(`${global.constants.winUrl}?c=Content&cataid=${cataid}&id=${id}&hid=${hid == undefined ? 0 : hid}`).then(res => {
      console.log(res);

      this.setState({
        aboutArr: res.aboutArr,
        feedArr: res.feedArr,
        hotArr: res.hotArr,
        nextArr: res.nextArr,
        index: res.feedArr.length,
        thid: hid ? true : false,
        hid: hid,
        id: id,
        cataid: cataid,
        data: res.data,
        title: res.data[0].title,
        i: res.nextArr[0],
        favorate: res.data[0].favorate,
        feednum: res.data[0].feednum,
        favid:res.data[0].favid
      });
    });
  }

  render() {
    return <section className='detailed'>

      <div className='main' ref={'top'}>
        {/* <div className='activity'>
        <img src="https://hx24.huoxing24.com/image/news/2018/06/06/1528271007010029.png?x-oss-process=style/image_jpg"
          alt="" />
      </div>*/}
        <div className='left-content'>
          {this.state.data ? this.state.data.map((item, index) => {
            let {cnt, cnt_phone, cnt_short, cnt_short_phone, hitnum, source, riqi, title, short, favorate} = item;

            return <div className='detailBox' key={index}>
              <div className='text-header'>
                <h1>
                  {title}
                </h1>
                <div className="issue-box">
                  <span>{source}</span>
                  <span>·</span>
                  <span>{riqi}</span>
                  <span>热度: <i>{hitnum}</i></span>
                </div>
              </div>
              <div className={'synopsisBox'}>
                <div className="synopsis" dangerouslySetInnerHTML={{__html: cnt_short}}>
                </div>
              </div>
              <div className="detail-text" dangerouslySetInnerHTML={{__html: cnt}}/>
            </div>
          }) : null}


          {
            /*<div className="keyword">
                        关键字：
                        <NavLink to="/list">火星晨报</NavLink>
                        <NavLink to="/list">赵长鹏</NavLink>
                        <NavLink to="/list">日本金融厅</NavLink>
                        <NavLink to="/list">火币</NavLink>
                        <NavLink to="/list">BitTrade</NavLink>
                        <NavLink to="/list">SPoS</NavLink>
                        <NavLink to="/list">共识机制</NavLink>
                      </div> */
          }
          <div className={this.state.goTop ? 'news-share' : 'news-share news-active'}>
            {
              /**<div className='author-left'>
               <img
               src="https://static-hx24.huoxing24.com/usericon/01a70e76781235638aa28b000b913c26/portrait/1522242402596504_300_300.png"
               alt="" />
               <b>火星财经</b>
               <span>关注</span>
               </div> */
            }
            <div className="author-right">
              <div className="back-top" onClick={this.goTop}></div>
              <div className="share-box">
                <a href="/#" className='icon-wechat'></a>
                <a href="/#" className='icon-weibo'>
                  <div className="wechat-qrcode">
                    <h4>微博扫一扫：分享</h4>
                    <div className="qrcode">
                      <img src="static\media\hx-ewm-c6929e3815.png" alt=''/>
                    </div>
                    <div className="help">
                      <p>微博里点“发现”，扫一下</p>
                      {/*<p>二维码便可将本文分享至朋友圈。</p> */}
                    </div>
                  </div>
                </a>
                <a href="/#" className='icon-qq'></a>
                <p>分享</p>
              </div>
              <div className="comment-btn">
                <p>{this.state.feednum}</p>
                <span></span>
              </div>
              <div className={this.state.favorate == 0 ? 'collect-img on' : 'collect-img'} onClick={(ev) => {
                if (this.state.favorate === 1) {
                  axios({
                    method: 'post',
                    url: `${global.constants.winUrl}?c=Content&a=ajax_favorate`,
                    data: {
                      "data": {
                        hid: this.state.hid,
                        pid: this.state.id,
                        cataid: this.state.cataid
                      }
                    }
                  }).then(res => {
                    console.log(res);
                  });
                  ev.target.className = 'collect-img on';
                } else {
                  axios({
                    method: 'post',
                    url: `${global.constants.winUrl}?c=h&a=ajax_del_favorate`,
                    data: {
                      "data": {
                        favid:this.state.favid
                      }
                    }
                  }).then(res => {
                    console.log(res);
                  });
                  ev.target.className = 'collect-img';
                }
                
              }}></div>
            </div>
          </div>
          <div className="new-interest">
            <h5>相关新闻</h5>
            <div className="interest-box">
              {this.state.aboutArr ? this.state.aboutArr.map((item, index) => {
                let {location: {search}} = this.props,
                    {cataid = 8} = Qs.parse(search.substr(1)) || {}, a;
                let {picdir_list, short, id} = item;
                return <NavLink to={`/Detailed?cataid=${cataid}&id=${id}`} onClick={() => window.location.reload(true)}
                                key={index}>
                  <img src={picdir_list} alt=""/>
                  <p>{short}</p>
                </NavLink>
              }) : null}
            </div>
          </div>
        </div>
        <div className='right-content'>

          <div className='recomend'>
            <h3>热门新闻</h3>
            {this.state.hotArr ? this.state.hotArr.map((item, index) => {
              let {location: {search}} = this.props,
                  {cataid = 8} = Qs.parse(search.substr(1)) || {}, a;
              let {picdir_list, title, riqi, id} = item;
              return <div className='listBox' key={index}>
                <NavLink to={`/Detailed?cataid=${cataid}&id=${id}`} onClick={() => window.location.reload(true)}>
                  <img src={picdir_list} alt=""/>
                  <span>{title}</span>
                  <p>{riqi}</p>
                </NavLink>
              </div>
            }) : null}
          </div>
          {this.state.i ? this.state.nextArr.map((item, index) => {
            let {location: {search}} = this.props,
                {cataid = 8} = Qs.parse(search.substr(1)) || {}, a;
            return <div className={this.state.nextActive ? 'next-page next-active' : 'next-page'} key={index}>
              <NavLink to={`/Detailed?cataid=${cataid}&id=${item.id}`} onClick={() => window.location.reload(true)}>
                <h5>下一篇</h5>
                <img src={item.picdir_list} alt=""/>
                <p>{item.title ? item.title : ''}</p>
              </NavLink>
            </div>
          }) : null}

        </div>
      </div>


      <div className="footer">
        <div className="footer-content">
          <div className="reply-issue">
            <div className="prompt-not-login" style={{display: !this.state.thid ? 'block' : 'none'}}>
              <p>请
                <span className="reply-login-button" onClick={() => {
                  this.props.history.push(`/login?cataid=${this.state.cataid}&id=${this.state.id}`)
                }}>登录
                </span> 后输入评论…
              </p>
            </div>

            <div className={'form_box'} style={{display: this.state.thid ? 'block' : 'none'}}>
              <TextArea placeholder="评论内容" autosize={{minRows: 2, maxRows: 6}}/>
              <Button type="primary" style={{marginTop: '0.2rem'}} onClick={(ev) => {
                let t = ev.target,
                    v = t.previousSibling.value,
                    {title, cataid, id, hid} = this.state,
                    obj = {
                      pname: title,
                      pid: id,
                      hid: hid,
                      hname: window.localStorage.getItem('NICKNAME'),
                      cataid: cataid,
                      content: v
                    };
                if (v == '') return;
                console.log(obj);
                axios({
                  method: 'post',
                  url: `${global.constants.winUrl}?c=Content&a=ajax_feedback`,
                  data: {"data": obj}
                }).then(res => {
                  res.success == 0 ? window.location.reload(true) : null;
                });
              }}>提交</Button>
            </div>
          </div>
          <div className="reply-module">
            <ul>
              <h3>评论（{this.state.index}条）</h3>
              {this.state.feedArr ? this.state.feedArr.map((item, index) => {
                let {picdir, name, content, riqi} = item;
                return <li key={index} style={{display: !this.state.index ? 'none' : 'block'}}>
                  <img src={picdir} alt=""/>
                  <div className="reply-detail">
                    <span>{name}</span>
                    <p>{content}</p>
                  </div>
                  <div className="reply-info">
                    <span>{riqi}</span>
                    <div className="reply-info-item">
                      {/*<Icon type="message" theme="outlined"/>*/}
                      {/*<p>回复</p>*/}
                    </div>
                  </div>
                </li>
              }) : null}
            </ul>
            <span className='all-reply-btn' style={{display: !this.state.index ? 'block' : 'none'}}>暂无数据！</span>
          </div>
        </div>
      </div>
    </section>
  }

  goTop = () => {
    let timer = setInterval(function () {
      let osTop = document.documentElement.scrollTop || document.body.scrollTop,
          isSpeed = Math.floor(-osTop / 6);
      document.documentElement.scrollTop = document.body.scrollTop = osTop + isSpeed;
      if (osTop == 0) clearInterval(timer);
    }, 17);
  }
}

export default (Detailed);