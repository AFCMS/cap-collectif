// @flow
import { graphql, type RecordSourceSelectorProxy } from 'react-relay';
import { ConnectionHandler } from 'relay-runtime';
import commitMutation from './commitMutation';
import environnement from '../createRelayEnvironment';
import type {
  FollowOpinionMutationVariables,
  FollowOpinionMutationResponse as Response,
} from './__generated__/FollowOpinionMutation.graphql';

const mutation = graphql`
  mutation FollowOpinionMutation($input: FollowOpinionInput!) {
    followOpinion(input: $input) {
      opinion {
        id
        ...OpinionFollowButton_opinion
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

const commit = (variables: FollowOpinionMutationVariables): Promise<Response> =>
  commitMutation(environnement, {
    mutation,
    variables,
    configs: [
      {
        type: 'RANGE_ADD',
        parentID: variables.input.opinionId,
        connectionInfo: [
          {
            key: 'OpinionFollowersBox_followers',
            rangeBehavior: 'append',
          },
        ],
        edgeName: 'followerEdge',
      },
    ],
    updater: (store: RecordSourceSelectorProxy) => {
      const payload = store.getRootField('followOpinion');
      if (!payload || !payload.getLinkedRecord('followerEdge')) {
        return;
      }
      const opinionProxy = store.get(variables.input.opinionId);
      if (!opinionProxy) return;
      const allFollowersProxy = opinionProxy.getLinkedRecord('followers', { first: 0 });
      if (!allFollowersProxy) return;
      const previousValue = parseInt(allFollowersProxy.getValue('totalCount'), 10);
      allFollowersProxy.setValue(previousValue + 1, 'totalCount');

      const connection = ConnectionHandler.getConnection(
        opinionProxy,
        'OpinionFollowersBox_followers',
      );
      connection.setValue(connection.getValue('totalCount') + 1, 'totalCount');
    },
  });

export default { commit };
