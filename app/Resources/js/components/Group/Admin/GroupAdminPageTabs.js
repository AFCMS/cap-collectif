// @flow
import React, { Component } from 'react';
import { Tabs, Tab } from 'react-bootstrap';
import { injectIntl, type IntlShape } from 'react-intl';
import { createFragmentContainer, graphql } from 'react-relay';
import GroupAdminUsers from './GroupAdminUsers';
import GroupAdminParameters from './GroupAdminParameters';
import type { GroupAdminPageTabs_group } from './__generated__/GroupAdminPageTabs_group.graphql';

type Props = { group: GroupAdminPageTabs_group, intl: IntlShape };

export class GroupAdminPageTabs extends Component<Props> {
  render() {
    const { intl, group } = this.props;
    return (
      <div className="col-xs-12">
        <Tabs>
          <Tab eventKey={1} title={intl.formatMessage({ id: 'group.admin.users' })}>
            {/* $FlowFixMe group is {||}. Probably typedef bug. */}
            <GroupAdminUsers group={group} />
          </Tab>
          <Tab eventKey={2} title={intl.formatMessage({ id: 'group.admin.parameters' })}>
            <GroupAdminParameters group={group} />
          </Tab>
        </Tabs>
      </div>
    );
  }
}

const container = injectIntl(GroupAdminPageTabs);

export default createFragmentContainer(container, {
  group: graphql`
    fragment GroupAdminPageTabs_group on Group {
      ...GroupAdminUsers_group
      ...GroupAdminParameters_group
    }
  `,
});
