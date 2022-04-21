// @flow
import React, { PropTypes } from 'react';
import { FormattedHTMLMessage, FormattedMessage } from 'react-intl';
import { connect, type MapStateToProps } from 'react-redux';
import { Alert, Button } from 'react-bootstrap';
import { resendConfirmation } from '../../redux/modules/user';
import type { State } from '../../types';

export const NewEmailNotConfirmedAlert = React.createClass({
  propTypes: {
    newEmailToConfirm: PropTypes.string,
    sendSucceed: PropTypes.bool,
  },

  render() {
    const { sendSucceed, newEmailToConfirm } = this.props;
    if (!newEmailToConfirm) {
      return null;
    }
    const editEmailUrl = `${window.location.protocol}//${
      window.location.host
    }/profile/edit-account`;
    return (
      <Alert bsStyle="warning" id="alert-new-email-not-confirmed">
        <div className="container">
          <div className="col-md-7" style={{ marginBottom: 5 }}>
            {sendSucceed && (
              <FormattedHTMLMessage
                id="user.confirm.new_email_send_succeed"
                values={{ email: newEmailToConfirm }}
              />
            )}
          </div>
          <div className="col-md-5">
            <Button
              style={{ marginRight: 15, marginBottom: 5 }}
              onClick={() => resendConfirmation()}>
              <FormattedMessage id="user.confirm.resend" />
            </Button>
            <Button bsStyle="link" style={{ marginBottom: 5 }} href={editEmailUrl}>
              {sendSucceed && (
                <FormattedMessage id="user.confirm.new_email_send_succeed_cancel_or_update" />
              )}
            </Button>
          </div>
        </div>
      </Alert>
    );
  },
});

const mapStateToProps: MapStateToProps<*, *, *> = (state: State) => ({
  newEmailToConfirm: state.user.user && state.user.user.newEmailToConfirm,
  sendSucceed: true,
});

export default connect(mapStateToProps)(NewEmailNotConfirmedAlert);
