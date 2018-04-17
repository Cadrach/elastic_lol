import React, { Component } from 'react'
import { Icon, Image, Dropdown } from 'semantic-ui-react'
import { connect } from 'react-redux'
import {filterMapStateToProps, filterMapDispatchToProps} from "../reducers/filters";
import _ from 'lodash'


const FilterTier = ({field, onChange, value, dictionnaries}) => {
    //If no dictionnaries, exit
    if( ! dictionnaries) return null;

    var options = _.map(['Bronze', 'Silver', 'Gold', 'Platinum', 'Diamond', 'Challenger', 'Master'], function(v){
        var src = 'images/tier/' + v.toLowerCase() + '.png';
        return {
            name: v,
            value: v.toUpperCase(),
            text: <span>
                <Image size={'mini'} verticalAlign='middle' src={src}/>
                <span className={'filter-item-title'}>{v}</span>
                </span>
        }
    })

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

export default connect(filterMapStateToProps, filterMapDispatchToProps)(FilterTier);