import {ACT_ADD_DICTIONNARIES} from '../constants'
import fetch from 'cross-fetch'

export const loadDictionnaries = () => {
    return function(dispatch){
        return fetch('api/match/participants').then(dictionnaries => dispatch({
            type: ACT_ADD_DICTIONNARIES,
            dictionnaries
        }))
    }
}