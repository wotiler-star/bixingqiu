import React from 'react';
import { NavLink } from 'react-router-dom';
import '../static/css/ListPage.less';
import '../static/css/search.less';
import axios from "axios";

import { localePath, t } from '../i18n/i18n';

// 安全读取 localStorage：隐私模式 / 禁用 storage 时 window.localStorage 取值会抛异常
function readStoredKeyword() {
    try {
        return window.localStorage.getItem('SEARCH') || '';
    } catch (e) {
        return '';
    }
}

function writeStoredKeyword(w) {
    try {
        window.localStorage.setItem('SEARCH', w);
    } catch (e) {
        /* 忽略：storage 不可用不应影响搜索本身 */
    }
}

// 从 URL 取搜索词，支持分享/刷新/直接访问 /search?w=btc
function keywordFromSearch(search) {
    const m = /[?&]w=([^&]*)/.exec(search || '');
    if (!m) return '';
    try {
        return decodeURIComponent(m[1].replace(/\+/g, ' '));
    } catch (e) {
        return m[1];
    }
}

class Search extends React.Component {
    constructor(props) {
        // 原实现写成 super(props.context)：props 未传进 React.Component，
        // 构造期 this.props 为 undefined，任何构造函数里读 props 都会崩。
        super(props);
        const fromUrl = keywordFromSearch(props.location && props.location.search);
        this.state = {
            data: null,
            cataid: null,
            search: fromUrl || readStoredKeyword(),
            len: 0,
            loading: false
        };
    }

    componentWillMount() {
        const el = document.getElementById('root');
        if (el && el.scrollIntoView) el.scrollIntoView(true);
    }

    componentDidMount() {
        this.fetch(this.state.search);
    }

    componentDidUpdate(prevProps) {
        // 从其它页面再次搜索（Header 提交）会只改 query，需要重新拉取
        const cur = keywordFromSearch(this.props.location && this.props.location.search);
        const old = keywordFromSearch(prevProps.location && prevProps.location.search);
        if (cur !== old && cur) {
            this.setState({ search: cur });
            this.fetch(cur);
        }
    }

    fetch(word) {
        const w = String(word == null ? '' : word).trim();
        if (!w) {
            this.setState({ data: [], len: 0, loading: false });
            return;
        }
        this.setState({ loading: true });
        // 关键：必须 encodeURIComponent。原实现直接拼接，搜索词含 & # + % 空格时
        // 会破坏 query 结构（`w=a&b` 变成两个参数），后端拿到的词是残缺的。
        axios.get(`${global.constants.winUrl}?c=so&w=${encodeURIComponent(w)}`).then(res => {
            const list = Array.isArray(res) ? res : [];
            this.setState({ data: list, len: list.length, loading: false });
        }).catch(() => {
            this.setState({ data: [], len: 0, loading: false });
        });
    }

    onKeyDown = (ev) => {
        if (ev.keyCode !== 13) return;
        const value = String(ev.target.value || '').trim();
        if (!value) return;
        writeStoredKeyword(value);
        this.setState({ search: value });
        this.fetch(value);
        // 同步到地址栏，使搜索结果可分享/可回退
        if (this.props.history) {
            this.props.history.push(localePath('/search?w=' + encodeURIComponent(value)));
        }
    };

    render() {
        const { data, len, search, loading } = this.state;
        return <section className='list-page'>
            <div className='main'>
                <div className='left-content'>
                    <div className="search-import">
                        <input
                            type="text"
                            className="search-input"
                            defaultValue={search}
                            placeholder={t('header.search.placeholder')}
                            onKeyDown={this.onKeyDown}
                        />
                    </div>
                    <div className="search-contet-top clearfix">
                        <div className="result-num">{t('search.result.prefix')}<span>{len}</span>{t('search.result.suffix')}</div>
                    </div>

                    <div className='list-content'>
                        {data && data.length ? data.map((item, index) => {
                            let { picdir_list, title, short, riqi, source, id, cataid } = item;
                            return <div className='news-list' key={id || index}>
                                <NavLink to={localePath(`/detailed?cataid=${cataid}&id=${id}`)}>
                                    <div className='imgBox'>
                                        <img src={picdir_list} alt={title || ''} />
                                    </div>
                                    <div className='content-text'>
                                        <h1>{title}</h1>
                                        <p>{short}</p>
                                    </div>
                                    <div className='list-bottom'>
                                        <span>{source}</span>
                                        <span>{riqi}</span>
                                    </div>
                                </NavLink>
                                <div className='shadow'></div>
                            </div>
                        }) : (loading ? null : <div className='result-num'>{t('search.empty')}</div>)}
                    </div>
                </div>
            </div>
        </section>;
    }
}
export default Search;
