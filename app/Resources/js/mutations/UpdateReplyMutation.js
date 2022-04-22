// @flow
import { graphql } from 'react-relay';
import environment from '../createRelayEnvironment';
import commitMutation from './commitMutation';
import type {
  UpdateReplyMutationVariables,
  UpdateReplyMutationResponse,
} from './__generated__/UpdateReplyMutation.graphql';

const mutation = graphql`
  mutation UpdateReplyMutation($input: UpdateReplyInput!) {
    updateReply(input: $input) {
      reply {
        id
      }
    }
  }
`;

const commit = (variables: UpdateReplyMutationVariables): Promise<UpdateReplyMutationResponse> =>
  commitMutation(environment, {
    mutation,
    variables,
  });

export default { commit };
