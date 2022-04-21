// @flow
import * as React from 'react';
import { FormattedMessage } from 'react-intl';
import { createFragmentContainer, graphql } from 'react-relay';
import { ButtonToolbar, Button } from 'react-bootstrap';
import ProposalCollectStatus from '../Proposal/ProposalCollectStatus';
import ProposalLastUpdateInfo from '../Proposal/ProposalLastUpdateInfo';
import type { ProposalFormEvaluationRow_proposal } from './__generated__/ProposalFormEvaluationRow_proposal.graphql';

type Props = { proposal: ProposalFormEvaluationRow_proposal };

export class ProposalFormEvaluationRow extends React.Component<Props> {
  render() {
    const { proposal } = this.props;
    return (
      <tr>
        <td>{proposal.reference}</td>
        <td>
          <a href={proposal.show_url}>{proposal.title}</a>
        </td>
        <td>
          {/* $FlowFixMe $fragmentRefs is missing */}
          <ProposalCollectStatus proposal={proposal} />
        </td>
        <td>
          {/* $FlowFixMe $fragmentRefs is missing */}
          <ProposalLastUpdateInfo proposal={proposal} />
        </td>
        <td>
          <ButtonToolbar>
            <Button href={proposal.show_url}>
              <FormattedMessage id="global.see" />
            </Button>
            <Button bsStyle="primary" href={`${proposal.show_url}#evaluation`}>
              <FormattedMessage id="global.eval" />
            </Button>
          </ButtonToolbar>
        </td>
      </tr>
    );
  }
}

export default createFragmentContainer(ProposalFormEvaluationRow, {
  proposal: graphql`
    fragment ProposalFormEvaluationRow_proposal on Proposal {
      show_url
      reference
      title
      ...ProposalCollectStatus_proposal
      ...ProposalLastUpdateInfo_proposal
    }
  `
});
