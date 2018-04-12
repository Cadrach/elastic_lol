import React, { Component } from 'react';
import ReactDOM from 'react-dom';
import { Provider } from 'react-redux'
import { store } from '../store'

// import MuiThemeProvider from 'material-ui/styles/MuiThemeProvider';
import Participants from './Participants';
import LoadDictionnaries from '../containers/loadDictionnaries'

import CssBaseline from 'material-ui/CssBaseline';

/* An example React component */
class Main extends Component {
    render() {
        return (
            <Provider store={store}>
                <div>
                    <LoadDictionnaries/>
                    <CssBaseline />
                    <h3>Ms</h3>
                    <Participants/>
                </div>
            </Provider>
        );
    }
}

/* The if statement is required so as to Render the component on pages that have a div with an ID of "root";
*/

if (document.getElementById('root')) {
    ReactDOM.render(<Main />, document.getElementById('root'));
}