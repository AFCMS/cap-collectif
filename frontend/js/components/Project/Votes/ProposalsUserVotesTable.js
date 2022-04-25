// @flow
import * as React from 'react';
import { Row } from 'react-bootstrap';
import styled, { type StyledComponent } from 'styled-components';
import ReactDOM from 'react-dom';
import { injectIntl, type IntlShape } from 'react-intl';
import { reduxForm, FieldArray, arrayMove } from 'redux-form';
import { connect } from 'react-redux';
import { graphql, createFragmentContainer } from 'react-relay';
import {
  DragDropContext,
  Draggable,
  Droppable,
  type DragStart,
  type DropResult,
  type DragUpdate,
  type DraggableProvided,
  type DroppableProvided,
  type DraggableStateSnapshot,
  type DroppableStateSnapshot,
  type ResponderProvided,
} from 'react-beautiful-dnd';
import ProposalUserVoteItem from './ProposalUserVoteItem';
import type { ProposalsUserVotesTable_step } from '~relay/ProposalsUserVotesTable_step.graphql';
import type { ProposalsUserVotesTable_votes } from '~relay/ProposalsUserVotesTable_votes.graphql';
import type { State, Dispatch } from '../../../types';
import config from '../../../config';
import invariant from '../../../utils/invariant';

type RelayProps = {|
  step: ProposalsUserVotesTable_step,
  votes: ProposalsUserVotesTable_votes,
|};

type Props = {|
  ...ReduxFormFormProps,
  ...RelayProps,
  dispatch: Dispatch,
  onSubmit: () => void,
  deletable: boolean,
  snapshot: DraggableStateSnapshot,
  intl: IntlShape,
  disabledKeyboard?: () => void,
  activeKeyboard?: () => void,
  isDropDisabled?: boolean,
|};

type VotesProps = {|
  ...ReduxFormFieldArrayProps,
  ...RelayProps,
  deletable: boolean,
|};

export const Wrapper: StyledComponent<
  { isDraggingOver: boolean, isDropDisabled: boolean },
  {},
  HTMLDivElement,
> = styled.div`
  background-color: ${({ isDraggingOver }) => (isDraggingOver ? 'blue' : 'grey')};
  display: flex;
  flex-direction: column;
  opacity: ${({ isDropDisabled }) => (isDropDisabled ? 0.5 : 'inherit')};
  transition: background-color 0.1s ease, opacity 0.1s ease;
  user-select: none;
`;

export const DraggableItem: StyledComponent<
  { isDragging: boolean },
  {},
  HTMLDivElement,
> = styled.div`
  background-color: ${({ isDragging }) => (isDragging ? 'green' : 'white')};
  box-shadow: ${({ isDragging }) => (isDragging ? `2px 2px 1px rgba(0,0,0,0.2)` : 'none')};
  user-select: none;
  transition: background-color 0.1s ease;
`;

let portal: ?HTMLElement = null;

if (config.canUseDOM && document) {
  portal = document.createElement('div');
  portal.classList.add('proposal-user-votes-portal');
  if (document.body) {
    document.body.appendChild(portal);
  }
}

const renderMembers = ({ fields, votes, step, deletable }: VotesProps): any => (
  <React.Fragment>
    {fields.map((member: string, index: number) => {
      const voteInReduxForm: ?{ id: string, public: boolean } = fields.get(index);
      invariant(voteInReduxForm, 'The vote should be found.');
      const voteId = voteInReduxForm.id;
      const voteEdge =
        votes.edges && votes.edges.filter(Boolean).filter(edge => edge.node.id === voteId)[0];
      if (!voteEdge) return null;
      const vote = voteEdge.node;
      return (
        <ProposalUserVoteItem
          key={index}
          member={member}
          isVoteVisibilityPublic={voteInReduxForm.public}
          vote={vote}
          step={step}
          onDelete={
            deletable
              ? () => {
                  fields.remove(index);
                }
              : null
          }
        />
      );
    })}
  </React.Fragment>
);

const renderDraggableMembers = ({
  fields,
  votes,
  step,
  deletable,
  intl,
}: {|
  ...VotesProps,
  ...Props,
|}): any => {
  if (!votes.edges) {
    return null;
  }

  return (
    <React.Fragment>
      {fields.map((member: string, index: number) => {
        const voteInReduxForm: ?{ id: string, public: boolean } = fields.get(index);
        invariant(voteInReduxForm, 'The vote should be found.');
        const voteId = voteInReduxForm.id;
        const voteEdge =
          votes.edges && votes.edges.filter(Boolean).filter(edge => edge.node.id === voteId)[0];
        if (!voteEdge) return null;
        const vote = voteEdge.node;

        return (
          <Draggable key={vote.proposal.id} draggableId={vote.proposal.id} index={index}>
            {(provided: DraggableProvided, snapshot: DraggableStateSnapshot) => {
              const usePortal: boolean = snapshot.isDragging;

              const child = (
                <div
                  className={`proposals-user-votes__draggable-item ${
                    usePortal ? 'item-in-portal' : ''
                  }`}>
                  <DraggableItem
                    ref={provided.innerRef}
                    isDragging={snapshot.isDragging}
                    {...provided.draggableProps}
                    {...provided.dragHandleProps}
                    aria-roledescription={intl.formatMessage(
                      { id: 'vote-dragndrop-item-description' },
                      { title: vote.proposal.title, position: index + 1 },
                    )}>
                    <ProposalUserVoteItem
                      member={member}
                      ranking={index + 1}
                      isVoteVisibilityPublic={voteInReduxForm.public}
                      vote={vote}
                      step={step}
                      showDraggableIcon={fields.length > 1}
                      onDelete={deletable ? () => fields.remove(index) : null}
                    />
                  </DraggableItem>
                </div>
              );

              if (config.isMobile || !portal || !usePortal) {
                return child;
              }

              // if dragging - put the item in a portal
              return ReactDOM.createPortal(child, portal);
            }}
          </Draggable>
        );
      })}
    </React.Fragment>
  );
};

export class ProposalsUserVotesTable extends React.Component<Props> {
  onDragStart = (start: DragStart, provided: ResponderProvided) => {
    const { votes, intl, disabledKeyboard } = this.props;

    if (disabledKeyboard) {
      disabledKeyboard();
    }

    // Add a little vibration if the browser supports it.
    // Add's a nice little physical feedback
    if (window.navigator.vibrate) {
      window.navigator.vibrate(100);
    }

    const title: string = this.getTitle(votes, start);

    provided.announce(intl.formatMessage({ id: 'dragndrop-drag-start' }, { title }));
  };

  onDragUpdate = (update: DragUpdate, provided: ResponderProvided) => {
    const { votes, intl } = this.props;

    const title: string = this.getTitle(votes, update);

    if (update.destination) {
      provided.announce(
        intl.formatMessage(
          { id: 'dragndrop-drag-update' },
          { title, position: update.destination.index + 1 },
        ),
      );
    }
  };

  onDragEnd = (result: DropResult, provided: ResponderProvided) => {
    const { votes, intl, activeKeyboard, dispatch, form } = this.props;

    if (activeKeyboard) {
      activeKeyboard();
    }

    const title = this.getTitle(votes, result);

    // cancel (esc)
    if (result.reason === 'CANCEL') {
      provided.announce(
        intl.formatMessage(
          { id: 'dragndrop-drag-cancel' },
          { title, position: result.source.index + 1 },
        ),
      );

      return;
    }

    // dropped outside the list
    if (!result.destination) {
      provided.announce(intl.formatMessage({ id: 'dragndrop-drag-outside' }));

      return;
    }
    // no movement
    if (result.destination.index === result.source.index) {
      provided.announce(intl.formatMessage({ id: 'dragndrop-drag-no-movement' }));

      return;
    }

    dispatch(arrayMove(form, 'votes', result.source.index, result.destination.index));

    if (result.destination) {
      provided.announce(
        intl.formatMessage(
          { id: 'dragndrop-drag-end' },
          { title, position: result.destination.index + 1 },
        ),
      );
    }
  };

  getTitle = (
    votes: ProposalsUserVotesTable_votes,
    position: DragStart | DragUpdate | DropResult,
  ) => {
    const draggedProposal =
      votes.edges && votes.edges.filter(el => el && el.node.proposal.id === position.draggableId);
    invariant(draggedProposal && draggedProposal[0], 'Dragged proposal should be found.');
    return draggedProposal[0].node.proposal.title;
  };

  render() {
    const { form, step, votes, deletable, intl, isDropDisabled = false } = this.props;

    if (!step.votesRanking) {
      return (
        <Row className="proposals-user-votes__table">
          <FieldArray
            step={step}
            votes={votes}
            deletable={deletable}
            name="votes"
            component={renderMembers}
          />
        </Row>
      );
    }

    return (
      <Row className="proposals-user-votes__table" style={{ boxSizing: 'border-box' }}>
        <DragDropContext
          onDragEnd={this.onDragEnd}
          onDragStart={this.onDragStart}
          onDragUpdate={this.onDragUpdate}>
          <Droppable droppableId={`droppable${form}`}>
            {(provided: DroppableProvided, snapshot: DroppableStateSnapshot) => (
              <Wrapper
                isDraggingOver={snapshot.isDraggingOver}
                ref={provided.innerRef}
                isDropDisabled={isDropDisabled}
                {...provided.droppableProps}>
                <FieldArray
                  step={step}
                  votes={votes}
                  intl={intl}
                  deletable={deletable}
                  name="votes"
                  component={renderDraggableMembers}
                />
                {provided.placeholder}
              </Wrapper>
            )}
          </Droppable>
        </DragDropContext>
      </Row>
    );
  }
}

const form = reduxForm({
  enableReinitialize: true,
})(ProposalsUserVotesTable);

export const getFormName = (step: { +id: string }) => `proposal-user-vote-form-step-${step.id}`;

const mapStateToProps = (state: State, props: RelayProps) => ({
  form: getFormName(props.step),
  initialValues: {
    votes:
      props.votes.edges &&
      props.votes.edges
        .filter(Boolean)
        .map(edge => ({ id: edge.node.id, public: !edge.node.anonymous })),
  },
});
const container = connect(mapStateToProps)(injectIntl(form));

export default createFragmentContainer(container, {
  votes: graphql`
    fragment ProposalsUserVotesTable_votes on ProposalVoteConnection {
      edges {
        node {
          id
          ...ProposalUserVoteItem_vote
          anonymous
          proposal {
            id
            title
          }
        }
      }
    }
  `,
  step: graphql`
    fragment ProposalsUserVotesTable_step on ProposalStep {
      id
      votesRanking
      ...ProposalUserVoteItem_step
    }
  `,
});
