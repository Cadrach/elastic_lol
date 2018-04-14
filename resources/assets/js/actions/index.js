import {ACT_ADD_DICTIONNARIES, ACT_LOAD_PARTICIPANTS, ACT_FILTER_ADD} from '../constants'
import 'whatwg-fetch'



export const addFilter = (key, value) => {
    return {
        type: ACT_FILTER_ADD,
        key,
        value
    }
}

export const loadParticipants = () => {
    return function(dispatch, getState){

        var filters = getState().filters;
        console.log(filters)

        return fetch('api/match/participants', {
            method: 'POST',
            body: JSON.stringify(filters)
        })
            .then(response => response.json())
            .then(participants => dispatch({
                    type: ACT_LOAD_PARTICIPANTS,
                    participants
                })
            )
    }
}

export const loadDictionnaries = () => {
    return function(dispatch){
        return fetch('api/match/dictionnaries')
            .then(response => response.json())
            .then(dictionnaries => dispatch({
                type: ACT_ADD_DICTIONNARIES,
                dictionnaries
            })
        )
    }
}