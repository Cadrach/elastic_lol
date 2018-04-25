import React, { Component } from 'react'
import { Icon, Image, Dropdown } from 'semantic-ui-react'
import { connect } from 'react-redux'
import {filterMapStateToProps, filterMapDispatchToProps} from "../reducers/filters";
import _ from 'lodash'


const FilterItem = ({field, onChange, value, dictionnaries}) => {
    //If no dictionnaries, exit
    if( ! dictionnaries) return null;

    //
    var options = _.chain(dictionnaries.items).map(function(v){
        if(v.depth>=3 && v.gold.purchasable){
            return {
                name: v.name,
                value: v.id,
                text: <span>
                    <Image size={'mini'} verticalAlign='middle' src={dictionnaries.urls.item+v.image.full}/>
                    <span className={'filter-item-title'}>{v.name}</span>
                    </span>
            }
        }
    })
        .filter()
        .sortBy('name')
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

export default connect(filterMapStateToProps, filterMapDispatchToProps)(FilterItem);