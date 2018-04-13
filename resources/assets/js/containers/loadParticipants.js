import React from 'react'
import { connect } from 'react-redux'
import { loadParticipants } from '../actions'

// const mapStateToProps = (state, ownProps) => {
//     return {
//         active: ownProps.filter === state.visibilityFilter
//     }
// }

const mapDispatchToProps = (dispatch, ownProps) => {
    return {
        onClick: (event) => {
            dispatch(loadParticipants())
        }
    }
}

const LoadParticipants = ({ onClick }) => {
    return <button onClick={onClick}>Apply</button>;
}

export default connect(null, mapDispatchToProps)(LoadParticipants)