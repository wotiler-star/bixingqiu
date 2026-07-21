import React from 'react';
import ReactDOM, { render } from 'react-dom';
import { NavLink } from 'react-router-dom';
import action from '../store/action';
import '../static/css/ListPage.less';
import '../static/css/search.less';
import axios from "axios";
import Qs from "qs";

class Search extends React.Component {
    constructor(props, context) {
        super(props.context);
        this.state = {
            data: null,
            cataid: null,
            search: window.localStorage.SEARCH,
            len: null
        }
    }

    componentWillMount() {
        document.getElementById('root').scrollIntoView(true);//为ture返回顶部，false为底部  
    }


    async componentDidMount() {
        console.log(this.state.search);
        axios.get(`${global.constants.winUrl}?c=so&w=${this.state.search}`).then(res => {
            console.log(res);
            this.setState({
                data: res,
                len: res.length,
            })
        })
    }


    render() {
        return <section className='list-page'>
            <div className='main'>
                <div className='left-content'>
                    <div class="search-import">
                        <input type="text" class="search-input" onKeyDown={(ev) => {
                            if (ev.keyCode !== 13) return;
                            if (ev.target.value == '') return;
                            axios.get(`${global.constants.winUrl}?c=so&w=${ev.target.value}`).then(res => {
                                this.setState({
                                    data: res,
                                    len:res.length
                                })
                            })
                            window.localStorage.SEARCH = ev.target.value;
                        }} />
                    </div>
                    <div class="search-contet-top clearfix">
                        <div class="result-num">搜索出<span>{this.state.len}</span>条结果</div>
                    </div>

                    <div className='list-content'>

                        {this.state.data ? this.state.data.map((item, index) => {
                            let { picdir_list, title, short, riqi, source, title2, id, cataid } = item;
                            return <div className='news-list'>
                                <NavLink to={`/Detailed?cataid=${cataid}&id=${id}`}>
                                    <div className='imgBox'>
                                        <img src={picdir_list} alt="" />
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
                        }) : null}
                    </div>
                </div>
                {/**   <div className='right-content'>
                    <div className='recomend'>
                        <h3>热门新闻</h3>
                        {this.state.hotArr ? this.state.hotArr.map((item, index) => {
                            let { picdir_list, title, riqi, id } = item;
                            return <div className='listBox' key={index}>
                                <NavLink to={`/Detailed?cataid=${cataid}&id=${id}`}>
                                    <img src={picdir_list} alt="" />
                                    <span>{title}</span>
                                    <p>{riqi}</p>
                                </NavLink>
                            </div>
                        }) : null}
                    </div>
                </div>
             */}
            </div>
        </section>
    }
}
export default (Search);
