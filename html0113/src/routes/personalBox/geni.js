import React from 'react';
import ReactDOM, {render} from 'react-dom';
import {Radio, Input, Upload, Icon, message} from 'antd';
import axios from "axios";
import Editor from "react-umeditor";

import { localePath } from '../../i18n/i18n';

const RadioGroup = Radio.Group;
const {TextArea} = Input;

function getBase64(img, callback) {
  const reader = new FileReader();
  reader.addEventListener('load', () => callback(reader.result));
  reader.readAsDataURL(img);
}

class geni extends React.Component {
  constructor(props, context) {
    super(props, context);
    this.state = {
      id: '',
      hname: '',
      value: 26,
      editId: null,
      form_data: {
        editor: ""
      },
      loading: false,
      imageUrl: '',
      tip: '',
      tipIf: false,
    }
  }

  componentDidMount() {
    let id = '';
    let hname = '';
    try {
      id = window.localStorage.getItem('HID') || '';
      hname = window.localStorage.getItem('HNAME') || '';
    } catch (e) { }
    this.setState({ id, hname });
    // 编辑态：URL 带 ?id= 时拉取该稿件预填
    try {
      const params = new URLSearchParams(window.location.search);
      const editId = params.get('id');
      if (editId) {
        this.fetchEdit(editId);
      }
    } catch (e) { }
  }

  // 编辑态：读取单篇稿件并预填表单
  fetchEdit = (id) => {
    let hid = '';
    try { hid = window.localStorage.getItem('HID') || ''; } catch (e) { }
    if (!hid) {
      this.showTip('请先登录后再投稿');
      return;
    }
    axios({
      method: "post",
      url: `${global.constants.winUrl}?c=h&a=ajax_get_myi_one&hid=${encodeURIComponent(hid)}`,
      data: { "data": { "id": id } }
    }).then(res => {
      if (res && res.success == 0 && res.data) {
        const d = res.data;
        const catNum = parseInt(d.cataid, 10) || this.state.value;
        this.setState({
          editId: parseInt(id, 10),
          value: catNum,
          imageUrl: d.picdir_list || '',
          form_data: { editor: d.cnt || '' }
        });
        // 同步非受控字段（title / gjc / nrzy）
        if (this.refs.title) this.refs.title.value = d.title || '';
        if (this.refs.gjc) this.refs.gjc.value = d.keywords || '';
        if (this.refs.nrzy && this.refs.nrzy.textAreaRef) this.refs.nrzy.textAreaRef.value = d.cnt_short || '';
        this.showTip('已载入待编辑文章');
      } else {
        this.showTip('文章加载失败或无权编辑');
      }
    }).catch(() => this.showTip('网络异常，文章加载失败'));
  };

  onChange = (e) => {
    this.setState({ value: e.target.value });
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

  handleEditorChange(content) {
    var form_data = this.state.form_data;
    form_data.editor = content;
    this.setState({ form_data: form_data });
  }

  // 内联提示（替代 alert，避免双弹窗）
  showTip = (msg) => {
    this.setState({ tip: msg, tipIf: true });
    setTimeout(() => this.setState({ tipIf: false }), 3000);
  };

  // 封面上传：本地直读 base64 后 return false，阻止 antd 默认上传到不存在的 static/media（与 init.js 一致）
  beforeUpload = (file) => {
    const isJpgOrPng = file.type === 'image/jpeg' || file.type === 'image/png';
    if (!isJpgOrPng) {
      message.error('封面仅支持 JPG / PNG 格式');
      return false;
    }
    const isLt2M = file.size / 1024 / 1024 < 2;
    if (!isLt2M) {
      message.error('封面图片不能超过 2MB');
      return false;
    }
    getBase64(file, imageUrl => this.setState({ imageUrl, loading: false }));
    return false;
  };

  handleChange = (info) => {
    if (info.file.status === 'uploading') {
      this.setState({loading: true});
      return;
    }
    if (info.file.status === 'done') {
      getBase64(info.file.originFileObj, imageUrl => this.setState({ imageUrl, loading: false }));
    }
  };

  // 提交审核 / 存草稿 共用逻辑（新建或编辑复用）
  submitArticle = (isDraft) => {
    const { id, hname, editId } = this.state;
    if (!id) {
      this.showTip('请先登录后再投稿');
      setTimeout(() => { this.props.history && this.props.history.push(localePath('/login')); }, 1200);
      return;
    }
    const { title, gjc, nrzy } = this.refs;
    const t = (title.value || '').trim();
    const cnt = this.state.form_data.editor || '';
    const cntText = cnt.replace(/<[^>]+>/g, '').trim();
    if (!t) {
      this.showTip('请填写文章标题');
      return;
    }
    if (cntText.length < 10) {
      this.showTip('正文内容过短，请完善后再提交');
      return;
    }
    const obj = {
      "hid": id,
      "hname": hname,
      "cataid": this.state.value,
      "title": t,
      "keywords": gjc.value,
      "cnt_short": nrzy.textAreaRef.value,
      "cnt": cnt,
      "picdir_list": this.state.imageUrl || ''
    };
    // 编辑态附带稿件 id，并切换到 geni_edit 接口
    const act = editId ? 'geni_edit' : 'geni';
    if (editId) obj.id = editId;
    axios({
      method: "post",
      url: `${global.constants.winUrl}?c=h&a=${act}&hid=${encodeURIComponent(id)}` + (isDraft ? '&cg' : ''),
      data: {"data": obj}
    }).then(res => {
      if (res.success == 0) {
        message.success(isDraft
          ? (editId ? '草稿已更新' : '已存草稿')
          : (editId ? '修改已提交，等待审核' : '提交成功，等待审核'));
        const go = localePath('/personal/myi');
        setTimeout(() => {
          if (this.props.history) this.props.history.push(go);
          else window.location.assign(go);
        }, 900);
      } else if (res.success == 401) {
        this.showTip('登录已失效，请重新登录');
      } else {
        this.showTip('提交失败，请稍后重试');
      }
    }).catch(() => this.showTip('网络异常，请稍后重试'));
  };

  render() {
    let icons = this.getIcons();
    let form_data = this.state.form_data;
    const uploadButton = (
        <div>
          <Icon type={this.state.loading ? 'loading' : 'plus'}/>
          <div className="ant-upload-text">点击上传</div>
        </div>
    );
    const imageUrl = this.state.imageUrl;
    return <div className="right-content-7 right-box">
      <h3>{this.state.editId ? '编辑文章' : '发布文章'}</h3>
      <div className='warning' style={{ display: this.state.tipIf ? 'block' : 'none' }}>{this.state.tip}</div>
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
        <input type="text" placeholder="请输入文章标题" ref={'title'}/>
      </div>
      <div className="particulars">
        <div>
          <Editor icons={icons} value={form_data.editor}
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
              beforeUpload={this.beforeUpload}
              onChange={this.handleChange}
          >
            {imageUrl ? <img src={imageUrl} alt="封面"/> : uploadButton}
          </Upload>
          <span>支持 JPG / PNG 格式，封面大小不超过 2MB。</span>
        </div>
      </div>
      <div className={'explain'}>
        <h4>说明:</h4>
        <p>考虑到用户浏览体验，所有投稿美好星球的稿件，美好星球均有权对文章的标题、头图进行调整，这些调整并不会影响正文内容，如果需要进行内容调整，编辑会与作者联系确认，不会直接修改。</p>
      </div>
      <div className="submit">
        <button onClick={() => this.submitArticle(false)}>{this.state.editId ? '保存修改' : '提交审核'}</button>
        &nbsp;&nbsp;&nbsp;
        <button onClick={() => this.submitArticle(true)}>{this.state.editId ? '更新草稿' : '存草稿'}</button>
        &nbsp;&nbsp;&nbsp;
      </div>
    </div>
  }
}

export default (geni);
