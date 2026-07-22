import axios from 'axios';
import Qs from 'qs';

// 与 config.js 保持一致：API 基址通过 REACT_APP_API_BASE 注入，默认回退本地
const API_BASE = process.env.REACT_APP_API_BASE || 'http://localhost:8090';
axios.defaults.baseURL = API_BASE + '/service';
axios.defaults.withCredentials = true;
axios.defaults.transformRequest = (data = {}) => Qs.stringify(data);
axios.interceptors.response.use(result => result.data);
export default axios;