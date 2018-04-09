// @flow
import * as React from 'react';
import { FormattedMessage } from 'react-intl';
import { Row, Col } from 'react-bootstrap';
import { connect, type MapStateToProps } from 'react-redux';
import ProposalVoteForm from './ProposalVoteForm';
import LoginButton from '../../User/Login/LoginButton';
import ProposalVoteBoxMessage from './ProposalVoteBoxMessage';
import { VOTE_TYPE_SIMPLE } from '../../../constants/ProposalConstants';
import RegistrationButton from '../../User/Registration/RegistrationButton';
import type { State } from '../../../types';

type Props = {
  proposal: Object,
  step: Object,
  creditsLeft: number,
  className: string,
  formWrapperClassName: string,
  isSubmitting: boolean,
  user: ?Object,
  features: Object,
};

class ProposalVoteBox extends React.Component<Props> {
  static defaultProps = {
    creditsLeft: null,
    className: '',
    formWrapperClassName: '',
    user: null,
  };

  userHasEnoughCredits = () => {
    const {
      creditsLeft,
      proposal,
      user,
      // step,
    } = this.props;
    if (user && creditsLeft !== null && proposal.estimation !== null) {
      return creditsLeft >= proposal.estimation;
    }
    return true;
  };

  displayForm = () => {
    const { step, user } = this.props;
    return step.voteType === VOTE_TYPE_SIMPLE || (user && this.userHasEnoughCredits());
  };

  render() {
    const {
      className,
      formWrapperClassName,
      isSubmitting,
      proposal,
      step,
      user,
      features,
    } = this.props;
    return (
      <div className={className} id="proposal-vote-box">
        {!user &&
          step.open && (
            <div>
              <p className="text-center small" style={{ fontWeight: 'bold' }}>
                {features.vote_without_account ? (
                  <FormattedMessage id="proposal.vote.authenticated" />
                ) : (
                  <FormattedMessage id="proposal.vote.please_authenticate" />
                )}
              </p>
              {!features.login_paris ? (
                <Row>
                  <Col xs={12} sm={6}>
                    <RegistrationButton className="btn-block" buttonStyle={{ margin: '0' }} />
                  </Col>
                  <Col xs={12} sm={6}>
                    <LoginButton className="btn-darkest-gray btn-block btn--connection" />
                  </Col>
                </Row>
              ) : (
                <p>
                  <LoginButton className="btn-darkest-gray btn-block btn--connection" />
                </p>
              )}
              {features.vote_without_account && (
                <p className="excerpt p--lined">
                  <span>{<FormattedMessage id="global.or" />}</span>
                </p>
              )}
            </div>
          )}
        {!user &&
          features.vote_without_account && (
            <p className="text-center small" style={{ marginBottom: '0', fontWeight: 'bold' }}>
              {<FormattedMessage id="proposal.vote.non_authenticated" />}
            </p>
          )}
        <div className={formWrapperClassName}>
          {(user || features.vote_without_account) &&
            this.displayForm() && <ProposalVoteForm proposal={proposal} step={step} />}
          <ProposalVoteBoxMessage
            enoughCredits={this.userHasEnoughCredits()}
            submitting={isSubmitting}
            step={step}
          />
        </div>
      </div>
    );
  }
}

const mapStateToProps: MapStateToProps<*, *, *> = (state: State) => ({
  features: state.default.features,
});

export default connect(mapStateToProps)(ProposalVoteBox);
