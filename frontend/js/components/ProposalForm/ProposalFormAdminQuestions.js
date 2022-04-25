// @flow
import * as React from 'react';
import { connect } from 'react-redux';
import { formValueSelector, arrayPush, arrayMove } from 'redux-form';
import { FormattedMessage, injectIntl, type IntlShape } from 'react-intl';
import styled, { type StyledComponent } from 'styled-components';
// TODO https://github.com/cap-collectif/platform/issues/7774
// eslint-disable-next-line no-restricted-imports
import { ListGroup, ListGroupItem, Button, ButtonToolbar } from 'react-bootstrap';
import {
  DragDropContext,
  Droppable,
  Draggable,
  DropResult,
  DraggableProvided,
  DraggableStyle,
} from 'react-beautiful-dnd';
import ProposalFormAdminQuestionModal from './ProposalFormAdminQuestionModal';
import type { GlobalState, Dispatch } from '../../types';
import { ProposalFormAdminDeleteQuestionModal } from './ProposalFormAdminDeleteQuestionModal';
import { QuestionAdmin } from '../Question/QuestionAdmin';
import SectionQuestionAdminModal from '../Question/SectionQuestionAdminModal';
import FlashMessages from '../Utils/FlashMessages';
import type { QuestionTypeValue } from '~relay/ProposalFormAdminConfigurationForm_proposalForm.graphql';

type Props = {
  dispatch: Dispatch,
  fields: { length: number, map: Function, remove: Function },
  questions: Array<Object>,
  hideSections: boolean,
  formName: string,
  intl: IntlShape,
};

type State = {
  editIndex: ?number,
  editIndexSection: ?number,
  showDeleteModal: boolean,
  deleteIndex: ?number,
  deleteType: ?string,
  flashMessages: Array<string>,
  isDragging: boolean,
};

const DraggableWrapper: StyledComponent<{}, {}, HTMLDivElement> = styled.div`
  height: 60px !important;

  @media (max-width: 1035px) {
    height: 90px !important;
  }
`;

const DraggableContainer: StyledComponent<{}, {}, ListGroupItem> = styled(ListGroupItem)`
  height: 60px !important;

  @media (max-width: 1035px) {
    height: 90px !important;
  }
`;

const AddSectionButtonToolbar: StyledComponent<{ isDragging: boolean }, {}, ButtonToolbar> = styled(
  ButtonToolbar,
)`
  margin-top: ${props => props.isDragging && '60px'};

  @media (max-width: 1035px) {
    margin-top: ${props => props.isDragging && '90px'};
  }
`;

const getItemStyle = (draggableStyle: DraggableStyle) => ({
  userSelect: 'none',
  ...draggableStyle,
});

const getDraggableStyle = (isDragging: boolean) => ({
  background: isDragging ? '#ddd' : '',
});

// TODO: Rename to a more generic name, like FormAdminQuestions
export class ProposalFormAdminQuestions extends React.Component<Props, State> {
  timeoutId: ?TimeoutID;

  constructor(props: Props) {
    super(props);

    this.state = {
      editIndex: null,
      editIndexSection: null,
      deleteIndex: null,
      showDeleteModal: false,
      deleteType: null,
      flashMessages: [],
      isDragging: false,
    };

    this.timeoutId = null;
  }

  onDragEnd = (result: DropResult) => {
    this.setState({ isDragging: false });
    const { dispatch } = this.props;
    // dropped outside the list
    if (!result.destination) {
      return;
    }
    const { formName } = this.props;
    dispatch(arrayMove(formName, 'questions', result.source.index, result.destination.index));
  };

  handleClose = (index: number) => {
    const { fields, questions } = this.props;
    if (!questions[index].id) {
      fields.remove(index);
    }

    this.setState({
      editIndex: null,
      editIndexSection: null,
    });
  };

  handleClickDelete = (index: number, type: QuestionTypeValue) => {
    const deleteType = type === 'section' ? 'section' : 'question';

    this.setState({
      showDeleteModal: true,
      deleteIndex: index,
      deleteType,
    });
  };

  handleDeleteAction = () => {
    const { deleteIndex, flashMessages, deleteType } = this.state;
    const { fields } = this.props;

    fields.remove(deleteIndex);

    const createSuccessMsgId =
      deleteType === 'section' ? 'your-section-has-been-deleted' : 'your-question-has-been-deleted';

    flashMessages.push(createSuccessMsgId);

    this.setState(
      {
        showDeleteModal: false,
        deleteIndex: null,
        flashMessages,
      },
      () => {
        if (this.timeoutId !== null) {
          clearTimeout(this.timeoutId);
        }

        this.timeoutId = setTimeout(() => {
          this.setState({
            flashMessages: [],
          });
          this.timeoutId = null;
        }, 5000);
      },
    );
  };

  handleClickEdit = (index: number, type: QuestionTypeValue) => {
    if (type === 'section') {
      this.setState({ editIndexSection: index });
    } else {
      this.setState({ editIndex: index });
    }
  };

  handleSubmit = (type: QuestionTypeValue) => {
    let createSuccessMsgId = 'your-question-has-been-registered';

    if (type === 'section') {
      createSuccessMsgId = 'your-section-has-been-registered';
    }

    const { flashMessages } = this.state;
    flashMessages.push(createSuccessMsgId);

    this.setState(
      {
        editIndex: null,
        editIndexSection: null,
        flashMessages,
      },
      () => {
        if (this.timeoutId !== null) {
          clearTimeout(this.timeoutId);
        }

        this.timeoutId = setTimeout(() => {
          this.setState({
            flashMessages: [],
          });
          this.timeoutId = null;
        }, 5000);
      },
    );
  };

  handleCreateQuestion = () => {
    const { fields, formName, dispatch } = this.props;

    dispatch(
      arrayPush(formName, 'questions', {
        private: false,
        required: false,
      }),
    );

    this.setState({ editIndex: fields.length });
  };

  handleCreateSection = () => {
    const { fields, formName, dispatch } = this.props;

    dispatch(
      arrayPush(formName, 'questions', {
        private: false,
        required: false,
        type: 'section',
      }),
    );
    this.setState({ editIndexSection: fields.length });
  };

  handleCancelModal = () => {
    this.setState({
      showDeleteModal: false,
      deleteIndex: null,
    });
  };

  render() {
    const { fields, questions, formName, hideSections } = this.props;
    const {
      editIndex,
      showDeleteModal,
      editIndexSection,
      deleteType,
      flashMessages,
      isDragging,
    } = this.state;

    return (
      <div className="form-group" id="proposal_form_admin_questions_panel_personal">
        <ProposalFormAdminDeleteQuestionModal
          isShow={showDeleteModal}
          cancelAction={this.handleCancelModal}
          deleteAction={this.handleDeleteAction}
          deleteType={deleteType || 'question'}
        />
        <ListGroup>
          <DragDropContext
            onDragEnd={this.onDragEnd}
            onDragStart={() => this.setState({ isDragging: true })}>
            <Droppable droppableId="droppable">
              {(provided: DraggableProvided) => (
                <div ref={provided.innerRef}>
                  {fields.length === 0 && (
                    <div>
                      <FormattedMessage id="highlighted.empty" />
                    </div>
                  )}
                  {fields.map((member, index) => (
                    <Draggable
                      key={questions[index].id}
                      draggableId={questions[index].id || `new-question-${index}`}
                      index={index}>
                      {(providedDraggable: DraggableProvided, snapshot) => (
                        <DraggableWrapper
                          ref={providedDraggable.innerRef}
                          {...providedDraggable.draggableProps}
                          {...providedDraggable.dragHandleProps}
                          style={getItemStyle(providedDraggable.draggableProps.style)}>
                          <DraggableContainer
                            key={index}
                            style={getDraggableStyle(snapshot.isDragging)}>
                            <ProposalFormAdminQuestionModal
                              isCreating={!!questions[index].id}
                              onClose={this.handleClose.bind(this, index)}
                              onSubmit={this.handleSubmit.bind(this, questions[index].type)}
                              member={member}
                              show={index === editIndex}
                              formName={formName}
                            />
                            <SectionQuestionAdminModal
                              show={index === editIndexSection}
                              member={member}
                              isCreating={!!questions[index].id}
                              onClose={this.handleClose.bind(this, index)}
                              onSubmit={this.handleSubmit.bind(this, 'section')}
                              formName={formName}
                            />
                            <QuestionAdmin
                              question={questions[index]}
                              provided={provided}
                              handleClickEdit={this.handleClickEdit}
                              handleClickDelete={this.handleClickDelete}
                              index={index}
                            />
                          </DraggableContainer>
                        </DraggableWrapper>
                      )}
                    </Draggable>
                  ))}
                </div>
              )}
            </Droppable>
          </DragDropContext>
        </ListGroup>
        <AddSectionButtonToolbar isDragging={isDragging}>
          {!hideSections && (
            <Button
              id="js-btn-create-section"
              bsStyle="primary"
              className="btn-outline-primary box-content__toolbar"
              onClick={this.handleCreateSection}>
              <i className="cap cap-small-caps-1" /> <FormattedMessage id="create-section" />
            </Button>
          )}
          <Button
            id="js-btn-create-question"
            bsStyle="primary"
            className="btn-outline-primary box-content__toolbar"
            onClick={this.handleCreateQuestion}>
            <i className="cap cap-bubble-add-2" />{' '}
            <FormattedMessage id="question_modal.create.title" />
          </Button>
        </AddSectionButtonToolbar>
        <FlashMessages className="mt-15" success={flashMessages} />
      </div>
    );
  }
}

const mapStateToProps = (state: GlobalState, props: Props) => {
  const selector = formValueSelector(props.formName);
  return {
    questions: selector(state, 'questions'),
  };
};

export default connect(mapStateToProps)(injectIntl(ProposalFormAdminQuestions));
