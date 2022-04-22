// @flow
import { graphql } from 'react-relay';
import environment from '../createRelayEnvironment';
import commitMutation from './commitMutation';
import type {
  AddOpinionVoteMutationVariables,
  AddOpinionVoteMutationResponse,
} from './__generated__/AddOpinionVoteMutation.graphql';

const mutation = graphql`
  mutation AddOpinionVoteMutation($input: AddOpinionVoteInput!) {
    addOpinionVote(input: $input) {
      previousVoteId
      voteEdge {
        cursor
        node {
          id
          ...OpinionUserVote_vote
          ...UnpublishedTooltip_publishable
          related {
            id
            ... on Opinion {
              viewerVote {
                id
                value
              }
              votes(first: 0) {
                totalCount
              }
              votesOk: votes(first: 0, value: YES) {
                totalCount
              }
              votesNok: votes(first: 0, value: NO) {
                totalCount
              }
              votesMitige: votes(first: 0, value: MITIGE) {
                totalCount
              }
            }
            ... on Version {
              viewerVote {
                id
                value
              }
              votes(first: 0) {
                totalCount
              }
              votesOk: votes(first: 0, value: YES) {
                totalCount
              }
              votesNok: votes(first: 0, value: NO) {
                totalCount
              }
              votesMitige: votes(first: 0, value: MITIGE) {
                totalCount
              }
            }
          }
        }
      }
    }
  }
`;

const commit = (
  variables: AddOpinionVoteMutationVariables,
): Promise<AddOpinionVoteMutationResponse> =>
  commitMutation(environment, {
    mutation,
    variables,
    configs: [
      // If the is previous vote, we remove it
      {
        type: 'NODE_DELETE',
        deletedIDFieldName: 'previousVoteId',
      },
      // Add the new vote
      {
        type: 'RANGE_ADD',
        parentID: variables.input.opinionId,
        connectionInfo: [
          {
            key: 'OpinionVotesBar_previewVotes',
            rangeBehavior: 'prepend',
            filters: {},
          },
        ],
        edgeName: 'voteEdge',
      },
    ],
  });

export default { commit };
