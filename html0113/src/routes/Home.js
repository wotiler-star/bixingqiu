import React from 'react';
import ReactDOM, { render } from 'react-dom';
import { NavLink } from 'react-router-dom';
import { Icon, Carousel } from 'antd';
import Swiper from 'swiper/dist/js/swiper.js'
import 'swiper/dist/css/swiper.min.css'
import '../static/css/Home.less';
import axios from 'axios';
import './config';
import Qs from 'qs';

// import 'antd/dist/antd.css';




class Home extends React.Component {
  constructor(props, context) {
    super(props, context);
    this.state = {
      tabSwitch: false,
      data: null,
      adshowArr: null,
      showArr: null,
      subshowArr: null,
      i2Arr: null,
      i1Arr: null,
      i3Arr: null,
      i4Arr: null,
      i5Arr: null,
      i6Arr: null,
      i7Arr: null,
      i8Arr: null,
      i9Arr: null,
      i10Arr: null,
      i11Arr: null,
      i12Arr: null,
      kuaiArr: null,
      pai1Arr: null,
      pai2Arr: null,
      tuishowArr: null,
      zhuanjiaArr: null,
      typeI: null,
      feedArr: null,
      text: {
        "a": "关注",
        "b": "已关注",
        "c": "+",
        "d": false
      }
    }
  }

  componentWillMount() {
    document.getElementById('root').scrollIntoView(true);//为ture返回顶部，false为底部
  }

  componentWillUnmount() {
    if (this.swiper) { // 销毁swiper
      this.swiper.destroy()
    }
  }

  componentDidUpdate() {
    if (this.swiper) {
      this.swiper.slideTo(0, 0);
      this.swiper.destroy();
      this.swiper = null;
    }
    this.swiper = new Swiper(this.refs.banner, {
      autoplay: true,
      loop: true,
      navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev',
      },
      pagination: {
        el: '.swiper-pagination',
        type: 'progressbar',
      },
    });
    this.swiper = new Swiper(this.refs.bannerT, {
      autoplay: true,
      loop: true,

      pagination: {
        el: '.swiper-pagination',
        clickable: true,
      }
    });
    this.swiper.el.onmouseover = function () {
      this.swiper.autoplay.stop();
    };
    var aa = this.refs.aa,
      bb = this.refs.bb,
      H = bb.offsetHeight - 500,
      c = () => {
        if (H <= 0) return;
        aa.scrollTop >= (H - 2) ? aa.scrollTop = 0 : aa.scrollTop += 2
      },
      timer = setInterval(c, 30);
    aa.onmouseover = () => clearInterval(timer);
    aa.onmouseleave = () => timer = setInterval(c, 30);
  }

  componentDidMount() {
    window.localStorage.HID != undefined ? this.setState({ typeI: true }) : this.setState({ typeI: false });
    document.body.scrollTop = 0;
    let hid = window.localStorage.getItem('HID');
    axios({
      method: 'post',
      url: `${global.constants.winUrl}`,
      data: { "data": { "hid": hid == undefined ? 0 : hid } }
    }).then(res => {
      let a = [], f = res.feedArr.slice(-3);
      for (var i = 0; i < res.tuishowArr.length; i += 3) {
        a.push(res.tuishowArr.slice(i, i + 3));
      }
      this.setState({
        adshowArr: res.adshowArr,
        showArr: res.showArr,
        subshowArr: res.subshowArr,
        i2Arr: res.i2Arr,
        data: res.i1Arr,
        i1Arr: res.i1Arr,
        i3Arr: res.i3Arr,
        i4Arr: res.i4Arr,
        i5Arr: res.i5Arr,
        i6Arr: res.i6Arr,
        i7Arr: res.i7Arr,
        i8Arr: res.i8Arr,
        i9Arr: res.i9Arr,
        i10Arr: res.i10Arr,
        i11Arr: res.i11Arr,
        i12Arr: res.i12Arr,
        kuaiArr: res.kuaiArr,
        pai1Arr: res.pai1Arr,
        pai2Arr: res.pai2Arr,
        tuishowArr: a,
        zhuanjiaArr: res.zhuanjiaArr,
        feedArr: f.concat(res.feedArr),
      });

    })
  }


  render() {
    let { adshowArr, showArr, subshowArr, kuaiArr, data, pai1Arr, pai2Arr, zhuanjiaArr, tuishowArr, text } = this.state;
    let indexID = 0,
      cataid = 0;
    return <section className='homeBox'>
      <div className='mainBox'>
        <div className='primaryBox'>
          <div className='primary'>
            <div className='primary-left'>
              <div className='carousel'>
                <div className=" swiper-container" ref={'banner'}>
                  <div className="swiper-wrapper">
                    {showArr ? showArr.map((item, index) => {
                      return <div className="swiper-slide" key={index}>
                        <span className='mode'>{item.title}</span>
                        <a href={item.url}>
                          <img src={item.picdir} alt="" />
                        </a>
                      </div>
                    }) : null}
                    {showArr ? showArr.map((item, index) => {
                      return <div className="swiper-slide" key={index}>
                        <span className='mode'>{item.title}</span>
                        <img src={item.picdir} alt="" />
                      </div>
                    }) : null}
                  </div>
                  <div className="swiper-button-prev">
                    <Icon type="left" theme="outlined" />
                  </div>
                  <div className="swiper-button-next">
                    <Icon type="right" theme="outlined" />
                  </div>
                  <div className="swiper-pagination"></div>
                </div>

                <div className='recommend-cont'>
                  {subshowArr ? subshowArr.map((item, index) => {
                    return <a href={item.url} key={index}>
                      <img src={item.picdir} alt="" />
                      <p>{item.title}</p>
                    </a>
                  }) : null}
                </div>
              </div>
              {adshowArr ? adshowArr.map((item, index) => {
                return <NavLink to='/#' className='imgBox' key={index}>
                  <img src={item.picdir} alt={item.title} />
                </NavLink>
              }) : null}


            </div>

            <div className='primary-right'>
              <h3>最新资讯</h3>
              <NavLink to='/livenews?cataid=8' className='more'> </NavLink>

              <div className='list-box' id='list-items'>
                <div className='item-box'>
                  {kuaiArr ? kuaiArr.map((item, index) => {
                    return <div className='item' key={index}>
                      <div className='item-icons'>
                        <div className='item-left' id={'active'}>
                          <span>{item.time}</span>
                        </div>
                      </div>
                      <NavLink to={`/Detailed?cataid=8&id=${item.id}`}>
                        <span>{item.title}</span>
                      </NavLink>
                    </div>
                  }) : ''}
                </div>
                <NavLink to='/livenews?cataid=1' className='filsh'>查看更多</NavLink>
              </div>
              <div className='gradual'></div>
            </div>
          </div>
        </div>

        <div className='main-content'>
          <div className='left-content'>
            <ul className="title" onClick={this.switch}>
              <li className="active">头条</li>
              <li className=" "> 行情</li>
              <li className=" ">研报</li>
              <li className=" ">人物</li>
              <li className=" ">宏观</li>
              <li className=" ">技术</li>
              <li className=" ">政策</li>
              <li className=" ">评级</li>
              <li className=" ">全球</li>
              <li className=" "><NavLink to='/column?cataid=25'>专栏</NavLink></li>
            </ul>
            <div className='list-content'>

              {data ? data.map((item, index) => {
                indexID = data[index].id;
                cataid = data[index].cataid;
                let { picdir_list, title, short, source, riqi, keywords, id } = item;
                return <div className='news-list' key={index}>
                  <NavLink to={`/Detailed?cataid=11&id=${id}`}>
                    <div className='imgBox'>
                      <img src={picdir_list} alt="" />
                    </div>
                    <div className='content-text'>
                      <h1>{title}</h1>
                      <p>{short}</p>
                    </div>
                    <div className='list-bottom'>
                      <span>{source}</span>
                      <span>{riqi}</span>
                      {/**<NavLink to='/#'>
                        {keywords}
                      </NavLink>
                      <p>关键字:</p> */}
                    </div>
                  </NavLink>
                  <div className='shadow'></div>
                </div>
              }) : null}

            </div>
            <div className="lazy" onClick={() => {
              let obj = {
                "id": indexID,
                "cataid": cataid
              }
              axios({
                method: 'post',
                url: `${global.constants.winUrl}?a=getMore`,
                data: { "data": obj }
              }).then(res => {
                console.log(res);
                this.setState({
                  data: this.state.data.concat(res)
                });
              })
            }}>
              点 击 加 载 更 多
          </div>
          </div>
          <div className='right-content'>
            <div className='advertising'>
              <h4>推广</h4>
              <div className="swiper-container swiper-t " ref={'bannerT'}>
                <div className="swiper-wrapper">
                  {tuishowArr ? tuishowArr.map((item, index) => {
                    return <div className="swiper-slide" key={index}>
                      {item.map((item2, index2) => {
                        let { picdir, short, title, url } = item2;
                        return <div className='swiper-list' key={index2}>
                          <a href={url}>
                            <div className='imgBox'>
                              <img src={picdir} alt="" />
                            </div>
                            <div className="textBox">
                              <span>{title}</span>
                              <p>{short}</p>
                            </div>
                          </a>
                        </div>
                      })}
                    </div>
                  }) : null}
                </div>
                <div className="swiper-pagination"></div>
              </div>
            </div>


            <div className='products-box'>
              <div className='title'>
                <h3>专栏作家</h3>
                <NavLink to='/author'>
                  <span>更多</span>
                  <i className="more-2"></i>
                </NavLink>
              </div>
              {zhuanjiaArr ? zhuanjiaArr.map((item, index) => {
                let { id, name, picdir, short, ifover } = item;
                return <div className='products' key={index}>
                  <a href={'javascript:;'}>
                    <div className='imgBox'>
                      <NavLink to={`/mydetail?id=${id}`}>
                        <img src={picdir} alt="" />
                      </NavLink>
                    </div>
                    <NavLink to={`/mydetail?id=${id}`} style={{ textDecoration: 'none' }}>
                      <div className="textBox">
                        <span>{name}</span>
                        <p>{short}</p>
                      </div>
                    </NavLink>
                    <div className='like' onClick={(ev) => {
                      if (!this.state.typeI) {
                        alert('请先登录后在关注！');
                        return;
                      }
                      let hid = window.localStorage.getItem('HID');
                      data = {
                        "hid": hid,
                        "mycarehid": id
                      };
                      if (ifover == 1) {
                        axios({
                          method: 'post',
                          url: `${global.constants.winUrl}?a=carehid`,
                          data: {
                            "data": data
                          }
                        }).then(res => console.log(res));
                      }

                      ev.target.innerHTML = '已关注';
                    }}>
                      <b>{ifover == 0 ? "" : "+"}</b>{ifover == 1 || ifover == undefined ? "关注" : "已关注"}
                    </div>
                  </a>
                </div>
              }) : null}
            </div>

            <div className="mostviews">
              <h3>一周点击排行</h3>
              {pai1Arr ? pai1Arr.map((item, index) => {
                let { id, hitnum, picdir_list, title, num_days, pinglunnum } = item;
                return <div className="listBox" key={index}>
                  <NavLink to={`/Detailed?cataid=11&id=${id}`}>
                    <div className="imgBox">
                      <img src={picdir_list} alt="" />
                    </div>
                    <p>{title}</p>
                  </NavLink>
                  <div className="project">
                    <Icon type="dashboard" theme="outlined" />&nbsp;
                    <span>{num_days}天前&nbsp;&nbsp;&nbsp;</span>
                    <Icon type="eye" theme="outlined" />&nbsp;
                    <span>{hitnum}&nbsp;&nbsp;&nbsp;</span>
                    <Icon type="message" theme="outlined" />&nbsp;
                    <span>{pinglunnum}&nbsp;&nbsp;&nbsp;</span>
                  </div>
                </div>
              }) : null}
            </div>

            <div className="mostviews">
              <h3>一周评论排行</h3>
              {pai2Arr ? pai2Arr.map((item, index) => {
                let { id, hitnum, picdir_list, title, pinglunnum, num_days } = item;
                return <div className="listBox" key={index}>
                  <NavLink to={`/Detailed?cataid=11&id=${id}`}>
                    <div className="imgBox">
                      <img src={picdir_list} alt="" />
                    </div>
                    <p>{title}</p>
                  </NavLink>
                  <div className="project">
                    <Icon type="dashboard" theme="outlined" />&nbsp;
                    <span>{num_days}天前&nbsp;&nbsp;&nbsp;</span>
                    <Icon type="eye" theme="outlined" />&nbsp;
                    <span>{hitnum}&nbsp;&nbsp;&nbsp;</span>
                    <Icon type="message" theme="outlined" />&nbsp;
                    <span>{pinglunnum}&nbsp;&nbsp;&nbsp;</span>
                  </div>
                </div>
              }) : null}
            </div>

            <div className="comment">
              <h3>实时最新评论</h3>
              <div className="list-content" ref={'aa'}>
                <div className="list-content-2" ref={'bb'}>
                  {this.state.feedArr ? this.state.feedArr.map((item, index) => {
                    let { content, name, picdir, pname, riqi } = item;
                    return <div className='listBox' key={index}>
                      <div className="topBox">
                        <img src={picdir == undefined ? "http://liancaijing.com/json/avatar.png" : picdir} alt="" />
                        <a href="javascript:;">{name == undefined ? '网友' : name}</a>
                        <span>{riqi}</span>
                      </div>
                      <div className="contentBox">
                        <a href="javascript:;">{content}</a>
                      </div>
                      <div className="bottomBox">
                        评论在：“ {pname} ”
                    </div>
                    </div>
                  }) : null}
                </div>
              </div>
            </div>

            <div className="tags-box">
              <h3>热门标签</h3>
              <div className="tags-cont">
                <a href="/#">智能合约</a>
                <a href="/#">挖矿</a>
                <a href="/#">比特币</a>
                <a href="/#">监管</a>
                <a href="/#">DAO</a>
                <a href="/#">王峰十问</a>
                <a href="/#">bitcoin</a>
                <a href="/#">瑞波币</a>
                <a href="/#">硬分叉</a>
                <a href="/#">侧链</a>
                <a href="/#">去中心化</a>
                <a href="/#">数字货币</a>
                <a href="/#">以太坊</a>
                <a href="/#">加密货币</a>
                <a href="/#">区块链</a>
                <a href="/#">比特币扩容</a>
                <a href="/#">EOS</a>
                <a href="/#">ETC</a>
                <a href="/#">中本聪</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  }

  switch = (ev) => {
    let target = ev.target,
      tarName = target.tagName,
      tarClass = target.className,
      liAry1 = target.parentNode.childNodes,
      liAry = [...liAry1],
      { i1Arr, i2Arr, i3Arr, i4Arr, i5Arr, i6Arr, i7Arr, i8Arr, i9Arr, i10Arr, i11Arr, i12Arr } = this.state;
    console.log(i12Arr);
    liAry.forEach(item => tarName === 'LI' ? item.setAttribute('class', '') : null);
    tarName === "LI" ? target.setAttribute('class', 'active') : null;
    switch (tarName === 'LI') {
      case liAry.indexOf(target) === 0:
        this.setState({ data: i1Arr });
        break;
      case liAry.indexOf(target) === 1:
        this.setState({ data: i2Arr });
        break;
      case liAry.indexOf(target) === 2:
        this.setState({ data: i3Arr });
        break;
      case liAry.indexOf(target) === 3:
        this.setState({ data: i4Arr });
        break;
      case liAry.indexOf(target) === 4:
        this.setState({ data: i5Arr });
        break;
      case liAry.indexOf(target) === 5:
        this.setState({ data: i6Arr });
        break;
      case liAry.indexOf(target) === 6:
        this.setState({ data: i7Arr });
        break;
      case liAry.indexOf(target) === 7:
        this.setState({ data: i8Arr });
        break;
      case liAry.indexOf(target) === 8:
        this.setState({ data: i9Arr });
        break;
      case liAry.indexOf(target) === 9:
        this.setState({ data: i10Arr });
        break;
      case liAry.indexOf(target) === 10:
        this.setState({ data: i11Arr });
        break;
      case liAry.indexOf(target) === 11:
        this.setState({ data: i12Arr });
        break;
    }
    console.log(ev.target.className);
  }
}

export default (Home);