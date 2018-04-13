import React, { Component } from 'react';
import { connect } from 'react-redux'
import _ from 'lodash'
import Item from './Item'

// import { withStyles } from 'material-ui/styles';
import Avatar from 'material-ui/Avatar';
import Grid from 'material-ui/Grid';

const mapStateToProps = state => {
    return {
        dictionnaries: state.dictionnaries
    }
}

const Participant = ({dictionnaries, participant}) => {
    //Must wait for dictionnaries
    if( ! dictionnaries.version){return null}

    var perkUrl = 'https://raw.communitydragon.org/latest/plugins/rcp-be-lol-game-data/global/default/v1/perk-images/';

    var p = participant;
    var champion = _.find(dictionnaries.champions, {id: p.championId});
    var version = dictionnaries.version;
    var imgChampion = 'http://ddragon.leagueoflegends.com/cdn/'+version+'/img/champion/'+champion.key+'.png';
    var imgTier = 'img/tier/'+p.highestAchievedSeasonTier.toLowerCase()+'.png';
    var items = p.itemBuildOrder ? p.itemBuildOrder:[];
    return <Grid container>
        <Grid item xs={4}>
            <img src={imgTier} style={{width: '32px', float: 'left'}}/>
            <Avatar src={imgChampion} style={{float: 'left'}}/>&nbsp;



            {Object.keys(items).map(key => (
                <Item key={key} itemId={items[key]}/>
            ))}
        </Grid>
        <Grid item xs={1}>
            <img src={'http://ddragon.leagueoflegends.com/cdn/5.5.1/img/ui/score.png'}/>
            {p.kills}/{p.deaths}/{p.assists}
        </Grid>
        <Grid item xs={1}>{p.identity.summonerName}</Grid>
        <Grid item xs={1}>{p.enemyTeam.damageType}</Grid>
        <Grid item xs={1}>{p.win ? 'WIN':'LOSE'}</Grid>
    </Grid>
    // {purchaseEvents.map((event, index) => {
    //     const showArrow = prevTime != null && (prevTime + 10000)<event['timestamp'];
    //     prevTime = event['timestamp'];
    //     return (
    //         <span key={index}>
    //                     {showArrow && <i className="fas fa-2x fa-angle-double-right" style={{float: 'left'}}></i>}
    //             <Item itemId={event.itemId} items={this.props.items}/>
    //                 </span>
    //     )
    // })}
}

export default connect(mapStateToProps)(Participant)