import React from 'react'
import { connect } from 'react-redux'
import { Button } from 'semantic-ui-react'
import { loadParticipants } from '../actions'

const mapDispatchToProps = (dispatch, ownProps) => {
    return {
        onClick: (event) => {
            dispatch(loadParticipants())
        }
    }
}

const LoadParticipants = ({ onClick }) => {
    return <Button onClick={onClick}>Apply</Button>;
}

export default connect(null, mapDispatchToProps)(LoadParticipants)