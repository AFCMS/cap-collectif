// @flow
import * as React from 'react';
import { connect } from 'react-redux';
import {FieldArray, formValueSelector} from 'redux-form';
import { FormattedMessage } from 'react-intl';
import { ListGroup, Button } from 'react-bootstrap';
import type {MapStateToProps} from "react-redux";
import QuestionJumpConditionsAdminForm from './QuestionJumpConditionsAdminForm';
import type {GlobalState} from "../../types";

type Props = {
  fields: { length: number, map: Function, remove: Function, push: Function },
  formName: string,
  oldMember: string,
  currentQuestion: Object
};

export class QuestionsJumpAdminForm extends React.Component<Props> {
  render() {
    const { fields, formName, currentQuestion } = this.props;
    return (
      <div className="form-group" id="questions_choice_panel_personal">
        <ListGroup>
          {fields.map((member, index) => (
            <div className="panel-custom panel panel-default">
              <div className="panel-heading">
                <i className="cap cap-android-menu" style={{ color: 'rgb(3, 136, 204)', fontSize:'20px' }} />
                <h3 className="panel-title">
                  <FormattedMessage id="answering-this-question" />
                </h3>
                <button type="button" style={{ border: 'none', fontSize:'20px', backgroundColor:'#f5f5f5' }} title="Remove Member" onClick={() => fields.remove(index)}>
                  X
                </button>
              </div>
              <div className="panel-body">
                <FieldArray
                  name={`${member}.conditions`}
                  component={QuestionJumpConditionsAdminForm}
                  formName={formName}
                  member={member}
                />
              </div>
            </div>
          ))}
        </ListGroup>
        <Button
          bsStyle="primary"
          className="btn--outline box-content__toolbar"
          onClick={() => {
            fields.push(
              {
                always: false,
                origin: {
                  id: currentQuestion.id
                },
                conditions: [
                  {
                    question: {
                      id: currentQuestion.id
                    },
                    value: currentQuestion.questionChoices[0],
                    operator: 'IS'
                  },
                ],
                destination: {
                  id: currentQuestion.id
                }
              }
            );
          }}>
          <i className="fa fa-plus-circle" /> <FormattedMessage id="global.add" />
        </Button>
      </div>
    );
  }
}

const mapStateToProps: MapStateToProps<*, *, *> = (state: GlobalState, props: Props) => {
  const selector = formValueSelector(props.formName);
  return {
    currentQuestion: selector(state, `${props.oldMember}`),
  };
};

export default connect(mapStateToProps)(QuestionsJumpAdminForm);
