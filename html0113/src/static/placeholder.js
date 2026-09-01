/**
 * 全站统一的占位图 / 装饰图标 —— 内联 SVG data URI。
 *
 * 背景：原代码把兜底封面、用户头像、币种图标、下拉箭头一律指向外域 CDN
 *   - https://img.jinse.com/...                （封面兜底）
 *   - https://static-hx24.huoxing24.com/...    （币种图标、作者头像）
 *   - https://liancaijing.com/json/avatar.png  （评论头像兜底）
 *   - https://resource.jinse.com/phenix/img/0001.svg （下拉箭头）
 * 这些外域现已全部不可达（DNS / 连接失败），线上必然显示破图，
 * 且每次请求都会把访客的 referer 泄露给第三方站点。
 *
 * 改为内联 SVG 的好处：
 *   1. 零网络请求，不需要打包器配置，永不 404；
 *   2. 不依赖任何第三方，隐私不外泄；
 *   3. 体积只有几百字节。
 *
 * 注意：SVG 内不能写 `#`（会被当成 URL 片段），所有字符统一走
 * encodeURIComponent，避免 data URI 被截断。
 */

function svg(inner, w, h) {
  return (
    'data:image/svg+xml;charset=utf-8,' +
    encodeURIComponent(
      '<svg xmlns="http://www.w3.org/2000/svg" width="' +
        w +
        '" height="' +
        h +
        '" viewBox="0 0 ' +
        w +
        ' ' +
        h +
        '">' +
        inner +
        '</svg>'
    )
  );
}

/** 封面 / 缩略图占位（文章、项目列表无图时的兜底） */
export const COVER_PLACEHOLDER = svg(
  '<rect width="240" height="160" fill="#eef1f5"/>' +
    '<rect x="96" y="56" width="48" height="36" rx="3" fill="none" stroke="#b6bfcc" stroke-width="2.5"/>' +
    '<circle cx="110" cy="68" r="4" fill="#b6bfcc"/>' +
    '<path d="M100 88 L114 74 L124 84 L132 78 L140 88 Z" fill="#b6bfcc"/>' +
    '<text x="120" y="118" font-family="Arial,Helvetica,sans-serif" font-size="13" text-anchor="middle" fill="#9aa4b2">No Image</text>',
  240,
  160
);

/** 用户头像占位（圆形灰底 + 人形剪影） */
export const AVATAR_PLACEHOLDER = svg(
  '<circle cx="40" cy="40" r="40" fill="#e3e8ef"/>' +
    '<circle cx="40" cy="31" r="13" fill="#aab4c2"/>' +
    '<path d="M14 72 a26 26 0 0 1 52 0 z" fill="#aab4c2"/>',
  80,
  80
);

/**
 * 币种图标占位。传入币种代号会渲染成圆形底 + 代号首字母，
 * 比一个纯灰圆更有信息量。
 */
export function coinPlaceholder(symbol) {
  var s = String(symbol || '?').replace(/[^A-Za-z0-9]/g, '').slice(0, 3).toUpperCase() || '?';
  var fs = s.length >= 3 ? 11 : 13;
  return svg(
    '<circle cx="16" cy="16" r="16" fill="#dde3ec"/>' +
      '<text x="16" y="21" font-family="Arial,Helvetica,sans-serif" font-size="' +
      fs +
      '" font-weight="bold" text-anchor="middle" fill="#5c6675">' +
      s +
      '</text>',
    32,
    32
  );
}

/**
 * 右向小箭头（导航下拉指示器）。
 * NavPage.less 里对 img 做了 transform: rotate(90deg) / rotate(-90deg) 且
 * width: 0.09rem，所以这里用「指向右侧」的三角形，由 CSS 负责旋转。
 */
export const ARROW_PLACEHOLDER = svg(
  '<path d="M2 1 L7.5 6 L2 11 Z" fill="#999"/>',
  9,
  12
);
