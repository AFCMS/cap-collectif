// @flow
import React from 'react';
import { graphql, createFragmentContainer } from 'react-relay';
import { FormattedMessage } from 'react-intl';
import OpinionUserVote from './OpinionUserVote';
import VotesBar from '../../Utils/VotesBar';
import OpinionVotesModal from './OpinionVotesModal';
import type { OpinionVotesBar_opinion } from './__generated__/OpinionVotesBar_opinion.graphql';

type Props = {
  opinion: OpinionVotesBar_opinion,
};

class OpinionVotesBar extends React.Component<Props> {
  render() {
    const { opinion } = this.props;
    if (!opinion.section) return null;
    return (
      <div>
        {opinion.section.votesThreshold && (
          <VotesBar
            max={opinion.section.votesThreshold}
            value={opinion.votesYes ? opinion.votesYes.totalCount : 0}
            helpText={opinion.section.votesThresholdHelpText}
          />
        )}
        <div style={{ paddingTop: '20px' }}>
          {opinion.previewVotes &&
            opinion.previewVotes.edges &&
            opinion.previewVotes.edges
              .filter(Boolean)
              .map(edge => edge.node)
              .filter(Boolean)
              .slice(0, 5)
              .map((vote, index) => {
                /* $FlowFixMe */
                return <OpinionUserVote key={index} vote={vote} style={{ marginRight: 5 }} />;
              })}
          {/* $FlowFixMe */}
          <OpinionVotesModal opinion={opinion} />
        </div>
        <div>
          {opinion.previewVotes && (
            <FormattedMessage
              id="global.votes"
              values={{
                num: opinion.previewVotes.totalCount,
              }}
            />
          )}
        </div>
      </div>
    );
  }
}

export default createFragmentContainer(OpinionVotesBar, {
  opinion: graphql`
    fragment OpinionVotesBar_opinion on OpinionOrVersion {
      ...OpinionVotesModal_opinion
      ... on Opinion {
        id
        votesYes: votes(first: 0, value: YES) {
          totalCount
        }
        previewVotes: votes(first: 5) @connection(key: "OpinionVotesBar_previewVotes") {
          totalCount
          edges {
            node {
              ...OpinionUserVote_vote
            }
          }
        }
        section {
          voteWidgetType
          votesThresholdHelpText
          votesThreshold
        }
      }
      ... on Version {
        id
        votesYes: votes(first: 0, value: YES) {
          totalCount
        }
        previewVotes: votes(first: 5) {
          totalCount
          edges {
            node {
              ...OpinionUserVote_vote
            }
          }
        }
        section {
          votesThreshold
          votesThresholdHelpText
          voteWidgetType
        }
        parent {
          id
        }
      }
    }
  `,
});
