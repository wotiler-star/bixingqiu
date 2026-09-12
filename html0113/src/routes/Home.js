import React from 'react';
import 'swiper/dist/css/swiper.min.css'
import '../static/css/Home.less';
import axios from 'axios';
import './config';
import { blocksIn } from '../config/homeBlocks';

// 首页容器：只负责拉数据 + 维护 tab 状态，
// 页面由哪些区块、以什么顺序呈现，全部交给 config/homeBlocks.js 决定。
class Home extends React.Component {
  constructor(props, context) {
    super(props, context);
    this.state = {
      tabSwitch: false,
      data: null,
      adshowArr: null,
      showArr: null,
      subshowArr: null,
      i2Arr: null,
      i1Arr: null,
      i3Arr: null,
      i4Arr: null,
      i5Arr: null,
      i6Arr: null,
      i7Arr: null,
      i8Arr: null,
      i9Arr: null,
      i10Arr: null,
      i11Arr: null,
      i12Arr: null,
      kuaiArr: null,
      pai1Arr: null,
      pai2Arr: null,
      tuishowArr: null,
      zhuanjiaArr: null,
      typeI: null,
      feedArr: null,
      text: {
        "a": "关注",
        "b": "已关注",
        "c": "+",
        "d": false
      }
    }
  }

  componentWillMount() {
    document.getElementById('root').scrollIntoView(true);//为ture返回顶部，false为底部
  }

  componentDidMount() {
    window.localStorage.HID != undefined ? this.setState({ typeI: true }) : this.setState({ typeI: false });
    document.body.scrollTop = 0;
    let hid = window.localStorage.getItem('HID');
    axios({
      method: 'post',
      url: `${global.constants.winUrl}`,
      data: { "data": { "hid": hid == undefined ? 0 : hid } }
    }).then(res => {
      let a = [], f = res.feedArr.slice(-3);
      for (var i = 0; i < res.tuishowArr.length; i += 3) {
        a.push(res.tuishowArr.slice(i, i + 3));
      }
      this.setState({
        adshowArr: res.adshowArr,
        showArr: res.showArr,
        subshowArr: res.subshowArr,
        i2Arr: res.i2Arr,
        data: res.i1Arr,
        i1Arr: res.i1Arr,
        i3Arr: res.i3Arr,
        i4Arr: res.i4Arr,
        i5Arr: res.i5Arr,
        i6Arr: res.i6Arr,
        i7Arr: res.i7Arr,
        i8Arr: res.i8Arr,
        i9Arr: res.i9Arr,
        i10Arr: res.i10Arr,
        i11Arr: res.i11Arr,
        i12Arr: res.i12Arr,
        kuaiArr: res.kuaiArr,
        pai1Arr: res.pai1Arr,
        pai2Arr: res.pai2Arr,
        tuishowArr: a,
        zhuanjiaArr: res.zhuanjiaArr,
        feedArr: f.concat(res.feedArr),
      });

    })
  }

  // 「加载更多」：以当前列表最后一条为游标继续翻页
  // （原实现在 render 里用闭包变量 indexID/cataid 记录，语义等价于取最后一项）
  loadMore = (data) => {
    const last = (data && data.length) ? data[data.length - 1] : { id: 0, cataid: 0 };
    axios({
      method: 'post',
      url: `${global.constants.winUrl}?a=getMore`,
      data: { "data": { "id": last.id, "cataid": last.cataid } }
    }).then(res => {
      this.setState({
        data: this.state.data.concat(res)
      });
    });
  };

  switch = (ev) => {
    let target = ev.target,
      tarName = target.tagName,
      tarClass = target.className,
      liAry1 = target.parentNode.childNodes,
      liAry = [...liAry1],
      { i1Arr, i2Arr, i3Arr, i4Arr, i5Arr, i6Arr, i7Arr, i8Arr, i9Arr, i10Arr, i11Arr, i12Arr } = this.state;
    liAry.forEach(item => tarName === 'LI' ? item.setAttribute('class', '') : null);
    tarName === "LI" ? target.setAttribute('class', 'active') : null;
    switch (tarName === 'LI') {
      case liAry.indexOf(target) === 0:
        this.setState({ data: i1Arr });
        break;
      case liAry.indexOf(target) === 1:
        this.setState({ data: i2Arr });
        break;
      case liAry.indexOf(target) === 2:
        this.setState({ data: i3Arr });
        break;
      case liAry.indexOf(target) === 3:
        this.setState({ data: i4Arr });
        break;
      case liAry.indexOf(target) === 4:
        this.setState({ data: i5Arr });
        break;
      case liAry.indexOf(target) === 5:
        this.setState({ data: i6Arr });
        break;
      case liAry.indexOf(target) === 6:
        this.setState({ data: i7Arr });
        break;
      case liAry.indexOf(target) === 7:
        this.setState({ data: i8Arr });
        break;
      case liAry.indexOf(target) === 8:
        this.setState({ data: i9Arr });
        break;
      case liAry.indexOf(target) === 9:
        this.setState({ data: i10Arr });
        break;
      case liAry.indexOf(target) === 10:
        this.setState({ data: i11Arr });
        break;
      case liAry.indexOf(target) === 11:
        this.setState({ data: i12Arr });
        break;
    }
  }

  // 按配置渲染单个区块：把容器 state 与回调透传给区块组件
  renderBlock = (b, ctx) => {
    const extra = b.getProps ? b.getProps(ctx) : null;
    const props = extra ? Object.assign({}, ctx, extra) : ctx;
    const C = b.C;
    return <C key={b.id} {...props} />;
  };

  render() {
    const ctx = Object.assign({}, this.state, {
      onSwitchTab: this.switch,
      onLoadMore: this.loadMore
    });
    return <section className='homeBox'>
      <div className='mainBox'>
        <div className='primaryBox'>
          <div className='primary'>
            <div className='primary-left'>
              {blocksIn('primaryLeft').map(b => this.renderBlock(b, ctx))}
            </div>
            {blocksIn('primaryRight').map(b => this.renderBlock(b, ctx))}
          </div>
        </div>

        <div className='main-content'>
          {blocksIn('leftContent').map(b => this.renderBlock(b, ctx))}
          <div className='right-content'>
            {blocksIn('rightContent').map(b => this.renderBlock(b, ctx))}
          </div>
        </div>
      </div>
    </section>
  }
}

export default (Home);
