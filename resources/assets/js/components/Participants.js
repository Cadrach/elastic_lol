import React, { Component } from 'react';
import ReactDOM from 'react-dom';
import { connect, PromiseState } from 'react-refetch'
import Participant from './Participant'

import List, {ListItem} from 'material-ui/List';

/* An example React component */
class Participants extends Component {
    // constructor(props) {
    //     super(props);
    //     this.state = {
    //         error: null,
    //         isLoaded: false,
    //         matches: []
    //     };
    // }

    // componentDidMount() {
    //     fetch("")
    //         .then(res => res.json())
    //         .then((result) => {
    //
    //             console.log(result);
    //             this.setState({
    //             isLoaded: true,
    //             matches: result.hits.hits
    //         });
    // },
    //     // Note: it's important to handle errors here
    //     // instead of a catch() block so that we don't swallow
    //     // exceptions from actual bugs in components.
    //     (error) => {
    //         this.setState({
    //             isLoaded: true,
    //             error
    //         });
    //     }
    // )
    // }

    render() {
        const { participants } = this.props

        if (participants.fulfilled) {
            return <div>

                <List>
                {participants.value.hits.hits.map((participant,index) => {
                    return <ListItem button key={index}>
                        <Participant participant={participant._source}/>
                    </ListItem>
                })}
                </List>
            </div>
        } else {
            return <i>WHAT?</i>
        }
    }
}


export default connect(props => ({
    participants: 'api/match/participants'
}))(Participants)