import React, { Component } from 'react';
import { connect } from 'react-redux'
import _ from 'lodash'


import { Grid, Image } from 'semantic-ui-react'
import Item from './Item'

// import { withStyles } from 'material-ui/styles';

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
    var imgChampion = dictionnaries.urls.champion+champion.key+'.png';
    var imgTier = 'images/tier/'+p.highestAchievedSeasonTier.toLowerCase()+'.png';
    var imgPerk0 = 'images/perk/' + p.perk0 + '.png';
    var imgSum1 = dictionnaries.urls.summonerSpell + dictionnaries.summonerSpells[Math.min(p.spell1Id, p.spell2Id)].key + '.png';
    var imgSum2 = dictionnaries.urls.summonerSpell + dictionnaries.summonerSpells[Math.max(p.spell1Id, p.spell2Id)].key + '.png';
    var items = p.itemBuildOrder ? p.itemBuildOrder:[];
    return <Grid>
        <Grid.Column width={6}>
            <Image src={imgPerk0} size="mini" floated="left"/>
            <Image src={imgTier} size="mini" floated="left"/>
            <Image src={imgChampion} size="mini" floated="left"/>
            <Image src={imgSum1} size="mini" floated="left"/>
            <Image src={imgSum2} size="mini" floated="left"/>

            &nbsp;

            {Object.keys(items).map(key => (
                <Item key={key} size="mini" itemId={items[key]}/>
            ))}
        </Grid.Column>
        <Grid.Column width={2}>
            <Image src={'http://ddragon.leagueoflegends.com/cdn/5.5.1/img/ui/score.png'} size="mini" floated="left"/>
            {p.kills}/{p.deaths}/{p.assists}
        </Grid.Column>
        <Grid.Column width={1}>{p.identity.summonerName}</Grid.Column>
        <Grid.Column width={1}>{p.enemyTeam.damageType}</Grid.Column>
        <Grid.Column width={1}>{p.win ? 'WIN':'LOSE'}</Grid.Column>
        <Grid.Column width={1}>{p.patchVersion}</Grid.Column>
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