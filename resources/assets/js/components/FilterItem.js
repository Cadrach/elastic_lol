import React, { Component } from 'react'
import { Image, Dropdown } from 'semantic-ui-react'
import { connect } from 'react-redux'
import {filterMapStateToProps, filterMapDispatchToProps} from "../reducers/filters";
import _ from 'lodash'


const FilterItem = ({field, onChange, value, dictionnaries}) => {
    // var search = function(){
    //     return _.
    // }
    if( ! dictionnaries) return null;
    var options = _.chain(dictionnaries.items).map(function(v){
        if(v.from && !v.into && v.gold.purchasable){
            return {
                name: v.name,
                value: v.id,
                text: <span><Image size={'mini'} verticalAlign='middle' src={dictionnaries.urls.item+v.image.full}/>&nbsp;{v.name}</span>
            }
        }
    })
        .filter()
        .sortBy('name')
        .value()

    return <Dropdown className={'filter-item'} placeholder='Item' search selectOnBlur={false} selection options={options} onChange={onChange}/>
}

export default connect(filterMapStateToProps, filterMapDispatchToProps)(FilterItem);