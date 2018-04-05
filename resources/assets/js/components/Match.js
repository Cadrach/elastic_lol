import React, { Component } from 'react';
import ReactDOM from 'react-dom';
import Participant from './Participant'
import _ from 'lodash'

import { withStyles } from 'material-ui/styles';
import List, {ListItem} from 'material-ui/List';
import Card, { CardActions, CardContent } from 'material-ui/Card';
import Grid from 'material-ui/Grid';

const styles = {
    card: {
        margin: '20px'
    },
};

class Match extends Component {
    render() {
        const { classes } = this.props;

        var teams = [
            _.filter(this.props.match.participants, {teamId: 100}),
            _.filter(this.props.match.participants, {teamId: 200})
        ];
        return <Card className={classes.card}>
            <CardContent>
                <Grid container>
                    {teams.map((team, index) => (
                        <Grid item xs={6} key={index}>
                            <List>
                            {team.map(participant => (
                                <ListItem button key={participant.participantId}>
                                    <Participant participant={participant} items={this.props.items} champions={this.props.champions}/>
                                </ListItem>
                            ))}
                            </List>
                        </Grid>
                    ))}
                </Grid>
            </CardContent>
        </Card>
    }
}

export default withStyles(styles)(Match);