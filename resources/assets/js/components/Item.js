import React, { Component } from 'react';
import ReactDOM from 'react-dom';
import Participant from './Participant'
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

class Item extends Component {
    render() {
        const { classes } = this.props;

        var itemId = this.props.itemId;
        var item = _.find(this.props.items.data, {id: itemId});
        var version = this.props.items.version;
        var img = 'http://ddragon.leagueoflegends.com/cdn/'+version+'/img/item/'+itemId+'.png';

        if(itemId){
            return <Avatar className={classes.avatar} src={img} style={{float: 'left'}}/>
        }
        else{
            return <Avatar className={classNames(classes.avatar, classes.emptyAvatar)} size={32} style={{float: 'left'}}/>
        }
    }
}

export default withStyles(styles)(Item);