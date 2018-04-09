import React, { Component } from 'react';
import ReactDOM from 'react-dom';
// import MuiThemeProvider from 'material-ui/styles/MuiThemeProvider';
import Matches from './Matches';
import Participants from './Participants';

import CssBaseline from 'material-ui/CssBaseline';

/* An example React component */
class Main extends Component {
    render() {
        return (
            <div>
                <CssBaseline />
                <h3>Ms</h3>
                <Participants/>
            </div>
        );
    }
}

/* The if statement is required so as to Render the component on pages that have a div with an ID of "root";
*/

if (document.getElementById('root')) {
    ReactDOM.render(<Main />, document.getElementById('root'));
}