// @flow
import React from 'react';
import moment from 'moment';
import { graphql, createFragmentContainer } from 'react-relay';
import { FormattedDate } from 'react-intl';
import UserAvatar from '../../User/UserAvatar';
import UserLink from '../../User/UserLink';
import { CardUser } from '../../Ui/Card/CardUser';
import type { ProposalPreviewHeader_proposal } from './__generated__/ProposalPreviewHeader_proposal.graphql';

type Props = { proposal: ProposalPreviewHeader_proposal };

export class ProposalPreviewHeader extends React.Component<Props> {
  render() {
    const proposal = this.props.proposal;
    return (
      <CardUser>
        <div className="card__user__avatar">
          <UserAvatar user={proposal.author} />
        </div>
        <div className="ellipsis">
          <UserLink user={proposal.author} />
          <p className="excerpt small">
            <FormattedDate
              value={moment(proposal.createdAt)}
              day="numeric"
              month="long"
              year="numeric"
            />
          </p>
        </div>
        <hr />
      </CardUser>
    );
  }
}

export default createFragmentContainer(ProposalPreviewHeader, {
  proposal: graphql`
    fragment ProposalPreviewHeader_proposal on Proposal {
      createdAt
      author {
        id
        displayName
      }
    }
  `,
});
