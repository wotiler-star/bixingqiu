import React from 'react';
import ReactDOM, {render} from 'react-dom';
import axios from "axios";

class mynotice extends React.Component {
  constructor(props, context) {
    super(props, context);
    this.state = {
      data: null,
    }
  }

  componentDidMount() {
    let id = window.localStorage.getItem('HID');
    axios.get(`${global.constants.winUrl}?c=h&a=mynotice&hid=${id}`).then(res => {
      console.log(res);
      this.setState({
        data: res
      })
    });
  }

  render() {
    return <div className="right-content-5 right-box">
      <h3>系统通知</h3>
      <ul>
        {this.state.data ? this.state.data.map((item, index) => {
          return <li key={index}>
            <img src={item.picdir_list} alt=""/>
            <a href="javascript:;"> {item.title}</a><br/>
            <span>{item.short}</span>
          </li>
        }) : null}
      </ul>
    </div>
  }
}

export default (mynotice);