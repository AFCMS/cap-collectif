import React, { PropTypes } from 'react';
import { Label } from 'react-bootstrap';
import { IntlMixin } from 'react-intl';

const ProposalDetailAdvancementStep = React.createClass({
  displayName: 'ProposalDetailAdvancementStep',
  propTypes: {
    step: PropTypes.object.isRequired,
    roundColor: PropTypes.string.isRequired,
    status: PropTypes.object,
    borderColor: PropTypes.string,
    children: PropTypes.node,
  },
  mixins: [IntlMixin],

  renderDate() {
    const { step } = this.props;

    if (step.timeless && !step.endAt && !step.startAt) {
      return this.getIntlMessage('proposal.detail.intervals.continuously');
    }

    if (!step.endAt) {
      return step.startAt;
    }
    return `${step.startAt} - ${step.endAt}`;
  },

  render() {
    const { borderColor, roundColor, step, status, children } = this.props;
    return (
        <span>
        <div
          style={
            borderColor
            ? {
              paddingTop: '10px',
              paddingBottom: '10px',
              borderLeftStyle: 'solid',
              borderLeftColor: borderColor,
              borderLeftWidth: '3px',
              paddingLeft: '10px',
            }
            : {
              paddingTop: '10px',
              paddingLeft: '13px',
            }
          }
        >
          <div
            style={{
              float: 'left',
              width: '12px',
              height: '12px',
              marginTop: '-10px',
              marginLeft: '-18px',
              lineHeight: '28px',
              color: '#767676',
              textAlign: 'center',
              backgroundColor: roundColor,
              borderRadius: '50%',
            }}
          />
          <div style={{ marginTop: '-15px' }}>
            <div>{ step.title }</div>
            <div className="excerpt small">
                <span>{ this.renderDate() }</span>
            </div>
            {
              status &&
                <Label bsStyle={status.color} style={{ marginTop: '5px' }}>
                  {
                    status.name.length > 25 ? `${status.name.substr(0, 25)}...` : status.name
                  }
                </Label>
            }
          </div>
          <br />
        </div>
          { children }
        </span>
      );
  },

});

export default ProposalDetailAdvancementStep;
