// @flow
import * as React from 'react';
import ReactDOM from 'react-dom';
import { graphql, createFragmentContainer } from 'react-relay';
import { FormattedMessage } from 'react-intl';
import { Button } from 'react-bootstrap';
import classNames from 'classnames';
import { type MapStateToProps, connect } from 'react-redux';
import { openVoteModal, deleteVote } from '../../../redux/modules/proposal';
import UnpublishedTooltip from '../../Publishable/UnpublishedTooltip';
import type { Uuid, Dispatch, GlobalState } from '../../../types';
import type { ProposalVoteButton_proposal } from './__generated__/ProposalVoteButton_proposal.graphql';

type Step = {
  +id: Uuid,
};

type ParentProps = {
  proposal: ProposalVoteButton_proposal,
  step: Step,
  user: { +id: string },
  isHovering: boolean,
  id: string,
};

type Props = ParentProps & {
  dispatch: Dispatch,
  isDeleting: boolean,
  disabled: boolean,
};

// Should only be used via ProposalVoteButtonWrapper
export class ProposalVoteButton extends React.Component<Props> {
  static defaultProps = { disabled: false, isHovering: false };

  target: null;

  getButtonStyle = () => {
    const { isHovering, proposal } = this.props;
    if (proposal.viewerVote && isHovering) {
      return 'btn btn-danger';
    }
    return 'btn btn-success';
  };

  getButtonText = () => {
    const { isHovering, proposal } = this.props;

    if (proposal.viewerVote) {
      return isHovering ? 'proposal.vote.delete' : 'proposal.vote.hasVoted';
    }
    return 'proposal.vote.add';
  };

  render() {
    const { dispatch, step, user, proposal, disabled, isDeleting, id } = this.props;
    const classes = classNames({ disabled });
    const action = !user
      ? null
      : proposal.viewerHasVote
        ? () => {
            deleteVote(step, proposal);
          }
        : () => {
            dispatch(openVoteModal(proposal.id));
          };

    return (
      <Button
        id={id}
        ref={button => {
          this.target = button;
        }}
        className={`mr-15 proposal__button__vote ${this.getButtonStyle()} ${classes} `}
        onClick={disabled ? null : action}
        active={proposal.viewerHasVote}
        disabled={disabled || isDeleting}>
        {proposal.viewerVote /* $FlowFixMe */ && (
          <UnpublishedTooltip
            target={() => ReactDOM.findDOMNode(this.target)}
            publishable={proposal.viewerVote}
          />
        )}
        <FormattedMessage id={this.getButtonText()} />
      </Button>
    );
  }
}

const mapStateToProps: MapStateToProps<*, *, *> = (state: GlobalState, props: ParentProps) => ({
  isDeleting: state.proposal.currentDeletingVote === props.proposal.id,
});

const container = connect(mapStateToProps)(ProposalVoteButton);

export default createFragmentContainer(container, {
  proposal: graphql`
    fragment ProposalVoteButton_proposal on Proposal
      @argumentDefinitions(
        isAuthenticated: { type: "Boolean", defaultValue: true }
        stepId: { type: "ID!", nonNull: true }
      ) {
      id
      viewerHasVote(step: $stepId) @include(if: $isAuthenticated)
      viewerVote(step: $stepId) @include(if: $isAuthenticated) {
        id
        ...UnpublishedTooltip_publishable
      }
    }
  `,
});
