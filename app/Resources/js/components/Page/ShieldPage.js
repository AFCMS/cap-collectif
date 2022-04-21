// @flow
import React, { PropTypes } from 'react';
import { IntlMixin } from 'react-intl';
import { submit, isSubmitting } from 'redux-form';
import { connect } from 'react-redux';
import { Button } from 'react-bootstrap';
import type { Connector } from 'react-redux';
import type { Dispatch, State } from '../../types';
import LoginButton from '../User/Login/LoginButton';
import LoginBox from '../User/Login/LoginBox';
import RegistrationButton from '../User/Registration/RegistrationButton';

type Props = {
  showRegistration: boolean,
  submitting: boolean,
  onSubmit: () => void
};
export const ShieldPage = React.createClass({
  propTypes: {
    showRegistration: PropTypes.bool.isRequired,
    submitting: PropTypes.bool.isRequired,
    onSubmit: PropTypes.func.isRequired,
  },
  mixins: [IntlMixin],

  render() {
    const { showRegistration, submitting, onSubmit }: Props = this.props;
    if (showRegistration) {
      return (
        <div style={{ background: 'white' }} className="col-md-4 col-md-offset-4 panel panel-default">
          <div className="panel-body">
            <LoginButton className="btn--connection btn-block" />
            <div style={{ marginTop: 10 }} />
            <RegistrationButton className="btn-block" />
          </div>
        </div>
      );
    }
    return (
      <div style={{ background: 'white' }} className="col-md-4 col-md-offset-4 panel panel-default">
        <div className="panel-body">
          <form id="login-form" onSubmit={onSubmit}>
            <LoginBox />
            <Button
              id="confirm-login"
              type="submit"
              style={{ marginTop: 10 }}
              className="btn-block btn-success"
              disabled={submitting}
              bsStyle="primary"
            >
              {
                submitting
                ? this.getIntlMessage('global.loading')
                : this.getIntlMessage('global.login_me')
              }
            </Button>
          </form>
        </div>
      </div>
    );
  },

});

const mapStateToProps = (state: State) => ({
  showRegistration: state.default.features.registration,
  submitting: isSubmitting('login')(state),
});
const mapDispatchToProps = (dispatch: Dispatch) => ({
  onSubmit: (e: Event) => {
    e.preventDefault();
    dispatch(submit('login'));
  },
});
const connector: Connector<{}, Props> = connect(mapStateToProps, mapDispatchToProps);
export default connector(ShieldPage);
