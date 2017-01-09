import React, { PropTypes } from 'react';
import { IntlMixin } from 'react-intl';
import { Button } from 'react-bootstrap';
import classNames from 'classnames';
import { connect } from 'react-redux';
import { openVoteModal, deleteVote } from '../../../redux/modules/proposal';

// Should only be used via ProposalVoteButtonWrapper
const ProposalVoteButton = React.createClass({
  propTypes: {
    disabled: PropTypes.bool,
    proposal: PropTypes.object.isRequired,
    step: PropTypes.object,
    user: PropTypes.object,
    dispatch: PropTypes.func.isRequired,
    style: PropTypes.object,
    userHasVote: PropTypes.bool.isRequired,
    isDeleting: PropTypes.bool.isRequired,
    className: PropTypes.string,
  },
  mixins: [IntlMixin],

  getDefaultProps() {
    return {
      disabled: false,
      style: {},
      className: '',
      step: null,
    };
  },

  render() {
    const {
      dispatch,
      style,
      step,
      user,
      className,
      proposal,
      disabled,
      userHasVote,
      isDeleting,
    } = this.props;
    const bsStyle = user && userHasVote ? 'danger' : 'success';
    let classes = classNames({ disabled });
    classes += ` ${className}`;
    const action = user && userHasVote
      ? () => {
        deleteVote(dispatch, step, proposal);
      }
      : () => {
        dispatch(openVoteModal(proposal.id));
      };
    return (
        <Button
          bsStyle={bsStyle}
          className={classes}
          style={style}
          onClick={disabled ? null : action}
          active={userHasVote}
          disabled={disabled || isDeleting}
        >
          {
            isDeleting
            ? this.getIntlMessage('proposal.vote.deleting')
            : (user && userHasVote
              ? this.getIntlMessage('proposal.vote.delete')
              : this.getIntlMessage('proposal.vote.add')
            )
          }
        </Button>
    );
  },

});

const mapStateToProps = (state, props) => ({
  isDeleting: state.proposal.currentDeletingVote === props.proposal.id,
  userHasVote: !!(props.step && state.proposal.userVotesByStepId[props.step.id].includes(props.proposal.id)),
});

export default connect(mapStateToProps)(ProposalVoteButton);
