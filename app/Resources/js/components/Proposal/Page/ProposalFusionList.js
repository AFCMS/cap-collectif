// @flow
import React from 'react';
import { graphql, createFragmentContainer } from 'react-relay';
import { Panel } from 'react-bootstrap';
import { FormattedMessage } from 'react-intl';
import type { ProposalFusionList_proposal } from './__generated__/ProposalFusionList_proposal.graphql';

type Props = {
  proposal: ProposalFusionList_proposal,
};

export class ProposalFusionList extends React.Component<Props> {
  render() {
    const { proposal } = this.props;
    return (
      <div>
        {proposal.mergedFrom.length > 0 && (
          <Panel
            header={
              <FormattedMessage
                id={`proposal.mergedFrom`}
                values={{ num: proposal.mergedFrom.length }}
              />
            }>
            {proposal.mergedFrom.map(child => (
              <div key={child.id}>
                <a href={child.show_url}>{child.title}</a>
              </div>
            ))}
          </Panel>
        )}
        {proposal.mergedIn.length > 0 && (
          <Panel
            header={
              <FormattedMessage
                id={`proposal.mergedIn`}
                values={{ num: proposal.mergedIn.length }}
              />
            }>
            {proposal.mergedIn.map(parent => (
              <div key={parent.id}>
                <a href={parent.show_url}>{parent.title}</a>
              </div>
            ))}
          </Panel>
        )}
      </div>
    );
  }
}

export default createFragmentContainer(
  ProposalFusionList,
  graphql`
    fragment ProposalFusionList_proposal on Proposal {
      id
      mergedFrom {
        id
        show_url
        title
      }
      mergedIn {
        id
        show_url
        title
      }
    }
  `,
);
