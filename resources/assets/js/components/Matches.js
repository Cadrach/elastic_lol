import React, { Component } from 'react';
import ReactDOM from 'react-dom';
import Match from './Match'

/* An example React component */
class Matches extends Component {
    constructor(props) {
        super(props);
        this.state = {
            error: null,
            isLoaded: false,
            matches: []
        };
    }

    componentDidMount() {
        fetch("api/match/search")
            .then(res => res.json())
    .then(
            (result) => {

            console.log(result);
            this.setState({
            isLoaded: true,
            matches: result.hits.hits
        });
    },
        // Note: it's important to handle errors here
        // instead of a catch() block so that we don't swallow
        // exceptions from actual bugs in components.
        (error) => {
            this.setState({
                isLoaded: true,
                error
            });
        }
    )
    }

    render() {
        const { error, isLoaded, matches } = this.state;
        if (error) {
            return <div>Error: {error.message}</div>;
        } else if (!isLoaded) {
            return <div>Loading...</div>;
        } else {
            return (
                <div>
                    {matches.map(item => (
                    <Match match={item} key={item._id}/>
                    ))}
                </div>
        );
        }
    }
}

export default Matches;