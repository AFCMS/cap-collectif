import React, { PropTypes } from 'react';
import { IntlMixin } from 'react-intl';
import { Alert } from 'react-bootstrap';
import UserActions from '../../../actions/UserActions';
import DeepLinkStateMixin from '../../../utils/DeepLinkStateMixin';
import Input from '../../Form/Input';

const LoginForm = React.createClass({
  propTypes: {
    isSubmitting: PropTypes.bool.isRequired,
    onSubmitSuccess: PropTypes.func.isRequired,
    onSubmitFailure: PropTypes.func.isRequired,
  },
  mixins: [IntlMixin, DeepLinkStateMixin],

  getInitialState() {
    return {
      form: {
        _username: '',
        _password: '',
      },
      hasError: false,
    };
  },

  componentWillReceiveProps(nextProps) {
    const {
      onSubmitFailure,
      onSubmitSuccess,
    } = this.props;
    if (nextProps.isSubmitting) {
      UserActions
        .login(this.state.form)
        .then(() => {
          this.setState(this.getInitialState());
          onSubmitSuccess();
        })
        .catch(() => {
          this.setState({ hasError: true });
          onSubmitFailure();
        });
    }
  },

  render() {
    return (
      <div>
        {
          this.state.hasError
          ? <Alert bsStyle="danger">
              <p>{this.getIntlMessage('global.login_failed')}</p>
            </Alert>
          : null
        }
        <Input
          type="email"
          autoFocus
          valueLink={this.linkState('form._username')}
          id="_username"
          label={this.getIntlMessage('global.email')}
        />
        <Input
          type="password"
          id="_password"
          valueLink={this.linkState('form._password')}
          labelClassName="w100 h5"
          label={this.getIntlMessage('global.password')}
        />
        <a className="small" href="/resetting/request">{this.getIntlMessage('global.forgot_password')}</a>
      </div>
    );
  },

});

export default LoginForm;
