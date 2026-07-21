import React from 'react';
import ReactDOM, {render} from 'react-dom';
import {Upload, Icon, message} from 'antd';
import axios from "axios";

function getBase64(img, callback) {
  const reader = new FileReader();
  reader.addEventListener('load', () => callback(reader.result));
  console.log(img);
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


class init extends React.Component {
  constructor(props, context) {
    super(props, context);
    this.state = {
      data: null,
      id: null,
      loading: false
    }
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
    console.log(this.state.image);
  }

  componentDidMount() {
    let id = window.localStorage.getItem('HID');
    this.setState({
      id: id
    });
    axios.get(`${global.constants.winUrl}?c=h&a=ajax_getInfo&hid=${id}`).then(res => {
      console.log(res);
      this.setState({
        data: res
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
              action="static/media"
              beforeUpload={beforeUpload}
              onChange={this.handleChange}
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
            <input type="text" placeholder={item.addr} ref={'d'}/>
          </li>
          <li>
            <button onClick={() => {
              let {a, b, c, d} = this.refs;

              let obj = {
                "hid": this.state.id,
                "sort": "geren",
                "name": a.value == '' ? item.name : a.value,
                "short": b.value == '' ? item.short : b.value,
                'email': c.value == '' ? item.email : c.value,
                'addr': d.value == '' ? item.addr : d.value,
                'picdir': imageUrl ? imageUrl : 'null'
            };
              axios({
                method: 'post',
                url: `${global.constants.winUrl}?c=h&a=ajax_setInfo&hid=${this.state.id}`,
                data: {"data": obj}
              }).then(res => {
                res.success == 0 ? alert('上传成功！') : alert('失败请重新上传！');
              })
            }}>确定
            </button>
            <button>重置</button>
            <b>提示：点击文字，进行编辑！</b>
          </li>
        </ul>
      </div>
    }) : null}
    </div>
  }
}

export default (init);