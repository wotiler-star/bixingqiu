import React from 'react';
import { NavLink } from 'react-router-dom';
import { Icon, Input, Upload, message, Select, Button } from 'antd';
import '../static/css/Apply.less';
import axios from 'axios';

function getBase64(img, callback) {
  const reader = new FileReader();
  reader.addEventListener('load', () => callback(reader.result));
  reader.readAsDataURL(img);
}

function beforeUpload(file) {
  // [BUG 修复] 原来只允许 image/jpeg，选 png 直接被拒且提示是英文；
  // 导航站 LOGO 绝大多数是 png，这里放宽为 jpg/png，提示改中文。
  const okType = file.type === 'image/jpeg' || file.type === 'image/png';
  if (!okType) {
    message.error('仅支持 JPG / PNG 格式的图片');
    return false;
  }
  const isLt2M = file.size / 1024 / 1024 < 2;
  if (!isLt2M) {
    message.error('图片大小不能超过 2MB');
    return false;
  }
  return true;
}

/*
 * [BUG 修复] 这个 Upload 只用于本地预览并生成 base64（LOGO 随表单一起提交给
 * ajax_site），本不需要真的上传。原实现 action="static/media" 会让 antd 真的
 * 把文件 POST 到该地址 —— 那是个目录，必然失败并弹出红色「上传失败」。
 * 改为 customRequest 空实现：走完上传生命周期拿到 done 状态，但不发请求。
 */
const noopUpload = () => {};

const { TextArea } = Input;
const InputGroup = Input.Group;
const Option = Select.Option;
const options = [];

class Apply extends React.Component {
  constructor(props, context) {
    super(props, context);
    this.state = {
      loading: false,
      sortValue: '投资机构',
    };
  }

  componentWillMount() {
    // [BUG 修复] 原实现未判空，#root 缺失（或直接本组件被单独挂载）时抛
    // TypeError 导致整个页面白屏。
    const root = document.getElementById('root');
    root && root.scrollIntoView && root.scrollIntoView(true); // 为 true 返回顶部，false 为底部
  }

  handleChange = (info) => {
    if (info.file.status === 'uploading') {
      this.setState({ loading: true });
      return;
    }
    if (info.file.status === 'done') {
      // Get this url from response in real world.
      getBase64(info.file.originFileObj, imageUrl => this.setState({
        imageUrl,
        loading: false,
      }));
    }
  };

  render() {
    const uploadButton = (
      <div>
        <Icon type={this.state.loading ? 'loading' : 'plus'} />
        <div className="ant-upload-text">上传</div>
      </div>
    );
    const imageUrl = this.state.imageUrl;
    return <div className="apply">
      <h2>申请加入网址导航</h2>
      <ul className='content'>
        <li>
          <div className='nameBox'>
            网站名称
            <span>*</span>
          </div>
          <Input placeholder="请输入网站名称" ref={'name'} />
        </li>
        <li>
          <div className="nameBox">
            LOGO
            <span>*</span>
          </div>
          <Upload
            name="picdir1"
            listType="picture-card"
            className="avatar-uploader"
            showUploadList={false}
            customRequest={noopUpload}
            beforeUpload={beforeUpload}
            onChange={this.handleChange}
          >
            {imageUrl ? <img src={imageUrl} alt="avatar" /> : uploadButton}
          </Upload>
        </li>
        <li>
          <div className='nameBox'>
            网站简介
            <span>*</span>
          </div>
          <TextArea rows={4} ref={'title'} />
        </li>
        <li>
          <div className='nameBox'>
            网址
            <span>*</span>
          </div>
          <Input placeholder="请输入网址" ref={'link'} />
        </li>
        <li>
          <div className='nameBox'>
            收录分类
          </div>
          <InputGroup compact>
            {/* [BUG 修复] 原实现提交时读 touzi.props.defaultValue —— 那是**初始**值，
                用户改了分类后提交的还是「投资机构」。改为受控 onChange。 */}
            <Select
              style={{ width: '80%', marginLeft: '20%', height: '0.55rem' }}
              value={this.state.sortValue}
              onChange={(v) => this.setState({ sortValue: v })}
            >
              <Option value="投资机构">投资机构</Option>
              <Option value="交易平台">交易平台</Option>
              <Option value="行情">行情</Option>
              <Option value="矿业">矿业</Option>
              <Option value="钱包">钱包</Option>
              <Option value="技术平台">技术平台</Option>
              <Option value="工具">工具</Option>
              <Option value="文档">文档</Option>
              <Option value="行业媒体">行业媒体</Option>
              <Option value="交流社区">交流社区</Option>
              <Option value="知名机构">知名机构</Option>
            </Select>
          </InputGroup>
        </li>
        <li>
          <div className='nameBox'>
            Alexa排名
          </div>
          <Input placeholder="请输入" ref={'paiming'} />
        </li>
        <li>
          <div className='nameBox'>
            联系人
          </div>
          <Input placeholder="请输入" ref={'lianxi'} />
        </li>
        <li>
          <div className='nameBox'>
            联系方式
          </div>
          <Input placeholder="请输入（如：手机或QQ号等）" ref={'number'} />
        </li>
        <li>
          <Button type="primary" block onClick={() => {
            let { name, title, link, paiming, lianxi, number } = this.refs,
              val = (r) => (r && r.input && typeof r.input.value === 'string') ? r.input.value.trim() : '',
              obj = {
                "sitename": val(name),
                "picdir": this.state.imageUrl || '',
                "short": (title && title.textAreaRef && typeof title.textAreaRef.value === 'string')
                        ? title.textAreaRef.value.trim() : '',
                "url": val(link),
                // [BUG 修复] 用受控 state 的当前值，而不是 defaultValue
                "sort": this.state.sortValue,
                "alexa": val(paiming),
                "lxr": val(lianxi),
                "tel": val(number)
              };
            if (!obj.sitename) { message.warning('请填写网站名称'); return; }
            if (!obj.url) { message.warning('请填写网址'); return; }
            // [BUG 修复] url 补协议，否则入库后前台跳转 /xxx 会被当成站内相对路径
            if (!/^https?:\/\//i.test(obj.url)) { obj.url = 'http://' + obj.url.replace(/^\/+/, ''); }
            axios({
              method: 'post',
              /* [BUG 修复] 原 URL 结尾带一个空格：'a=ajax_site ' 。
                 后端 sanitizeRouteToken 只放行 [A-Za-z0-9_]{1,50}，含空格会被判非法
                 并回退到默认 action —— 这个表单点提交其实一条都没入库。 */
              url: `${global.constants.winUrl}?c=Content&a=ajax_site`,
              data: { "data": obj }
            }).then(res => {
              if (!res) { message.error('提交失败，请稍后重试'); return; }
              if (res.success === 0) {
                message.success('提交成功，请等待审核！');
                window.location.reload(true);
              } else {
                // [BUG 修复] 原来只处理成功分支，失败时用户完全无感知
                message.error(res.msg || '提交失败，请稍后重试');
              }
            }).catch(() => message.error('提交失败，请检查网络后重试'))
          }}>提交</Button>
          {/*<Button block>重新填写</Button>*/}
        </li>
      </ul>
      <div className="footer-Box">
        <div className="box l">
          <h6>收录原则：</h6>
          <main>
            <p>1、需要与贵网站置换首页友情链接</p>
            <p>2、需要贵网站能在不需要科学上网的前提下正常、顺畅地访问</p>
            <p>3、网站内容健康,合法合规，无色情、无反动信息等</p>
            <p>4、网站无挂马，无虚假信息</p>
          </main>
        </div>
        <div className="box r">
          <h6>含有以下信息可能无法收录</h6>
          <main>
            <p>1、网站名称与实际内容不符</p>
            <p>2、以关键词为网站名称</p>
            <p>3、无实质内容</p>
            <p>4、非顶级域名</p>
          </main>
        </div>
        <div className="box b">
          <h6>友情提示：</h6>
          <main>
            <p>1、我们会优先考虑收录已做金色财经友链的站点</p>
            <p>2、如果申请后两周内未被收录，说明网站还不符合收录条件，届时我们可能不会一一通知，还望知晓。</p>
          </main>
        </div>
      </div>
    </div>
  }
}

export default (Apply);