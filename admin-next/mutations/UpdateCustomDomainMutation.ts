import { graphql } from 'react-relay';
import { environment } from 'utils/relay-environement';
import commitMutation from './commitMutation';
import type {
  UpdateCustomDomainMutation,
  UpdateCustomDomainMutationResponse,
  UpdateCustomDomainMutationVariables,
} from '@relay/UpdateCustomDomainMutation.graphql';

const mutation = graphql`
    mutation UpdateCustomDomainMutation($input: UpdateCustomDomainInput!) {
        updateCustomDomain(input: $input) {
            siteSettings {
                capcoDomain
                customDomain
                status
            }
            errorCode
        }
    }
    
`;

const commit = (
  variables: UpdateCustomDomainMutationVariables,
): Promise<UpdateCustomDomainMutationResponse> =>
  commitMutation<UpdateCustomDomainMutation>(environment, {
    mutation,
    variables,
    updater: (store: any) => {
      const root = store.getRoot()
      const payload = store.getRootField('updateCustomDomain');
      const errorCode = payload.getValue('errorCode')
      if (errorCode) return;

      const payloadSiteSettings = payload.getLinkedRecord('siteSettings')
      const status = payloadSiteSettings.getValue('status')
      const customDomain = payloadSiteSettings.getValue('customDomain')

      const rootSiteSettings = root.getLinkedRecord('siteSettings')
      rootSiteSettings.setValue(status, 'status')
      rootSiteSettings.setValue(customDomain, 'customDomain')
    }
  });

export default { commit };
