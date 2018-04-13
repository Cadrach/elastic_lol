import { combineReducers } from 'redux'
import dictionnaries from './dictionnaries'
import participants from './participants'
import filters from './filters'

export default combineReducers({dictionnaries, participants, filters})