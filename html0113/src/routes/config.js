// API 基址统一通过环境变量 REACT_APP_API_BASE 配置（构建时注入）
// 例如：REACT_APP_API_BASE=http://你的服务器IP  或  https://你的域名
// 本地开发未设置时，默认回退为 http://localhost:8090
const API_BASE = process.env.REACT_APP_API_BASE || 'http://localhost:8090';

global.constants = {
    winUrl: API_BASE + '/service/',
    winUrl2: API_BASE + '/#/'
};
