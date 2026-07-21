import React from 'react';
import ReactDOM, { render } from 'react-dom';
import axios from "axios";
import Qs from "qs";
import '../static/css/Details.less';
import { Icon } from "antd";
import { NavLink } from "react-router-dom";

class init extends React.Component {
  constructor(props, context) {
    super(props, context);
    this.state = {
      data: null,
      date: {
        yue: null,
        ri: null,
        shijian: null,
        xinqi: null
      }
    }
  }

  componentWillMount() {
    document.getElementById('root').scrollIntoView(true);//为ture返回顶部，false为底部
  }

  componentDidMount() {
    let { location: { search } } = this.props,
      { id = 0 } = Qs.parse(search.substr(1)) || {},
      { cataid = 0 } = Qs.parse(search.substr(1)) || {},
      weekDay = ["星期天", "星期一", "星期二", "星期三", "星期四", "星期五", "星期六"];
    id = parseFloat(id);
    axios.get(`${global.constants.winUrl}?c=Content&cataid=${cataid}&id=${id}`).then(res => {
      console.log(res, res.data[0].riqi);
      let timeArr = res.data[0].riqi.replace(' ', ':').replace(/\:/g, '-').split('-'),
        dt = new Date(`${timeArr[0]}-${timeArr[1]}-${timeArr[2]}`).getDay();

      this.setState({
        data: res.data,
        date: {
          yue: timeArr[1],
          ri: timeArr[2],
          shijian: `${timeArr[3]}:${timeArr[4]}`,
          xinqi: weekDay[dt]
        }
      })
    })
  }

  render() {
    let { date } = this.state;
    console.log(date);
    return <div className="details">
      <div className="leftBox">
        <div className="title">
          <div className="riqi">
            <h2>{date.ri}</h2>
            <p>{date.yue}月</p>
          </div>
          <div className={'riqi2'}>
            <span>{date.xinqi}</span>
          </div>
          <br />
          <h1>{date.shijian}</h1>
        </div>
        {this.state.data ? this.state.data.map((item, index) => {
          let { title, cnt_short, lihao, likong, id } = item;
          return <div className="content" key={index}>
            <h1>{title}</h1>
            <div className={'text'} dangerouslySetInnerHTML={{ __html: cnt_short }}>
            </div>
            <div className="judge-profit">
              <a href='javascript:;'>
                <Icon type="rise" theme="outlined" />
                <span className='span' id='false'>+1</span>
                <span onClick={(ev) => {
                  let t = ev.target,
                    s = t.previousElementSibling;
                  s.style.display = 'block';
                  setTimeout(() => s.style.display = 'none', 1000)
                  if (s.id == 'false') {
                    s.innerHTML = '+1';
                    s.setAttribute('id', 'true');
                    axios({
                      method: 'post',
                      url: `${global.constants.winUrl}?c=Content&a=ajax_set_lihao`,
                      data: { "data": { "id": id, "type": 0 } }
                    });
                    ev.target.nextElementSibling.innerHTML = parseFloat(ev.target.nextElementSibling.innerHTML) + 1;
                    return;
                  }
                  if (s.id == 'true') {
                    s.innerHTML = '-1';
                    s.setAttribute('id', 'false');
                    axios({
                      method: 'post',
                      url: `${global.constants.winUrl}?c=Content&a=ajax_set_lihao`,
                      data: { "data": { "id": id, "type": 1 } }
                    });
                    ev.target.nextElementSibling.innerHTML = parseFloat(ev.target.nextElementSibling.innerHTML) - 1;
                    return;
                  }
                }}>利好</span>
                <b>{lihao}</b>
              </a>
              <a href='javascript:;'>
                <Icon type="fall" theme="outlined" />
                <span className='span2' id='fals'>+1</span>
                <span onClick={(ev) => {
                  let t = ev.target,
                    s = t.previousElementSibling;
                  s.style.display = 'block';
                  setTimeout(() => s.style.display = 'none', 1000)
                  if (s.id == 'fals') {
                    s.innerHTML = '+1';
                    s.setAttribute('id', 'tru');
                    axios({
                      method: 'post',
                      url: `${global.constants.winUrl}?c=Content&a=ajax_set_likong`,
                      data: { "data": { "id": id, "type": 0 } }
                    });
                    ev.target.nextElementSibling.innerHTML = parseFloat(ev.target.nextElementSibling.innerHTML) + 1;
                    return;
                  }
                  if (s.id == 'tru') {
                    s.innerHTML = '-1';
                    s.setAttribute('id', 'fals');
                    axios({
                      method: 'post',
                      url: `${global.constants.winUrl}?c=Content&a=ajax_set_likong`,
                      data: { "data": { "id": id, "type": 1 } }
                    });
                    ev.target.nextElementSibling.innerHTML = parseFloat(ev.target.nextElementSibling.innerHTML) - 1;
                    return;
                  }
                }}>利空</span>
                <b>{likong}</b>
              </a>
            </div>
          </div>
        }) : null}

      </div>
      <div className='market'>
        <div className="title">
          <h3>涨幅榜</h3>
          <NavLink to='/#' className='more-6'></NavLink>
        </div>
        <div className='tab-box'>
          <div className='tab-switch'>
            <span className={this.state.tabSwitch ? '' : 'active'}
              onClick={() => this.setState({ tabSwitch: false })}>涨幅</span>
            <span className={this.state.tabSwitch ? 'active' : ''}
              onClick={() => this.setState({ tabSwitch: true })}>跌幅</span>
          </div>
          <div className='tab-switch-box'>
            <ul className='titleBox'>
              <li>排名</li>
              <li>名称</li>
              <li>价格</li>
              <li>涨幅</li>
            </ul>
            <ul className='tab-list-box' style={{ display: this.state.tabSwitch ? 'none' : 'block' }}>
              <li className='list-box'>
                <span>1</span>
                <span>
                  <img src="https://static-hx24.huoxing24.com/coin/icon/LHC.png" alt="" />
                  LHC
                </span>
                <span>￥0.30053</span>
                <span>￥0.30053</span>
              </li>
              <li className='list-box'>
                <span>2</span>
                <span>
                  <img src="https://static-hx24.huoxing24.com/coin/icon/LHC.png" alt="" />
                  LHC
                </span>
                <span>￥0.30053</span>
                <span>￥0.30053</span>
              </li>
              <li className='list-box'>
                <span>3</span>
                <span>
                  <img src="https://static-hx24.huoxing24.com/coin/icon/LHC.png" alt="" />
                  LHC
                </span>
                <span>￥0.30053</span>
                <span>￥0.30053</span>
              </li>
              <li className='list-box'>
                <span>4</span>
                <span>
                  <img src="https://static-hx24.huoxing24.com/coin/icon/LHC.png" alt="" />
                  LHC
                </span>
                <span>￥0.30053</span>
                <span>￥0.30053</span>
              </li>
            </ul>
            <ul className='tab-list-box tab-list-box-2'
              style={{ display: this.state.tabSwitch ? 'block' : 'none' }}>
              <li className='list-box'>
                <span>1</span>
                <span>
                  <img src="https://static-hx24.huoxing24.com/coin/icon/NLX.png" alt="" />
                  LHC
                </span>
                <span>￥0.00128</span>
                <span>-93.61 %</span>
              </li>
              <li className='list-box'>
                <span>2</span>
                <span>
                  <img src="https://static-hx24.huoxing24.com/coin/icon/NLX.png" alt="" />
                  LHC
                </span>
                <span>￥0.00128</span>
                <span>-93.61 %</span>
              </li>
              <li className='list-box'>
                <span>3</span>
                <span>
                  <img src="https://static-hx24.huoxing24.com/coin/icon/NLX.png" alt="" />
                  LHC
                </span>
                <span>￥0.00128</span>
                <span>-93.61 %</span>
              </li>
              <li className='list-box'>
                <span>4</span>
                <span>
                  <img src="https://static-hx24.huoxing24.com/coin/icon/NLX.png" alt="" />
                  LHC
                </span>
                <span>￥0.00128</span>
                <span>-93.61 %</span>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  }
}

export default (init);