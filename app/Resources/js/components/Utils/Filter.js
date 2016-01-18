import React from 'react';
import {IntlMixin} from 'react-intl';

const Filter = React.createClass({
  propTypes: {
    value: React.PropTypes.any.isRequired,
    values: React.PropTypes.array,
    onChange: React.PropTypes.func.isRequired,
  },
  mixins: [IntlMixin],

  getDefaultProps() {
    return {
      values: ['popular', 'last', 'old'],
    };
  },

  render() {
    return (
      <select
        className="form-control pull-right"
        value={this.props.value}
        onChange={this.props.onChange}
      >
        {
          this.props.values.map((value, index) => {
            return (
              <option value={value} key={index}>
                {this.getIntlMessage('global.filter_' + value)}
              </option>
            );
          })
        }
      </select>
    );
  },

});

export default Filter;
