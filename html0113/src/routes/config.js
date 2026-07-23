// API 基址：部署时默认使用「当前页面来源」（同源），前端与后端 /service/ 同域即可，
// 这样无论在哪个域名/IP 下运行都无需为不同环境重新构建。
// 如需显式指定（构建时注入），设置 REACT_APP_API_BASE 即可覆盖。
const INJECTED_API_BASE = (typeof process !== 'undefined' && process.env) ? process.env.REACT_APP_API_BASE : undefined;
const API_BASE = INJECTED_API_BASE
  || (typeof window !== 'undefined' && window.location && window.location.origin)
  || '';

global.constants = {
    winUrl: API_BASE + '/service/',
    winUrl2: API_BASE + '/#/'
};
