// @flow
import { compose, createStore, applyMiddleware, combineReducers } from 'redux';
import createSagaMiddleware from 'redux-saga';
import { reducer as formReducer } from 'redux-form';
import LocalStorageService from '../services/LocalStorageService';
import { reducer as reportReducer } from '../redux/modules/report';
import { reducer as projectReducer, saga as projectSaga } from '../redux/modules/project';
import { reducer as ideaReducer, saga as ideaSaga } from '../redux/modules/idea';
import { reducer as proposalReducer, saga as proposalSaga } from '../redux/modules/proposal';
import { reducer as opinionReducer, saga as opinionSaga } from '../redux/modules/opinion';
import { reducer as userReducer } from '../redux/modules/user';
import type { SubmitConfirmPasswordAction } from '../redux/modules/user';

export default function configureStore(initialState: Object) {
  if (initialState.user.user === null) {
    LocalStorageService.remove('jwt');
  }
  if (initialState.project && initialState.proposal && initialState.project.currentProjectStepById && LocalStorageService.isValid('proposal.filtersByStep')) {
    const filtersByStep = LocalStorageService.get('proposal.filtersByStep') || {};
    initialState.proposal.filters = filtersByStep[initialState.project.currentProjectStepById];
  }
  if (initialState.project && initialState.proposal && initialState.project.currentProjectStepById && LocalStorageService.isValid('proposal.orderByStep')) {
    const orderByStep = LocalStorageService.get('proposal.orderByStep') || {};
    initialState.proposal.order = orderByStep[initialState.project.currentProjectStepById];
  }

  const sagaMiddleware = createSagaMiddleware();

  const reducers = {
    default: () => initialState.default,
    idea: ideaReducer,
    proposal: proposalReducer,
    project: projectReducer,
    report: reportReducer,
    user: userReducer,
    opinion: opinionReducer,
    form: formReducer.plugin({
      account: (state, action: SubmitConfirmPasswordAction) => {
        switch (action.type) {
          case 'SUBMIT_CONFIRM_PASSWORD_FORM':
            return {
              ...state,
              values: { ...state.values, password: action.password },
            };
          default:
            return state;
        }
      },
    }),
  };

  const reducer = combineReducers(reducers);
  const store = createStore(
    reducer,
    initialState,
    compose(
      applyMiddleware(sagaMiddleware),
      typeof window === 'object' && typeof window.devToolsExtension !== 'undefined' ? window.devToolsExtension() : f => f,
    ),
  );

  sagaMiddleware.run(ideaSaga);
  // $FlowFixMe
  sagaMiddleware.run(proposalSaga);
  // $FlowFixMe
  sagaMiddleware.run(projectSaga);
  sagaMiddleware.run(opinionSaga);

  return store;
}
