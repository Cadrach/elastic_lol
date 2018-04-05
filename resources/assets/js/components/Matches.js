import React, { Component } from 'react';
import ReactDOM from 'react-dom';
import { connect, PromiseState } from 'react-refetch'
import Match from './Match'

/* An example React component */
class Matches extends Component {
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
        const { items, matches, champions } = this.props

        if (items.fulfilled && matches.fulfilled && champions.fulfilled) {
            return <div>
                {matches.value.hits.hits.map(item => (
                    <Match match={item._source} key={item._id} items={items.value} champions={champions.value}/>
                ))}
            </div>
        } else {
            return <i>WHAT?</i>
        }
    }
}


export default connect(props => ({
    items: 'json/items.json',
    champions: 'json/champions.json',
    matches: 'api/match/search'
}))(Matches)