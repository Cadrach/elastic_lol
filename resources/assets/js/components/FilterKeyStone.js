import React, { Component } from 'react'
import { Icon, Image, Dropdown } from 'semantic-ui-react'
import { connect } from 'react-redux'
import {filterMapStateToProps, filterMapDispatchToProps} from "../reducers/filters";
import _ from 'lodash'


const FilterKeyStone = ({field, onChange, value, dictionnaries}) => {
    //If no dictionnaries, exit
    if( ! dictionnaries) return null;

    //
    var options = _.chain(dictionnaries.runePaths).map(function(v){return v.slots[0].runes}).flatten().map(function(v){
        console.log(v);
        var src = 'images/perk/' + v.id + '.png';
        return {
            name: v.name,
            value: v.id,
            text: <span>
                <Image size={'mini'} verticalAlign='middle' src={src}/>
                <span className={'filter-item-title'}>{v.name}</span>
                </span>
        }
    })
        // .sortBy('name')
        .value();

    options.unshift({
        name: '',
        value: '',
        text: <Icon name={'cube'}/>
    })

    //Custom search
    var search = function(list, value){
        return _.filter(list, function(v){return v.name.match(new RegExp(value, 'i'))!==null});
    }

    var placeholder = <Icon name={'cube'}/>

    return <Dropdown className={'filter-item'} placeholder={placeholder} compact search={search} inline selectOnBlur={false}  options={options} onChange={onChange}/>
}

export default connect(filterMapStateToProps, filterMapDispatchToProps)(FilterKeyStone);