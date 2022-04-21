import React from 'react';
import {IntlMixin, FormattedMessage} from 'react-intl';

import DatesInterval from './../Utils/DatesInterval';

const StepPreview = React.createClass({
  propTypes: {
    step: React.PropTypes.object.isRequired,
    className: React.PropTypes.string,
  },
  mixins: [IntlMixin],

  getDefaultProps() {
    return {
      className: '',
    };
  },

  render() {
    const step = this.props.step;
    return (
      <li className={this.props.className}>
        <div>
          <div className="navbar__step-nb">{step.position}</div>
          <div className="navbar__step">
            <h3 className="navbar__step-title">
              {step.title}
            </h3>
            <p className="excerpt small">
              <DatesInterval startAt={step.startAt} endAt={step.endAt} />
            </p>
            {
              step.openingStatus
              ? <p className="label label-default">
                {this.getIntlMessage('step.status.' + step.openingStatus)}
              </p>
              : null
            }
            {
              step.votesCount === null
              ? null
              : <p style={{marginTop: '5px'}}>
                  <span className="nb--highlighted">
                    {step.votesCount}
                  </span>
                  <span className="excerpt">
                    {' '}
                    <FormattedMessage
                      message={this.getIntlMessage('vote.count_no_nb')}
                      count={step.votesCount}
                    />
                  </span>
              </p>
            }
          </div>
        </div>
      </li>
    );
  },

});

export default StepPreview;
