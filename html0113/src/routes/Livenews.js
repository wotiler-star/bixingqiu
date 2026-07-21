import React from 'react';
import ReactDOM, { render } from 'react-dom';
import { connect } from 'react-redux';
import { NavLink } from 'react-router-dom';
import { Icon } from 'antd';
import '../static/css/Livenews.less';
import axios from "axios";
import Qs from "qs";

class Livenews extends React.Component {
  constructor(props, context) {
    super(props, context);
    this.state = {
      data: null,
      hotArr: null,
      subArr: null,
      index: false,
      fn: null,
      cataid: null
    }
  }

  componentWillMount() {
    document.getElementById('root').scrollIntoView(true);//为ture返回顶部，false为底部
  }

  componentWillReceiveProps(nextProps) {
    let { location: { search } } = this.props,
      { cataid = 0 } = Qs.parse(search.substr(1)) || {};
    this.setState({
      cataid: cataid
    }, () => {
      this.state.fn();
    });
  }

  async componentDidMount() {
    document.body.scrollTop = 0;
    let a = () => {
      let { location: { search } } = this.props,
        { cataid = 0 } = Qs.parse(search.substr(1)) || {};
      axios.get(`${global.constants.winUrl}?c=Content&cataid=${cataid}`).then(res => {
        this.setState({
          data: res.data,
          hotArr: res.hotArr,
          subArr: res.subArr,
          cataid: cataid
        })
      })
    }
    a()
    this.setState({
      fn: a
    })
  }

  render() {
    let { data, subArr, hotArr, index, cataid } = this.state;
    let indexID = 0;
    return <section className={'livenews'}>
      <i keywords={`${this.state.key}`} />
      <div className="main">
        {
          /* <a href="/#" className='activity'>
                    <img src="https://hx24.huoxing24.com/image/news/2018/06/06/1528271007010029.png?x-oss-process=style/image_jpg"
                      alt="" />
                  </a>*/
        }
        <div className="left-content">
          <ul className='title' onClick={this.switch}>
            {subArr ? subArr.map((item, index) => {

              let a = item.cataid == cataid ? "active" : "";
              return <li>
                <NavLink to={`/livenews?cataid=${item.cataid}`} className={a}>{item.sort}</NavLink>
              </li>
            }) : null}
          </ul>

          {data ? data.map((item, index) => {
            indexID = data[index].id;
            let { riqi, short, title, id, lihao, likong } = item;
            return <div className='listBox' key={index}>
              <div className="item-icons">
                <div className="round"></div>
                <div className="time-left">{riqi}</div>
              </div>
              <NavLink to={`/Details?cataid=${cataid}&id=${id}`}>
                <h1>{title}</h1>
                <p>{short}</p>
              </NavLink>
              <div className="judge-profit" index={id}>
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
                      }).then(res => console.log(res));
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
                      }).then(res => console.log(res));
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
                      }).then(res => console.log(res));
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
                      }).then(res => console.log(res));
                      ev.target.nextElementSibling.innerHTML = parseFloat(ev.target.nextElementSibling.innerHTML) - 1;
                      return;
                    }
                  }}>利空</span>
                  <b>{likong}</b>
                </a>
              </div>
            </div>
          }) : null}

          <div className="lazy" onClick={() => {
            let obj = {
              "id": indexID,
              "cataid": cataid
            }
            console.log(obj);
            axios({
              method: 'post',
              url: `${global.constants.winUrl}?c=Content&a=getMore`,
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
        <div className="right-content">
          <div className='recomend'>
            <h3>热门新闻</h3>
            {hotArr ? hotArr.map((item, index) => {
              let { location: { search } } = this.props,
                { cataid = 8 } = Qs.parse(search.substr(1)) || {}, a;
              let { picdir_list, title, riqi, id } = item;
              return <div className='listBox' key={index}>
                <NavLink to={`/Detailed?cataid=${cataid}&id=${id}`}>
                  <img src={picdir_list} alt="" />
                  <span>{title}</span>
                  <p>{riqi}</p>
                </NavLink>
              </div>
            }) : null}
          </div>
        </div>
      </div>
    </section>
  }
}

export default (Livenews);