import React from 'react';
import { IntlMixin, FormattedMessage } from 'react-intl';

const ProjectPreviewCounter = React.createClass({
  propTypes: {
    value: React.PropTypes.number.isRequired,
    label: React.PropTypes.string.isRequired,
    style: React.PropTypes.object,
    showZero: React.PropTypes.bool,
  },
  mixins: [IntlMixin],

  getDefaultProps() {
    return {
      style: {},
      showZero: false,
    };
  },

  render() {
    const { value, label, style } = this.props;
    if (value > 0 || this.props.showZero) {
      return (
        <div className="thumbnail__number-block" style={style}>
            <div className="thumbnail__number">
              {value}
            </div>
            <div className="thumbnail__number__label excerpt small">
              <FormattedMessage
                message={this.getIntlMessage(label)}
                num={value}
              />
          </div>
        </div>
      );
    }
    return null;
  },

});

export default ProjectPreviewCounter;
