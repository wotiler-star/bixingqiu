import axios from './index';

export function queryHome() {
  return axios.get('/').then(res => res);
}
