// @flow
import React, { Component } from 'react';
import { type ReadyState, QueryRenderer, graphql } from 'react-relay';
import environment, { graphqlError } from '../../createRelayEnvironment';
import ProposalFormAdminPageTabs from './ProposalFormAdminPageTabs';
import Loader from '../Utils/Loader';
import type { ProposalFormAdminPageQueryResponse } from './__generated__/ProposalFormAdminPageQuery.graphql';
import type { Uuid } from '../../types';

type Props = { proposalFormId: Uuid };

const component = ({
  error,
  props,
}: ReadyState & {
  props: ?ProposalFormAdminPageQueryResponse,
}) => {
  if (error) {
    console.log(error); // eslint-disable-line no-console
    return graphqlError;
  }
  if (props) {
    // eslint-disable-next-line
    if (props.proposalForm !== null) {
      return <ProposalFormAdminPageTabs {...props} />;
    }
    return graphqlError;
  }
  return <Loader />;
};

export class ProposalFormAdminPage extends Component<Props> {
  render() {
    return (
      <div className="admin_proposal_form">
        <QueryRenderer
          environment={environment}
          query={graphql`
            query ProposalFormAdminPageQuery($id: ID!) {
              proposalForm: node(id: $id) {
                ...ProposalFormAdminPageTabs_proposalForm
              }
            }
          `}
          variables={{
            id: this.props.proposalFormId,
          }}
          render={component}
        />
      </div>
    );
  }
}

export default ProposalFormAdminPage;
