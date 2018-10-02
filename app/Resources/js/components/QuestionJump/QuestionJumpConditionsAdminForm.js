// @flow
import * as React from 'react';
import { connect, type MapStateToProps } from 'react-redux';
import { formValueSelector, Field, FieldArray } from 'redux-form';
import { FormattedMessage } from 'react-intl';
import { Button } from 'react-bootstrap';
import type { GlobalState } from '../../types';
import component from '../Form/Field';
import QuestionJumpConditionAdminForm from './QuestionJumpConditionAdminForm';

type Props = {
  fields: { length: number, map: Function, remove: Function, push: Function },
  questions: Object,
  formName: string,
  member: string,
  currentJump: Object,
};

export class QuestionJumpConditionsAdminForm extends React.Component<Props> {
  render() {
    const { fields, questions, member, formName, currentJump } = this.props;
    const arrayQuestions = [];
    questions.map(question => {
        arrayQuestions[question.id] = question.questionChoices;
    });

    return (
      <div className="form-group" id="questions_choice_panel_personal">
        {fields.map((memberConditions, index) => {
          return (
            <FieldArray
              component={QuestionJumpConditionAdminForm}
              questions={questions}
              member={memberConditions}
              formName={formName}
              index={index}
              oldMember={member}
            />
          );
        })}
          <div>
            <Button
              bsStyle="primary"
              className="btn--outline box-content__toolbar"
              onClick={() => {
                fields.push({
                  question: {
                    id: currentJump.origin.id
                  },
                  value: {
                    id: undefined
                  },
                  operator: 'IS'
                });
              }}>
              <i className="fa fa-plus-circle" /> <FormattedMessage id="global.add" />
            </Button>
            {fields.length > 0 && (
              <div>
                <p style={{ marginTop: 5 }}>
                  <b>
                    <FormattedMessage id="then-go-to" />
                  </b>
                </p>
                <Field
                  id={`${member}.destination.id`}
                  name={`${member}.destination.id`}
                  normalize={val => val && parseInt(val, 10)}
                  type="select"
                  component={component}>
                  {questions.map((question, questionIndex) => {
                    return (
                      <option value={question.id}>
                        {questionIndex}. {question.title}
                      </option>
                    );
                  })}
                </Field>
              </div>
              )}
          </div>
      </div>
    );
  }
}

const mapStateToProps: MapStateToProps<*, *, *> = (state: GlobalState, props: Props) => {
  const selector = formValueSelector(props.formName);
  return {
    questions: selector(state, 'questions'),
    currentJump: selector(state, `${props.member}`),
  };
};

export default connect(mapStateToProps)(QuestionJumpConditionsAdminForm);
