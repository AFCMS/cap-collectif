// @flow
import React from 'react';
import { FormattedMessage } from 'react-intl';
import { Field, reduxForm } from 'redux-form';
import { Alert } from 'react-bootstrap';
import renderInput from '../../Form/Field';
import { login as onSubmit } from '../../../redux/modules/user';

type LoginValues = {
  username: string,
  password: string,
};
const formName = 'login';

const initialValues: LoginValues = {
  username: '',
  password: '',
};

type Props = {
  error?: string,
};

export class LoginForm extends React.Component<Props> {
  render() {
    const { error } = this.props;
    return (
      <div className="form_no-bold-label">
        {error && (
          <Alert bsStyle="danger">
            <p>
              <FormattedMessage id={error} />
            </p>
          </Alert>
        )}
        <Field
          name="username"
          type="email"
          autoFocus
          disableValidation
          id="username"
          label={<FormattedMessage id="global.email" />}
          autoComplete="email"
          labelClassName="font-weight-normal"
          component={renderInput}
        />
        <Field
          name="password"
          type="password"
          autoFocus
          disableValidation
          id="password"
          label={<FormattedMessage id="global.password" />}
          labelClassName="w-100 font-weight-normal"
          autoComplete="current-password"
          component={renderInput}
        />
        <a href="/resetting/request">{<FormattedMessage id="global.forgot_password" />}</a>
      </div>
    );
  }
}

export default reduxForm({
  initialValues,
  onSubmit,
  form: formName,
  destroyOnUnmount: true,
})(LoginForm);
