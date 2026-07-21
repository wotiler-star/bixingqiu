import React from 'react';
import ReactDOM, { render } from 'react-dom';
import axios from "axios";
import { NavLink } from 'react-router-dom';

class myi extends React.Component {
  constructor(props, context) {
    super(props, context);
    this.state = {
      data: null,
      index: 0,
      index2: 10,
      len: null,
      id: null
    }
  }

  componentDidMount() {
    let hid = window.localStorage.getItem('HID');

    axios.get(`${global.constants.winUrl}?c=h&a=myi&hid=${hid}`).then(res => {
      console.log(res);
      this.setState({
        data: res,
        len: res.length,
        id: res[res.length - 1].id
      })
    });
  }

  render() {
    return <div className="right-content-3 right-box">
      <h3>我的文章</h3>
      <ul className="content-box">
        <li className="release">
          <NavLink to={'/personal/geni'}>
            发布新文章
          </NavLink>
        </li>
        <li>投稿审核不通过，请联系小编,QQ:2991479936</li>
        <li onClick={this.sw}>
          <span>状态：</span>
          <span value={'0'}>全部</span>
          <span value={'1'}>审核中</span>
          <span value={'2'}>已通过</span>
          <span value={'3'}>未通过</span>
          <span value={'4'}>草稿</span>
        </li>
        <li onClick={this.sw}>
          <span>栏目：</span>
          <span value={'10'}>全部</span>
          <span value={'26'}>行情</span>
          <span value={'27'}>研报</span>
          <span value={'28'}>人物</span>
          <span value={'29'}>宏观</span>
          <span value={'30'}>技术</span>
          <span value={'202'}>政策</span>
          <span value={'203'}>评级</span>
          <span value={'204'}>全球</span>
          <span value={'205'}>资产</span>
          <span value={'207'}>币链</span>
          <span value={'208'}>媒体</span>
          <span value={'209'}>项目</span>

        </li>
      </ul>
      <ul className="list-content">
        {this.state.data ? this.state.data.map((item, index) => {
          return <li key={index}>
            <img src={item.picdir_list} alt="" />
            <a href={`${global.constants.winUrl2}Detailed?cataid=${item.cataid}&id=${item.id}`} target='_blank'><h4>{item.title}</h4></a><br />
            <span>【{item.riqi}】</span><br />
            <a href={`${global.constants.winUrl2}Detailed?cataid=${item.cataid}&id=${item.id}`} target='_blank' className="btn">查看</a>
            <button onClick={(ev) => {
              ev.target.parentNode.style.display = 'none';
              let hid = window.localStorage.getItem('HID');
              axios({
                method: "post",
                url: `${global.constants.winUrl}?c=h&a=ajax_del_myi&hid=${hid}`,
                data: {
                  "data": {
                    "hid": hid,
                    "id": item.id
                  }
                }
              }).then(res => console.log(res))
            }}>删除</button>
          </li>
        }) : null}
      </ul>
      <div className="lazy" style={{ "display": this.state.len < 10 ? 'none' : 'block' }} onClick={() => {
        let obj = {

          status: this.state.index,
          cataid: this.state.index2,
          id: this.state.id
        }
        console.log(obj)
        axios({
          method: 'post',
          url: `${global.constants.winUrl}??c=h&a=getMore&hid=${window.localStorage.HID}`,
          data: { "data": obj }
        }).then(res => {
          console.log(this.state.data, res);
          this.setState({
            data: this.state.data.concat(res)
          });
        })
      }}>
        点 击 加 载 更 多
      </div>

    </div>
  }

  sw = (ev) => {
    let t = ev.target;
    if (t.tagName == 'LI') return;
    t.parentNode.childNodes.forEach((item) => {
      item.tagName == 'SPAN' ? item.style.color = '#337ab7' : null;
    });
    if (t.getAttribute('value') != undefined) {
      t.style.color = '#f6821b';
      let id = window.localStorage.getItem('HID'),
        val = parseFloat(t.getAttribute('value'));
      this.setState({
        index: val < 10 ? val : this.state.index,
        index2: val > 9 ? val : this.state.index2,
        len: this.state.data.length
      });
      console.log(val, this.state.index, this.state.index2);
      axios({
        method: 'post',
        url: `${global.constants.winUrl}?c=h&a=myi&hid=${id}`,
        data: {
          "data": {
            status: val < 10 ? val : this.state.index,
            cataid: val > 9 ? val : this.state.index2
          }
        }
      }).then(res => {
        console.log(res);
        this.setState({ data: res, len: res.length });
      });
    }
  }
}

export default (myi);