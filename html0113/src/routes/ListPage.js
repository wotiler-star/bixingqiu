import React from 'react';
import ReactDOM, { render } from 'react-dom';
import { NavLink } from 'react-router-dom';
import action from '../store/action';
import '../static/css/ListPage.less';
import axios from "axios";
import Qs from "qs";

class ListPage extends React.Component {
  constructor(props, context) {
    super(props.context);
    this.state = {
      data: null,
      hotArr: null,
      subArr: null,
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
    let indexID = 0,
      cataid = this.state.cataid;
    return <section className='list-page'>
      <div className='navBox'>
        <ul>
          {this.state.subArr ? this.state.subArr.map((item, index) => {
            let { location: { search } } = this.props,
              { cataid = 8 } = Qs.parse(search.substr(1)) || {}, a;
            item.cataid == cataid ? a = "active" : a = "";
            return <li>
              <NavLink to={`/list?cataid=${item.cataid}`} className={a}>{item.sort}</NavLink>
            </li>
          }) : null}
        </ul>
      </div>
      <div className='main'>
        <div className='left-content'>
          <div className='list-content'>

            {this.state.data ? this.state.data.map((item, index) => {
              indexID = this.state.data[index].id;
              let { picdir_list, title, short, riqi, source, title2, id } = item;
              return <div className='news-list'>
                <NavLink to={`/Detailed?cataid=${cataid}&id=${id}`}>
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
                      {title2}
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
        <div className='right-content'>
          <div className='recomend'>
            <h3>热门新闻</h3>
            {this.state.hotArr ? this.state.hotArr.map((item, index) => {
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

export default (ListPage);