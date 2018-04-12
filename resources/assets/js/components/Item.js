import React, { Component } from 'react';
import { connect } from 'react-redux'
import _ from 'lodash'

import { withStyles } from 'material-ui/styles';
import classNames from 'classnames';
import Avatar from 'material-ui/Avatar';

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
    var img = 'http://ddragon.leagueoflegends.com/cdn/'+version+'/img/item/'+itemId+'.png';

    if(itemId){
        return <Avatar className={classes.avatar} src={img} style={{float: 'left'}}/>
    }
    else{
        return <Avatar className={classNames(classes.avatar, classes.emptyAvatar)} size={32} style={{float: 'left'}}/>
    }
}

export default connect(mapStateToProps)(withStyles(styles)(Item));