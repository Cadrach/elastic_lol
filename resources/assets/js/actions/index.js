import {ACT_ADD_DICTIONNARIES, ACT_LOAD_PARTICIPANTS} from '../constants'
import 'whatwg-fetch'

export const loadParticipants = () => {
    return function(dispatch){
        return fetch('api/match/participants')
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