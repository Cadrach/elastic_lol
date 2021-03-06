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
const Item = ({dictionnaries, itemId, size, classes}) => {

    var item = _.find(dictionnaries.items, {id: itemId});
    var version = dictionnaries.version;
    var img = dictionnaries.urls.item+itemId+'.png';
    var s = size ? size:'mini';

    if(itemId){
        return <Image src={img} size={s} style={{float:'left'}}/>
    }
    else{
        return <Image size={s} style={{float:'left'}}/>
    }
}

export default connect(mapStateToProps)(Item);