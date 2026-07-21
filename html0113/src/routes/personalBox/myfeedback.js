import React from 'react';
import ReactDOM, {render} from 'react-dom';
import axios from "axios";

class myfeedback extends React.Component {
  constructor(props, context) {
    super(props, context);
    this.state = {
      data: null,
    }
  }

  componentDidMount() {
    let id = window.localStorage.getItem('HID');
    axios.get(`${global.constants.winUrl}?c=h&a=myfeedback&hid=${id}`).then(res => {
      console.log(res);
      this.setState({
        data: res
      })
    });
  }

  render() {
    return <div className="right-content-2 right-box">
      <h3>我的评论</h3>
      <ul>
        {this.state.data ? this.state.data.map((item, index) => {
          return <li key={index}>
            <a href="javascript:;">
              {item.pname}
            </a>
            <span>【{item.riqi}】</span>
            <span>{item.content}</span>
          </li>
        }) : null}

      </ul>
    </div>
  }
}

export default (myfeedback);