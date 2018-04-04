import React, { Component } from 'react';
import ReactDOM from 'react-dom';
import Matches from './Matches';

/* An example React component */
class Main extends Component {
    render() {
        return (
            <div>
                <h3>Ms</h3>
                <Matches/>
            </div>
        );
    }
}

export default Main;

/* The if statement is required so as to Render the component on pages that have a div with an ID of "root";
*/

if (document.getElementById('root')) {
    ReactDOM.render(<Main />, document.getElementById('root'));
}