// @flow
import * as React from 'react';
import {
  FormattedDate,
  FormattedMessage,
  FormattedTime,
  type IntlShape,
  injectIntl,
} from 'react-intl';
import { graphql, createFragmentContainer } from 'react-relay';
import { Row, Col, Popover, OverlayTrigger } from 'react-bootstrap';
import { Field } from 'redux-form';
import moment from 'moment';
import toggle from '../../Form/Toggle';
import ProposalDetailEstimation from '../../Proposal/Detail/ProposalDetailEstimation';
import type { ProposalUserVoteItem_vote } from './__generated__/ProposalUserVoteItem_vote.graphql';
import type { ProposalUserVoteItem_step } from './__generated__/ProposalUserVoteItem_step.graphql';

type Props = {
  vote: ProposalUserVoteItem_vote,
  step: ProposalUserVoteItem_step,
  ranking?: number,
  isVoteVisibilityPublic: boolean,
  intl: IntlShape,
  onDelete?: ?() => void,
  member: string,
  showDraggableIcon: boolean,
};

export class ProposalUserVoteItem extends React.Component<Props> {
  static defaultProps = {
    showDraggableIcon: false,
  };

  render() {
    const {
      isVoteVisibilityPublic,
      onDelete,
      member,
      showDraggableIcon,
      step,
      intl,
      vote,
      ranking,
    } = this.props;
    const proposal = vote.proposal;

    const colTitleWidth = () => {
      if (step.votesRanking === true && step.voteType === 'BUDGET') {
        return 6;
      }

      if (step.votesRanking === true) {
        return 8;
      }

      if (step.voteType === 'BUDGET') {
        return 7;
      }

      return 9;
    };

    const getTitle = title => {
      const windowWidth = window.innerWidth;

      let maxItemLength;

      if (windowWidth > 400) {
        maxItemLength = 85;
      } else {
        maxItemLength = 60;
      }

      return title.length > maxItemLength ? `${title.substring(0, maxItemLength)}...` : title;
    };

    const getToggleLabel = () => {
      if (isVoteVisibilityPublic) {
        return intl.formatMessage({ id: 'public' });
      }

      return intl.formatMessage({ id: 'admin.fields.idea_vote.private' });
    };

    const popoverConfirmDelete = (
      <Popover id="popover-positioned-right">
        <i className="cap cap-attention icon--red" />
        <FormattedMessage id="are-you-sure-you-want-to-delete-this-vote" />
        <div className="mt-10 d-flex justify-content-end">
          <button
            onClick={() => {
              this.refs.popover.hide();
            }}
            className="btn btn-default mr-10">
            <FormattedMessage id="global.no" />
          </button>
          {onDelete && (
            <button
              onClick={() => {
                onDelete();
              }}
              className="proposal-vote__delete btn btn-danger"
              disabled={!step.open}>
              <FormattedMessage id="btn-delete" />
            </button>
          )}
        </div>
      </Popover>
    );

    return (
      <Row
        className="proposals-user-votes__row d-flex flex-wrap"
        id={`vote-step${step.id}-proposal${proposal.id}`}>
        {ranking && (
          <Col md={1} sm={12} xs={12} className="proposals-user-votes__col">
            <div className="proposals-user-votes__content justify-content-between">
              {showDraggableIcon && <i className="cap cap-android-menu excerpt mr-5" />}
              <div className="d-flex">
                <span className="badge label-primary m-auto">{ranking}</span>
              </div>
            </div>
          </Col>
        )}
        <Col className="proposals-user-votes__col" md={colTitleWidth()} sm={12} xs={12}>
          <div className="proposals-user-votes__content">
            <div>
              <a href={proposal.show_url} className="proposals-user-votes__title">
                {getTitle(proposal.title)}
              </a>
              <br />
              {vote.createdAt ? (
                <FormattedMessage
                  id="voted-on-date-at-time"
                  values={{
                    date: (
                      <FormattedDate
                        value={moment(vote.createdAt)}
                        day="numeric"
                        month="long"
                        year="numeric"
                      />
                    ),
                    time: (
                      <FormattedTime
                        value={moment(vote.createdAt)}
                        hour="numeric"
                        minute="numeric"
                      />
                    ),
                  }}
                />
              ) : (
                <FormattedMessage id="notification-subject-new-vote" />
              )}
            </div>
          </div>
        </Col>
        <Col
          id={`${proposal.id}-proposal-vote__private`}
          className="proposals-user-votes__col"
          md={onDelete ? 2 : 3}
          sm={12}
          xs={12}>
          <div className="proposals-user-votes__content justify-content-end">
            <div className="toggle-group">
              <Field
                labelSide="RIGHT"
                component={toggle}
                label={getToggleLabel()}
                roledescription={intl.formatMessage({ id: 'vote-toggle-aria-roledescription' })}
                name={`${member}.public`}
                normalize={val => !!val}
                id={`${proposal.id}-proposal-vote__private-toggle`}
              />
            </div>
          </div>
        </Col>
        {step.voteType === 'BUDGET' && (
          <Col className="proposals-user-votes__col" md={2} sm={12} xs={12}>
            <div className="proposals-user-votes__content justify-content-center">
              {/* $FlowFixMe */}
              <ProposalDetailEstimation proposal={proposal} showNullEstimation />
            </div>
          </Col>
        )}
        {onDelete && (
          <Col className="proposals-user-votes__col proposal-vote-col__delete" md={1}>
            <OverlayTrigger
              trigger="click"
              placement="bottom"
              overlay={popoverConfirmDelete}
              ref="popover">
              <a
                className="proposal-vote__delete"
                disabled={!step.open}
                aria-label={intl.formatMessage({ id: 'aria-label-delete-vote' })}>
                <i
                  className="cap cap-ios-close"
                  id={`${proposal.id}-proposal-vote__private-delete`}
                />
              </a>
            </OverlayTrigger>
          </Col>
        )}
        {showDraggableIcon && (
          <div className="draggable-icon__mobile">
            <i className="cap cap-android-menu excerpt" />
          </div>
        )}
      </Row>
    );
  }
}

const container = injectIntl(ProposalUserVoteItem);

export default createFragmentContainer(container, {
  vote: graphql`
    fragment ProposalUserVoteItem_vote on ProposalVote {
      createdAt
      proposal {
        id
        title
        show_url
        ...ProposalDetailEstimation_proposal
      }
    }
  `,
  step: graphql`
    fragment ProposalUserVoteItem_step on ProposalStep {
      id
      open
      voteType
      votesRanking
    }
  `,
});
