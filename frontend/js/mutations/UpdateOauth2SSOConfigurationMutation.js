// @flow
import { graphql } from 'react-relay';
import environment from '../createRelayEnvironment';
import commitMutation from './commitMutation';

import type {
  UpdateOauth2SSOConfigurationMutationVariables,
  UpdateOauth2SSOConfigurationMutationResponse,
} from '~relay/UpdateOauth2SSOConfigurationMutation.graphql';

const mutation = graphql`
  mutation UpdateOauth2SSOConfigurationMutation($input: UpdateOauth2SSOConfigurationInput!) {
    updateOauth2SSOConfiguration(input: $input) {
      ssoConfiguration {
        id
        name
        enabled
        clientId
        secret
        authorizationUrl
        accessTokenUrl
        userInfoUrl
        logoutUrl
        profileUrl
        disconnectSsoOnLogout
      }
    }
  }
`;

const commit = (
  variables: UpdateOauth2SSOConfigurationMutationVariables,
): Promise<UpdateOauth2SSOConfigurationMutationResponse> =>
  commitMutation(environment, {
    mutation,
    variables,
  });

export default { commit };
