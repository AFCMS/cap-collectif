import React, { PropTypes } from 'react';
import { IntlMixin } from 'react-intl';
import { connect } from 'react-redux';
import type { State } from '../../../types';

export const SamlLoginButton = React.createClass({
  displayName: 'SamlLoginButton',
  propTypes: {
    show: PropTypes.bool.isRequired,
  },
  mixins: [IntlMixin],

  render() {
    const { show } = this.props;
    if (!show) {
      return null;
    }
    const title = this.getIntlMessage('login.saml');
    return (
      <a
       href={`/login-saml?_destination=${window.location.href}`}
       title={title}
       className="btn login__social-btn login__social-btn--saml"
      >
        {title}
      </a>
    );
  },

});

const mapStateToProps = (state: State) => {
  return {
    show: state.default.features.login_saml,
  };
};

export default connect(mapStateToProps)(SamlLoginButton);
