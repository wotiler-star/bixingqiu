import React from 'react';
import ReactDOM, {render} from 'react-dom';
import {Upload, Icon, message} from 'antd';
import axios from "axios";

// [修复] 原 beforeUpload 返回 true 会触发 antd 把文件 POST 到 action="static/media"（一个不存在的
// 假地址），请求 404 后 onChange 拿不到 'done'，于是头像 base64 永远设不上、且控制台报错。
// 改为在 beforeUpload 内直接读成 dataURL 并写入 state，随后 return false 阻止自动上传。
function getBase64(img, callback) {
  const reader = new FileReader();
  reader.addEventListener('load', () => callback(reader.result));
  reader.readAsDataURL(img);
}

function beforeUpload(file) {
  const isImage = /image\/(jpeg|png|gif|bmp|webp)/.test(file.type);
  if (!isImage) {
    message.error('只能上传 JPG/PNG 等图片文件！');
    return false;
  }
  const isLt2M = file.size / 1024 / 1024 < 2;
  if (!isLt2M) {
    message.error('图片大小不能超过 2MB！');
    return false;
  }
  getBase64(file, (imageUrl) => {
    // 延迟到下一帧，避免与 antd 内部 setState 冲突
    setTimeout(() => this.setState({imageUrl: imageUrl, loading: false}), 0);
  });
  return false; // 阻止自动上传到假地址
}


class init extends React.Component {
  constructor(props, context) {
    super(props, context);
    this.state = {
      data: null,
      id: null,
      loading: false,
      imageUrl: null
    }
  }

  componentDidMount() {
    let id = window.localStorage.getItem('HID');
    this.setState({
      id: id
    });
    axios.get(`${global.constants.winUrl}?c=h&a=ajax_getInfo&hid=${id}`).then(res => {
      // [修复] 后端未登录/会话过期会返回空数组而非数组，这里兜底避免 .map 崩溃
      this.setState({
        data: Array.isArray(res) ? res : []
      })
    });
  }

  render() {
    const uploadButton = (
        <div>
          <Icon type={this.state.loading ? 'loading' : 'plus'}/>
          <div className="ant-upload-text">更改头像</div>
        </div>
    );
    const imageUrl = this.state.imageUrl;
    return <div>{this.state.data ? this.state.data.map((item, index) => {
      return <div className={"right-content-1 right-box"} key={index}>
        <h3>
          账户信息
        </h3>

        <div className="userimg">
          <Upload
              name="picdir1"
              listType="picture-card"
              className="avatar-uploader"
              showUploadList={false}
              beforeUpload={beforeUpload.bind(this)}
          >
            {imageUrl ? <img src={imageUrl} alt="avatar"/> : uploadButton}
          </Upload>
          {/*<span>上传头像</span>*/}
        </div>
        <ul className="information">
          <li>
            <span>用户名</span>{item.name}
          </li>
          <li>
            <span>手机号</span>{item.hname}
          </li>
          <li>
            <span>注册日期</span>{item.riqi}
          </li>
          <li>
            <span>昵称</span>
            <input type="text" placeholder={item.name} ref={'a'}/>
          </li>
          <li>
            <span>简介</span>
            <input type="text" placeholder={item.short} ref={'b'}/>
          </li>
          <li>
            <span>邮箱</span>
            <input type="text" placeholder={item.email} ref={'c'}/>
          </li>
          <li>
            <span>地址</span>
            <input type="text" placeholder={item.address} ref={'d'}/>
          </li>
          <li>
            <button onClick={() => {
              let {a, b, c, d} = this.refs;

              let obj = {
                "sort": "geren",
                "name": a.value == '' ? item.name : a.value,
                "short": b.value == '' ? item.short : b.value,
                'email': c.value == '' ? item.email : c.value,
                // [修复] 后端 profileWhitelist 字段名是 address，原代码误用 addr → 地址永远存不上
                'address': d.value == '' ? item.address : d.value
              };
              // [修复] 仅当用户真正选择了新头像（dataURL）时才上传 picdir，
              // 否则传字符串 'null' 会被后端 base64_image_content 误判并清空原头像。
              if (imageUrl && imageUrl.indexOf('data:') === 0) {
                obj.picdir = imageUrl;
              }
              axios({
                method: 'post',
                url: `${global.constants.winUrl}?c=h&a=ajax_setInfo&hid=${this.state.id}`,
                data: {"data": obj}
              }).then(res => {
                res.success == 0 ? alert('保存成功！') : alert('保存失败，请重试！');
              })
            }}>确定
            </button>
            <button onClick={() => {
              let {a, b, c, d} = this.refs;
              a.value = ''; b.value = ''; c.value = ''; d.value = '';
              this.setState({imageUrl: null});
            }}>重置</button>
            <b>提示：点击文字，进行编辑！</b>
          </li>
        </ul>
      </div>
    }) : null}
    </div>
  }
}

export default (init);