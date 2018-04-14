import React, { Component } from 'react';
import { connect } from 'react-redux'
import _ from 'lodash'

import classNames from 'classnames';
import { Image } from 'semantic-ui-react'

const styles = {
    avatar: {
        width: '32px',
        height: '32px',
        margin: '5px',
    },
    emptyAvatar: {
        backgroundColor: '#000'
    },
};

const mapStateToProps = state => {
    return {
        dictionnaries: state.dictionnaries
    }
}

// class Item extends Component {
const Item = ({dictionnaries, itemId, classes}) => {

    var item = _.find(dictionnaries.items, {id: itemId});
    var version = dictionnaries.version;
    var img = dictionnaries.urls.item+itemId+'.png';

    if(itemId){
        return <Image src={img} size="mini" floated="left"/>
    }
    else{
        return <Image size="mini" floated="left"/>
    }
}

export default connect(mapStateToProps)(Item);