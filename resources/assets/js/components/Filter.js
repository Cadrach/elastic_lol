import React, { Component } from 'react';
import { connect } from 'react-redux'
import { withStyles } from 'material-ui/styles';
import { addFilter} from '../actions'

import List, {ListItem} from 'material-ui/List';
import Card, { CardActions, CardContent } from 'material-ui/Card';
import Grid from 'material-ui/Grid';

const styles = {

};

const mapStateToProps = (state, ownProps) => {
    return {
        active: ownProps.filter === state.visibilityFilter
    }
}

const mapDispatchToProps = (dispatch, ownProps) => {
    return {
        onChange: (event) => {
            dispatch(addFilter(ownProps.field, event.target.value))
        }
    }
}

const Filter = ({field, onChange}) => {
    console.log(field)
    return <input type="text" placeholder={field} onChange={onChange}/>;
}

export default connect(mapStateToProps, mapDispatchToProps)(withStyles(styles)(Filter));