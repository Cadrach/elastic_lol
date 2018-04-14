import React, { Component } from 'react'
import { Input, Dropdown } from 'semantic-ui-react'
import { connect } from 'react-redux'
import {filterMapDispatchToProps, filterMapStateToProps} from "../reducers/filters";
import _ from 'lodash'

export const mapStateToProps = (state, ownProps) => {
    var result = filterMapStateToProps(state, ownProps);

    return Object.assign(result, {
        dictionnaries: state.dictionnaries
    })
}


const FilterChampion = ({field, onChange, value, dictionnaries}) => {
    var options = _.chain(dictionnaries.champions).map(function(v){
        return {
            value: v.id,
            icon: {as: 'img', src: dictionnaries.urls.champion + v.key + '.png'},
            text: v.name
        }
    })
        .sortBy('text')
        .value()

    return <Dropdown placeholder='Champion' search selection options={options} onChange={onChange}/>
}

export default connect(mapStateToProps, filterMapDispatchToProps)(FilterChampion);