import React from 'react';
import { NavLink, Link } from 'react-router-dom';
import { Icon } from 'antd';
import Swiper from 'swiper/dist/js/swiper.js';
import axios from 'axios';

import { AVATAR_PLACEHOLDER } from '../../static/placeholder';
import { localePath } from '../../i18n/i18n';
import { articleUrl } from '../../util/link';

/* 首页各区块组件 —— 每个区块自成一个组件，管理自己的 Swiper / 跑马灯生命周期，
 * 由 config/homeBlocks.js 决定放在哪个区域、什么顺序、是否显示。
 * 抽取原则是视觉结构（class 名与嵌套层级）与原有保持一致，避免样式错位。
 */

/* 首页「热门标签」关键词（数据驱动，增删只改这里） */
export const HOT_TAGS = [
  '智能合约', '挖矿', '比特币', '监管', 'DAO', '王峰十问', 'bitcoin', '瑞波币',
  '硬分叉', '侧链', '去中心化', '数字货币', '以太坊', '加密货币', '区块链',
  '比特币扩容', 'EOS', 'ETC', '中本聪'
];

/* 通用：Swiper 实例的安全销毁 */
function destroySwiper(inst) {
  if (inst) {
    try { inst.destroy(true, true); } catch (e) { /* 已销毁则忽略 */ }
  }
}

// ---------------------------------------------------------------- 焦点轮播
export class BannerCarousel extends React.Component {
  componentDidMount() { this.initSwiper(); }
  componentDidUpdate() { this.initSwiper(); }
  componentWillUnmount() { destroySwiper(this.swiper); this.swiper = null; }

  initSwiper() {
    const el = this.refs.banner;
    if (!el) return;
    // 先销毁再重建：原实现每次 update 都会新建实例却不销毁旧的，Swiper 会不断叠加。
    destroySwiper(this.swiper);
    this.swiper = new Swiper(el, {
      autoplay: true,
      loop: true,
      navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
      pagination: { el: '.swiper-pagination', type: 'progressbar' }
    });
  }

  render() {
    const { showArr, subshowArr } = this.props;
    return <div className='carousel'>
      <div className=" swiper-container" ref={'banner'}>
        <div className="swiper-wrapper">
          {/* loop 模式下 Swiper 需要首尾各一份 slide，故渲染两次（与原实现一致） */}
          {showArr ? showArr.map((item, index) => {
            return <div className="swiper-slide" key={'a' + index}>
              <span className='mode'>{item.title}</span>
              <a href={item.url}>
                <img src={item.picdir} alt="" />
              </a>
            </div>
          }) : null}
          {showArr ? showArr.map((item, index) => {
            return <div className="swiper-slide" key={'b' + index}>
              <span className='mode'>{item.title}</span>
              <img src={item.picdir} alt="" />
            </div>
          }) : null}
        </div>
        <div className="swiper-button-prev">
          <Icon type="left" theme="outlined" />
        </div>
        <div className="swiper-button-next">
          <Icon type="right" theme="outlined" />
        </div>
        <div className="swiper-pagination"></div>
      </div>

      <div className='recommend-cont'>
        {subshowArr ? subshowArr.map((item, index) => {
          return <a href={item.url} key={index}>
            <img src={item.picdir} alt="" />
            <p>{item.title}</p>
          </a>
        }) : null}
      </div>
    </div>;
  }
}

// ---------------------------------------------------------------- 横幅广告图
export class AdStrip extends React.Component {
  render() {
    const { adshowArr } = this.props;
    if (!adshowArr) return null;
    return <React.Fragment>
      {adshowArr.map((item, index) => {
        /* 原为 <NavLink to={localePath('/#')}> —— 广告图点击后跳到无意义锚点（死链）。
           改为：数据自带 url 时用它（外链走 <a href>，站内走 Link），都没有才回退首页。 */
        const target = item.url || '/home';
        const external = /^https?:/i.test(target);
        const img = <img src={item.picdir} alt={item.title} />;
        return external
          ? <a className='imgBox' key={index} href={target} target='_blank'
               rel='noopener noreferrer'>{img}</a>
          : <Link className='imgBox' key={index} to={localePath(target)}>{img}</Link>;
      })}
    </React.Fragment>;
  }
}

// ---------------------------------------------------------------- 最新资讯(快讯)
export class FlashNews extends React.Component {
  render() {
    const { kuaiArr } = this.props;
    return <div className='primary-right'>
      <h3>最新资讯</h3>
      <NavLink to={localePath('/livenews?cataid=8')} className='more'> </NavLink>
      <div className='list-box' id='list-items'>
        <div className='item-box'>
          {kuaiArr ? kuaiArr.map((item, index) => {
            return <div className='item' key={index}>
              <div className='item-icons'>
                <div className='item-left' id={'active'}>
                  <span>{item.time}</span>
                </div>
              </div>
              <NavLink to={localePath(articleUrl(item, 8))}>
                <span>{item.title}</span>
              </NavLink>
            </div>
          }) : ''}
        </div>
        <NavLink to={localePath('/livenews?cataid=1')} className='filsh'>查看更多</NavLink>
      </div>
      <div className='gradual'></div>
    </div>;
  }
}

// ---------------------------------------------------------------- 头条新闻流(tab + 加载更多)
export class NewsFeed extends React.Component {
  /* “加载更多”语义：以当前列表最后一条为游标继续翻页（与原实现闭包变量一致） */
  loadMore = () => {
    const { data, onLoadMore } = this.props;
    if (onLoadMore) onLoadMore(data);
  };

  render() {
    const { data, onSwitchTab } = this.props;
    return <div className='left-content'>
      <ul className="title" onClick={onSwitchTab}>
        <li className="active">头条</li>
        <li className=" "> 行情</li>
        <li className=" ">研报</li>
        <li className=" ">人物</li>
        <li className=" ">宏观</li>
        <li className=" ">技术</li>
        <li className=" ">政策</li>
        <li className=" ">评级</li>
        <li className=" ">全球</li>
        <li className=" "><NavLink to={localePath('/column?cataid=25')}>专栏</NavLink></li>
      </ul>
      <div className='list-content'>
        {data ? data.map((item, index) => {
          let { picdir_list, title, short, source, riqi } = item;
          return <div className='news-list' key={index}>
            <NavLink to={localePath(articleUrl(item, 11))}>
              <div className='imgBox'>
                <img src={picdir_list} alt="" />
              </div>
              <div className='content-text'>
                <h1>{title}</h1>
                <p>{short}</p>
              </div>
              <div className='list-bottom'>
                <span>{source}</span>
                <span>{riqi}</span>
              </div>
            </NavLink>
            <div className='shadow'></div>
          </div>
        }) : null}
      </div>
      <div className="lazy" onClick={this.loadMore}>
        点 击 加 载 更 多
      </div>
    </div>;
  }
}

// ---------------------------------------------------------------- 右栏推广轮播
export class PromoSwiper extends React.Component {
  componentDidMount() { this.initSwiper(); }
  componentDidUpdate() { this.initSwiper(); }
  componentWillUnmount() { destroySwiper(this.swiper); this.swiper = null; }

  initSwiper() {
    const el = this.refs.bannerT;
    if (!el) return;
    destroySwiper(this.swiper);
    const inst = new Swiper(el, {
      autoplay: true,
      loop: true,
      pagination: { el: '.swiper-pagination', clickable: true }
    });
    // 悬停暂停自动播放。原实现写在 handler 里用 this，实际指向 DOM 元素，行为不可靠；
    // 这里显式引用实例变量，语义明确。
    inst.el.onmouseover = () => inst.autoplay && inst.autoplay.stop();
    this.swiper = inst;
  }

  render() {
    const { tuishowArr } = this.props;
    return <div className='advertising'>
      <h4>推广</h4>
      <div className="swiper-container swiper-t " ref={'bannerT'}>
        <div className="swiper-wrapper">
          {tuishowArr ? tuishowArr.map((item, index) => {
            return <div className="swiper-slide" key={index}>
              {item.map((item2, index2) => {
                let { picdir, short, title, url } = item2;
                return <div className='swiper-list' key={index2}>
                  <a href={url}>
                    <div className='imgBox'>
                      <img src={picdir} alt="" />
                    </div>
                    <div className="textBox">
                      <span>{title}</span>
                      <p>{short}</p>
                    </div>
                  </a>
                </div>
              })}
            </div>
          }) : null}
        </div>
        <div className="swiper-pagination"></div>
      </div>
    </div>;
  }
}

// ---------------------------------------------------------------- 专栏作家
export class Authors extends React.Component {
  follow = (id, ifover, ev) => {
    if (!this.props.typeI) {
      alert('请先登录后在关注！');
      return;
    }
    const hid = window.localStorage.getItem('HID');
    if (ifover == 1) {
      axios({
        method: 'post',
        url: `${global.constants.winUrl}?a=carehid`,
        data: { "data": { "hid": hid, "mycarehid": id } }
      }).then(res => console.log(res));
    }
    ev.target.innerHTML = '已关注';
  };

  render() {
    const { zhuanjiaArr } = this.props;
    return <div className='products-box'>
      <div className='title'>
        <h3>专栏作家</h3>
        <NavLink to={localePath('/author')}>
          <span>更多</span>
          <i className="more-2"></i>
        </NavLink>
      </div>
      {zhuanjiaArr ? zhuanjiaArr.map((item, index) => {
        let { id, name, picdir, short, ifover } = item;
        return <div className='products' key={index}>
          <a href={'javascript:;'}>
            <div className='imgBox'>
              <NavLink to={localePath(`/mydetail?id=${id}`)}>
                <img src={picdir} alt="" />
              </NavLink>
            </div>
            <NavLink to={localePath(`/mydetail?id=${id}`)} style={{ textDecoration: 'none' }}>
              <div className="textBox">
                <span>{name}</span>
                <p>{short}</p>
              </div>
            </NavLink>
            <div className='like' onClick={(ev) => this.follow(id, ifover, ev)}>
              <b>{ifover == 0 ? "" : "+"}</b>{ifover == 1 || ifover == undefined ? "关注" : "已关注"}
            </div>
          </a>
        </div>
      }) : null}
    </div>;
  }
}

// ---------------------------------------------------------------- 排行榜(点击/评论)
export class MostViews extends React.Component {
  render() {
    const { title, items } = this.props;
    return <div className="mostviews">
      <h3>{title}</h3>
      {items ? items.map((item, index) => {
        let { hitnum, picdir_list, title: t, num_days, pinglunnum } = item;
        return <div className="listBox" key={index}>
          <NavLink to={localePath(articleUrl(item, 11))}>
            <div className="imgBox">
              <img src={picdir_list} alt="" />
            </div>
            <p>{t}</p>
          </NavLink>
          <div className="project">
            <Icon type="dashboard" theme="outlined" />&nbsp;
            <span>{num_days}天前&nbsp;&nbsp;&nbsp;</span>
            <Icon type="eye" theme="outlined" />&nbsp;
            <span>{hitnum}&nbsp;&nbsp;&nbsp;</span>
            <Icon type="message" theme="outlined" />&nbsp;
            <span>{pinglunnum}&nbsp;&nbsp;&nbsp;</span>
          </div>
        </div>
      }) : null}
    </div>;
  }
}

// ---------------------------------------------------------------- 实时最新评论(跑马灯)
export class CommentsFeed extends React.Component {
  componentDidMount() { this.initMarquee(); }
  componentDidUpdate() { this.initMarquee(); }
  componentWillUnmount() { this.clearMarquee(); }

  clearMarquee() {
    if (this.timer) { clearInterval(this.timer); this.timer = null; }
  }

  initMarquee() {
    const aa = this.refs.aa, bb = this.refs.bb;
    if (!aa || !bb) return;
    // 每次 update 重建前先清掉旧定时器：原实现没有清理，切 Tab 会叠加多个 interval。
    this.clearMarquee();
    const H = bb.offsetHeight - 500;
    if (H <= 0) return;
    const step = () => {
      const el = this.refs.aa;
      if (!el) return;
      el.scrollTop >= (H - 2) ? el.scrollTop = 0 : el.scrollTop += 2;
    };
    this.timer = setInterval(step, 30);
    aa.onmouseover = () => this.clearMarquee();
    aa.onmouseleave = () => { this.clearMarquee(); this.timer = setInterval(step, 30); };
  }

  render() {
    const { feedArr } = this.props;
    return <div className="comment">
      <h3>实时最新评论</h3>
      <div className="list-content" ref={'aa'}>
        <div className="list-content-2" ref={'bb'}>
          {feedArr ? feedArr.map((item, index) => {
            let { content, name, picdir, pname, riqi } = item;
            return <div className='listBox' key={index}>
              <div className="topBox">
                <img src={picdir == undefined ? AVATAR_PLACEHOLDER : picdir} alt="" />
                <a href="javascript:;">{name == undefined ? '网友' : name}</a>
                <span>{riqi}</span>
              </div>
              <div className="contentBox">
                <a href="javascript:;">{content}</a>
              </div>
              <div className="bottomBox">
                评论在：“ {pname} ”
              </div>
            </div>
          }) : null}
        </div>
      </div>
    </div>;
  }
}

// ---------------------------------------------------------------- 热门标签
export class TagsCloud extends React.Component {
  render() {
    return <div className="tags-box">
      <h3>热门标签</h3>
      <div className="tags-cont">
        {HOT_TAGS.map(tag => (
          <Link key={tag} to={localePath('/search?w=' + encodeURIComponent(tag))}>{tag}</Link>
        ))}
      </div>
    </div>;
  }
}
