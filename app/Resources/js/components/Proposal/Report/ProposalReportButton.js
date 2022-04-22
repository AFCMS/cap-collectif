// @flow
import * as React from 'react';
import { connect } from 'react-redux';
import { graphql, createFragmentContainer } from 'react-relay';
import ReportBox from '../../Report/ReportBox';
import { submitProposalReport } from '../../../redux/modules/report';
import type { ProposalReportButton_proposal } from './__generated__/ProposalReportButton_proposal.graphql';

type Props = {
  proposal: ProposalReportButton_proposal,
  dispatch: Function,
};

export class ProposalReportButton extends React.Component<Props> {
  handleReport = (data: Object) => {
    const { proposal, dispatch } = this.props;
    return submitProposalReport(proposal, data, dispatch);
  };

  render() {
    const { proposal } = this.props;
    return (
      <ReportBox
        id={`proposal-${proposal.id}`}
        buttonStyle={{ marginLeft: '15px' }}
        reported={proposal.viewerHasReported || false}
        onReport={this.handleReport}
        author={proposal.author}
        buttonClassName="proposal__btn--report"
      />
    );
  }
}

const container = connect()(ProposalReportButton);

export default createFragmentContainer(
  container,
  graphql`
    fragment ProposalReportButton_proposal on Proposal {
      id
      author {
        id
      }
      viewerHasReported @include(if: $isAuthenticated)
    }
  `,
);
