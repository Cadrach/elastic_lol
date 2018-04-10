import React, { Component } from 'react';
import ReactDOM from 'react-dom';
import _ from 'lodash'
import Item from './Item'

// import { withStyles } from 'material-ui/styles';
import Avatar from 'material-ui/Avatar';
import Icon from 'material-ui/Icon';

export default class Participant extends Component {
    render() {
        var p = this.props.participant;
        var champion = _.find(this.props.champions.data, {id: p.championId});
        var version = this.props.champions.version;
        var img = 'http://ddragon.leagueoflegends.com/cdn/'+version+'/img/champion/'+champion.key+'.png';
        var items = p.itemBuildOrder;
        var purchaseEvents = p.events.ITEM_PURCHASED ? p.events.ITEM_PURCHASED:[];
        var prevTime = null;
        return (
            <div>
                <Avatar src={img} size={64} style={{float: 'left'}}/>&nbsp;
                {items.map((item, index) => (
                    <Item key={index} itemId={item} items={this.props.items}/>
                ))}
                <span>{p.kills}/{p.deaths}/{p.assists}</span>
                <span>{p.identity.summonerName}</span>
                <span>{p.enemyTeam.damageType}</span>
                <hr/>

                {purchaseEvents.map((event, index) => {
                    const showArrow = prevTime != null && (prevTime + 10000)<event['timestamp'];
                    prevTime = event['timestamp'];
                    return (
                        <span key={index}>
                            {showArrow && <i className="fas fa-2x fa-angle-double-right" style={{float: 'left'}}></i>}
                            <Item itemId={event.itemId} items={this.props.items}/>
                        </span>
                    )
                })}
            </div>
        );
    }
}