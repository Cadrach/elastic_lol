import React, { Component } from 'react'
import { Image, Dropdown } from 'semantic-ui-react'
import { connect } from 'react-redux'
import {filterMapStateToProps, filterMapDispatchToProps} from "../reducers/filters";
import _ from 'lodash'


const FilterChampion = ({field, onChange, value, dictionnaries}) => {
    if( ! dictionnaries) return null;
    var options = _.chain(dictionnaries.champions).map(function(v){
        return {
            name: v.name,
            value: v.id,
            text: <span><Image size="mini" verticalAlign='middle' src={dictionnaries.urls.champion + v.key + '.png'}/>&nbsp;{v.name}</span>
        }
    })
        .sortBy('name')
        .value()

    return <Dropdown placeholder='Champion' search selection options={options} onChange={onChange}/>
}

export default connect(filterMapStateToProps, filterMapDispatchToProps)(FilterChampion);