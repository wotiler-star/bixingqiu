import React from 'react';
import ReactDOM, {render} from 'react-dom';
import {NavLink} from 'react-router-dom';
import axios from "axios";
import {Icon, message, Upload} from "antd";

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

class media extends React.Component {
  constructor(props, context) {
    super(props, context);
    this.state = {
      data: null,
    }
  }

  componentDidMount() {
    let id = window.localStorage.getItem('HID');
    this.setState({
      id
    })
  }

  handleChange = (info) => {
    if (info.file.status === 'uploading') {
      this.setState({loading: true});
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
  handleChange2 = (info) => {
    if (info.file.status === 'uploading') {
      this.setState({loading: true});
      return;
    }
    if (info.file.status === 'done') {
      // Get this url from response in real world.
      getBase64(info.file.originFileObj, imageUrl2 => this.setState({
        imageUrl2,
        loading: false,
      }));
    }
  };
  handleChange3 = (info) => {
    if (info.file.status === 'uploading') {
      this.setState({loading: true});
      return;
    }
    if (info.file.status === 'done') {
      // Get this url from response in real world.
      getBase64(info.file.originFileObj, imageUrl3 => this.setState({
        imageUrl3,
        loading: false,
      }));
    }
  };

  render() {
    const uploadButton = (
        <div>
          <Icon type={this.state.loading ? 'loading' : 'plus'}/>
          <div className="ant-upload-text">点击上传</div>
        </div>
    );
    const imageUrl = this.state.imageUrl;
    const imageUrl2 = this.state.imageUrl2;
    const imageUrl3 = this.state.imageUrl3;
    return <div className="right-content-9 right-box">
      <h3>实名认证</h3>
      <img src="static/media/step2.jpg" alt=""/>
      <h3>运营者信息</h3>
      <div className="name a">
        <h4>组织名称：</h4>
        <input type="text" ref={'mtname'}/>
      </div>
      <div className="name a">
        <h4>组织机构代码：</h4>
        <input type="text" ref={'mtnumber'}/>
      </div>
      <div className="pn a">
        <h4>营业执照扫描件：</h4>
        <div className="content">
          <Upload
              name="picdir1"
              listType="picture-card"
              className="avatar-uploader"
              showUploadList={false}
              action="static/media"
              beforeUpload={beforeUpload}
              onChange={this.handleChange3}
          >
            {imageUrl3 ? <img src={imageUrl3} alt="avatar"/> : uploadButton}
          </Upload>
          <span>支持jpeg、png等格式，照片大小不超过5M。</span>
        </div>
      </div>
      <h3>主体信息</h3>
      <div className="certificates a">
        <h4>证件类型：</h4>
        <select className="form-control">
          <option ref={'sfz'}>身份证</option>
        </select>

      </div>
      <div className="name a">
        <h4>身份证姓名：</h4>
        <input type="text" ref={'name'}/>
      </div>
      <div className="number a">
        <h4>身份证号码：</h4>
        <input type="text" ref={'number'}/>
      </div>
      <div className="pn a">
        <h4>身份证正面：</h4>
        <div className="content">
          <Upload
              name="picdir1"
              listType="picture-card"
              className="avatar-uploader"
              showUploadList={false}
              action="static/media"
              beforeUpload={beforeUpload}
              onChange={this.handleChange}
          >
            {imageUrl ? <img src={imageUrl} alt="avatar"/> : uploadButton}
          </Upload>
          <span>支持jpeg、png等格式，照片大小不超过5M。</span>
        </div>
      </div>
      <div className="pn a">
        <h4>身份证反面：</h4>
        <div className="content">
          <Upload
              name="picdir2"
              listType="picture-card"
              className="avatar-uploader"
              showUploadList={false}
              action="static/media"
              beforeUpload={beforeUpload}
              onChange={this.handleChange2}
          >
            {imageUrl2 ? <img src={imageUrl2} alt="avatar"/> : uploadButton}
          </Upload>
          <span>支持jpeg、png等格式，照片大小不超过5M。</span>
        </div>
      </div>
      <div className="checkbox a">
        <label>
          <input type="checkbox"/>&nbsp;&nbsp;&nbsp;我已阅读并接受
          <a href="javascript:;">《美好星球平台服务协议》 </a>
        </label>
      </div>
      <div className="btn">
        <button onClick={() => {
          let {mtname, mtnumber, sfz, name, number} = this.refs;
          let obj = {
            "hid": this.state.id,
            "sort": sfz.value,
            "sort0": "2",
            "number": number.value,
            "name": name.value,
            "mtname": mtname.value,
            "mtnumber": mtnumber.value,
            "picdir1":imageUrl,
            "picdir2":imageUrl2,
            "picdir3":imageUrl3
          };
          axios({
            method: 'post',
            url: `${global.constants.winUrl}?c=h&a=certStep2&hid=${this.state.id}`,
            data: {"data": obj}
          }).then(res => {
            res.success == 0 ? this.props.history.push("/personal/tips") : alert('认证失败，请重新认证！');
          })
        }}>提交
        </button>
      </div>
    </div>
  }
}

export default (media);