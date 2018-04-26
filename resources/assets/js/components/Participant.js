import React, { Component } from 'react';
import { connect } from 'react-redux'
import _ from 'lodash'


import { Grid, Image, Step } from 'semantic-ui-react'
import Item from './Item'
import Champion from './Champion'

// import { withStyles } from 'material-ui/styles';

const mapStateToProps = state => {
    return {
        dictionnaries: state.dictionnaries
    }
}

const Participant = ({dictionnaries, participant}) => {
    //Must wait for dictionnaries
    if( ! dictionnaries.version){return null}

    var p = participant;
    var imgTier = 'images/tier/'+p.highestAchievedSeasonTier.toLowerCase()+'.png';
    var imgPerk0 = 'images/perk/' + p.perk0 + '.png';
    var imgSum1 = dictionnaries.urls.summonerSpell + dictionnaries.summonerSpells[Math.min(p.spell1Id, p.spell2Id)].key + '.png';
    var imgSum2 = dictionnaries.urls.summonerSpell + dictionnaries.summonerSpells[Math.max(p.spell1Id, p.spell2Id)].key + '.png';
    var items = p.itemBuildOrder ? p.itemBuildOrder:[];

    //Create purchases
    var purchases = [];
    var currentPurchase = null;
    var currentTime = null;
    _.forEach(participant.events.ITEM_PURCHASED, function(event){
        if(currentPurchase === null || (event.timestamp - currentTime)>10000){
            //Create a new step
            currentPurchase = {timestamp: event.timestamp, items: [event.itemId]};
            purchases.push(currentPurchase);
        }
        else{
            //Add item to current step
            currentPurchase.items.push(event.itemId);
        }

        //Update current evaluated time
        currentTime = currentPurchase.timestamp;
    });

    console.log(purchases);

    return <Grid>
        <Grid.Column width={8}>
            <Image src={imgPerk0} size="mini" floated="left"/>
            <Image src={imgTier} size="mini" floated="left"/>
            <Champion champId={p.championId} size="mini"/>
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
        <Grid.Column width={1}>
            {p.identity.summonerName}<br/>
            {p.timeline.lane} / {p.timeline.role}
        </Grid.Column>
        <Grid.Column width={1}>
            Vs {p.enemyTeam.damageType}<br/>

            {p.playVersus.map((champId, key) => (
                <Champion key={key} champId={champId} size="pico"/>
            ))}
        </Grid.Column>
        <Grid.Column width={1}>{p.win ? 'WIN':'LOSE'}</Grid.Column>
        <Grid.Column width={1}>{p.patchVersion}</Grid.Column>
        <Grid.Column width={16}>
            <Step.Group>
                {purchases.map((purchase, key) => (
                    <Step key={key}>
                        <Step.Description>
                            {purchase.items.map((itemId, key) => (
                                <Item key={key} size="pico" itemId={itemId}/>
                            ))}
                        </Step.Description>
                    </Step>
                ))}
            </Step.Group>
        </Grid.Column>
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