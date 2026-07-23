import axios from 'axios';
import Qs from 'qs';

// 与 config.js 保持一致：API 基址通过 REACT_APP_API_BASE 注入，默认回退本地
const API_BASE = process.env.REACT_APP_API_BASE || 'http://localhost:8090';
axios.defaults.baseURL = API_BASE + '/service';
axios.defaults.withCredentials = true;
axios.defaults.transformRequest = (data = {}) => Qs.stringify(data);
// 常见数组型响应字段：缺失或为 null 时统一兜底为空数组，
// 避免组件 .then 内直接调用 .slice()/.map()/.length 抛 TypeError 导致整页白屏。
const ARRAY_FIELDS = [
  'data', 'feedArr', 'hotArr', 'nextArr', 'aboutArr', 'subArr', 'showArr', 'adshowArr',
  'tuishowArr', 'i1Arr', 'i2Arr', 'i3Arr', 'i4Arr', 'i5Arr', 'i6Arr', 'i7Arr', 'i8Arr',
  'i9Arr', 'i10Arr', 'i11Arr', 'i12Arr', 'kuaiArr', 'pai1Arr', 'pai2Arr', 'zhuanjiaArr',
  'listArr', 'moreArr', 'myiArr', 'careArr'
];

function safeResponse(obj) {
  if (obj && typeof obj === 'object') {
    const out = Array.isArray(obj) ? obj.slice() : Object.assign({}, obj);
    if (!Array.isArray(out)) {
      ARRAY_FIELDS.forEach(k => {
        if (out[k] === undefined || out[k] === null) out[k] = [];
      });
    }
    return out;
  }
  // 后端返回非 JSON（如 showmessage 的 HTML 错误页）或空响应时，返回安全空对象，避免解析崩溃
  return {};
}

axios.interceptors.response.use(
  result => safeResponse(result.data),
  error => {
    console.error('[api] 请求失败:', error.config && error.config.url, error.message);
    // 网络/服务器错误时 resolve 为空对象，由组件空态兜底，避免未捕获的 Promise 告警与白屏
    return Promise.resolve({});
  }
);
export default axios;