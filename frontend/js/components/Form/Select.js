// @flow
import * as React from 'react';
import { HelpBlock } from 'react-bootstrap';
import Select from 'react-select';
import Async from 'react-select/lib/Async';
import { FormattedMessage } from 'react-intl';
import debouncePromise from 'debounce-promise';

type Options = Array<{ value: string, label: string }>;
export type ReactSelectValue = { value: string, label: string };
type Value = string | Array<{ value: string }> | ReactSelectValue;
type OnChangeInput = Array<{ value: string }>;
type Props = {
  input: {
    name: string,
    value: Value,
    onBlur: () => void,
    onChange: (value: Value) => void,
    onFocus: () => void,
  },
  id: string,
  meta: { touched: boolean, error: ?string },
  label: string | React.Node,
  help: string | React.Node,
  placeholder?: string,
  autoload?: boolean,
  clearable?: boolean,
  disabled?: boolean,
  multi: boolean,
  options?: Options, // or loadOptions for async
  loadOptions?: () => Options, // or options for sync
  filterOption?: Function,
  onChange: () => void,
  labelClassName?: string,
  inputClassName?: string,
  selectFieldIsObject?: boolean,
  debounce?: boolean, // add delay in async load
  debounceMs?: number,
  cacheOptions?: boolean,
};

const ClearIndicator = props => {
  const {
    // eslint-disable-next-line react/prop-types
    innerProps: { ref, ...restInnerProps },
  } = props;
  return (
    <div role="button" className="select__clear-zone" {...restInnerProps} ref={ref}>
      <i className="cap cap-times mr-10 ml-10" />
    </div>
  );
};

class renderSelect extends React.Component<Props> {
  myRef: any;

  debouncedLoadOptions: any;

  static defaultProps = {
    multi: false,
    disabled: false,
    autoload: false,
    debounce: false,
    debounceMs: 1300,
    cacheOptions: false,
    clearable: true,
  };

  constructor(props: Props) {
    super(props);
    const { loadOptions, debounceMs } = props;
    this.myRef = React.createRef();

    this.debouncedLoadOptions = debouncePromise(loadOptions, debounceMs, {
      leading: true,
    });
  }

  clearValues = () => {
    // sometimes the default options remain selected in async, we have to do this to reset the input
    this.myRef.current.state.defaultOptions = [];
  };

  render() {
    const {
      onChange,
      input,
      label,
      labelClassName,
      inputClassName,
      multi,
      options,
      disabled,
      autoload,
      clearable,
      placeholder,
      loadOptions,
      filterOption,
      debounce,
      selectFieldIsObject,
      id,
      help,
      cacheOptions,
      meta: { touched, error },
    } = this.props;
    const { name, value, onBlur, onFocus } = input;

    let selectValue = null;
    let selectLabel = null;

    if (typeof loadOptions === 'function') {
      selectValue = value;
    } else if (multi) {
      selectLabel =
        options &&
        options.filter(option => Array.isArray(value) && value.some(o => o.value === option.value));
      selectValue = value !== undefined || value !== null ? selectLabel && selectLabel : [];
    } else if (Object.prototype.hasOwnProperty.call(value, 'value')) {
      // Here, we are dealing with a select question that uses `react-select`.
      // React select option choice must have the shape { value: xxx, label: xxx } in Redux to work
      // See https://www.firehydrant.io/blog/using-react-select-with-redux-form/ (part: `Other Gotchas`)
      selectLabel =
        options &&
        options.filter(
          option =>
            option && option.value && option.value === ((value: any): ReactSelectValue).value,
        );
      selectValue = value && value.value ? selectLabel && selectLabel[0] : null;
    } else {
      selectLabel =
        options && options.filter(option => option && option.value && option.value === value);
      selectValue = value !== undefined || value !== null ? selectLabel && selectLabel[0] : null;
    }

    return (
      <div className={`form-group ${touched && error ? ' has-error' : ''}`}>
        {label && (
          <label htmlFor={id} className={labelClassName || 'control-label'}>
            {label}
          </label>
        )}
        {help && <HelpBlock>{help}</HelpBlock>}
        <div id={id} className={inputClassName || ''}>
          {typeof loadOptions === 'function' ? (
            <Async
              filterOption={filterOption}
              ref={this.myRef}
              components={{ ClearIndicator }}
              isDisabled={disabled}
              defaultOptions={autoload}
              isClearable={clearable}
              placeholder={
                placeholder || <FormattedMessage id="admin.fields.menu_item.parent_empty" />
              }
              loadOptions={
                debounce ? inputValue => this.debouncedLoadOptions(inputValue) : loadOptions
              }
              cacheOptions={cacheOptions}
              value={selectValue || ''}
              className="react-select-container"
              classNamePrefix="react-select"
              name={name}
              isMulti={multi}
              noOptionsMessage={() => <FormattedMessage id="result-not-found" />}
              loadingMessage={() => <FormattedMessage id="global.loading" />}
              onBlur={() => onBlur()}
              onFocus={onFocus}
              onChange={(newValue: OnChangeInput) => {
                if (typeof onChange === 'function') {
                  onChange();
                }
                if ((multi && Array.isArray(newValue)) || selectFieldIsObject) {
                  input.onChange(newValue);
                  return;
                }
                if (!Array.isArray(newValue)) {
                  input.onChange(newValue ? newValue.value : '');
                }
              }}
            />
          ) : (
            <Select
              name={name}
              components={{ ClearIndicator }}
              isDisabled={disabled}
              className="react-select-container"
              classNamePrefix="react-select"
              options={options}
              filterOption={filterOption}
              onBlurResetsInput={false}
              onCloseResetsInput={false}
              placeholder={
                placeholder || <FormattedMessage id="admin.fields.menu_item.parent_empty" />
              }
              isClearable={clearable}
              isMulti={multi}
              value={selectValue || ''}
              noOptionsMessage={() => <FormattedMessage id="result-not-found" />}
              loadingMessage={() => <FormattedMessage id="global.loading" />}
              onBlur={() => onBlur()}
              onFocus={onFocus}
              onChange={(newValue: OnChangeInput) => {
                if (typeof onChange === 'function') {
                  onChange();
                }
                if ((multi && Array.isArray(newValue)) || selectFieldIsObject) {
                  return input.onChange(newValue);
                }
                if (!Array.isArray(newValue)) {
                  input.onChange(newValue ? newValue.value : '');
                }
              }}
            />
          )}
          {touched && error && (
            <span className="error-block">
              <FormattedMessage id={error} />
            </span>
          )}
        </div>
      </div>
    );
  }
}

export default renderSelect;
