import React from 'react';
import ReactDOM, { render } from 'react-dom';
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
  const isJPG = file.type === 'image/jpeg';
  if (!isJPG) {
    message.error('You can only upload JPG file!');
  }
  const isLt2M = file.size / 1024 / 1024 < 2;
  if (!isLt2M) {
    message.error('Image must smaller than 2MB!');
  }
  return isJPG && isLt2M;
}

const { TextArea } = Input;
const InputGroup = Input.Group;
const Option = Select.Option;
const options = [];

class Apply extends React.Component {
  constructor(props, context) {
    super(props, context);
    this.state = {
      loading: false,
    };
  }

  componentWillMount() {
    document.getElementById('root').scrollIntoView(true);//为ture返回顶部，false为底部
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
            action="static/media"
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
          <InputGroup compact ref={'tttt'}>
            <Select style={{ width: '80%', marginLeft: '20%', height: '0.55rem' }} defaultValue="投资机构" ref={'touzi'}>
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
            let { name, title, link, touzi, paiming, lianxi, number } = this.refs,
              obj = {
                "sitename": name.input.value,
                "picdir": this.state.imageUrl,
                "short": title.textAreaRef.value,
                "url": link.input.value,
                "sort": touzi.props.defaultValue,
                "alexa": paiming.input.value,
                "lxr": lianxi.input.value,
                "tel": number.input.value
              };
            console.log(this.refs.tttt, touzi.props.defaultValue, touzi.querySelector('.ant-select-selection-selected-value').getAttribute('title'));
            console.log(touzi.querySelector('.ant-select-selection-selected-value').innerHTML);
            console.log(this.refs.tttt.value);
            console.log(touzi.value);
            axios({
              method: 'post',
              url: `${global.constants.winUrl}?c=Content&a=ajax_site `,
              data: { "data": obj }
            }).then(res => {
              if (res.success == 0) {
                alert('提交成功！');
                window.location.reload(true);
              }
            })
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