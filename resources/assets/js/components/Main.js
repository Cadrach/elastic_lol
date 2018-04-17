import React, { Component } from 'react';
import ReactDOM from 'react-dom';
import { Provider } from 'react-redux'
import { store } from '../store'

import Participants from './Participants';
import FilterKeyStone from './FilterKeyStone';
import FilterItem from './FilterItem';
import FilterChampion from './FilterChampion';
import LoadDictionnaries from '../containers/loadDictionnaries'
import LoadParticipants from '../containers/loadParticipants'

// import CssBaseline from 'material-ui/CssBaseline';

/* An example React component */
class Main extends Component {
    render() {
        return (
            <Provider store={store}>
                <div>
                    <FilterKeyStone field={'perk0'}/>
                    <FilterChampion field={'championId'}/>
                    <FilterItem field={'itemBuildOrder.item0'}/>
                    <FilterItem field={'itemBuildOrder.item1'}/>
                    <FilterItem field={'itemBuildOrder.item2'}/>
                    <FilterItem field={'itemBuildOrder.item3'}/>
                    <FilterItem field={'itemBuildOrder.item4'}/>
                    <FilterItem field={'itemBuildOrder.item5'}/>
                    {/*<CssBaseline />*/}
                    <LoadDictionnaries/>
                    <LoadParticipants/>
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