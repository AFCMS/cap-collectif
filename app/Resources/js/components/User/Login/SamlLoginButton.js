// @flow
import React, { PropTypes } from 'react';
import { FormattedMessage } from 'react-intl';
import { connect } from 'react-redux';
import type { State } from '../../../types';

export const SamlLoginButton = React.createClass({
  displayName: 'SamlLoginButton',

  propTypes: {
    show: PropTypes.bool.isRequired,
  },

  render() {
    const { show } = this.props;
    if (!show) {
      return null;
    }
    const title = <FormattedMessage id="login.saml" />;
    return (
      <a
        href={`/login-saml?_destination=${window && window.location.href}`}
        title={title}
        className="btn login__social-btn login__social-btn--saml">
        {title}
      </a>
    );
  },
});

const mapStateToProps = (state: State) => ({
  show: state.default.features.login_saml,
});

export default connect(mapStateToProps)(SamlLoginButton);
