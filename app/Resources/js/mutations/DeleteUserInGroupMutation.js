// @flow
import { graphql } from 'react-relay';
import environment from '../createRelayEnvironment';
import commitMutation from './commitMutation';
import type {
  DeleteUserInGroupMutationVariables,
  DeleteUserInGroupMutationResponse
} from './__generated__/DeleteUserInGroupMutation.graphql';

const mutation = graphql`
  mutation DeleteUserInGroupMutation($input: DeleteUserInGroupInput!) {
    deleteUserInGroup(input: $input) {
      group {
        id
        usersConnection {
          edges {
            node {
              id
            }
          }
        }
      }
    }
  }
`;

const commit = (
  variables: DeleteUserInGroupMutationVariables
): Promise<DeleteUserInGroupMutationResponse> =>
  commitMutation(environment, {
    mutation,
    variables
  });

export default { commit };
