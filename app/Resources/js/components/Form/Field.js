// @flow
import React from 'react';
import { FormattedMessage } from 'react-intl';
import Input from './Input';

type Props = {
  meta: {
    touched: boolean,
    dirty?: boolean,
    pristine?: boolean,
    error?: any,
  },
  labelClassName?: string,
  divClassName?: string,
  wrapperClassName?: string,
  help?: string,
  description?: string,
  formName?: string,
  autoComplete?: string,
  disableValidation?: boolean,
  type:
    | 'address'
    | 'text'
    | 'number'
    | 'datetime'
    | 'textarea'
    | 'editor'
    | 'select'
    | 'checkbox'
    | 'password'
    | 'captcha'
    | 'email'
    | 'radio-buttons'
    | 'image'
    | 'medias'
    | 'ranking'
    | 'radio'
    | 'button',
  addonAfter?: any,
  addonBefore?: any,
  label?: any,
  placeholder?: string,
  disabled?: boolean,
  isOtherAllowed?: boolean,
  image?: string,
  children?: any,
  id: string,
  popover?: Object,
  choices?: Array<$FlowFixMe>,
  radioChecked?: boolean,
  input: {
    name: string,
    autoFocus?: boolean,
    onChange?: Function,
    onBlur?: Function,
    value?: any,
    disableValidation?: boolean,
  },
  style?: Object,
  radioImage?: Object,
};

class Field extends React.Component<Props> {
  render() {
    const { touched, error, dirty } = this.props.meta;
    const {
      popover,
      children,
      id,
      autoComplete,
      disableValidation,
      placeholder,
      type,
      label,
      divClassName,
      wrapperClassName,
      labelClassName,
      disabled,
      help,
      description,
      formName,
      addonAfter,
      addonBefore,
      choices,
      isOtherAllowed,
      style,
      radioImage,
      radioChecked,
      returnValue,
    } = this.props;
    const { autoFocus, name } = this.props.input;
    const check = touched || (dirty && !disableValidation);

    let errorMessage = null;

    if (check && error) {
      if (error.id) {
        errorMessage = <FormattedMessage id={error.id} values={error.values} />;
      } else {
        errorMessage = <FormattedMessage id={error} />;
      }
    }

    const input = (
      <Input
        id={id}
        type={type}
        name={name}
        help={help}
        description={description}
        formName={formName}
        disabled={disabled}
        popover={popover}
        addonAfter={addonAfter}
        image={radioImage ? radioImage.url : null}
        addonBefore={addonBefore}
        isOtherAllowed={isOtherAllowed}
        wrapperClassName={wrapperClassName || ''}
        labelClassName={labelClassName || ''}
        label={label || null}
        placeholder={placeholder || null}
        errors={errorMessage}
        validationState={check ? (error ? 'error' : 'success') : null}
        hasFeedback={check}
        autoComplete={autoComplete}
        autoFocus={autoFocus || false}
        choices={choices}
        style={style}
        radioChecked={radioChecked}
        returnValue={returnValue}
        {...this.props.input}>
        {children}
      </Input>
    );
    if (divClassName) {
      return <div className={divClassName}>{input}</div>;
    }
    return input;
  }
}

export default Field;
