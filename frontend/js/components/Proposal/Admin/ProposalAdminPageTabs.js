// @flow
import React, { Component } from 'react';
import { Tabs, Tab } from 'react-bootstrap';
import { injectIntl, FormattedMessage, type IntlShape } from 'react-intl';
import { createFragmentContainer, graphql } from 'react-relay';
import ProposalAdminSelections from './ProposalAdminSelections';
import ProposalAdminStatusForm from './ProposalAdminStatusForm';
import ProposalAdminContentForm from './ProposalAdminContentForm';
import ProposalAdminNotationForm from './ProposalAdminNotationForm';
import ProposalAdminNewsForm from './ProposalAdminNewsForm';
import ProposalAdminFollowers from './ProposalAdminFollowers';
import type { ProposalAdminPageTabs_proposal } from '~relay/ProposalAdminPageTabs_proposal.graphql';

type Props = { proposal: ProposalAdminPageTabs_proposal, intl: IntlShape };

export class ProposalAdminPageTabs extends Component<Props> {
  render() {
    const { intl, proposal } = this.props;

    return (
      <div>
        <p>
          <strong>
            <FormattedMessage id="permalink" /> :{' '}
          </strong>{' '}
          <a href={proposal.url}>{proposal.url}</a> |{' '}
          <b>{intl.formatMessage({ id: 'global.ref' })} :</b> {proposal.reference}
        </p>
        <Tabs defaultActiveKey={1} id="proposal-admin-page-tabs">
          <Tab eventKey={1} title={intl.formatMessage({ id: 'global.contenu' })}>
            <ProposalAdminContentForm proposal={proposal} />
          </Tab>
          <Tab eventKey={2} title={intl.formatMessage({ id: 'proposal.admin.advancement' })}>
            <ProposalAdminSelections proposal={proposal} />
          </Tab>
          <Tab eventKey={3} title={intl.formatMessage({ id: 'proposal.admin.news' })}>
            <ProposalAdminNewsForm proposal={proposal} />
          </Tab>
          <Tab
            eventKey={4}
            title={
              <div>
                <FormattedMessage id="proposal.tabs.followers" />
                <span className="badge ml-10">
                  {proposal.allFollowers ? proposal.allFollowers.totalCount : 0}
                </span>
              </div>
            }>
            <ProposalAdminFollowers proposal={proposal} />
          </Tab>
          <Tab eventKey={5} title={intl.formatMessage({ id: 'proposal.tabs.evaluation' })}>
            <ProposalAdminNotationForm proposal={proposal} />
          </Tab>
          <Tab eventKey={6} title={intl.formatMessage({ id: 'global.state' })}>
            <ProposalAdminStatusForm proposal={proposal} />
          </Tab>
        </Tabs>
      </div>
    );
  }
}

const container = injectIntl(ProposalAdminPageTabs);

export default createFragmentContainer(container, {
  proposal: graphql`
    fragment ProposalAdminPageTabs_proposal on Proposal
      @argumentDefinitions(proposalRevisionsEnabled: { type: "Boolean!" }) {
      url
      reference
      ...ProposalAdminStatusForm_proposal
      ...ProposalAdminSelections_proposal
      ...ProposalAdminContentForm_proposal
        @arguments(proposalRevisionsEnabled: $proposalRevisionsEnabled)
      ...ProposalAdminNotationForm_proposal
      ...ProposalAdminNewsForm_proposal
      ...ProposalAdminFollowers_proposal
      allFollowers: followers(first: 0) {
        totalCount
      }
    }
  `,
});
