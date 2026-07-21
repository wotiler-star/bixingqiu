import React from 'react';
import { connect } from 'react-redux';
import { withRouter, NavLink } from 'react-router-dom';
import { Icon } from 'antd';
import axios from "axios";
import { hashHistory } from 'react-router';

class Header extends React.Component {
  constructor(props, context) {
    super(props, context);
    this.state = {
      search: false,
      typeI: null,
      image: null
    }
  }


  componentDidMount() {
    let id = window.localStorage.getItem('HID');
    id ? this.setState({ typeI: true }) : this.setState({ typeI: false });
    axios.get(`${global.constants.winUrl}?c=h&a=ajax_getInfo&hid=${id}`).then(res => {
      this.setState({
        image: res[0].picdir
      });
    });
  }

  render() {
    return <section className='navBox'>
      <div className='interlayer'>
        <div className='content'>

          <div className='logBox'>
            <NavLink to='/course'>
              <img src="static\media\logo.png" alt="" />
            </NavLink>
          </div>

          <div className="middle" style={{ display: this.state.search ? 'none' : 'block' }}>
            <NavLink to='/home'>首页</NavLink>
            <NavLink to='/livenews?cataid=1'>7*24快讯</NavLink>
            <NavLink to='/list?cataid=3'>宏观经济</NavLink>
            <NavLink to='/list?cataid=87'>投资理财</NavLink>
            <NavLink to='/column?cataid=25'>作家专栏</NavLink>
            <NavLink to='/list?cataid=88'>股市楼市</NavLink>
            <NavLink to='/list?cataid=31'>区块链+</NavLink>
            <NavLink to='/list?cataid=2'>数字货币</NavLink>

          </div>

          <div className='navFinally' style={{ display: this.state.search ? 'none' : 'block' }}>
            <span>...</span>
            <div>
              <NavLink to='/list?cataid=102'>产业公司</NavLink>
              <NavLink to='/list?cataid=103'>商业精英</NavLink>
              <NavLink to='/list?cataid=104'>创新创业</NavLink>
              <NavLink to='/list?cataid=105'>生活时尚</NavLink>
              <NavLink to='/list?cataid=106'>轻松一刻</NavLink>
              <NavLink to='/list?cataid=107'>推广特区</NavLink>
            </div>
          </div>

          <div className='navRight'>
            <NavLink to={this.state.typeI ? '/personal' : '/login'}>
              <img src={this.state.image == undefined ? 'static/media/avatar.png' : this.state.image}
                alt="" />
            </NavLink>

            <div className='form'>
              <Icon type="form" theme="outlined" />
              <span>投稿</span>
              <div className='contributeBox'>
                <NavLink to={this.state.typeI ? '/personal/myi' : '/login'}>
                  文章
                </NavLink>

              </div>
            </div>

            <div className='mobile'>
              <Icon type="mobile" theme="outlined" />
              <span>APP</span>
              {
                /**<div className='qrCode'>
                                <img src="static\media\wechat.png" alt="" />
                              </div> */
              }
            </div>

            <div className='search'>
              <Icon type="search" theme="outlined" style={{ display: this.state.search ? 'none' : 'block' }}
                onClick={() => {
                  this.setState({
                    search: !this.state.search
                  })
                }} />
              <div className='search-pop' style={{ display: this.state.search ? 'block' : 'none' }}>
                <Icon type="search" theme="outlined" onClick={() => {
                  if (this.refs.val.value == '') return;
                  this.props.history.push('/search');
                  window.localStorage.SEARCH = this.refs.val.value;
                  this.refs.val.value = '';
                  this.setState({
                    search: !this.state.search
                  })
                }} />
                <input type="text" placeholder="搜索" ref={'val'} onKeyDown={(ev) => {
                  if (ev.keyCode !== 13) return;
                  if (this.refs.val.value == '') return;
                  this.props.history.push('/search');
                  window.localStorage.SEARCH = this.refs.val.value;
                  this.refs.val.value = '';
                  this.setState({
                    search: !this.state.search
                  })
                  // hashHistory.push({ pathname: '/search', state: this.refs.val.value })
                }} />
                <Icon type="close" theme="outlined" onClick={() => {
                  this.setState({
                    search: !this.state.search
                  })
                }} />
              </div>
            </div>

          </div>
        </div>
      </div>
    </section>
  }
}
export default withRouter(connect()(Header));
