import React, { Component } from 'react'
import { connect } from 'react-redux'
import { addFilter} from '../actions'
import { Input } from 'semantic-ui-react'


const mapStateToProps = (state, ownProps) => {
    return {
        value: state.filters[ownProps.field]
    }
}

const mapDispatchToProps = (dispatch, ownProps) => {
    return {
        onChange: (event) => {
            dispatch(addFilter(ownProps.field, event.target.value))
        }
    }
}

const Filter = ({field, onChange, value}) => {
    console.log(field, value)
    return <Input type="text" placeholder={field} onChange={onChange} value={value}/>;
}

export default connect(mapStateToProps, mapDispatchToProps)(Filter);