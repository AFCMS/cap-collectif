// @flow
import React from 'react';
import { graphql, createFragmentContainer } from 'react-relay';
import { OverlayTrigger, Tooltip } from 'react-bootstrap';
import UserAvatar from '../../User/UserAvatar';
import type { OpinionUserVote_vote } from './__generated__/OpinionUserVote_vote.graphql';

type Props = {
  vote: OpinionUserVote_vote,
  style?: Object,
};

class OpinionUserVote extends React.Component<Props> {
  static defaultProps = {
    style: {},
  };

  render() {
    const { vote, style } = this.props;
    return (
      <OverlayTrigger
        placement="top"
        overlay={<Tooltip id={`opinion-vote-tooltip-${vote.id}`}>{vote.author.displayName}</Tooltip>}>
        <UserAvatar user={vote.author} style={style} />
      </OverlayTrigger>
    );
  }
}

export default createFragmentContainer(OpinionUserVote, {
  vote: graphql`
    fragment OpinionUserVote_vote on OpinionVote {
      id
      author {
        displayName
        media {
          url
        }
      }
    }
  `,
});
