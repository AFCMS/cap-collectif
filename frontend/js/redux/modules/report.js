// @flow
import type { Exact, Action, Dispatch, Uuid } from '~/types';
import { UPDATE_ALERT } from '~/constants/AlertConstants';
import FluxDispatcher from '../../dispatchers/AppDispatcher';
import Fetcher from '../../services/Fetcher';
import ReportMutation from '~/mutations/ReportMutation';
import { getType } from '~/components/Report/ReportForm';

export type State = {
  +currentReportingModal: ?number | ?string,
  +isLoading: boolean,
  +elements: Array<?number | ?string>,
};

export type ReportData = {|
  status: number,
  body: string,
|};

const baseUrl = (opinion: {
  parent?: ?{ id: number },
  related?: ?{ __typename: string, id: number },
  __typename?: string,
}) => {
  if (opinion.related && opinion.__typename) {
    return opinion.__typename === 'Version'
      ? `opinions/${opinion.related.id}/versions`
      : 'opinions';
  }

  return opinion.parent ? `opinions/${opinion.parent.id}/versions` : 'opinions';
};

const initialState: State = {
  currentReportingModal: null,
  isLoading: false,
  elements: [],
};

type OpenModalAction = { type: 'report/OPEN_MODAL', id: number };
type CloseModalAction = { type: 'report/CLOSE_MODAL' };
type StartReportingAction = { type: 'report/START_LOADING' };
type StopReportingAction = { type: 'report/STOP_LOADING' };
type AddReportedAction = { type: 'report/ADD_REPORTED' };

export type ReportAction =
  | OpenModalAction
  | CloseModalAction
  | StartReportingAction
  | StopReportingAction
  | AddReportedAction;

export const openModal = (id: number): OpenModalAction => ({
  type: 'report/OPEN_MODAL',
  id,
});

export const closeModal = (): CloseModalAction => ({
  type: 'report/CLOSE_MODAL',
});

const startLoading = (): StartReportingAction => ({
  type: 'report/START_LOADING',
});

const stopLoading = (): StopReportingAction => ({
  type: 'report/STOP_LOADING',
});

const addReported = (): AddReportedAction => ({
  type: 'report/ADD_REPORTED',
});

const onSuccess = (dispatch, successMessage) => {
  dispatch(addReported());
  dispatch(stopLoading());
  dispatch(closeModal());
  FluxDispatcher.dispatch({
    actionType: UPDATE_ALERT,
    alert: { bsStyle: 'success', content: successMessage },
  });
};

const submitReport = (
  url: string,
  data: ReportData,
  dispatch: Dispatch,
  successMessage: string,
): Promise<void> => {
  dispatch(startLoading());
  return new Promise((resolve, reject) => {
    Fetcher.post(url, data)
      .then(() => {
        onSuccess(dispatch, successMessage);
        resolve();
      })
      .catch(() => {
        dispatch(stopLoading());
        reject({ _error: 'Failed to submit report!' });
      });
  });
};

export const submitSourceReport = (
  opinion: Object,
  sourceId: Uuid,
  data: ReportData,
  dispatch: Dispatch,
) =>
  submitReport(
    `/${baseUrl(opinion)}/${opinion.id}/sources/${sourceId}/reports`,
    data,
    dispatch,
    'alert.success.report.source',
  );

export const submitArgumentReport = (
  opinion: Object,
  argument: Uuid,
  data: ReportData,
  dispatch: Dispatch,
) =>
  submitReport(
    `/${baseUrl(opinion)}/${opinion.id}/arguments/${argument}/reports`,
    data,
    dispatch,
    'alert.success.report.argument',
  );

export const submitOpinionReport = (opinion: Object, data: ReportData, dispatch: Dispatch) =>
  submitReport(
    `/${baseUrl(opinion)}/${opinion.id}/reports`,
    data,
    dispatch,
    'alert.success.report.proposal',
  );

export const submitCommentReport = (commentId: string, report: ReportData, dispatch: Dispatch) => {
  ReportMutation.commit({
    input: {
      reportableId: commentId,
      type: getType(report.status.toString()),
      body: report.body,
    },
  }).then(() => {
    onSuccess(dispatch, 'alert.success.report.comment');
  });
};

export const submitProposalReport = (proposal: Object, data: ReportData, dispatch: Dispatch) =>
  submitReport(
    `/proposals/${proposal.id}/reports`,
    data,
    dispatch,
    'alert.success.report.proposal',
  );

export const reducer = (state: State = initialState, action: Action): Exact<State> => {
  switch (action.type) {
    case 'report/START_LOADING':
      return { ...state, isLoading: true };
    case 'report/STOP_LOADING':
      return { ...state, isLoading: false };
    case 'report/OPEN_MODAL':
      return { ...state, currentReportingModal: action.id };
    case 'report/CLOSE_MODAL':
      return { ...state, currentReportingModal: null };
    case 'report/ADD_REPORTED': {
      return { ...state, elements: [...state.elements, state.currentReportingModal] };
    }
    default:
      return state;
  }
};
