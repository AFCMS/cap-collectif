// @flow
import * as React from 'react';
import { FormattedMessage } from 'react-intl';
import { ProgressBar } from 'react-bootstrap';
import { graphql, createFragmentContainer } from 'react-relay';
import type { ProposalVoteThresholdProgressBar_proposal } from '~relay/ProposalVoteThresholdProgressBar_proposal.graphql';
import type { ProposalVoteThresholdProgressBar_step } from '~relay/ProposalVoteThresholdProgressBar_step.graphql';
import { isInterpellationContextFromProposal } from '~/utils/interpellationLabelHelper';

type Props = {
  proposal: ProposalVoteThresholdProgressBar_proposal,
  step: ProposalVoteThresholdProgressBar_step,
};

export class ProposalVoteThresholdProgressBar extends React.Component<Props> {
  render() {
    const { proposal, step } = this.props;
    const votesCount = proposal.votes.totalCount;
    const { voteThreshold } = step;
    if (voteThreshold === null || typeof voteThreshold === 'undefined') {
      return null;
    }
    const voteCountLabel = isInterpellationContextFromProposal(proposal)
      ? 'support.count'
      : 'vote.count';

    return (
      <div className="card__threshold" style={{ fontSize: '85%', marginTop: '15px' }}>
        <p>
          <i className="cap cap-hand-like-2-1" />{' '}
          {votesCount >= voteThreshold && <FormattedMessage id="proposal.vote.threshold.reached" />}
          {votesCount < voteThreshold && (
            <FormattedMessage
              id={voteCountLabel}
              values={{
                count: votesCount,
              }}
            />
          )}
        </p>
        <ProgressBar
          min={0}
          max={votesCount >= voteThreshold ? votesCount : voteThreshold}
          now={votesCount}
          className="mb-0"
          bsStyle="success"
          label={
            votesCount >= voteThreshold ? (
              <FormattedMessage id="proposal.vote.threshold.reached" />
            ) : (
              ''
            )
          }
        />
      </div>
    );
  }
}

export default createFragmentContainer(ProposalVoteThresholdProgressBar, {
  proposal: graphql`
    fragment ProposalVoteThresholdProgressBar_proposal on Proposal {
      id
      votes(stepId: $stepId, first: 0) {
        totalCount
      }
      ...interpellationLabelHelper_proposal @relay(mask: false)
    }
  `,
  step: graphql`
    fragment ProposalVoteThresholdProgressBar_step on Step {
      id
      ... on CollectStep {
        voteThreshold
      }
      ... on SelectionStep {
        voteThreshold
      }
    }
  `,
});
