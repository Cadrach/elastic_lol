import React, { Component } from 'react';
import { connect } from 'react-redux'
import _ from 'lodash'

import classNames from 'classnames';
import { Image } from 'semantic-ui-react'

const mapStateToProps = state => {
    return {
        dictionnaries: state.dictionnaries
    }
}

// class Item extends Component {
const Champion = ({dictionnaries, champId, size, classes}) => {

    var champion = _.find(dictionnaries.champions, {id: champId});
    var img = dictionnaries.urls.champion+champion.key+'.png';
    var s = size ? size:'mini';

    if(champId){
        return <Image src={img} size={s} style={{float:'left'}}/>
    }
    else{
        return <Image size={s} style={{float:'left'}}/>
    }
}

export default connect(mapStateToProps)(Champion);