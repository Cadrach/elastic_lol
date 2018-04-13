import {ACT_FILTER_ADD, ACT_FILTER_REMOVE, ACT_FILTER_UPDATE} from '../constants'

const filters = (state = {}, action) => {
    if(action.type == ACT_FILTER_ADD){
        return Object.assign({}, state, {
            [action.key]: action.value
        });
    }
    return state;
}

export default filters;