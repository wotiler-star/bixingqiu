import axios from './index';

export function queryHome() {
  return axios.get('http://localhost/k3_bixingqiu').then(res => res);
}
