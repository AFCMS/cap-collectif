// @flow
import * as React from 'react';
import { connect, type MapStateToProps } from 'react-redux';
import { formValueSelector, arrayPush, arrayMove } from 'redux-form';
import { FormattedMessage, injectIntl, type IntlShape } from 'react-intl';
import { ListGroup, ListGroupItem, Button } from 'react-bootstrap';
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
import { ProposalFormAdminQuestion } from './ProposalFormAdminQuestion';
import ProposalFormAdminSectionModal from './ProposalFormAdminSectionModal';
import FlashMessages from '../Utils/FlashMessages';

type Props = {
  dispatch: Dispatch,
  fields: { length: number, map: Function, remove: Function },
  questions: Array<Object>,
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
};

const getItemStyle = (draggableStyle: DraggableStyle) => ({
  userSelect: 'none',
  ...draggableStyle,
});

const getDraggableStyle = (isDragging: boolean) => ({
  background: isDragging ? '#ddd' : '',
});

export class ProposalFormAdminQuestions extends React.Component<Props, State> {
  constructor(props: Props) {
    super(props);

    this.state = {
      editIndex: null,
      editIndexSection: null,
      deleteIndex: null,
      showDeleteModal: false,
      deleteType: null,
      flashMessages: [],
    };

    this.timeoutId = null;
  }

  onDragEnd = (result: DropResult) => {
    const { dispatch } = this.props;
    // dropped outside the list
    if (!result.destination) {
      return;
    }

    dispatch(
      arrayMove(this.props.formName, 'questions', result.source.index, result.destination.index),
    );
  };

  timeoutId: ?TimeoutID;

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

  handleClickDelete = (index: number, type: string) => {
    let deleteType = 'question';
    if (type === 'section') {
      deleteType = 'section';
    }

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

    let createSuccessMsgId = 'your-question-has-been-deleted';

    if (deleteType === 'section') {
      createSuccessMsgId = 'your-section-has-been-deleted';
    }

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

  handleClickEdit = (index: number, type: string) => {
    if (type === 'section') {
      this.setState({ editIndexSection: index });
    } else {
      this.setState({ editIndex: index });
    }
  };

  handleSubmit = (type: string) => {
    let createSuccessMsgId = 'your-question-has-been-registered';

    if (type === 'section') {
      createSuccessMsgId = 'your-section-has-been-registered';
    }

    const flashMessages = this.state.flashMessages;
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
    const { fields, questions, formName } = this.props;
    const { editIndex, showDeleteModal, editIndexSection, deleteType, flashMessages } = this.state;

    return (
      <div className="form-group" id="proposal_form_admin_questions_panel_personal">
        <ProposalFormAdminDeleteQuestionModal
          isShow={showDeleteModal}
          cancelAction={this.handleCancelModal}
          deleteAction={this.handleDeleteAction}
          deleteType={deleteType || 'question'}
        />
        <ListGroup>
          <DragDropContext onDragEnd={this.onDragEnd}>
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
                      draggableId={questions[index].id}
                      index={index}>
                      {(providedDraggable: DraggableProvided, snapshot) => (
                        <div
                          ref={providedDraggable.innerRef}
                          {...providedDraggable.draggableProps}
                          {...providedDraggable.dragHandleProps}
                          style={getItemStyle(providedDraggable.draggableProps.style)}>
                          <ListGroupItem key={index} style={getDraggableStyle(snapshot.isDragging)}>
                            <ProposalFormAdminQuestionModal
                              isCreating={!!questions[index].id}
                              onClose={this.handleClose.bind(this, index)}
                              onSubmit={this.handleSubmit.bind(this, 'question')}
                              member={member}
                              show={index === editIndex}
                              formName={formName}
                            />
                            <ProposalFormAdminSectionModal
                              show={index === editIndexSection}
                              member={member}
                              isCreating={!!questions[index].id}
                              onClose={this.handleClose.bind(this, index)}
                              onSubmit={this.handleSubmit.bind(this, 'section')}
                              formName={formName}
                            />
                            <ProposalFormAdminQuestion
                              question={questions[index]}
                              provided={provided}
                              handleClickEdit={this.handleClickEdit}
                              handleClickDelete={this.handleClickDelete}
                              index={index}
                            />
                          </ListGroupItem>
                        </div>
                      )}
                    </Draggable>
                  ))}
                </div>
              )}
            </Droppable>
          </DragDropContext>
        </ListGroup>
        <Button
          id="js-btn-create-section"
          bsStyle="primary"
          className="btn-outline-primary box-content__toolbar"
          onClick={this.handleCreateSection}>
          <i className="cap cap-small-caps-1" /> <FormattedMessage id="create-section" />
        </Button>
        <Button
          id="js-btn-create-question"
          bsStyle="primary"
          className="btn-outline-primary box-content__toolbar ml-5"
          onClick={this.handleCreateQuestion}>
          <i className="cap cap-bubble-add-2" />{' '}
          <FormattedMessage id="question_modal.create.title" />
        </Button>

        <FlashMessages style={{ marginTop: '15px' }} success={flashMessages} />
      </div>
    );
  }
}

const mapStateToProps: MapStateToProps<*, *, *> = (state: GlobalState, props: Props) => {
  const selector = formValueSelector(props.formName);
  return {
    questions: selector(state, 'questions'),
  };
};

export default connect(mapStateToProps)(injectIntl(ProposalFormAdminQuestions));
