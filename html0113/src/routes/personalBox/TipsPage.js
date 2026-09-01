import React from 'react';
import ReactDOM, {render} from 'react-dom';
import stepImg from '../../static/image/step3.jpg'; // 修复：原为硬编码 static/media 字面量，webpack 未打包该文件 -> 线上 404

class TipsPage extends React.Component {
  constructor(props, context) {
    super(props, context);
  }

  render() {
    return <div className="right-content-9 right-box">
      <h3>实名认证</h3>
      <img src={stepImg} alt=""/>
    </div>
  }
}

export default (TipsPage);