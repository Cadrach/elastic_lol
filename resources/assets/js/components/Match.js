import React, { Component } from 'react';
import ReactDOM from 'react-dom';
import Participant from './Participant'
import _ from 'lodash'

import {List, ListItem} from 'material-ui/List';
// import Grid from 'material-ui/Grid';

export default class Match extends Component {
    render() {
        var teams = [
            _.filter(this.props.match.participants, {teamId: 100}),
            _.filter(this.props.match.participants, {teamId: 200})
        ];
        return <div>
            {teams.map((team, index) => (
                <div key={index}>
                    {team.map(participant => (
                        <ListItem key={participant.participantId}>
                            <Participant participant={participant} items={this.props.items} champions={this.props.champions}/>
                        </ListItem>
                    ))}
                </div>
            ))}
        </div>
    }
}