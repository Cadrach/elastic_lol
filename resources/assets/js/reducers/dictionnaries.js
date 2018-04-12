import {ACT_ADD_DICTIONNARIES} from '../constants'

const dictionnaries = (state = {}, action) => {
    if(action.type == ACT_ADD_DICTIONNARIES){
        console.log('ADDING DICT!!!');
        return action.dictionnaries;
    }
    return state;
}

export default dictionnaries;