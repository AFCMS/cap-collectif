// @flow
import { graphql } from 'react-relay';
import environment from '../createRelayEnvironment';
import commitMutation from './commitMutation';
import type {
  ChangeUserNotificationsConfigurationMutationVariables,
  ChangeUserNotificationsConfigurationMutationResponse,
} from './__generated__/ChangeUserNotificationsConfigurationMutation.graphql';

const mutation = graphql`
  mutation ChangeUserNotificationsConfigurationMutation(
    $input: ChangeUserNotificationsConfigurationInput!
  ) {
    changeUserNotificationsConfiguration(input: $input) {
      user {
        notificationsConfiguration {
          onProposalCommentMail
        }
      }
    }
  }
`;

const commit = (
  variables: ChangeUserNotificationsConfigurationMutationVariables,
): Promise<ChangeUserNotificationsConfigurationMutationResponse> =>
  commitMutation(environment, {
    mutation,
    variables,
  });

export default { commit };
