import React, { PropTypes } from 'react';
import { IntlMixin, FormattedHTMLMessage } from 'react-intl';
import { connect } from 'react-redux';
import { Field, reduxForm } from 'redux-form';
import { isEmail } from '../../../services/Validator';
import type { State } from '../../../types';
import { register as onSubmit } from '../../../redux/modules/user';
import renderComponent from '../../Form/Field';

export const validate = (values, props) => {
  const errors = {};
  if (!values.username || values.username.length < 2) {
    errors.username = 'registration.constraints.username.min';
  }
  if (!values.email || !isEmail(values.email)) {
    errors.email = 'registration.constraints.email.invalid';
  }
  if (!values.plainPassword || values.plainPassword.length < 8) {
    errors.plainPassword = 'registration.constraints.password.min';
  }
  if (values.plainPassword && values.plainPassword.length > 72) {
    errors.plainPassword = 'registration.constraints.password.max';
  }
  if (!values.charte) {
    errors.charte = 'registration.constraints.charte.check';
  }
  if (!values.captcha && (window && window.location.host !== 'capco.test')) {
    errors.captcha = 'registration.constraints.captcha.invalid';
  }
  for (const field of props.dynamicFields) {
    if (field.required && !values[`dynamic-${field.id}`]) {
      errors[`dynamic-${field.id}`] = 'global.required';
    }
  }
  return errors;
};

export const form = 'registration-form';
export const RegistrationForm = React.createClass({
  propTypes: {
    addUserTypeField: PropTypes.bool.isRequired,
    addZipcodeField: PropTypes.bool.isRequired,
    userTypes: PropTypes.array.isRequired,
    cguLink: PropTypes.string.isRequired,
    cguName: PropTypes.string.isRequired,
    handleSubmit: PropTypes.func.isRequired,
    dynamicFields: PropTypes.array.isRequired,
  },
  mixins: [IntlMixin],

  render() {
    const {
      cguLink,
      cguName,
      dynamicFields,
      addZipcodeField,
      addUserTypeField,
      userTypes,
      handleSubmit,
     } = this.props;
    return (
      <form onSubmit={handleSubmit}>
        <Field
          name="username"
          id="username"
          component={renderComponent}
          type="text"
          label={this.getIntlMessage('registration.username')}
          labelClassName="h5"
        />
        <Field
          name="email"
          id="email"
          component={renderComponent}
          type="email"
          label={this.getIntlMessage('global.email')}
          labelClassName="h5"
          popover={{
            id: 'registration-email-tooltip',
            message: this.getIntlMessage('registration.tooltip.email'),
          }}
        />
        <Field
          name="plainPassword"
          id="password"
          component={renderComponent}
          type="password"
          label={this.getIntlMessage('registration.password')}
          labelClassName="h5"
          popover={{
            id: 'registration-password-tooltip',
            message: this.getIntlMessage('registration.tooltip.password'),
          }}
        />
        {
          addUserTypeField &&
            <Field
              id="user_type"
              name="userType"
              component={renderComponent}
              type="select"
              label={
                <span>
                  {this.getIntlMessage('registration.type')} <span className="excerpt">{this.getIntlMessage('global.form.optional')}</span>
                </span>
              }
              labelClassName="h5"
            >
              <option value="">{this.getIntlMessage('registration.select.type')}</option>
              {
                userTypes.map((type, i) => (<option key={i + 1} value={type.id}>{type.name}</option>))
              }
            </Field>
        }
        {
          addZipcodeField &&
            <Field
              id="zipcode"
              name="zipcode"
              component={renderComponent}
              type="text"
              label={
                <span>
                  {this.getIntlMessage('registration.zipcode')} <span className="excerpt">{this.getIntlMessage('global.form.optional')}</span>
                </span>
              }
              labelClassName="h5"
              autoComplete="postal-code"
            />
        }
        {
          dynamicFields.map((field, key) => (
            <Field
              id={field.id}
              key={key}
              name={`dynamic-${field.id}`}
              component={renderComponent}
              type={field.type}
              label={
                <span>
                  {field.question} {
                    !field.required && <span className="excerpt">{this.getIntlMessage('global.form.optional')}</span>
                  }
                </span>
              }
              labelClassName="h5"
            />
          ))
        }
        <Field
          id="charte"
          name="charte"
          component={renderComponent}
          type="checkbox"
          label={
            <FormattedHTMLMessage
              message={this.getIntlMessage('registration.charte')}
              link={<a className="external-link" href={cguLink}>{cguName}</a>}
            />
          }
          labelClassName="h5"
        />
        <Field
          id="captcha"
          component={renderComponent}
          name="captcha"
          type="captcha"
        />
      </form>
    );
  },
});

const mapStateToProps = (state: State) => ({
  addUserTypeField: state.default.features.user_type,
  addZipcodeField: state.default.features.user_type,
  userTypes: state.default.userTypes,
  cguName: state.default.parameters['signin.cgu.name'],
  cguLink: state.default.parameters['signin.cgu.link'],
  dynamicFields: state.user.registration_form_fields,
});

const connector = connect(mapStateToProps);
export default connector(reduxForm({
  form,
  validate,
  onSubmit,
})(RegistrationForm));
