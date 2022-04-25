// @flow
import * as React from 'react';
import { graphql, QueryRenderer } from 'react-relay';
import type { AuthentificationAdminPageQueryResponse } from '~relay/AuthentificationAdminPageQuery.graphql';
import environment, { graphqlError } from '../../../createRelayEnvironment';
import Loader from '../../Ui/FeedbacksIndicators/Loader';
import SSOByPassAuthForm from './SSOByPassAuthForm';
import AuthentificationAdminPageContent from './AuthentificationAdminPageContent';

const component = ({
  error,
  props,
}: {
  ...ReactRelayReadyState,
  props: ?AuthentificationAdminPageQueryResponse,
}) => {
  if (error) {
    console.log(error); // eslint-disable-line no-console
    return graphqlError;
  }
  if (props) {
    if (props.shieldAdminForm !== null && props.ssoConfigurations !== null) {
      // $FlowFixMe
      return <AuthentificationAdminPageContent {...props} />;
    }
    return graphqlError;
  }
  return <Loader />;
};

export const AuthentificationAdminPage = () => (
  <>
    <QueryRenderer
      environment={environment}
      query={graphql`
        query AuthentificationAdminPageQuery {
          shieldAdminForm {
            ...AuthentificationAdminPageContent_shieldAdminForm
          }
          ssoConfigurations {
            ...AuthentificationAdminPageContent_ssoConfigurations
          }
        }
      `}
      variables={{}}
      render={component}
    />
    <SSOByPassAuthForm />
  </>
);

export default AuthentificationAdminPage;
