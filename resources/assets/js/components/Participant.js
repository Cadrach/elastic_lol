import React, { Component } from 'react';
import ReactDOM from 'react-dom';
import _ from 'lodash'
import Item from './Item'

// import { withStyles } from 'material-ui/styles';
import Avatar from 'material-ui/Avatar';

export default class Participant extends Component {
    render() {
        var p = this.props.participant;
        var champion = _.find(this.props.champions.data, {id: p.championId});
        var version = this.props.champions.version;
        var img = 'http://ddragon.leagueoflegends.com/cdn/'+version+'/img/champion/'+champion.key+'.png';
        var stat = p.stats
        var items = [stat.item0, stat.item1, stat.item2, stat.item3, stat.item4, stat.item6];
        return (
            <div>
                <Avatar src={img} size={64} style={{float: 'left'}}/>&nbsp;
                {items.map((item, index) => (
                    <Item key={index} itemId={item} items={this.props.items}/>
                ))}
                <span>{stat.kills}/{stat.deaths}/{stat.assists}</span>
            </div>
        );
    }
}