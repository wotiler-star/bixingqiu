import React from 'react';
import { withRouter } from 'react-router-dom';
import { setSEO } from './util/seo';

// 各路由对应的 SEO 元信息。新增页面时在此补充即可。
const PAGE_META = {
  '/home': {
    title: '币星球 - 区块链人物与链圈资讯平台',
    description: '币星球聚焦区块链行业人物、专家专栏、7×24快讯、数字货币与宏观经济资讯，提供深度的链圈人物库与内容社区。',
  },
  '/column': {
    title: '作家专栏 - 币星球',
    description: '币星球作家专栏，汇聚区块链行业专家与专栏作者的深度观点文章。',
  },
  '/author': {
    title: '链圈人物 - 币星球',
    description: '币星球人物库，收录区块链行业人物档案、观点与最新动态。',
  },
  '/list': {
    title: '资讯列表 - 币星球',
    description: '区块链、数字货币、宏观经济等领域的最新资讯列表。',
  },
  '/detailed': {
    title: '资讯详情 - 币星球',
    description: '区块链行业深度资讯与报道，跟踪链圈热点事件。',
  },
  '/details': {
    title: '人物详情 - 币星球',
    description: '区块链人物详细档案、观点与动态。',
  },
  '/livenews': {
    title: '7×24快讯 - 币星球',
    description: '区块链与数字货币 7×24 小时实时快讯，第一时间掌握行业动态。',
  },
  '/navpage': {
    title: '栏目导航 - 币星球',
    description: '币星球内容栏目导航，快速进入你感兴趣的板块。',
  },
  '/apply': {
    title: '入驻申请 - 币星球',
    description: '申请成为币星球专栏作家或入驻平台，共建链圈内容社区。',
  },
  '/search': {
    title: '搜索 - 币星球',
    description: '在币星球搜索区块链人物、专家与资讯内容。',
  },
  '/login': {
    title: '登录 - 币星球',
    description: '登录币星球，使用你的账号访问个人中心与专属内容。',
  },
  '/register': {
    title: '注册 - 币星球',
    description: '注册币星球账号，加入区块链人物与资讯社区。',
  },
  '/personal': {
    title: '个人中心 - 币星球',
    description: '币星球用户个人中心，管理资料、收藏与动态。',
  },
  '/mydetail': {
    title: '我的资料 - 币星球',
    description: '编辑与管理你的币星球个人资料。',
  },
};

class SeoManager extends React.Component {
  componentDidMount() {
    this.apply();
  }

  componentDidUpdate(prevProps) {
    if (prevProps.location.pathname !== this.props.location.pathname) {
      this.apply();
    }
  }

  apply() {
    const path = this.props.location.pathname || '/';
    const meta = PAGE_META[path] || PAGE_META['/home'];
    setSEO({ ...meta, path });
  }

  render() {
    return null;
  }
}

export default withRouter(SeoManager);
