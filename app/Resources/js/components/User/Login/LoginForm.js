import React, { PropTypes } from 'react';
import { IntlMixin } from 'react-intl';
import { Alert } from 'react-bootstrap';
import UserActions from '../../../actions/UserActions';
import DeepLinkStateMixin from '../../../utils/DeepLinkStateMixin';
import FlashMessages from '../../Utils/FlashMessages';
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
    if (nextProps.isSubmitting) {
      UserActions
        .login(this.state.form)
        .then(() => {
          this.setState(this.getInitialState());
          this.props.onSubmitSuccess();
        })
        .catch(() => {
          this.setState({hasError: true});
          this.props.onSubmitFailure();
        });
      return;
    }
  },

  render() {
    return (
      <form id="login-form" ref={form => this.form = form}>
        {this.state.hasError
        ? <Alert bsStyle="danger">
            <p>{this.getIntlMessage('global.login_failed')}</p>
          </Alert>
        : null
        }
        <Input
          type="text"
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
          label={
            <span>
              {this.getIntlMessage('global.password')}
              <span className="pull-right">
                <a href="/resetting/request">Mot de passe oublié ?</a>
              </span>
            </span>
          }
        />
      </form>
    );
  },

});

export default LoginForm;
