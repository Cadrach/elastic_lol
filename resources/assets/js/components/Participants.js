import React, { Component } from 'react';
import { connect } from 'react-redux'
import Participant from './Participant'

import { Segment } from 'semantic-ui-react'

const mapStateToProps = state => {
    return {
        participants: state.participants
    }
}

const Participants = ({participants}) => {
        if (participants.took) {
            return <Segment.Group>
                {participants.hits.hits.map((participant,index) => {
                    return <Segment key={index}>
                        <Participant participant={participant._source}/>
                    </Segment>
                })}
            </Segment.Group>
        } else {
            return <i>WHAT?</i>
        }
}

export default connect(mapStateToProps)(Participants)