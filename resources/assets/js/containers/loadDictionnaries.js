import React from 'react'
import { connect } from 'react-redux'
import { loadDictionnaries } from '../actions'

const LoadDictionnaries = ({ dispatch }) => {
    dispatch(loadDictionnaries());
    return null;
}

export default connect()(LoadDictionnaries)