// @flow
import React, { PropTypes } from 'react';
import { IntlMixin } from 'react-intl';
import { connect } from 'react-redux';
import { Field, reduxForm } from 'redux-form';
import renderInput from '../Form/Field';
import { editOpinionVersion as onSubmit } from '../../redux/modules/opinion';
import type { State } from '../../types';

export const formName = 'opinion-version-edit';

const validate = ({ confirm, title, comment }) => {
  const errors = {};
  if (!confirm) {
    errors.confirm = 'global.required';
  }
  if (title) {
    if (title.length <= 2) {
      errors.title = 'opinion.version.title_error';
    }
  } else {
    errors.title = 'global.required';
  }
  if (comment) {
    if (comment.length <= 2) {
      errors.comment = 'opinion.version.comment_error';
    }
  } else {
    errors.comment = 'global.required';
  }
  return errors;
};

const OpinionVersionEditForm = React.createClass({
  propTypes: {
    versionId: PropTypes.string.isRequired,
  },
  mixins: [IntlMixin],

  render() {
    return (
      <form>
        <div className="alert alert-warning edit-confirm-alert">
          <Field
            name="confirm"
            type="checkbox"
            component={renderInput}
            label={this.getIntlMessage('opinion.version.confirm')}
          />
        </div>
        <Field
          name="title"
          type="text"
          component={renderInput}
          label={this.getIntlMessage('opinion.version.title')}
        />
        <Field
          name="body"
          type="editor"
          component={renderInput}
          label={this.getIntlMessage('opinion.version.body')}
          help={this.getIntlMessage('opinion.version.body_helper')}
        />
        <Field
          name="comment"
          type="editor"
          component={renderInput}
          label={this.getIntlMessage('opinion.version.comment')}
          help={this.getIntlMessage('opinion.version.comment_helper')}
        />
      </form>
    );
  },
});

export default connect((state: State) => ({
  initialValues: {
    title: state.opinion.currentVersionId && state.opinion.versionsById[state.opinion.currentVersionId].title,
    body: state.opinion.currentVersionId && state.opinion.versionsById[state.opinion.currentVersionId].body,
    comment: state.opinion.currentVersionId && state.opinion.versionsById[state.opinion.currentVersionId].comment,
  },
  opinionId: state.opinion.currentVersionId && state.opinion.versionsById[state.opinion.currentVersionId].parent.id,
  versionId: state.opinion.currentVersionId,
}))(reduxForm({
  form: formName,
  onSubmit,
  validate,
})(OpinionVersionEditForm));
