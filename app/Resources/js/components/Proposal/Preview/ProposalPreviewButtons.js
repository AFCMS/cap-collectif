import ProposalVoteButton from '../Vote/ProposalVoteButton';
import ProposalDetailsButton from './ProposalDetailsButton';
import React from 'react';
import {Row, Col} from 'react-bootstrap';
import {IntlMixin} from 'react-intl';

const ProposalPreviewButtons = React.createClass({
  propTypes: {
    proposal: React.PropTypes.object.isRequired,
    selectionStepId: React.PropTypes.number,
    creditsLeft: React.PropTypes.number,
  },
  mixins: [IntlMixin],

  getDefaultProps() {
    return {
      selectionStepId: null,
      creditsLeft: null,
    };
  },

  render() {
    return (
      <Row className="proposal__buttons text-center" >
        <Col xs={6} sm={12} md={12} lg={12}>
          <ProposalVoteButton {...this.props} />
        </Col>
        <Col xs={6} sm={12} md={12} lg={12}>
          <ProposalDetailsButton proposal={this.props.proposal} />
        </Col>
      </Row>
    );
  },

});

export default ProposalPreviewButtons;
