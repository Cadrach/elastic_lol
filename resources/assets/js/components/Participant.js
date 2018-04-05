import React, { Component } from 'react';
import ReactDOM from 'react-dom';
import _ from 'lodash'
import Item from './Item'

// import { withStyles } from 'material-ui/styles';
import Avatar from 'material-ui/Avatar';

export default class Participant extends Component {
    render() {
        var champion = _.find(this.props.champions.data, {id: this.props.participant.championId});
        var version = this.props.champions.version;
        var img = 'http://ddragon.leagueoflegends.com/cdn/'+version+'/img/champion/'+champion.key+'.png';
        var stat = this.props.participant.stats
        var items = [stat.item0, stat.item1, stat.item2, stat.item3, stat.item4, stat.item6];
        return (
            <div>
                <Avatar src={img} size={64} style={{float: 'left'}}/>&nbsp;{champion.name}
                {items.map((item, index) => (
                    <Item key={index} itemId={item} items={this.props.items}/>
                ))}
            </div>
        );
    }
}