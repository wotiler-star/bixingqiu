import React from 'react';
import ReactDOM, {render} from 'react-dom';
import {Radio, Input, Upload, Icon, message} from 'antd';
import axios from "axios";
import Editor from "react-umeditor";

const RadioGroup = Radio.Group;
const {TextArea} = Input;

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

class geni extends React.Component {
  constructor(props, context) {
    super(props, context);
    this.state = {
      data: null,
      value: 26,
      form_data: {
        text: "123",
        editor: ""
      },
      loading: false,
    }
  }

  componentDidMount() {
    let id = window.localStorage.getItem('HID'),
        hname = window.localStorage.getItem('HNAME');
    this.setState({
      id,
      hname
    });
  }

  onChange = (e) => {
    this.setState({
      value: e.target.value,
    });
  };

  getIcons() {
    return [
      "source | undo redo | bold italic underline strikethrough fontborder | ",
      "paragraph fontfamily fontsize | superscript subscript | ",
      "forecolor backcolor | removeformat | insertorderedlist insertunorderedlist | selectall | ",
      "cleardoc  | indent outdent | justifyleft justifycenter justifyright | touppercase tolowercase | ",
      "horizontal date time  | image formula spechars | inserttable"
    ]
  }

  handleFormChange(e) {
    e = e || window.event;
    var target = e.target || e.srcElement;
    var value = target.value;
    var form_data = this.state.form_data;
    form_data.text = value;
    this.setState({
      form_data: form_data
    })
  }

  handleEditorChange(content) {
    var form_data = this.state.form_data;
    form_data.editor = content;
    this.setState({
      form_data: form_data
    })
  }

  handleSubmitForm() {
    var form_data = this.state.form_data;
    alert(form_data.editor);
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

  render() {
    let icons = this.getIcons();
    let plugins = {
      image: {
        uploader: {
          url: "../../static/media",
          name: "file",
          filter: (res) => res.url
        }
      }
    };
    let form_data = this.state.form_data;
    const uploadButton = (
        <div>
          <Icon type={this.state.loading ? 'loading' : 'plus'}/>
          <div className="ant-upload-text">点击上传</div>
        </div>
    );
    const imageUrl = this.state.imageUrl;
    return <div className="right-content-7 right-box">
      <h3>发布文章</h3>
      <div className={'column-box'}>
        <h4>选择栏目:</h4>
        <RadioGroup onChange={this.onChange} value={this.state.value}>
          <Radio value={26}>行情</Radio>
          <Radio value={27}>研报</Radio>
          <Radio value={28}>人物</Radio>
          <Radio value={29}>宏观</Radio>
          <Radio value={30}>技术</Radio>
          <Radio value={202}>政策</Radio>
          <Radio value={203}>评级</Radio>
          <Radio value={204}>全球</Radio>
          <Radio value={205}>资产</Radio>
          <Radio value={207}>币链</Radio>
          <Radio value={208}>媒体</Radio>
          <Radio value={209}>项目</Radio>
        </RadioGroup>
      </div>
      <div className={'headline'}>
        <h4>文章标题:</h4>
        <input type="text" ref={'title'}/>
      </div>
      <div className="particulars">
        <div>
          <Editor icons={icons} plugins={plugins} value={form_data.editor}
                  onChange={this.handleEditorChange.bind(this)}/>
        </div>
      </div>
      <div className="antistop">
        <h4>关键词：</h4>
        <input type="text" placeholder={'关键词之间请用空格或者中、英文下的逗号隔开'} ref={'gjc'}/>
      </div>
      <div className="content">
        <h4>内容摘要:</h4>
        <TextArea rows={4} ref={'nrzy'}/>
      </div>
      <div className="uploading">
        <h4>上传封面:</h4>
        <div className="content">
          <Upload
              name="header"
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
      <div className={'explain'}>
        <h4>说明:</h4>
        <p>考虑到用户浏览体验，所有投稿美好星球的稿件，美好星球均有权对文章的标题、头图进行调整，这些调整并不会影响正文内容，如果需要进行内容调整，编辑会与作者联系确认，不会直接修改。</p>
      </div>
      <div className="submit">
        <button onClick={() => {
          let {title, gjc, nrzy} = this.refs;
          let obj = {
            "hid": this.state.id,
            "hname": this.state.hname,
            "cataid": this.state.value,
            "title": title.value,
            "keywords": gjc.value,
            "cnt_short": nrzy.textAreaRef.value,
            "cnt": this.state.form_data.editor,
            "picdir_list": imageUrl,
          };
          axios({
            method: "post",
            url: `${global.constants.winUrl}?c=h&a=geni&hid=${this.state.id}`,
            data: {"data": obj}
          }).then(res => {
            if (res.success == 0) {
              alert('提交成功');
              window.location.reload();
            } else if (res.success == 1) {
              alert('提交失败')
            }
          })
        }
        }>提交审核
        </button>
        &nbsp;&nbsp;&nbsp;
        <button  onClick={() => {
          let {title, gjc, nrzy} = this.refs;
          let obj = {
            "hid": this.state.id,
            "hname": this.state.hname,
            "cataid": this.state.value,
            "title": title.value,
            "keywords": gjc.value,
            "cnt_short": nrzy.textAreaRef.value,
            "cnt": this.state.form_data.editor,
            "picdir_list": imageUrl
          };
          axios({
            method: "post",
            url: `${global.constants.winUrl}?c=h&a=geni&hid=${this.state.id}&cg`,
            data: {"data": obj}
          }).then(res => {
            if (res.success == 0) {
              alert('提交成功');
              window.location.reload();
            } else if (res.success == 1) {
              alert('提交失败')
            }
          })
        }
        }>存草稿</button>
        &nbsp;&nbsp;&nbsp;
      </div>
    </div>
  }
}

export default (geni);