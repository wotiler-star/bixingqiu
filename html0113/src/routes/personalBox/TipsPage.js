import React from 'react';
import ReactDOM, {render} from 'react-dom';

class TipsPage extends React.Component {
  constructor(props, context) {
    super(props, context);
  }

  render() {
    return <div className="right-content-9 right-box">
      <h3>实名认证</h3>
      <img src="static/media/step3.jpg" alt=""/>
    </div>
  }
}

export default (TipsPage);