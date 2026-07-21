import React from 'react';
import ReactDOM, { render } from 'react-dom';
import { NavLink } from 'react-router-dom';
import { Icon } from 'antd';
import '../static/css/MyDetail.less';
import Qs from "qs";
import axios from "axios";

class MyDetail extends React.Component {
  constructor(props, context) {
    super(props, context);
    this.state = {
      share: false,
      headerD: null,
      contentD: null,
      gz: '+关注',
      typeI: null
    }
  }

  componentWillMount() {
    window.localStorage.HID != undefined ? this.setState({ typeI: true }) : this.setState({ typeI: false });
    document.getElementById('root').scrollIntoView(true);//为ture返回顶部，false为底部
  }

  async componentDidMount() {
    let { location: { search } } = this.props,
      { id = 0 } = Qs.parse(search.substr(1)) || {};
    id = parseFloat(id);
    let hid = window.localStorage.getItem('HID');
    axios({
      method: 'post',
      url: `${global.constants.winUrl}?c=Content&a=getExpert&hid=${id}`,
      data: {
        "data": {
          "hid": hid
        }
      }
    }).then(res => {
      this.setState({
        headerD: res.hArr,
        contentD: res.iArr
      })
    });
  }

  render() {
    let { headerD, contentD } = this.state;
    return <section className='myDetail'>

      {headerD ? headerD.map((item, index) => {
        let { picdir, name, short, id, ifover, sort, num_contents, hitnum, num_fans } = item;
        return <div className='author-brands' key={index}>
          <div className='share' onClick={() => {
            this.setState({
              share: !this.state.share
            })
          }}>
            <Icon type="export" theme="outlined" />
            分享
          </div>
          <div className='shareBox' style={{ display: this.state.share ? 'block' : 'none' }}>
            <NavLink to='/#'>
              <Icon type="qq" theme="outlined" />
            </NavLink>
            <NavLink to='/#'>
              <Icon type="weibo" theme="outlined" />
            </NavLink>
            <NavLink to='/#' className='w'>
              <Icon type="wechat" theme="filled" />
              <div className='wechatBox'>
                <p>微信扫一扫：分享</p>
                <img
                  src="data:image/png;base64,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"
                  alt="" />
                <span>微信里点“发现”，扫一下</span>
                <span>二维码便可将本文分享至朋友圈。</span>
              </div>
            </NavLink>
          </div>
          <div className='avatar'>
            <img src={picdir} alt="" />
          </div>
          <div className='name'>
            <h3>{name}</h3>
            <span>
              <div>v</div>
              {sort}
            </span>
          </div>
          <p>{short}</p>
          <a href={'javascript:;'} className='attention' onClick={(ev) => {
            if (!this.state.typeI) {
              alert('请先登录后在关注！');
              return;
            }
            let hid = window.localStorage.getItem('HID');
            if (ifover == 1) {
              axios({
                method: 'post',
                url: `${global.constants.winUrl}?a=carehid`,
                data: {
                  "data": {
                    "hid": hid,
                    "mycarehid": id
                  }
                }
              }).then(res => console.log(res));
              ev.target.innerHTML = '已关注';

            }

          }}>{ifover == 1 || ifover == undefined ? "关注" : '已关注'}</a>
          <div className='data'>
            <div>
              <span>{num_contents}</span>
              <p>文章</p>
            </div>
            <div>
              <span>{hitnum}</span>
              <p>浏览</p>
            </div>
            <div>
              <span>{num_fans}</span>
              <p>粉丝</p>
            </div>
          </div>
        </div>
      }) : null}


      <div className='main'>
        {contentD ? contentD.map((item, index) => {
          let { picdir_list, title, cnt_short, riqi, hitnum, id } = item;
          return <div className='list' key={index}>
            <NavLink to={`/Detailed?cataid=11&id=${id}`} className='imgBox'>
              <img src={picdir_list} alt="" />
            </NavLink>
            <div className='textBox'>
              <NavLink to={`/Detailed?cataid=11&id=${id}`}>
                {title}
              </NavLink>
              <p>
                {cnt_short}
              </p>
              <span>{riqi}</span>
              <span><Icon type="eye" theme="outlined" />{hitnum}</span>
            </div>
          </div>
        }) : null}

      </div>
    </section>
  }
}

export default (MyDetail);