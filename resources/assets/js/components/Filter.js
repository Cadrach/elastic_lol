import React, { Component } from 'react'
import { connect } from 'react-redux'
import { Input } from 'semantic-ui-react'
import {filterMapDispatchToProps, filterMapStateToProps} from "../reducers/filters";


const Filter = ({field, onChange, value}) => {
    return <Input type="text" placeholder={field} onChange={onChange} value={value}/>;
}

export default connect(filterMapStateToProps, filterMapDispatchToProps)(Filter);