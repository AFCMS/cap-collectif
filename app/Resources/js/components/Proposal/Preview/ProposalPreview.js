// @flow
import React from 'react';
import { Col } from 'react-bootstrap';
import { graphql, createFragmentContainer } from 'react-relay';
import ProposalPreviewHeader from './ProposalPreviewHeader';
import ProposalPreviewBody from './ProposalPreviewBody';
import ProposalPreviewFooter from './ProposalPreviewFooter';
import ProposalStatus from './ProposalStatus';
import { CardContainer } from '../../Ui/Card/CardContainer';
import type { ProposalPreview_proposal } from './__generated__/ProposalPreview_proposal.graphql';
import type { ProposalPreview_step } from './__generated__/ProposalPreview_step.graphql';
import type { ProposalPreview_viewer } from './__generated__/ProposalPreview_viewer.graphql';

type Props = {
  proposal: ProposalPreview_proposal,
  step: ?ProposalPreview_step,
  viewer: ?ProposalPreview_viewer,
};

export class ProposalPreview extends React.Component<Props> {
  render() {
    const { proposal, step, viewer } = this.props;

    return (
      <Col componentClass="li" xs={12} sm={6} md={4} lg={3}>
        <CardContainer
          id={`proposal-${proposal.id}`}
          className={
            proposal.author && proposal.author.vip ? 'bg-vip proposal-preview' : 'proposal-preview'
          }>
          {/* $FlowFixMe */}
          <ProposalPreviewHeader proposal={proposal} />
          <ProposalPreviewBody proposal={proposal} step={step} viewer={viewer} />
          {/* $FlowFixMe */}
          {step && <ProposalPreviewFooter proposal={proposal} />}
          {/* $FlowFixMe */}
          <ProposalStatus proposal={proposal} />
        </CardContainer>
      </Col>
    );
  }
}

export default createFragmentContainer(ProposalPreview, {
  viewer: graphql`
    fragment ProposalPreview_viewer on User {
      ...ProposalPreviewBody_viewer
    }
  `,
  step: graphql`
    fragment ProposalPreview_step on Step {
      ...ProposalPreviewBody_step
    }
  `,
  proposal: graphql`
    fragment ProposalPreview_proposal on Proposal
      @argumentDefinitions(
        stepId: { type: "ID", nonNull: false }
        isAuthenticated: { type: "Boolean!" }
        isProfileView: { type: "Boolean", defaultValue: false }
      ) {
      id
      author {
        vip
      }
      ...ProposalPreviewHeader_proposal
      ...ProposalPreviewFooter_proposal @arguments(stepId: $stepId, isProfileView: $isProfileView)
      ...ProposalPreviewBody_proposal
        @arguments(isAuthenticated: $isAuthenticated, isProfileView: $isProfileView)
      ...ProposalStatus_proposal @arguments(stepId: $stepId, isProfileView: $isProfileView)
    }
  `,
});
