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
      index2: 0
    }
  }

  componentDidMount() {
    let hid = window.localStorage.getItem('HID');
    axios.get(`${global.constants.winUrl}?c=h&a=mycarehid&hid=${hid}`).then(res => {
      console.log(res);
      this.setState({
        data: res
      })
    });
  }

  render() {
    return <div className="right-content-3 right-box">
      <h3>我的关注</h3>
      <ul className="list-content">
        {this.state.data ? this.state.data.map((item, index) => {
          let { ifover, carehid, picdir, name, short} = item;
          console.log(ifover);
          return <li key={index}>
            <img src={picdir} alt="" />
            <h4>{name}</h4><br />
            <span>{short}</span><br />
            <a href={`${global.constants.winUrl2}mydetail?id=${carehid}`} target='_blank'>查看</a>
            <button onClick={(ev) => {
              ev.target.parentNode.style.display = 'none';
              let hid = window.localStorage.getItem('HID');
              axios({
                method: 'post',
                url: `${global.constants.winUrl}?c=h&a=delmycarehid`,
                data: {
                  "data": {
                    "hid": hid,
                    "mycarehid": carehid
                  }
                }
              }).then(res => console.log(res));
            }}>取消关注</button>
          </li>
        }) : null}
      </ul>
    </div>
  }
}

export default (myi);