import React from 'react'
import { connect } from 'react-redux'
import { loadParticipants } from '../actions'

const LoadParticipants = ({ dispatch }) => {
    dispatch(loadParticipants());
    return null;
}

export default connect()(LoadParticipants)