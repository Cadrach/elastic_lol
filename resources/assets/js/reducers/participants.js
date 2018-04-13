import {ACT_LOAD_PARTICIPANTS} from '../constants'

const participants = (state = {}, action) => {
    if(action.type == ACT_LOAD_PARTICIPANTS){
        console.log('ADDING PARTICIPANTS!!!');
        return action.participants;
    }
    return state;
}

export default participants;