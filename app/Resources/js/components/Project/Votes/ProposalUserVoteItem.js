// @flow
import React, { PropTypes } from 'react';
import { IntlMixin } from 'react-intl';
import { connect } from 'react-redux';
import { Button, Row, Col } from 'react-bootstrap';
import UserLink from '../../User/UserLink';
import { VOTE_TYPE_BUDGET } from '../../../constants/ProposalConstants';
import ProposalDetailsEstimation from '../../Proposal/Detail/ProposalDetailEstimation';
import { deleteVote } from '../../../redux/modules/proposal';

export const ProposalUserVoteItem = React.createClass({
  propTypes: {
    dispatch: PropTypes.func.isRequired,
    proposal: PropTypes.object.isRequired,
    step: PropTypes.object.isRequired,
  },
  mixins: [IntlMixin],

  render() {
    const {
      step,
      proposal,
      dispatch,
    } = this.props;

    const colWidth = proposal.district ? 2 : 3;

    return (
      <Row className="proposals-user-votes__row" id={`vote-step${step.id}-proposal${proposal.id}`}>
        <Col md={colWidth + 1} xs={12} className={{ 'center-block': true }}><a href={proposal._links.show}>{proposal.title}</a></Col>
        <Col md={colWidth + 1} xs={12} className={{ 'center-block': true }}><i className="cap cap-user-2"></i><UserLink user={proposal.author} /></Col>
        {
          proposal.district &&
          <Col md={colWidth} xs={12}><i className="cap cap-marker-1"></i>{proposal.district.name}</Col>
        }
        <Col md={colWidth} xs={12} className={{ 'center-block': true }}>
          <ProposalDetailsEstimation
            proposal={proposal}
            showNullEstimation={step.voteType === VOTE_TYPE_BUDGET}
          />
        </Col>
        <Col md={colWidth} xs={12} className={{ 'center-block': true }}>
          <Button
            onClick={() => {
              deleteVote(dispatch, step, proposal);
            }}
            className="proposal-vote__delete"
            disabled={!step.open}
          >
            {this.getIntlMessage('project.votes.delete')}
          </Button>
        </Col>
      </Row>
    );
  },

});

export default connect()(ProposalUserVoteItem);
