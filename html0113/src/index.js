import React from 'react';
import ReactDOM, { render } from 'react-dom';
import { BrowserRouter, Switch, Route, Redirect } from 'react-router-dom';
import SeoManager from './SeoManager';


import { Provider } from 'react-redux';
import store from './store/index';

import './static/css/Header.less';
import './static/css/Footer.less';

import Header from './component/Header';
import Footer from './component/Footer';
import Column from './routes/Column';
import MyDetail from './routes/MyDetail';
import Home from './routes/Home';
import List from './routes/ListPage';
import Detailed from './routes/Detailed';
import Livenews from './routes/Livenews';
import NavPage from './routes/NavPage';
import Author from './routes/Author';
import Login from './routes/Login';
import Register from './routes/Register';
import Personal from './routes/Personal';
import Details from './routes/Details';
import Apply from './routes/Apply';
import Search from './routes/search';

const GetBaidu = props => {
  let children = props.children;
  let _hmt = _hmt || [];
  (function () {
    var hm = document.createElement("script");
    hm.src = "https://hm.baidu.com/hm.js?ccb0e8f10ba18ccb5041e8aa48068c1b";
    var s = document.getElementsByTagName("script")[0];
    s.parentNode.insertBefore(hm, s);
  })();
  return children;
};


render(<Provider store={store}>
  <BrowserRouter>
    <div>
      <SeoManager />
      <Header />
      <Switch>
        <Route path='/home' exact component={Home} />
        <Route path='/column' component={Column} />
        <Route path='/mydetail' component={MyDetail} />
        <Route path='/list' component={List} />
        <Route path='/detailed' component={Detailed} />
        <Route path='/livenews' component={Livenews} />
        <Route path='/navpage' component={NavPage} />
        <Route path='/author' component={Author} />
        <Route path='/login' component={Login} />
        <Route path='/register' component={Register} />
        <Route path='/personal' component={Personal} />
        <Route path='/details' component={Details} />
        <Route path='/apply' component={Apply} />
        <Route path='/search' component={Search} />
        <Redirect to='/home' />
      </Switch>
      <Footer>
        <GetBaidu></GetBaidu>
      </Footer>
    </div>
  </BrowserRouter>
</Provider>
  , root);

window.onbeforeunload = function () {
  window.localStorage.removeItem('SEARCH');
}