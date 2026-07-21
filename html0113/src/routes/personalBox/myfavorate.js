import React from 'react';
import ReactDOM, {render} from 'react-dom';
import axios from "axios";

class myfavorate extends React.Component {
  constructor(props, context) {
    super(props, context);
    this.state = {
      data: null,
    }
  }

  componentDidMount() {
    let id = window.localStorage.getItem('HID');
    axios.get(`${global.constants.winUrl}?c=h&a=myfavorate&hid=${id}`).then(res => {
      console.log(res);
      this.setState({
        data: res
      })
    });
  }

  render() {
    return <div className="right-content-4 right-box">
      <h3>我的收藏</h3>
      <ul>
        {this.state.data ? this.state.data.map((item, index) => {
          return <li key={index}>
            <a href={`${global.constants.winUrl2}Detailed?c=Content&cataid=${item.cataid}&id=${item.pid}`} target="_blank">{item.pname}</a>
            <span>【收藏日期：{item.riqi}】</span><button onClick={(ev) => {
            ev.target.parentNode.style.display = 'none';
            // let hid = window.localStorage.getItem('HID');
            axios({
              method: "post",
              url: `${global.constants.winUrl}?c=h&a=ajax_del_favorate`,
              data: {
                "data": {
                  "id": item.id
                }
              }
            }).then(res => console.log(res))
          }}>取消收藏</button>
          </li>
        }) : null}
      </ul>
    </div>
  }
}

export default (myfavorate);