import React, { PropTypes } from 'react';
import { IntlMixin, FormattedHTMLMessage } from 'react-intl';
import FormMixin from '../../../utils/FormMixin';
import DeepLinkStateMixin from '../../../utils/DeepLinkStateMixin';
import FlashMessages from '../../Utils/FlashMessages';
import ArrayHelper from '../../../services/ArrayHelper';
import Input from '../../Form/Input';
import Radio from '../../Form/Radio';
import Checkbox from '../../Form/Checkbox';
import Ranking from '../../Form/Ranking';
import ReplyActions from '../../../actions/ReplyActions';

const ReplyForm = React.createClass({
  propTypes: {
    form: PropTypes.object.isRequired,
    isSubmitting: PropTypes.bool.isRequired,
    onValidationFailure: PropTypes.func.isRequired,
    onSubmitSuccess: PropTypes.func.isRequired,
    onSubmitFailure: PropTypes.func.isRequired,
    reply: PropTypes.object,
    disabled: PropTypes.bool,
  },
  mixins: [IntlMixin, DeepLinkStateMixin, FormMixin],

  getDefaultProps() {
    return {
      reply: {
        responses: [],
      },
      disabled: false,
    };
  },

  getInitialState() {
    const { reply } = this.props;
    const form = {};
    this.props.form.fields.forEach((field) => {
      form[field.id] = field.type === 'checkbox' ? [] : '';

      reply.responses.map((response) => {
        form[response.field.id] = response.value;
      });

      let fieldRules = {};

      if (field.required) {
        if (field.type === 'checkbox' || field.type === 'ranking') {
          fieldRules = {
            notEmpty: { message: 'reply.constraints.field_mandatory' },
          };
        } else {
          fieldRules = {
            notBlank: { message: 'reply.constraints.field_mandatory' },
          };
        }
      }
      if (field.validationRule) {
        const rule = field.validationRule;
        switch (rule.type) {
          case 'min':
            fieldRules.min = {
              message: 'reply.constraints.choices_min',
              messageParams: { nb: rule.number },
              value: rule.number,
            };
            break;
          case 'max':
            fieldRules.max = {
              message: 'reply.constraints.choices_max',
              messageParams: { nb: rule.number },
              value: rule.number,
            };
            break;
          case 'equal':
            fieldRules.length = {
              message: 'reply.constraints.choices_equal',
              messageParams: { nb: rule.number },
              value: rule.number,
            };
            break;
          default:
            break;
        }
      }
      this.formValidationRules[field.id] = fieldRules;
    });

    return {
      form,
      errors: {},
      private: false,
    };
  },

  componentWillReceiveProps(nextProps) {
    const {
      onSubmitSuccess,
      onSubmitFailure,
      onValidationFailure,
      disabled,
      form,
      isSubmitting,
    } = this.props;
    if (!disabled && nextProps.isSubmitting && !isSubmitting) {
      if (this.isValid()) {
        const responses = [];
        const data = {};
        Object.keys(this.state.form).map((key) => {
          const response = { question: key };

          if (Array.isArray(this.state.form[key])) {
            let currentField = null;
            form.fields.map((field) => {
              if (String(field.id) === key) {
                currentField = field;
              }
            });

            const choicesLabels = [];
            currentField.choices.forEach((choice) => {
              choicesLabels.push(choice.label);
            });

            let other = null;
            this.state.form[key].map((value, i) => {
              if (choicesLabels.indexOf(value) === -1) {
                this.state.form[key].splice(i, 1);
                other = value;
              }
            });
            response.value = other ? { labels: this.state.form[key], other } : { labels: this.state.form[key] };
          } else {
            response.value = this.state.form[key];
          }
          responses.push(response);
        });

        data.responses = responses;
        if (form.anonymousAllowed) {
          data.private = this.state.private;
        }

        return ReplyActions
          .add(form.id, data)
          .then(onSubmitSuccess)
          .catch(onSubmitFailure);
      }
      onValidationFailure();
    }
  },

  onChange(field, value) {
    const form = this.state.form;
    if (field) {
      form[field.id] = value;
    }
    this.setState({
      form,
    });
  },

  getResponseForField(id) {
    const { reply } = this.props;
    const index = ArrayHelper.getElementIndexFromArray(
      reply.responses,
      { field: { id } },
      'field',
      'id'
    );
    if (index > -1) {
      return reply.responses[index].value;
    }
    return '';
  },

  emptyForm() {
    const form = {};
    this.props.form.fields.forEach((field) => {
      form[field.id] = (field.type === 'checkbox' || field.type === 'ranking') ? [] : '';
      if (field.type === 'checkbox' || field.type === 'radio' || field.type === 'ranking') {
        this[`field-${field.id}`].empty();
      }
    });
    this.setState({
      form,
      private: false,
    });
  },

  formValidationRules: {},

  renderFormErrors(field) {
    const errors = this.getErrorsMessages(field);
    if (errors.length === 0) {
      return null;
    }
    return <FlashMessages errors={errors} form />;
  },

  render() {
    const optional = this.getIntlMessage('global.form.optional');
    const {
      disabled,
      form,
    } = this.props;
    return (
      <form id="reply-form" ref="form">
        {
          form.description &&
          <div>
            <FormattedHTMLMessage message={form.description} />
            <hr />
          </div>
        }
        {
          form.fields.map((field) => {
            const key = field.slug;
            const inputType = field.type || 'text';

            switch (inputType) {
              case 'checkbox':
                return (
                  <Checkbox
                    key={key}
                    ref={c => this[`field-${field.id}`] = c}
                    id={`reply-${field.id}`}
                    field={field}
                    getGroupStyle={this.getGroupStyle}
                    renderFormErrors={this.renderFormErrors}
                    onChange={this.onChange}
                    values={this.state.form}
                    labelClassName="h4"
                    disabled={disabled}
                  />
                );

              case 'radio':
                return (
                  <Radio
                    key={key}
                    ref={c => this[`field-${field.id}`] = c}
                    id={`reply-${field.id}`}
                    field={field}
                    getGroupStyle={this.getGroupStyle}
                    renderFormErrors={this.renderFormErrors}
                    onChange={this.onChange}
                    labelClassName="h4"
                    disabled={disabled}
                  />
                );

              case 'select':
                return (
                  <Input
                    key={key}
                    ref={c => this[`field-${field.id}`] = c}
                    id={`reply-${field.id}`}
                    type={inputType}
                    label={field.question + (field.required ? '' : optional)}
                    help={field.helpText}
                    groupClassName={this.getGroupStyle(field.id)}
                    valueLink={this.linkState(`form.${field.id}`)}
                    errors={this.renderFormErrors(field.id)}
                    defaultValue=""
                    labelClassName="h4"
                    disabled={disabled}
                  >
                    <option value="" disabled>{this.getIntlMessage('global.select')}</option>
                    {
                      field.choices.map((choice) => {
                        return <option key={choice.id} value={choice.label}>{choice.label}</option>;
                      })
                    }
                  </Input>
                );

              case 'ranking':
                return (
                  <Ranking
                    key={key}
                    ref={c => this[`field-${field.id}`] = c}
                    id={`reply-${field.id}`}
                    field={field}
                    getGroupStyle={this.getGroupStyle}
                    renderFormErrors={this.renderFormErrors}
                    onChange={this.onChange}
                    labelClassName="h4"
                    disabled={disabled}
                  />
                );

              default:
                return (
                  <Input
                    ref={c => this[`field-${field.id}`] = c}
                    key={key}
                    id={`reply-${field.id}`}
                    type={inputType}
                    label={field.question + (field.required ? '' : optional)}
                    help={field.helpText}
                    groupClassName={this.getGroupStyle(field.id)}
                    valueLink={this.linkState(`form.${field.id}`)}
                    errors={this.renderFormErrors(field.id)}
                    placeholder={this.getIntlMessage('reply.your_response')}
                    labelClassName="h4"
                    disabled={disabled}
                  />
                );
            }
          })
        }
        {
          form.anonymousAllowed
            ? <div>
              <hr style={{ marginBottom: '30px' }} />
              <Input
                type="checkbox"
                name="reply-private"
                checkedLink={this.linkState('private')}
                label={this.getIntlMessage('reply.form.private')}
                disabled={disabled}
              />
            </div>
            : null
        }
      </form>
    );
  },

});

export default ReplyForm;
