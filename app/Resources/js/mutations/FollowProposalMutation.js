// @flow
import { graphql, type RecordSourceSelectorProxy } from 'react-relay';
import { ConnectionHandler } from 'relay-runtime';
import commitMutation from './commitMutation';
import environnement from '../createRelayEnvironment';
import type {
  FollowProposalMutationVariables,
  FollowProposalMutationResponse as Response,
} from './__generated__/FollowProposalMutation.graphql';

const mutation = graphql`
  mutation FollowProposalMutation($input: FollowProposalInput!) {
    followProposal(input: $input) {
      proposal {
        id
        ...ProposalFollowButton_proposal
      }
      followerEdge {
        node {
          id
          show_url
          displayName
          username
          media {
            url
          }
        }
        cursor
      }
    }
  }
`;

const commit = (variables: FollowProposalMutationVariables): Promise<Response> =>
  commitMutation(environnement, {
    mutation,
    variables,
    configs: [
      {
        type: 'RANGE_ADD',
        parentID: variables.input.proposalId,
        connectionInfo: [
          {
            key: 'ProposalPageFollowers_followers',
            rangeBehavior: 'append',
          },
        ],
        edgeName: 'followerEdge',
      },
    ],
    updater: (store: RecordSourceSelectorProxy) => {
      const payload = store.getRootField('followProposal');
      if (!payload || !payload.getLinkedRecord('followerEdge')) {
        return;
      }
      const proposalProxy = store.get(variables.input.proposalId);
      if (!proposalProxy) return;
      const allFollowersProxy = proposalProxy.getLinkedRecord('followers', { first: 0 });
      if (!allFollowersProxy) return;
      const previousValue = parseInt(allFollowersProxy.getValue('totalCount'), 10);
      allFollowersProxy.setValue(previousValue + 1, 'totalCount');

      const connection = ConnectionHandler.getConnection(
        proposalProxy,
        'ProposalPageFollowers_followers',
      );
      connection.setValue(connection.getValue('totalCount') + 1, 'totalCount');
    },
  });

export default { commit };
