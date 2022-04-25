// @flow
import * as React from 'react';
import { graphql, createFragmentContainer } from 'react-relay';
import { FormattedMessage } from 'react-intl';
import { ProgressBar } from 'react-bootstrap';
import type { ProposalPageVoteThreshold_step } from '~relay/ProposalPageVoteThreshold_step.graphql';
import type { ProposalPageVoteThreshold_proposal } from '~relay/ProposalPageVoteThreshold_proposal.graphql';
import { isInterpellationContextFromProposal } from '~/utils/interpellationLabelHelper';

type Props = {
  proposal: ProposalPageVoteThreshold_proposal,
  step: ProposalPageVoteThreshold_step,
};

export class ProposalPageVoteThreshold extends React.Component<Props> {
  render() {
    const { step, proposal } = this.props;
    // We should use a new query render to fetch votes only from the step
    const votesCount = proposal.votes.totalCount;
    const { voteThreshold } = step;
    if (voteThreshold === null || typeof voteThreshold === 'undefined') {
      return null;
    }
    const votesRemaining = voteThreshold - votesCount;
    const votesPercentage = Math.ceil((votesCount * 100) / voteThreshold);
    const isInterpellation = isInterpellationContextFromProposal(proposal);
    return (
      <div className="proposal__page__vote_threshold">
        <div className="proposal__infos" style={{ marginTop: '-15px' }}>
          <h4>
            {votesPercentage >= 100 ? (
              <FormattedMessage
                id={
                  isInterpellation
                    ? 'interpellation.vote.threshold.reached'
                    : 'proposal.vote.threshold.reached'
                }
              />
            ) : (
              <FormattedMessage
                id={
                  isInterpellation
                    ? 'interpellation.support.threshold.title'
                    : 'proposal.vote.threshold.title'
                }
              />
            )}
          </h4>
          <p className="proposal__page__vote_threshold__votes">
            <i className="cap cap-hand-like-2-1" />{' '}
            <FormattedMessage
              id={isInterpellation ? 'interpellation.support.count' : 'proposal.vote.count'}
              values={{
                num: votesCount,
              }}
            />
          </p>
          <ProgressBar
            now={votesPercentage}
            label={`${votesPercentage}%`}
            min={0}
            max={votesPercentage > 100 ? votesPercentage : 100}
            bsStyle="success"
          />
          <div>
            {votesPercentage >= 100 && (
              <FormattedMessage
                id={
                  isInterpellation
                    ? 'interpellation.support.threshold.progress_reached'
                    : 'proposal.vote.threshold.progress_reached'
                }
                values={{
                  num: votesCount,
                  max: voteThreshold,
                }}
              />
            )}
            {votesPercentage < 100 && (
              <FormattedMessage
                id={
                  isInterpellation
                    ? 'interpellation.support.threshold.progress'
                    : 'proposal.vote.threshold.progress'
                }
                values={{
                  num: votesRemaining,
                  max: voteThreshold,
                }}
              />
            )}
          </div>
        </div>
      </div>
    );
  }
}

export default createFragmentContainer(ProposalPageVoteThreshold, {
  proposal: graphql`
    fragment ProposalPageVoteThreshold_proposal on Proposal {
      id
      votes {
        totalCount
      }
      ...interpellationLabelHelper_proposal @relay(mask: false)
    }
  `,
  step: graphql`
    fragment ProposalPageVoteThreshold_step on Step {
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
