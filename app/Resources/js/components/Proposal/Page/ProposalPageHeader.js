import React, { PropTypes } from 'react';
import { IntlMixin, FormattedMessage, FormattedDate } from 'react-intl';
import classNames from 'classnames';
import moment from 'moment';
import UserAvatar from '../../User/UserAvatar';
import UserLink from '../../User/UserLink';
import ProposalVoteButtonWrapper from '../Vote/ProposalVoteButtonWrapper';

const ProposalPageHeader = React.createClass({
  displayName: 'ProposalPageHeader',
  propTypes: {
    proposal: PropTypes.object.isRequired,
    className: PropTypes.string,
    showThemes: PropTypes.bool.isRequired,
    onVote: PropTypes.func.isRequired,
    creditsLeft: PropTypes.number,
    step: PropTypes.object,
  },
  mixins: [IntlMixin],

  getDefaultProps() {
    return {
      className: '',
      creditsLeft: null,
      step: null,
    };
  },

  render() {
    const {
      proposal,
      showThemes,
      creditsLeft,
      step,
      onVote,
      className,
    } = this.props;

    const createdDate = (
      <FormattedDate
       value={moment(proposal.created_at)}
       day="numeric" month="long" year="numeric" hour="numeric" minute="numeric"
      />
    );
    const updatedDate = (
      <FormattedDate
        value={moment(proposal.updated_at)}
        day="numeric" month="long" year="numeric" hour="numeric" minute="numeric"
      />
    );

    const classes = {
      proposal__header: true,
    };
    classes[className] = true;

    return (
      <div className={classNames(classes)}>
        {
          showThemes && proposal.theme
          && <p className="excerpt">{proposal.theme.title}</p>
        }
        <h1 className="consultation__header__title h1">{proposal.title}</h1>
        <div className="media">
          <UserAvatar className="pull-left" user={proposal.author} />
          <div className="media-body">
            <p className="media--aligned excerpt">
              <FormattedMessage
                message={this.getIntlMessage('proposal.infos.header')}
                user={<UserLink user={proposal.author} />}
                createdDate={createdDate}
              />
              {
                (moment(proposal.updated_at).diff(proposal.created_at, 'seconds') > 1)
                && <span>
                  {' • '}
                  <FormattedMessage
                    message={this.getIntlMessage('global.edited_on')}
                    updated={updatedDate}
                  />
                </span>
              }
              <ProposalVoteButtonWrapper
                step={step}
                proposal={proposal}
                creditsLeft={creditsLeft}
                onClick={onVote}
                className="visible-xs pull-right"
              />
            </p>
          </div>
        </div>
      </div>
    );
  },

});

export default ProposalPageHeader;
