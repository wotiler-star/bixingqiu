import React from 'react';
import ReactDOM, { render } from 'react-dom';
import { NavLink } from 'react-router-dom';
import { Icon } from 'antd';
import '../static/css/Column.less';
import Qs from "qs";
import axios from "axios";

class Column extends React.Component {
  constructor(props, context) {
    super(props, context);
    this.state = {
      seniority: false,
      data: null,
      zhuanjiaArr: null,
      paihangArr: null,
      typeI: null,
      cataArr: null,
      dataTab: null,
      tuijian: null
    }
  }

  componentWillMount() {
    document.getElementById('root').scrollIntoView(true);//为ture返回顶部，false为底部
  }

  async componentDidMount() {
    window.localStorage.HID != undefined ? this.setState({ typeI: true }) : this.setState({ typeI: false });
    axios.get(`${global.constants.winUrl}?c=Content&a=getExpertContentList`).then(res => {
      this.setState({
        data: res.data,
        cataArr: res.cataArr,
      })
    });
    let hid = window.localStorage.getItem('HID');
    axios({
      method: 'post',
      url: `${global.constants.winUrl}?c=Content&a=getExpertListHot`,
      data: { "data": { "hid": hid } }
    }).then(res => {
      console.log(res);
      this.setState({
        zhuanjiaArr: res
      });
    });
    axios({
      method: 'post',
      url: `${global.constants.winUrl}?c=Content&a=getExpertListRank`,
      data: { "data": { "hid": hid } }
    }).then(res => {
      console.log(res);
      this.setState({
        paihangArr: res
      });
    });
    axios.get(`${global.constants.winUrl}?c=Content&a=getHotExpertContentList`).then(res => {
      console.log(res);
      this.setState({
        tuijian: res,
        dataTab: res
      });
    });
  }


  render() {
    let { seniority, data, zhuanjiaArr, paihangArr, cataArr, dataTab } = this.state;
    let indexID = 0,
      cataid = 0;
    return <section>
      <div className='column'>
        <div className='leftBox'>

          <div className='title' onClick={this.switch}>
            <a href='javascript:;' className='active'>推荐</a>
            {cataArr ? cataArr.map((item, index) => {
              let { sort } = item;
              return <a href="javascript:;" key={index}>{sort}</a>
            }) : null}
          </div>

          {dataTab ? dataTab.map((item, index) => {
            indexID = dataTab[index].id;
            cataid = dataTab[index].cataid;
            let { picdir_list, title, cnt_short, riqi, hitnum, cataid, hid, picdir_h, nickname, id } = item;
            return <div className='list' key={index}>
              <NavLink to={`/Detailed?cataid=${cataid}&id=${id}&`}>
                <img src={picdir_list} alt="" />
              </NavLink>
              <div className='text'>
                <NavLink to={`/Detailed?cataid=${cataid}&id=${id}&`} style={{ textDecoration: 'none' }}>
                  <h2>{title}</h2>
                </NavLink>
                <p>{cnt_short}</p>
                <div>
                  <NavLink to={`/mydetail?id=${hid}&`}>
                    <img src={picdir_h} alt="" />
                    <span>{nickname}</span>
                  </NavLink>
                  <em>{hitnum}</em>
                  <Icon type="eye" theme="outlined" />
                </div>
              </div>
            </div>
          }) : null}

          <div className="lazy" onClick={() => {
            let obj = {
              "id": indexID,
              "cataid": cataid
            }
            axios({
              method: 'post',
              url: `${global.constants.winUrl}?c=Content&a=getMore`,
              data: { "data": obj }
            }).then(res => {
              console.log(this.state.dataTab, res);
              this.setState({
                dataTab: this.state.dataTab.concat(res)
              });
            })
          }}>
            点 击 加 载 更 多
          </div>

        </div>
        <div className='rightBox'>

          <div className='enter'>
            <h3>申请入驻</h3>
            <p>成为专栏作者，让更多人看到您的观点</p>
            <NavLink to={this.state.typeI ? '/personal/realname' : '/login'}>入驻</NavLink>
          </div>

          <div className='ranking'>
            <div className='tabs-height'>
              <h3>专栏排行</h3>
              <div>
                {
                  /**<span className={seniority ? '' : 'active'} onClick={() => {
                                    this.setState({
                                      seniority: false
                                    })
                                  }}>日</span>
                                  <span className={seniority ? 'active' : ''} onClick={() => {
                                    this.setState({
                                      seniority: true
                                    })
                                  }}>周</span>
                                 */
                }
              </div>
            </div>

            {paihangArr ? paihangArr.map((item, index) => {
              let { picdir, name, short, id, ifover } = item;
              return <div className='main' key={index}>
                <a href='javascript:;'>
                  <NavLink to={`/mydetail?id=${id}`} style={{ textDecoration: 'none' }}>
                    <img src={picdir == undefined ? "https://img.jinse.com/957312_image20.png" : picdir} alt="" />

                    <div className='name'>
                      <span>{name}</span>
                      <span>{short}</span>
                    </div>
                  </NavLink>

                  <div className='attention'>
                    <span>21万+</span>
                    <a href="javascript:;" className='a' onClick={(ev) => {
                      if (!this.state.typeI) {
                        alert('请先登录后在关注！');
                        return;
                      }
                      let hid = window.localStorage.getItem('HID');
                      if (ifover == 1) {
                        axios({
                          method: 'post',
                          url: `${global.constants.winUrl}?a=carehid`,
                          data: {
                            "data": {
                              "hid": hid,
                              "mycarehid": id
                            }
                          }
                        }).then(res => console.log(res));
                      }

                      ev.target.innerHTML = '已关注';
                    }} >{ifover == 1 || ifover == undefined ? "+ 关注" : "已关注"}</a>
                  </div>
                </a>
              </div>

            }) : null}
          </div>

          <div className='recommended'>
            <h3>推荐作者</h3>

            <div className='control'>
              {zhuanjiaArr ? zhuanjiaArr.map((item, index) => {
                let { id, name, picdir, short, ifover, num_wenzhang, hitnum } = item;
                return <div className={'listBox'} key={index}>
                  <NavLink to={`/mydetail?id=${id}`}>
                    <img src={picdir == undefined ? "https://img.jinse.com/285444_image20.png" : picdir} alt="Three" />
                  </NavLink>
                  <div className="title">
                    <NavLink to={`/mydetail?id=${id}`}>
                      {name}
                    </NavLink>
                    <span>{short}</span>
                  </div>
                  <ul>
                    <i>{num_wenzhang}</i>
                    <span>文章数</span>
                    <i>{hitnum}</i>
                    <span>浏览数</span>
                  </ul>
                  <div className='attention' onClick={(ev) => {
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
                  }} >
                    {ifover == 1 || ifover == undefined ? "+ 关注" : "已关注"}
                  </div>
                </div>

              }) : null}

              {/*<NavLink to='/#'>
                <img src="https://img.jinse.com/285444_image20.png" alt="Three" />
                <div>
                  <NavLink to='/#'>
                    Three
                  </NavLink>
                  <span>公众号作者-极界区块链</span>
                </div>
                <ul>
                  <i>39</i>
                  <span>文章数</span>
                  <i>128.1万</i>
                  <span>浏览数</span>
                </ul>
                <NavLink to='/#' className='attention'>
                  + 关注
                </NavLink>
            </NavLink>*/}

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
      { tuijian, data } = this.state;
    liAry.forEach(item => tarName === 'A' ? item.setAttribute('class', '') : null);
    tarName === "A" ? target.setAttribute('class', 'active') : null;
    switch (tarName === 'A') {
      case liAry.indexOf(target) === 0:
        this.setState({ dataTab: tuijian });
        break;
      case liAry.indexOf(target) === 1:
        this.setState({ dataTab: data.cataid26 })
        break;
      case liAry.indexOf(target) === 2:
        this.setState({ dataTab: data.cataid27 })
        break;
      case liAry.indexOf(target) === 3:
        this.setState({ dataTab: data.cataid28 })
        break;
      case liAry.indexOf(target) === 4:
        this.setState({ dataTab: data.cataid29 })
        break;
      case liAry.indexOf(target) === 5:
        this.setState({ dataTab: data.cataid30 })
        break;
      case liAry.indexOf(target) === 6:
        this.setState({ dataTab: data.cataid202 })
        break;
      case liAry.indexOf(target) === 7:
        this.setState({ dataTab: data.cataid203 })
        break;
      case liAry.indexOf(target) === 8:
        this.setState({ dataTab: data.cataid204 })
        break;
      case liAry.indexOf(target) === 9:
        this.setState({ dataTab: data.cataid205 })
        break;
      case liAry.indexOf(target) === 10:
        this.setState({ dataTab: data.cataid207 })
        break;
      case liAry.indexOf(target) === 11:
        this.setState({ dataTab: data.cataid208 })
        break;
      case liAry.indexOf(target) === 12:
        this.setState({ dataTab: data.cataid209 })
        break;
    }
    console.log(ev.target.className);
  }
}

export default (Column);