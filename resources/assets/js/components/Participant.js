import React, { Component } from 'react';
import ReactDOM from 'react-dom';
import _ from 'lodash'
import Item from './Item'

// import { withStyles } from 'material-ui/styles';
import Avatar from 'material-ui/Avatar';

export default class Participant extends Component {
    render() {
        var p = this.props.participant;
        console.log(p)
        var champion = _.find(this.props.champions.data, {id: p.championId});
        var version = this.props.champions.version;
        var img = 'http://ddragon.leagueoflegends.com/cdn/'+version+'/img/champion/'+champion.key+'.png';
        var items = [p.item0, p.item1, p.item2, p.item3, p.item4, p.item6];
        return (
            <div>
                <Avatar src={img} size={64} style={{float: 'left'}}/>&nbsp;
                {items.map((item, index) => (
                    <Item key={index} itemId={item} items={this.props.items}/>
                ))}
                <span>{p.kills}/{p.deaths}/{p.assists}</span>
            </div>
        );
    }
}