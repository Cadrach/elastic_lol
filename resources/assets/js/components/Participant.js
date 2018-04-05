import React, { Component } from 'react';
import ReactDOM from 'react-dom';
import _ from 'lodash'

// import { withStyles } from 'material-ui/styles';
import Avatar from 'material-ui/Avatar';

export default class Participant extends Component {
    render() {
        var champion = _.find(this.props.champions.data, {id: this.props.participant.championId});
        var version = this.props.champions.version;
        var img = 'http://ddragon.leagueoflegends.com/cdn/'+version+'/img/champion/'+champion.key+'.png';
        return (
            <div>
                <Avatar src={img} size={64} style={{float: 'left'}}/>&nbsp;{champion.name}
            </div>
        );
    }
}