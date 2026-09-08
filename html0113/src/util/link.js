// 站内链接统一构造器 —— 单一事实来源（single source of truth）
//
// 背景/问题：
//   详情链接此前散布在多处，普遍使用「来源页 cataid」或「硬编码 cataid」（如 /detailed?cataid=11&id=123）。
//   但后端 Content::detail() 查文章只用 id（WHERE ifhidden='1' and id=$id），cataid 仅用于「相关/热门」推荐的范围限定。
//   于是同一篇文章可以通过任意 cataid 打开，产生：
//     ① 同一内容对应多个 URL（/detailed?cataid=8/11/95&id=123）→ 搜索引擎判定重复内容、权重分散；
//     ② 推荐位（相关/热门/下一篇）的范围被错误 cataid 带偏。
//
// 规范做法：每篇文章的规范链接固定为其「自身主栏目 cataid + id」，
//   与后端 detail() 取 $data["cataid"] 逗号串第一段作为主栏目（$mycataidArr[0]）的逻辑一致。
//   全站所有入口（列表/首页/详情相关推荐/作者页/搜索/专栏）统一走本模块，保证 URL 收敛到唯一规范形式。

// 文章 cataid 字段是逗号分隔串，例如 "cataid95,cataid11" 或纯数字 "11"。
// 取第一段作为主栏目，与后端生成 canonical / 面包屑的逻辑对齐。
export function primaryCataid(cataid) {
  if (cataid == null) return 0;
  const first = String(cataid).split(',')[0].trim();
  const m = first.match(/\d+/);
  return m ? parseInt(m[0], 10) : 0;
}

// 文章详情规范链接。
//   item:           { id, cataid } 对象（或任意带 id/cataid 字段的行），也可传纯数字 id；
//   fallbackCataid: 当 item 无有效 cataid 时回退（通常传来源页 cataid），
//                  确保 URL 永远含一个有效存在的 cataid —— 否则后端 init() 会因
//                  "cataid 对应栏目不存在" 直接跳首页，变成死链。
// 返回形如 /detailed?cataid=95&id=123（无多余 &）。
export function articleUrl(item, fallbackCataid) {
  const id = (item != null && item.id != null) ? item.id : item;
  let cid = primaryCataid(item && item.cataid);
  if (!cid && fallbackCataid) cid = parseInt(fallbackCataid, 10) || 0;
  return `/detailed?cataid=${cid}&id=${id}`;
}

// 作者主页链接：/mydetail?id=<hid>
export function authorUrl(hid) {
  return `/mydetail?id=${hid}`;
}
