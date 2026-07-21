import * as TYPES from '../action-types';

let INIT_STATE = {
    homeData: [],
};
export default function course(state = INIT_STATE, action) {
    state = JSON.parse(JSON.stringify(state));
    switch (action.type) {
        case TYPES.COURSE_QUERY_HOME:
            let {code, data} = action.homeData;
          console.log(code, data);
          if (parseFloat(code) === 0) {
                state.homeData = data;
            }
            break;
    }
    return state;
};
