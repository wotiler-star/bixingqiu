// 首页区块注册表 —— 「首页区块可配置化」的唯一入口。
//
// 用法（无需再改 Home.js 的 JSX）：
//   · 调整某个区块的位置        → 改它的 region 和 order（order 越小越靠前）
//   · 临时下线某个区块          → enabled: false
//   · 新增区块                  → 在 component/home/HomeBlocks.js 写好组件，再在这里加一条
//
// region 决定区块挂载到首页的哪个容器，容器外的布局与 class 层级保持原样，避免样式错位。
import {
  BannerCarousel,
  AdStrip,
  FlashNews,
  NewsFeed,
  PromoSwiper,
  Authors,
  MostViews,
  CommentsFeed,
  TagsCloud
} from '../component/home/HomeBlocks';

export const REGIONS = ['primaryLeft', 'primaryRight', 'leftContent', 'rightContent'];

export const HOME_BLOCKS = [
  { id: 'banner', region: 'primaryLeft', order: 10, enabled: true, C: BannerCarousel, note: '焦点轮播 + 推荐位' },
  { id: 'ad', region: 'primaryLeft', order: 20, enabled: true, C: AdStrip, note: '横幅广告图' },

  { id: 'flash', region: 'primaryRight', order: 10, enabled: true, C: FlashNews, note: '最新资讯(快讯榜)' },

  { id: 'feed', region: 'leftContent', order: 10, enabled: true, C: NewsFeed, note: '头条新闻流 + 加载更多' },

  { id: 'promo', region: 'rightContent', order: 10, enabled: true, C: PromoSwiper, note: '推广轮播' },
  { id: 'authors', region: 'rightContent', order: 20, enabled: true, C: Authors, note: '专栏作家' },
  {
    id: 'hotViews', region: 'rightContent', order: 30, enabled: true, C: MostViews,
    note: '一周点击排行',
    getProps: s => ({ title: '一周点击排行', items: s.pai1Arr })
  },
  {
    id: 'hotComments', region: 'rightContent', order: 40, enabled: true, C: MostViews,
    note: '一周评论排行',
    getProps: s => ({ title: '一周评论排行', items: s.pai2Arr })
  },
  { id: 'comments', region: 'rightContent', order: 50, enabled: true, C: CommentsFeed, note: '实时最新评论(跑马灯)' },
  { id: 'tags', region: 'rightContent', order: 60, enabled: true, C: TagsCloud, note: '热门标签' }
];

// 取某个区域的可见区块，按 order 升序
export function blocksIn(region) {
  return HOME_BLOCKS
    .filter(b => b.enabled && b.region === region)
    .sort((a, b) => a.order - b.order);
}
