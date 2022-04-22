// @flow
import * as React from 'react';
import { FormattedMessage, injectIntl, type IntlShape } from 'react-intl';
import { connect, type MapStateToProps } from 'react-redux';
import { reduxForm, Field, FieldArray, type FormProps } from 'redux-form';
import { ButtonToolbar, Button } from 'react-bootstrap';
import { createFragmentContainer, graphql } from 'react-relay';
import AlertForm from '../Alert/AlertForm';
import component from '../Form/Field';
import UpdateQuestionnaireConfigurationMutation from '../../mutations/UpdateQuestionnaireConfigurationMutation';
import ProposalFormAdminQuestions from '../ProposalForm/ProposalFormAdminQuestions';
import type { QuestionnaireAdminConfigurationForm_questionnaire } from './__generated__/QuestionnaireAdminConfigurationForm_questionnaire.graphql';
import type { State, FeatureToggles } from '../../types';

type RelayProps = { questionnaire: QuestionnaireAdminConfigurationForm_questionnaire };
type Props = RelayProps &
  FormProps & {
    intl: IntlShape,
    features: FeatureToggles,
  };

export type Jumps = $ReadOnlyArray<{|
  +id: string,
  +always: boolean,
  +origin: ?Object,
  +destination: ?Object,
  +conditions: ?Object,
|}>;
export type MultipleChoiceQuestionValidationRulesTypes = 'EQUAL' | 'MAX' | 'MIN';
export type QuestionChoiceColor = 'DANGER' | 'INFO' | 'PRIMARY' | 'SUCCESS' | 'WARNING';
export type QuestionTypeValue =
  | 'button'
  | 'checkbox'
  | 'editor'
  | 'medias'
  | 'radio'
  | 'ranking'
  | 'select'
  | 'text'
  | 'textarea';
type FormValues = {
  questionnaireId: string,
  title: string,
  description: ?string,
  questions: $ReadOnlyArray<{|
    id: string,
    title: string,
    private: boolean,
    required: boolean,
    helpText: ?string,
    description: ?string,
    type: QuestionTypeValue,
    isOtherAllowed?: boolean,
    isRandomQuestionChoices?: boolean,
    validationRule?: ?{|
      type: MultipleChoiceQuestionValidationRulesTypes,
      number: number,
    |},
    jumps: Jumps,
    questionChoices?: ?$ReadOnlyArray<{|
      id: string,
      title: string,
      description: ?string,
      color: ?QuestionChoiceColor,
      image: ?{|
        id: string,
        url: string,
        name: string,
        size: number,
      |},
    |}>,
  |}>,
};
const formName = 'questionnaire-admin-configuration';
const multipleChoiceQuestions = ['button', 'radio', 'select', 'checkbox', 'ranking'];

const validate = (values: FormValues) => {
  const errors = {};
  if (!values.title || values.title.length <= 2) {
    errors.title = 'title';
  }

  if (values.questions.length) {
    const questionsArrayErrors = [];
    values.questions.forEach((question: Object, questionIndex: number) => {
      const questionErrors = {};
      if (!question.title || question.title.length === 0) {
        questionErrors.title = 'admin.fields.questionnaire.title';
        questionsArrayErrors[questionIndex] = questionErrors;
      }

      if (!question.type || question.type.length === 0) {
        questionErrors.type = 'admin.fields.proposal_form.errors.question.type';
        questionsArrayErrors[questionIndex] = questionErrors;
      }
    });

    if (questionsArrayErrors.length) {
      errors.questions = questionsArrayErrors;
    }
  }

  return errors;
};

const onSubmit = (values: FormValues, dispatch: Dispatch, props: Props) => {
  const input = {
    ...values,
    id: undefined,
    questionnaireId: props.questionnaire.id,
    questions: values.questions.map(question => {
      const questionInput = {
        question: {
          ...question,
          kind: undefined,
          otherAllowed: question.isOtherAllowed,
          randomQuestionChoices: question.isRandomQuestionChoices,
          isOtherAllowed: undefined,
          isRandomQuestionChoices: undefined
        },
      };
      if (multipleChoiceQuestions.indexOf(question.type) !== -1 && question.questionChoices) {
        questionInput.question.questionChoices = question.questionChoices.map(choice => ({
          ...choice,
          kind: undefined,
          image: choice.image ? choice.image.id : null
        }));
        if(question.jumps) {
          questionInput.question.jumps = question.jumps.map(jump => ({
            ...jump,
            origin: parseInt(jump.origin.id, 10),
            destination: parseInt(jump.destination.id, 10),
            conditions: jump.conditions.map(condition => ({
              ...condition,
              question: parseInt(condition.question.id, 10),
              value: condition.value.id
            }))
          }))
        }
      }

      return questionInput;
    }),
  };

  // $FlowFixMe
  return UpdateQuestionnaireConfigurationMutation.commit({ input });
};

export class QuestionnaireAdminConfigurationForm extends React.Component<Props> {
  render() {
    const {
      intl,
      invalid,
      pristine,
      valid,
      handleSubmit,
      submitting,
      submitSucceeded,
      submitFailed,
    } = this.props;

    return (
      <div className="box box-primary container-fluid">
        <div className="box-header">
          <h3 className="box-title">
            <FormattedMessage id="admin.label.settings.global" />
          </h3>
          <a
            className="pull-right link"
            rel="noopener noreferrer"
            href={intl.formatMessage({ id: 'admin.help.link.form.configuration' })}>
            <i className="fa fa-info-circle" /> <FormattedMessage id="global.help" />
          </a>
        </div>
        <div className="box-content">
          <form onSubmit={handleSubmit}>
            <Field
              name="title"
              component={component}
              type="text"
              id="questionnaire_title"
              label={<FormattedMessage id="admin.fields.questionnaire.title" />}
            />
            <Field
              name="description"
              component={component}
              type="editor"
              id="proposal_form_description"
              label={<FormattedMessage id="admin.fields.questionnaire.description" />}
            />
            <div className="box-header">
              <h3 className="box-title">
                <FormattedMessage id="proposal.admin.content" />
              </h3>
            </div>
            <FieldArray
              name="questions"
              component={ProposalFormAdminQuestions}
              formName={formName}
            />
            <ButtonToolbar className="box-content__toolbar">
              <Button
                disabled={invalid || pristine || submitting}
                id="parameters-submit"
                type="submit"
                bsStyle="primary">
                <FormattedMessage id={submitting ? 'global.loading' : 'global.save'} />
              </Button>
              <Button bsStyle="danger" disabled>
                <FormattedMessage id="global.delete" />
              </Button>
              <AlertForm
                valid={valid}
                invalid={invalid}
                submitSucceeded={submitSucceeded}
                submitFailed={submitFailed}
                submitting={submitting}
              />
            </ButtonToolbar>
          </form>
        </div>
      </div>
    );
  }
}

const form = reduxForm({
  validate,
  onSubmit,
  form: formName,
  enableReinitialize: true,
})(QuestionnaireAdminConfigurationForm);

const mapStateToProps: MapStateToProps<*, *, *> = (state: State, props: RelayProps) => ({
  initialValues: { ...props.questionnaire, id: undefined },
});

const container = connect(mapStateToProps)(form);
const intlContainer = injectIntl(container);

export default createFragmentContainer(
  intlContainer,
  graphql`
    fragment QuestionnaireAdminConfigurationForm_questionnaire on Questionnaire {
      id
      title
      description
      questions {
        id
        title
        helpText
        description
        type
        private
        required
        jumps {
          id
          always
          origin {
            id
            title
          }
          destination {
            id
            title
          }
          conditions {
            id
            operator
            question {
              id
              title
            }
            ... on MultipleChoiceQuestionLogicJumpCondition {
              value {
                id
                title
              }
            }
          }
        }
        kind
        ... on MultipleChoiceQuestion {
          isRandomQuestionChoices
          isOtherAllowed
          validationRule {
            type
            number
          }
          questionChoices {
            id
            title
            description
            color
            image {
              id
              url
              name
              size
            }
          }
        }
      }
    }
  `,
);
