// @flow
import * as React from 'react';
import { FormattedMessage } from 'react-intl';
import { graphql, createFragmentContainer } from 'react-relay';
import { Box, Popover } from '@cap-collectif/ui';
import type { VoteButtonOverlay_step } from '~relay/VoteButtonOverlay_step.graphql';
import ResetCss from '~/utils/ResetCss';

type Props = {
  children: React.Node,
  userHasVote: boolean,
  popoverId: string,
  hasReachedLimit: boolean,
  hasUserEnoughCredits: boolean,
  step: ?VoteButtonOverlay_step,
};

export class VoteButtonOverlay extends React.Component<Props> {
  static defaultProps = {
    hasReachedLimit: false,
    hasUserEnoughCredits: true,
  };

  render() {
    const { children, step, popoverId, userHasVote, hasReachedLimit, hasUserEnoughCredits } =
      this.props;
    if (userHasVote || (hasUserEnoughCredits && !hasReachedLimit)) {
      return children;
    }
    let title = '';
    let content = '';
    let help = '';
    if (!hasUserEnoughCredits && hasReachedLimit) {
      title = (
        <FormattedMessage id="proposal.vote.popover.limit_reached_and_not_enough_credits_title" />
      );
      content = (
        <FormattedMessage
          id="proposal.vote.popover.limit_reached_and_not_enough_credits_text"
          values={{
            num: step?.votesLimit,
          }}
        />
      );
      help = <FormattedMessage id="proposal.vote.popover.not_enough_credits_help" />;
    } else if (!hasUserEnoughCredits) {
      title = <FormattedMessage id="proposal.vote.popover.not_enough_credits_title" />;
      content = <FormattedMessage id="proposal.vote.popover.not_enough_credits_text" />;
      help = <FormattedMessage id="proposal.vote.popover.not_enough_credits_help" />;
    } else if (hasReachedLimit) {
      title = <FormattedMessage id="proposal.vote.popover.limit_reached_title" />;
      content = (
        <FormattedMessage
          id="proposal.vote.popover.limit_reached_text"
          values={{
            num: step?.votesLimit,
          }}
        />
      );
      help = <FormattedMessage id="proposal.vote.popover.limit_reached_help" />;
    }
    return (
      <Popover
        baseId={popoverId}
        placement="top"
        disclosure={
          <Box width="fit-content">
            {
              /* $FlowFixMe */
              React.cloneElement(children, {
                disabled: true,
                /* $FlowFixMe */
                style: { ...children.props.style },
              })
            }
          </Box>
        }>
        <ResetCss>
          <Popover.Header>{title}</Popover.Header>
        </ResetCss>

        <Popover.Body marginBottom={0}>
          {content}
          <p className="excerpt" style={{ marginTop: 10 }}>
            {help}
          </p>
        </Popover.Body>
      </Popover>
    );
  }
}

export default createFragmentContainer(VoteButtonOverlay, {
  step: graphql`
    fragment VoteButtonOverlay_step on ProposalStep {
      votesLimit
    }
  `,
});
