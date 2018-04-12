import React from 'react'
import { connect } from 'react-redux'
import { addDictionnaries } from '../actions'

const LoadDictionnaries = ({ dispatch }) => {
    console.log('LOADED DIC')
    dispatch(addDictionnaries({runes: 'TEST'}));
    return null;
}

export default connect()(LoadDictionnaries)