import React from 'react'
import { connect } from 'react-redux'
import { loadDictionnaries } from '../actions'

const LoadDictionnaries = ({ dispatch }) => {
    console.log('LOADED DIC')
    dispatch(loadDictionnaries({runes: 'TEST'}));
    return null;
}

export default connect()(LoadDictionnaries)