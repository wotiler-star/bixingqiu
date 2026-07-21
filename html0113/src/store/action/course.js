import * as TYPES from '../action-types';
import {queryHome} from '../../api/course';


let course = {
  queryHome() {
    return async dispatch => {
      let homeData = await queryHome();
      console.log(homeData);
      dispatch({
        type: TYPES.COURSE_QUERY_HOME,
        homeData
      })
    };
  },
};
export default course;