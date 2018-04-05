import React, { Component } from 'react';
import ReactDOM from 'react-dom';
// import MuiThemeProvider from 'material-ui/styles/MuiThemeProvider';
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

/* The if statement is required so as to Render the component on pages that have a div with an ID of "root";
*/

if (document.getElementById('root')) {
    ReactDOM.render(<Main />, document.getElementById('root'));
}