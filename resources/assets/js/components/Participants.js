import React, { Component } from 'react';
import { connect } from 'react-redux'
import Participant from './Participant'

import List, {ListItem} from 'material-ui/List';

const mapStateToProps = state => {
    return {
        participants: state.participants
    }
}

const Participants = ({participants}) => {
        if (participants.took) {
            return <div>

                <List>
                {participants.hits.hits.map((participant,index) => {
                    return <ListItem button key={index}>
                        <Participant participant={participant._source}/>
                    </ListItem>
                })}
                </List>
            </div>
        } else {
            return <i>WHAT?</i>
        }
}

export default connect(mapStateToProps)(Participants)