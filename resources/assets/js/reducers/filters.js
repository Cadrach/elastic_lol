import {ACT_FILTER_ADD, ACT_FILTER_REMOVE, ACT_FILTER_UPDATE} from '../constants'
import { addFilter} from '../actions'

const filters = (state = {championId: 56, 'itemBuildOrder.item0':3078}, action) => {
    if(action.type == ACT_FILTER_ADD){
        return Object.assign({}, state, {
            [action.key]: action.value
        });
    }
    return state;
}

export const filterMapStateToProps = (state, ownProps) => {
    return {
        value: state.filters[ownProps.field],
        dictionnaries: state.dictionnaries
    }
}

export const filterMapDispatchToProps = (dispatch, ownProps) => {
    return {
        onChange: (event, data) => {
            dispatch(addFilter(ownProps.field, data.value))
        }
    }
}

export default filters;