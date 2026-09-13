import * as TYPES from '../action-types';

let person = {
    // 登录成功 / 注册后自动登录时调用，payload: {hid, hname, nickname, image?}
    setPerson(info) {
        return {type: TYPES.SET_PERSON, payload: info || {}};
    },
    clearPerson() {
        return {type: TYPES.CLEAR_PERSON};
    }
};
export default person;