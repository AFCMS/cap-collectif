// @flow
import { graphql } from 'react-relay';
import environment from '../createRelayEnvironment';
import commitMutation from './commitMutation';
import type {
  CreateGroupMutationResponse,
  CreateGroupMutationVariables,
} from './__generated__/CreateGroupMutation.graphql';

const mutation = graphql`
  mutation CreateGroupMutation($input: CreateGroupInput!) {
    createGroup(input: $input) {
      group {
        id
      }
    }
  }
`;

const commit = (variables: CreateGroupMutationVariables): Promise<CreateGroupMutationResponse> =>
  commitMutation(environment, {
    mutation,
    variables,
  });

export default { commit };
