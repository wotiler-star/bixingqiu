import * as TYPES from '../action-types';

// 应用启动时从 localStorage 水合登录态，避免刷新后 redux 丢失导致 Header 误判未登录
function loadInitial() {
    try {
        const hid = window.localStorage.getItem('HID');
        if (!hid) return {};
        return {
            hid: hid,
            hname: window.localStorage.getItem('HNAME') || '',
            nickname: window.localStorage.getItem('NICKNAME') || ''
        };
    } catch (e) {
        return {};
    }
}

let INIT_STATE = loadInitial();
export default function person(state = INIT_STATE, action) {
    switch (action.type) {
        case TYPES.SET_PERSON:
            return Object.assign({}, state, action.payload);
        case TYPES.CLEAR_PERSON:
            return {};
        default:
            return state;
    }
};