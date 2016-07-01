import React, { PropTypes } from 'react';
import { IntlMixin } from 'react-intl';
import { reduxForm, Field as ReduxFormField } from 'redux-form';
import Field from '../../Form/Field';

export const validate = values => {
  const errors = {};
  if (!values.title || values.title.length < 2) {
    errors.title = 'opinion.constraints.title';
  }
  if (!values.body || values.body.length < 2) {
    errors.body = 'opinion.constraints.body';
  }
  return errors;
};

export const labels = {
  title: 'opinion.title',
  body: 'opinion.body',
};

const OpinionForm = React.createClass({
  propTypes: {
    fields: PropTypes.array.isRequired,
    handleSubmit: PropTypes.func.isRequired,
  },
  mixins: [IntlMixin],

  render() {
    const {
      fields,
      handleSubmit,
    } = this.props;
    return (
      <form id="opinion-form" onSubmit={handleSubmit}>
      {
        fields.map((field, index) => {
          return (
            <ReduxFormField
              key={index}
              autoFocus={index === 0}
              label={labels[field.name] ? this.getIntlMessage(labels[field.name]) : field.name}
              name={field.name}
              type={field.type}
              component={Field}
              defaultValue={field.defaultValue || ''}
              placeholder={field.name}
            />
          );
        })
      }
      </form>
    );
  },

});

export default reduxForm({
  validate,
})(OpinionForm);
