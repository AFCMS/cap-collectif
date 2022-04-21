// @flow
import { takeEvery } from 'redux-saga';
import { select, call, put } from 'redux-saga/effects';
import flatten from 'flat';
import { SubmissionError } from 'redux-form';
import LocalStorageService from '../../services/LocalStorageService';
import Fetcher, { json } from '../../services/Fetcher';
import FluxDispatcher from '../../dispatchers/AppDispatcher';
import { UPDATE_ALERT } from '../../constants/AlertConstants';
import { CREATE_COMMENT_SUCCESS } from '../../constants/CommentConstants';
import type { Exact, State as GlobalState, Dispatch, Action } from '../../types';

const PROPOSAL_PAGINATION = 51;

type Status = { id: number };
type ChangeFilterAction = { type: 'proposal/CHANGE_FILTER', filter: string, value: string };
type ChangeOrderAction = { type: 'proposal/CHANGE_ORDER', order: string };
type SubmitFusionFormAction = { type: 'proposal/SUBMIT_FUSION_FORM', proposalForm: number };
type FetchVotesRequestedAction = {
  type: 'proposal/VOTES_FETCH_REQUESTED',
  stepId: number,
  proposalId: number
};
type LoadSelectionsAction = { type: 'proposal/LOAD_SELECTIONS_REQUEST', proposalId: number };
type LoadSelectionsSucessAction = { type: 'proposal/LOAD_SELECTIONS_SUCCEEDED', proposalId: number };
type CloseCreateFusionModalAction = { type: 'proposal/CLOSE_CREATE_FUSION_MODAL' };
type OpenCreateFusionModalAction = { type: 'proposal/OPEN_CREATE_FUSION_MODAL' };
type CancelSubmitFusionFormAction = { type: 'proposal/CANCEL_SUBMIT_FUSION_FORM' };
type OpenVotesModalAction = { type: 'proposal/OPEN_VOTES_MODAL', stepId: number };
type CloseVotesModalActionAction = { type: 'proposal/CLOSE_VOTES_MODAL', stepId: number };
type VoteSuccessAction = {
  type: 'proposal/VOTE_SUCCEEDED',
  proposalId: number,
  stepId: number,
  vote: Object,
  comment: Object
};
type RequestLoadVotesAction = {
  type: 'proposal/VOTES_FETCH_REQUESTED',
  stepId: number,
  proposalId: number
};
type DeleteVoteSucceededAction = {
  type: 'proposal/DELETE_VOTE_SUCCEEDED',
  proposalId: number,
  stepId: number,
  vote: Object
};
type RequestDeleteProposalVoteAction = {
  type: 'proposal/DELETE_VOTE_REQUESTED',
  proposalId: number
};
type CloseEditProposalModalAction = { type: 'proposal/CLOSE_EDIT_MODAL' };
type OpenEditProposalModalAction = { type: 'proposal/OPEN_EDIT_MODAL' };
type CloseDeleteProposalModalAction = { type: 'proposal/CLOSE_DELETE_MODAL' };
type OpenDeleteProposalModalAction = { type: 'proposal/OPEN_DELETE_MODAL' };
type SubmitProposalFormAction = { type: 'proposal/SUBMIT_PROPOSAL_FORM' };
type EditProposalFormAction = { type: 'proposal/EDIT_PROPOSAL_FORM' };
type OpenCreateModalAction = { type: 'proposal/OPEN_CREATE_MODAL' };
type CancelSubmitProposalAction = { type: 'proposal/CANCEL_SUBMIT_PROPOSAL' };
type CloseCreateModalAction = { type: 'proposal/CLOSE_CREATE_MODAL' };
type OpenVoteModalAction = { type: 'proposal/OPEN_VOTE_MODAL', id: number };
type CloseVoteModalAction = { type: 'proposal/CLOSE_VOTE_MODAL' };
type ChangePageAction = { type: 'proposal/CHANGE_PAGE', page: number };
type ChangeTermAction = { type: 'proposal/CHANGE_TERMS', terms: string };
type RequestLoadProposalsAction = { type: 'proposal/FETCH_REQUESTED', step: ?number };
type RequestVotingAction = { type: 'proposal/VOTE_REQUESTED' };
type VoteFailedAction = { type: 'proposal/VOTE_FAILED' };
type SendProposalNotificationSuceedAction = { type: 'proposal/SEND_PROPOSAL_NOTIFICATION_SUCCEED', proposalId: number, stepId: number };
type SendProposalNotificationFailedAction = { type: 'proposal/SEND_PROPOSAL_NOTIFICATION_ERROR', error: string };

// type Step = {
//   type: string,
//   id: number
// };
type ProposalMap = {[id: number]: Object};
export type State = {
  queryCount: ?number,
  currentProposalId: ?number,
  proposalShowedId: Array<number>,
  creditsLeftByStepId: Object,
  proposalsById: ProposalMap,
  userVotesByStepId: Object,
  currentVotesModal: ?Object,
  currentVoteModal: ?number,
  currentDeletingVote: ?number,
  showCreateModal: boolean,
  isCreating: boolean,
  isCreatingFusion: boolean,
  isSubmittingFusion: boolean,
  showDeleteModal: boolean,
  isDeleting: boolean,
  isVoting: boolean,
  isLoading: boolean,
  isEditing: boolean,
  showEditModal: boolean,
  order: string,
  filters: Object,
  terms: ?string,
  lastEditedStepId: ?number,
  currentPaginationPage: number,
  lastEditedProposalId: ?number,
  lastNotifiedStepId: ?number
};

const initialState: State = {
  currentProposalId: null,
  proposalShowedId: [],
  queryCount: undefined,
  creditsLeftByStepId: {},
  proposalsById: {},
  lastEditedStepId: null,
  userVotesByStepId: {},
  currentVotesModal: null,
  currentVoteModal: null,
  currentDeletingVote: null,
  showCreateModal: false,
  isCreating: false,
  isCreatingFusion: false,
  isSubmittingFusion: false,
  showDeleteModal: false,
  isDeleting: false,
  isVoting: false,
  isLoading: true,
  isEditing: false,
  showEditModal: false,
  order: 'random',
  filters: {},
  terms: null,
  currentPaginationPage: 1,
  lastEditedProposalId: null,
  lastNotifiedStepId: null,
};


export const loadSelections = (proposalId: number): LoadSelectionsAction => ({ type: 'proposal/LOAD_SELECTIONS_REQUEST', proposalId });
export const loadSelectionsSucess = (proposalId: number): LoadSelectionsSucessAction => ({ type: 'proposal/LOAD_SELECTIONS_SUCCEEDED', proposalId });
export const closeCreateFusionModal = (): CloseCreateFusionModalAction => ({ type: 'proposal/CLOSE_CREATE_FUSION_MODAL' });
export const openCreateFusionModal = (): OpenCreateFusionModalAction => ({ type: 'proposal/OPEN_CREATE_FUSION_MODAL' });
export const submitFusionForm = (proposalForm: number): SubmitFusionFormAction => ({ type: 'proposal/SUBMIT_FUSION_FORM', proposalForm });
export const cancelSubmitFusionForm = (): CancelSubmitFusionFormAction => ({ type: 'proposal/CANCEL_SUBMIT_FUSION_FORM' });
export const openVotesModal = (stepId: number): OpenVotesModalAction => ({ type: 'proposal/OPEN_VOTES_MODAL', stepId });
export const closeVotesModal = (stepId: number): CloseVotesModalActionAction => ({ type: 'proposal/CLOSE_VOTES_MODAL', stepId });
export const voteSuccess = (proposalId: number, stepId: number, vote: Object, comment: Object): VoteSuccessAction => ({
  type: 'proposal/VOTE_SUCCEEDED',
  proposalId,
  stepId,
  vote,
  comment,
});
export const loadVotes = (stepId: number, proposalId: number): RequestLoadVotesAction => ({
  type: 'proposal/VOTES_FETCH_REQUESTED',
  stepId,
  proposalId,
});
export const deleteVoteSucceeded = (stepId: number, proposalId: number, vote: Object): DeleteVoteSucceededAction => ({ type: 'proposal/DELETE_VOTE_SUCCEEDED', proposalId, stepId, vote });
const deleteVoteRequested = (proposalId: number): RequestDeleteProposalVoteAction => ({ type: 'proposal/DELETE_VOTE_REQUESTED', proposalId });
export const closeEditProposalModal = (): CloseEditProposalModalAction => ({ type: 'proposal/CLOSE_EDIT_MODAL' });
export const openEditProposalModal = (): OpenEditProposalModalAction => ({ type: 'proposal/OPEN_EDIT_MODAL' });
export const closeDeleteProposalModal = (): CloseDeleteProposalModalAction => ({ type: 'proposal/CLOSE_DELETE_MODAL' });
export const openDeleteProposalModal = (): OpenDeleteProposalModalAction => ({ type: 'proposal/OPEN_DELETE_MODAL' });
export const submitProposalForm = (): SubmitProposalFormAction => ({ type: 'proposal/SUBMIT_PROPOSAL_FORM' });
export const editProposalForm = (): EditProposalFormAction => ({ type: 'proposal/EDIT_PROPOSAL_FORM' });
export const openCreateModal = (): OpenCreateModalAction => ({ type: 'proposal/OPEN_CREATE_MODAL' });
export const cancelSubmitProposal = (): CancelSubmitProposalAction => ({ type: 'proposal/CANCEL_SUBMIT_PROPOSAL' });
export const closeCreateModal = (): CloseCreateModalAction => ({ type: 'proposal/CLOSE_CREATE_MODAL' });
export const openVoteModal = (id: number): OpenVoteModalAction => ({ type: 'proposal/OPEN_VOTE_MODAL', id });
export const closeVoteModal = (): CloseVoteModalAction => ({ type: 'proposal/CLOSE_VOTE_MODAL' });
export const changePage = (page: number): ChangePageAction => ({ type: 'proposal/CHANGE_PAGE', page });
export const changeOrder = (order: string): ChangeOrderAction => ({ type: 'proposal/CHANGE_ORDER', order });
export const changeTerm = (terms: string): ChangeTermAction => ({ type: 'proposal/CHANGE_TERMS', terms });
export const changeFilter = (filter: string, value: string): ChangeFilterAction => ({
  type: 'proposal/CHANGE_FILTER',
  filter,
  value,
});
type RequestDeleteAction = { type: 'proposal/DELETE_REQUEST' };
const deleteRequest = (): RequestDeleteAction => ({ type: 'proposal/DELETE_REQUEST' });
export const loadProposals = (step: ?number): RequestLoadProposalsAction => ({ type: 'proposal/FETCH_REQUESTED', step });
export const deleteProposal = (form: number, proposal: Object, dispatch: Dispatch): void => {
  dispatch(deleteRequest());
  Fetcher
    .delete(`/proposal_forms/${form}/proposals/${proposal.id}`)
    .then(() => {
      dispatch(closeDeleteProposalModal());
      window.location.href = proposal._links.index;
      FluxDispatcher.dispatch({
        actionType: UPDATE_ALERT,
        alert: { bsStyle: 'success', content: 'proposal.request.delete.success' },
      });
    })
    .catch(() => {
      FluxDispatcher.dispatch({
        actionType: UPDATE_ALERT,
        alert: { bsStyle: 'warning', content: 'proposal.request.delete.failure' },
      });
    });
};
export const startVoting = (): RequestVotingAction => ({ type: 'proposal/VOTE_REQUESTED' });
export const stopVoting = (): VoteFailedAction => ({ type: 'proposal/VOTE_FAILED' });


type SelectStepSucceedAction = { type: 'proposal/SELECT_SUCCEED', stepId: number, proposalId: number };
type UnSelectStepSucceedAction = { type: 'proposal/UNSELECT_SUCCEED', stepId: number, proposalId: number };

const unSelectStepSucceed = (stepId, proposalId): UnSelectStepSucceedAction => ({ type: 'proposal/UNSELECT_SUCCEED', stepId, proposalId });
const selectStepSucceed = (stepId: number, proposalId: number): SelectStepSucceedAction => ({ type: 'proposal/SELECT_SUCCEED', stepId, proposalId });

type UpdateSelectionStatusSucceedAction = { type: 'proposal/UPDATE_SELECTION_STATUS_SUCCEED', stepId: number, proposalId: number, status: ?Status };
const updateSelectionStatusSucceed = (stepId: number, proposalId: number, status: ?Status): UpdateSelectionStatusSucceedAction => ({ type: 'proposal/UPDATE_SELECTION_STATUS_SUCCEED', stepId, proposalId, status });

type UpdateProposalCollectStatusSucceedAction = { type: 'proposal/UPDATE_PROPOSAL_STATUS_SUCCEED', proposalId: number, stepId: number, status: ?Status };
const updateProposalCollectStatusSucceed = (proposalId: number, stepId: number, status: ?Status): UpdateProposalCollectStatusSucceedAction => ({ type: 'proposal/UPDATE_PROPOSAL_STATUS_SUCCEED', proposalId, stepId, status });

export const sendProposalNotificationSucceed = (proposalId: number, stepId: number): SendProposalNotificationSuceedAction => ({ type: 'proposal/SEND_PROPOSAL_NOTIFICATION_SUCCEED', proposalId, stepId });
const sendProposalNotificationError = (error: string): SendProposalNotificationFailedAction => ({ type: 'proposal/SEND_PROPOSAL_NOTIFICATION_ERROR', error });

export const sendProposalNotification = (dispatch: Dispatch, proposalId: number, stepId: number): void => {
  Fetcher.post(`/proposals/${proposalId}/notify-status-changed`)
    .then(() => { dispatch(sendProposalNotificationSucceed(proposalId, stepId)); })
    .catch((error) => { dispatch(sendProposalNotificationError(error)); });
};

export const sendSelectionNotification = (dispatch: Dispatch, proposalId: number, stepId: number): void => {
  Fetcher.post(`/selection_step/${stepId}/proposals/${proposalId}/notify-status-changed`, { proposalId, stepId })
    .then(() => { dispatch(sendProposalNotificationSucceed(proposalId, stepId)); })
    .catch((error) => { dispatch(sendProposalNotificationError(error)); });
};

export const updateProposalStatus = (dispatch: Dispatch, proposalId: number, stepId: number, value: number) => {
  Fetcher
    .patch(`/proposals/${proposalId}`, { status: value })
    .then(json)
    .then((status) => {
      dispatch(updateProposalCollectStatusSucceed(proposalId, stepId, status));
    })
    .catch(() => {
      dispatch(updateProposalCollectStatusSucceed(proposalId, stepId));
    });
};

export const updateSelectionStatus = (dispatch: Dispatch, proposalId: number, stepId: number, value: number) => {
  Fetcher
    .patch(`/selection_steps/${stepId}/selections/${proposalId}`, { status: value })
    .then(json)
    .then((status) => {
      dispatch(updateSelectionStatusSucceed(stepId, proposalId, status));
    })
    .catch(() => {
      dispatch(updateSelectionStatusSucceed(stepId, proposalId, null));
    });
};
export const updateStepStatus = (dispatch: Dispatch, proposalId: number, step: Object, value: number) => {
  if (step.step_type === 'selection') {
    updateSelectionStatus(dispatch, proposalId, step.id, value);
  } else {
    updateProposalStatus(dispatch, proposalId, step.id, value);
  }
};

export const unSelectStep = (dispatch: Dispatch, proposalId: number, stepId: number) => {
  Fetcher
    .delete(`/selection_steps/${stepId}/selections/${proposalId}`)
    .then(() => {
      dispatch(unSelectStepSucceed(stepId, proposalId));
    });
};
export const selectStep = (dispatch: Dispatch, proposalId: number, stepId: number) => {
  Fetcher
    .post(`/selection_steps/${stepId}/selections`, { proposal: proposalId })
    .then(() => {
      dispatch(selectStepSucceed(stepId, proposalId));
    });
};

export const vote = (dispatch: Dispatch, step: Object, proposal: Object, data: Object) => {
  let url = '';
  switch (step.type) {
    case 'selection':
      url = `/selection_steps/${step.id}/proposals/${proposal.id}/votes`;
      break;
    case 'collect':
      url = `/collect_steps/${step.id}/proposals/${proposal.id}/votes`;
      break;
    default:
      console.log('unknown step'); // eslint-disable-line no-console
      return false;
  }
  dispatch(startVoting());
  return Fetcher.postToJson(url, data)
    .then((newVote) => {
      dispatch(voteSuccess(proposal.id, step.id, newVote, data.comment));
      if (data.comment) {
        FluxDispatcher.dispatch({
          actionType: CREATE_COMMENT_SUCCESS,
          message: null,
        });
      }
      FluxDispatcher.dispatch({
        actionType: UPDATE_ALERT,
        alert: { bsStyle: 'success', content: 'proposal.request.vote.success' },
      });
    })
    .catch(({ response }) => {
      if (response.message === 'Validation Failed') {
        dispatch(stopVoting());
        if (typeof response.errors.children.email === 'object') {
          throw new SubmissionError({ _error: response.errors.children.email.errors[0] });
        }
      }
      dispatch(closeVoteModal());
      FluxDispatcher.dispatch({
        actionType: UPDATE_ALERT,
        alert: { bsStyle: 'danger', content: 'proposal.request.vote.failure' },
      });
    });
};

export const deleteVote = (dispatch: Dispatch, step: Object, proposal: Object) => {
  dispatch(deleteVoteRequested(proposal.id));
  let url = '';
  switch (step.type) {
    case 'selection':
      url = `/selection_steps/${step.id}/proposals/${proposal.id}/votes`;
      break;
    case 'collect':
      url = `/collect_steps/${step.id}/proposals/${proposal.id}/votes`;
      break;
    default:
      console.log('unknown step'); // eslint-disable-line no-console
      return false;
  }
  return Fetcher
      .delete(url)
      .then(json)
      .then((v) => {
        dispatch(deleteVoteSucceeded(step.id, proposal.id, v));
        FluxDispatcher.dispatch({
          actionType: UPDATE_ALERT,
          alert: { bsStyle: 'success', content: 'proposal.request.delete_vote.success' },
        });
      })
      .catch((e) => {
        console.log(e); // eslint-disable-line no-console
        FluxDispatcher.dispatch({
          actionType: UPDATE_ALERT,
          alert: { bsStyle: 'warning', content: 'proposal.request.delete_vote.failure' },
        });
      });
};

export const submitProposal = (dispatch: Dispatch, form: number, data: Object): Promise<*> => {
  const formData = new FormData();
  const flattenedData = flatten(data);
  Object.keys(flattenedData).map((key) => {
    if (flattenedData[key] !== -1) {
      formData.append(key, flattenedData[key]);
    }
  });
  return Fetcher
      .postFormData(`/proposal_forms/${form}/proposals`, formData)
      .then(() => {
        dispatch(closeCreateModal());
        dispatch(loadProposals());
        FluxDispatcher.dispatch({
          actionType: UPDATE_ALERT,
          alert: { bsStyle: 'success', content: 'proposal.request.create.success' },
        });
      })
      .catch(() => {
        dispatch(cancelSubmitProposal());
        FluxDispatcher.dispatch({
          actionType: UPDATE_ALERT,
          alert: { bsStyle: 'warning', content: 'proposal.request.create.failure' },
        });
      })
    ;
};

export const updateProposal = (dispatch: Dispatch, form: number, id: number, data: Object) => {
  const formData = new FormData();
  const flattenedData = flatten(data);
  Object.keys(flattenedData).map(key => formData.append(key, flattenedData[key]));
  return Fetcher
    .postFormData(`/proposal_forms/${form}/proposals/${id}`, formData)
    .then(() => {
      dispatch(closeEditProposalModal());
      location.reload();
      FluxDispatcher.dispatch({
        actionType: UPDATE_ALERT,
        alert: { bsStyle: 'success', content: 'alert.success.update.proposal' },
      });
    })
    .catch(() => {
      dispatch(cancelSubmitProposal());
      FluxDispatcher.dispatch({
        actionType: UPDATE_ALERT,
        alert: { bsStyle: 'warning', content: 'proposal.request.update.failure' },
      });
    });
};

export function* fetchVotesByStep(action: FetchVotesRequestedAction): Generator<*, *, *> {
  const { stepId, proposalId } = action;
  try {
    let hasMore = true;
    let iterationCount = 0;
    const votesPerIteration = 50;
    while (hasMore) {
      const result = yield call(
        Fetcher.get,
        `/steps/${stepId}/proposals/${proposalId}/votes?offset=${iterationCount * votesPerIteration}&limit=${votesPerIteration}`,
      );
      hasMore = result.hasMore;
      iterationCount++;
      yield put({
        type: 'proposal/VOTES_FETCH_SUCCEEDED',
        votes: result.votes,
        stepId,
        proposalId,
      });
    }
  } catch (e) {
    yield put({ type: 'proposal/VOTES_FETCH_FAILED', error: e });
  }
}

function* submitFusionFormData(action: SubmitFusionFormAction): Generator<*, *, *> {
  const { proposalForm } = action;
  const globalState: GlobalState = yield select();
  const formData = new FormData();
  const data = { ...globalState.form.proposal.values };
  delete data.project;
  if (data.responses.length === 0) {
    delete data.responses;
  }
  const flattenedData = flatten(data);
  Object.keys(flattenedData).map((key) => {
    formData.append(key, flattenedData[key]);
  });
  try {
    yield call(
      Fetcher.postFormData,
      `/proposal_forms/${proposalForm}/proposals`,
      formData,
    );
    yield put(closeCreateFusionModal());
    location.reload();
  } catch (e) {
    yield put(cancelSubmitFusionForm());
  }
}

export function* fetchProposals(action: Object): Generator<*, *, *> {
  let { step } = action;
  const globalState: GlobalState = yield select();
  if (globalState.project.currentProjectById) {
    step = step || globalState.project.projectsById[globalState.project.currentProjectById].steps.filter(s => s.id === globalState.project.currentProjectStepById)[0];
  }
  const state = globalState.proposal;
  let url = '';
  switch (step.type) {
    case 'collect':
      url = `/collect_steps/${step.id}/proposals/search`;
      break;
    case 'selection':
      url = `/selection_steps/${step.id}/proposals/search`;
      break;
    default:
      console.log('Unknown step type'); // eslint-disable-line no-console
      return false;
  }
  url += `?page=${state.currentPaginationPage}&pagination=${PROPOSAL_PAGINATION}&order=${state.order}`;
  const result = yield call(
    Fetcher.postToJson,
    url,
    {
      terms: state.terms,
      filters: state.filters,
    },
  );
  yield put({ type: 'proposal/FETCH_SUCCEEDED', proposals: result.proposals, count: result.count });
}


type RequestFetchProposalPostsAction = { type: 'proposal/POSTS_FETCH_REQUESTED', proposalId: number };
export const fetchProposalPosts = (proposalId: number): RequestFetchProposalPostsAction => ({ type: 'proposal/POSTS_FETCH_REQUESTED', proposalId });

export function* fetchPosts(action: RequestFetchProposalPostsAction): Generator<*, *, *> {
  try {
    const result = yield call(Fetcher.get, `/proposals/${action.proposalId}/posts`);
    yield put({ type: 'proposal/POSTS_FETCH_SUCCEEDED', posts: result.posts, proposalId: action.proposalId });
  } catch (e) {
    yield put({ type: 'proposal/POSTS_FETCH_FAILED', error: e });
  }
}
export function* fetchSelections(action: LoadSelectionsAction): Generator<*, *, *> {
  try {
    const selections = yield call(Fetcher.get, `/proposals/${action.proposalId}/selections`);
    yield put({ type: 'proposal/LOAD_SELECTIONS_SUCCEEDED', selections, proposalId: action.proposalId });
  } catch (e) {
    console.log(e); // eslint-disable-line
  }
}

export function* storeFiltersInLocalStorage(action: ChangeFilterAction): Generator<*, *, *> {
  const { filter, value } = action;
  const state: GlobalState = yield select();
  const filters = { ...state.proposal.filters, [filter]: value };
  const filtersByStep: {[id: number]: Object} = LocalStorageService.get('proposal.filtersByStep') || {};
  if (state.project.currentProjectStepById) {
    filtersByStep[state.project.currentProjectStepById] = filters;
  }
  LocalStorageService.set('proposal.filtersByStep', filtersByStep);
}

export function* storeOrderInLocalStorage(action: ChangeOrderAction): Generator<*, *, *> {
  const { order } = action;
  const state: GlobalState = yield select();
  const orderByStep: {[id: number]: string} = LocalStorageService.get('proposal.orderByStep') || {};
  if (state.project.currentProjectStepById) {
    orderByStep[state.project.currentProjectStepById] = order;
  }
  LocalStorageService.set('proposal.orderByStep', orderByStep);
}

export type ProposalAction =
    SendProposalNotificationSuceedAction
  | SendProposalNotificationFailedAction
  | FetchVotesRequestedAction
  | SubmitFusionFormAction
  | ChangeFilterAction
  | VoteFailedAction
  | RequestVotingAction
  | RequestLoadProposalsAction
  | ChangeTermAction
  | OpenDeleteProposalModalAction
  | ChangePageAction
  | CloseCreateModalAction
  | OpenVoteModalAction
  | CancelSubmitProposalAction
  | SubmitProposalFormAction
  | OpenDeleteProposalModalAction
  | RequestFetchProposalPostsAction
  | DeleteVoteSucceededAction
  | LoadSelectionsAction
  | CloseEditProposalModalAction
  | RequestDeleteProposalVoteAction
  | CloseVoteModalAction
  | VoteSuccessAction
  | SelectStepSucceedAction
  | UnSelectStepSucceedAction
  | UpdateSelectionStatusSucceedAction
  | UpdateProposalCollectStatusSucceedAction
  | CloseDeleteProposalModalAction
  | RequestDeleteAction
;

export function* saga(): Generator<*, *, *> {
  yield [
    takeEvery('proposal/POSTS_FETCH_REQUESTED', fetchPosts),
    takeEvery('proposal/VOTES_FETCH_REQUESTED', fetchVotesByStep),
    takeEvery('proposal/FETCH_REQUESTED', fetchProposals),
    takeEvery('proposal/SUBMIT_FUSION_FORM', submitFusionFormData),
    takeEvery('proposal/LOAD_SELECTIONS_REQUEST', fetchSelections),
    takeEvery('proposal/CHANGE_FILTER', storeFiltersInLocalStorage),
    takeEvery('proposal/CHANGE_ORDER', storeOrderInLocalStorage),
  ];
}

const voteReducer = (state: State, action): Exact<State> => {
  const proposal = state.proposalsById[action.proposalId];
  const votesByStepId = proposal.votesByStepId || {};
  votesByStepId[action.stepId].unshift(action.vote);
  const votesCountByStepId = proposal.votesCountByStepId;
  votesCountByStepId[action.stepId]++;
  let commentsCount = proposal.comments_count;
  if (action.comment) {
    commentsCount++;
  }
  const proposalsById = state.proposalsById;
  const userVotesByStepId = state.userVotesByStepId;
  userVotesByStepId[action.stepId].push(proposal.id);
  proposalsById[action.proposalId] = { ...proposal, votesCountByStepId, votesByStepId, comments_count: commentsCount };
  const creditsLeftByStepId = state.creditsLeftByStepId;
  creditsLeftByStepId[action.stepId] -= proposal.estimation || 0;
  return {
    ...state,
    proposalsById,
    userVotesByStepId,
    isVoting: false,
    currentVoteModal: null,
    creditsLeftByStepId,
  };
};
const deleteVoteReducer = (state: State, action): Exact<State> => {
  const proposal = state.proposalsById[action.proposalId];
  if (!proposal) {
    const userVotesByStepId = state.userVotesByStepId;
    userVotesByStepId[action.stepId] = userVotesByStepId[action.stepId].filter(voteId => voteId !== action.proposalId);
    return { ...state, userVotesByStepId };
  }// Fix for user votes page
  const votesCountByStepId = proposal.votesCountByStepId;
  votesCountByStepId[action.stepId]--;
  const votesByStepId = proposal.votesByStepId || [];
  if (action.vote.user) {
    votesByStepId[action.stepId] = votesByStepId[action.stepId].filter(v => !v.user || v.user.uniqueId !== action.vote.user.uniqueId);
  } else {
    votesByStepId[action.stepId].slice(votesByStepId[action.stepId].findIndex(v => v.user === null), 1);
  }
  const proposalsById = state.proposalsById;
  const userVotesByStepId = state.userVotesByStepId;
  userVotesByStepId[action.stepId] = userVotesByStepId[action.stepId].filter(voteId => voteId !== action.proposalId);
  proposalsById[action.proposalId] = { ...proposal, votesCountByStepId, votesByStepId };
  const creditsLeftByStepId = state.creditsLeftByStepId;
  creditsLeftByStepId[action.stepId] += proposal.estimation || 0;
  return {
    ...state,
    proposalsById,
    userVotesByStepId,
    creditsLeftByStepId,
    isVoting: false,
    currentDeletingVote: null,
  };
};

const updateSelectionStatusSucceedReducer = (state: State, action): Exact<State> => {
  const proposalsById = state.proposalsById;
  const proposal = proposalsById[action.proposalId];
  const selections = proposal.selections.map((s) => {
    if (s.step.id === action.stepId) {
      s.status = action.status;
    }
    return s;
  });
  proposalsById[action.proposalId] = { ...proposal, selections };
  const lastEditedStepId = action.status === -1 ? null : action.stepId;
  return { ...state, proposalsById, lastEditedStepId, lastNotifiedStepId: null };
};

const updateProposalStatusReducer = (state: State, action): Exact<State> => {
  const proposalsById = state.proposalsById;
  const proposal = proposalsById[action.proposalId];
  proposalsById[action.proposalId] = { ...proposal, status: action.status };
  const lastEditedStepId = action.status === -1 ? null : action.stepId;
  return { ...state, proposalsById, lastEditedStepId, lastNotifiedStepId: null };
};

const unselectReducer = (state: State, action): Exact<State> => {
  const proposalsById = state.proposalsById;
  const proposal = proposalsById[action.proposalId];
  const selections = proposal.selections.filter(s => s.step.id !== action.stepId);
  proposalsById[action.proposalId] = { ...proposal, selections };
  return { ...state, proposalsById, lastEditedStepId: null, lastNotifiedStepId: null };
};

const fetchSucceededReducer = (state: State, action): Exact<State> => {
  const proposalsById = action.proposals.reduce((map, obj) => {
    map[obj.id] = obj;
    return map;
  }, {});
  const proposalShowedId = action.proposals.map(proposal => proposal.id);
  return { ...state, proposalsById, proposalShowedId, isLoading: false, queryCount: action.count };
};

const selectSucceededReducer = (state: State, action): Exact<State> => {
  const proposalsById = state.proposalsById;
  const proposal = proposalsById[action.proposalId];
  const selections = [...proposal.selections, { step: { id: action.stepId }, status: null }];
  proposalsById[action.proposalId] = { ...proposal, selections };
  return { ...state, proposalsById };
};

const fetchVotesSucceedReducer = (state: State, action): Exact<State> => {
  const proposal = state.proposalsById[action.proposalId];
  const votesByStepId = proposal.votesByStepId || [];
  votesByStepId[action.stepId] = action.votes;
  const proposalsById = state.proposalsById;
  proposalsById[action.proposalId] = { ...proposal, votesByStepId };
  return { ...state, proposalsById };
};

export const reducer = (state: State = initialState, action: Action): Exact<State> => {
  switch (action.type) {
    case '@@INIT':
      return { ...initialState, ...state };
    case 'proposal/CHANGE_FILTER': {
      const filters = { ...state.filters, [action.filter]: action.value };
      return { ...state, filters, currentPaginationPage: 1 };
    }
    case 'proposal/OPEN_CREATE_FUSION_MODAL':
      return { ...state, isCreatingFusion: true };
    case 'proposal/CLOSE_CREATE_FUSION_MODAL':
      return { ...state, isCreatingFusion: false };
    case 'proposal/SUBMIT_FUSION_FORM':
      return { ...state, isSubmittingFusion: true };
    case 'proposal/CANCEL_SUBMIT_FUSION_FORM':
      return { ...state, isSubmittingFusion: false };
    case 'proposal/OPEN_VOTES_MODAL':
      return { ...state, currentVotesModal: { proposalId: state.currentProposalId, stepId: action.stepId } };
    case 'proposal/CLOSE_VOTES_MODAL':
      return { ...state, currentVotesModal: null };
    case 'proposal/CHANGE_ORDER':
      return { ...state, order: action.order, currentPaginationPage: 1 };
    case 'proposal/CHANGE_PAGE':
      return { ...state, currentPaginationPage: action.page };
    case 'proposal/CHANGE_TERMS':
      return { ...state, terms: action.terms, currentPaginationPage: 1 };
    case 'proposal/SUBMIT_PROPOSAL_FORM':
      return { ...state, isCreating: true };
    case 'proposal/CANCEL_SUBMIT_PROPOSAL':
      return { ...state, isCreating: false, isEditing: false };
    case 'proposal/EDIT_PROPOSAL_FORM':
      return { ...state, isEditing: true };
    case 'proposal/OPEN_EDIT_MODAL':
      return { ...state, showEditModal: true };
    case 'proposal/CLOSE_EDIT_MODAL':
      return { ...state, showEditModal: false, isEditing: false };
    case 'proposal/OPEN_DELETE_MODAL':
      return { ...state, showDeleteModal: true };
    case 'proposal/CLOSE_DELETE_MODAL':
      return { ...state, showDeleteModal: false, isDeleting: false };
    case 'proposal/OPEN_CREATE_MODAL':
      return { ...state, showCreateModal: true };
    case 'proposal/CLOSE_CREATE_MODAL':
      return { ...state, showCreateModal: false, isCreating: false };
    case 'proposal/OPEN_VOTE_MODAL':
      return { ...state, currentVoteModal: action.id };
    case 'proposal/CLOSE_VOTE_MODAL':
      return { ...state, currentVoteModal: null, isVoting: false };
    case 'proposal/VOTE_REQUESTED':
      return { ...state, isVoting: true };
    case 'proposal/VOTE_FAILED':
      return { ...state, isVoting: false };
    case 'proposal/SELECT_SUCCEED':
      return selectSucceededReducer(state, action);
    case 'proposal/UNSELECT_SUCCEED':
      return unselectReducer(state, action);
    case 'proposal/UPDATE_PROPOSAL_STATUS_SUCCEED':
      return updateProposalStatusReducer(state, action);
    case 'proposal/UPDATE_SELECTION_STATUS_SUCCEED':
      return updateSelectionStatusSucceedReducer(state, action);
    case 'proposal/DELETE_VOTE_REQUESTED':
      return { ...state, currentDeletingVote: action.proposalId };
    case 'proposal/VOTE_SUCCEEDED':
      return voteReducer(state, action);
    case 'proposal/DELETE_VOTE_SUCCEEDED':
      return deleteVoteReducer(state, action);
    case 'proposal/DELETE_REQUEST':
      return { ...state, isDeleting: true };
    case 'proposal/FETCH_REQUESTED':
      return { ...state, isLoading: true };
    case 'proposal/FETCH_SUCCEEDED':
      return fetchSucceededReducer(state, action);
    case 'proposal/LOAD_SELECTIONS_SUCCEEDED': {
      const proposalsById = state.proposalsById;
      proposalsById[action.proposalId] = {
        ...state.proposalsById[action.proposalId], selections: action.selections };
      return { ...state, proposalsById };
    }
    case 'proposal/POSTS_FETCH_SUCCEEDED': {
      const posts = action.posts;
      const proposalsById = state.proposalsById;
      proposalsById[action.proposalId] = { ...state.proposalsById[action.proposalId], posts };
      return { ...state, proposalsById };
    }
    case 'proposal/VOTES_FETCH_SUCCEEDED':
      return fetchVotesSucceedReducer(state, action);
    case 'proposal/POSTS_FETCH_FAILED': {
      console.log('proposal/POSTS_FETCH_FAILED', action.error); // eslint-disable-line no-console
      return state;
    }
    case 'proposal/SEND_PROPOSAL_NOTIFICATION_SUCCEED': {
      return { ...state, lastNotifiedStepId: action.stepId };
    }
    case 'proposal/SEND_PROPOSAL_NOTIFICATION_ERROR': {
      console.log('proposal/SEND_PROPOSAL_NOTIFICATION_ERROR', action.error); // eslint-disable-line no-console
      return state;
    }
    default:
      return state;
  }
};
