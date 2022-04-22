// @flow
import { submit, change, SubmissionError } from 'redux-form';
import Fetcher from '../../services/Fetcher';
import FluxDispatcher from '../../dispatchers/AppDispatcher';
import { UPDATE_ALERT } from '../../constants/AlertConstants';
import type { Exact, Dispatch, Action } from '../../types';

export type User = {
  +id: string,
  +username: string,
  +isEmailConfirmed: boolean,
  +isPhoneConfirmed: boolean,
  +phone: string,
  +isAdmin: boolean,
  +email: string,
  +newEmailToConfirm: ?string,
  +media: ?{
    +url: string,
  },
  +roles: Array<string>,
  +displayName: string,
  +uniqueId: string,
};

export type State = {
  +showLoginModal: boolean,
  +showRegistrationModal: boolean,
  +isSubmittingAccountForm: boolean,
  +showConfirmPasswordModal: boolean,
  +confirmationEmailResent: boolean,
  +registration_form: {
    +bottomTextDisplayed: boolean,
    +topTextDisplayed: boolean,
    +bottomText: string,
    +topText: string,
    +questions: Array<Object>,
    +domains: Array<string>,
  },
  +user: ?{
    +id: string,
    +username: string,
    +isEmailConfirmed: boolean,
    +isPhoneConfirmed: boolean,
    +phone: string,
    +isAdmin: boolean,
    +email: string,
    +newEmailToConfirm: ?string,
    +media: ?{
      +url: string,
    },
    +roles: Array<string>,
    +displayName: string,
    +uniqueId: string,
  },
  +groupAdminUsersUserDeletionSuccessful: boolean,
  +groupAdminUsersUserDeletionFailed: boolean,
};

type AddRegistrationFieldAction = { type: 'ADD_REGISTRATION_FIELD_SUCCEEDED', element: Object };
type UpdateRegistrationFieldAction = {
  type: 'UPDATE_REGISTRATION_FIELD_SUCCEEDED',
  id: number,
  element: Object,
};
type CloseRegistrationModalAction = { type: 'CLOSE_REGISTRATION_MODAL' };
type ShowRegistrationModalAction = { type: 'SHOW_REGISTRATION_MODAL' };
type CloseLoginModalAction = { type: 'CLOSE_LOGIN_MODAL' };
type ShowLoginModalAction = { type: 'SHOW_LOGIN_MODAL' };
type UserRequestEmailChangeAction = { type: 'USER_REQUEST_EMAIL_CHANGE', email: string };
type StartSubmittingAccountFormAction = { type: 'SUBMIT_ACCOUNT_FORM' };
type StopSubmittingAccountFormAction = { type: 'STOP_SUBMIT_ACCOUNT_FORM' };
type CancelEmailChangeSucceedAction = { type: 'CANCEL_EMAIL_CHANGE' };
type ConfirmPasswordAction = { type: 'SHOW_CONFIRM_PASSWORD_MODAL' };
export type SubmitConfirmPasswordAction = {
  type: 'SUBMIT_CONFIRM_PASSWORD_FORM',
  password: string,
};
type CloseConfirmPasswordModalAction = { type: 'CLOSE_CONFIRM_PASSWORD_MODAL' };
type DeleteRegistrationFieldSucceededAction = {
  type: 'DELETE_REGISTRATION_FIELD_SUCCEEDED',
  id: number,
};
type ReorderSucceededAction = { type: 'REORDER_REGISTRATION_QUESTIONS', questions: Array<Object> };
type GroupAdminUsersUserDeletionSuccessfulAction = {
  type: 'GROUP_ADMIN_USERS_USER_DELETION_SUCCESSFUL',
};
type GroupAdminUsersUserDeletionFailedAction = {
  type: 'GROUP_ADMIN_USERS_USER_DELETION_FAILED',
};
type GroupAdminUsersUserDeletionResetAction = {
  type: 'GROUP_ADMIN_USERS_USER_DELETION_RESET',
};

export type UserAction =
  | UpdateRegistrationFieldAction
  | ShowRegistrationModalAction
  | CloseRegistrationModalAction
  | ShowLoginModalAction
  | CloseLoginModalAction
  | StartSubmittingAccountFormAction
  | ConfirmPasswordAction
  | StopSubmittingAccountFormAction
  | CancelEmailChangeSucceedAction
  | CloseConfirmPasswordModalAction
  | UserRequestEmailChangeAction
  | DeleteRegistrationFieldSucceededAction
  | ReorderSucceededAction
  | AddRegistrationFieldAction
  | SubmitConfirmPasswordAction
  | GroupAdminUsersUserDeletionSuccessfulAction
  | GroupAdminUsersUserDeletionFailedAction
  | GroupAdminUsersUserDeletionResetAction;

const initialState: State = {
  showLoginModal: false,
  showRegistrationModal: false,
  isSubmittingAccountForm: false,
  confirmationEmailResent: false,
  showConfirmPasswordModal: false,
  user: null,
  registration_form: {
    bottomText: '',
    topText: '',
    bottomTextDisplayed: false,
    topTextDisplayed: false,
    questions: [],
    domains: [],
  },
  groupAdminUsersUserDeletionSuccessful: false,
  groupAdminUsersUserDeletionFailed: false,
};

export const addRegistrationFieldSucceeded = (element: Object): AddRegistrationFieldAction => ({
  type: 'ADD_REGISTRATION_FIELD_SUCCEEDED',
  element,
});
export const updateRegistrationFieldSucceeded = (
  id: number,
  element: Object,
): UpdateRegistrationFieldAction => ({ type: 'UPDATE_REGISTRATION_FIELD_SUCCEEDED', element, id });
export const deleteRegistrationFieldSucceeded = (
  id: number,
): DeleteRegistrationFieldSucceededAction => ({ type: 'DELETE_REGISTRATION_FIELD_SUCCEEDED', id });
export const showRegistrationModal = (): ShowRegistrationModalAction => ({
  type: 'SHOW_REGISTRATION_MODAL',
});
export const closeRegistrationModal = (): CloseRegistrationModalAction => ({
  type: 'CLOSE_REGISTRATION_MODAL',
});
export const closeLoginModal = (): CloseLoginModalAction => ({ type: 'CLOSE_LOGIN_MODAL' });
export const showLoginModal = (): ShowLoginModalAction => ({ type: 'SHOW_LOGIN_MODAL' });
export const confirmPassword = (): ConfirmPasswordAction => ({
  type: 'SHOW_CONFIRM_PASSWORD_MODAL',
});
export const closeConfirmPasswordModal = (): CloseConfirmPasswordModalAction => ({
  type: 'CLOSE_CONFIRM_PASSWORD_MODAL',
});
export const startSubmittingAccountForm = (): StartSubmittingAccountFormAction => ({
  type: 'SUBMIT_ACCOUNT_FORM',
});
export const stopSubmittingAccountForm = (): StopSubmittingAccountFormAction => ({
  type: 'STOP_SUBMIT_ACCOUNT_FORM',
});
export const userRequestEmailChange = (email: string): UserRequestEmailChangeAction => ({
  type: 'USER_REQUEST_EMAIL_CHANGE',
  email,
});
export const cancelEmailChangeSucceed = (): CancelEmailChangeSucceedAction => ({
  type: 'CANCEL_EMAIL_CHANGE',
});
export const submitConfirmPasswordFormSucceed = (
  password: string,
): SubmitConfirmPasswordAction => ({ type: 'SUBMIT_CONFIRM_PASSWORD_FORM', password });

export const setRegistrationEmailDomains = (values: {
  domains: Array<{ value: string }>,
}): Promise<*> => {
  return Fetcher.put('/registration_form', values);
};

export const login = (
  data: { username: string, password: string },
  dispatch: Dispatch,
): Promise<*> => {
  return fetch(`${window.location.protocol}//${window.location.host}/login_check`, {
    method: 'POST',
    body: JSON.stringify(data),
    credentials: 'include',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    },
  })
    .then(response => response.json())
    .then((response: { success: boolean }) => {
      if (response.success) {
        dispatch(closeLoginModal());
        window.location.reload();
        return true;
      }
      throw new SubmissionError({ _error: 'global.login_failed' });
    });
};

export const register = (values: Object, dispatch: Dispatch, { dynamicFields }: Object) => {
  const form = { ...values };
  delete form.charte;
  const responses = [];
  const apiForm = {};
  Object.keys(form).map(key => {
    if (key.startsWith('dynamic-')) {
      const question = key.split('-')[1];
      if (typeof form[key] !== 'undefined' && form[key].length > 0) {
        const field = dynamicFields.find(fi => String(fi.id) === question);
        let value = form[key];
        if (field.type === 'select') {
          value = { labels: [form[key]], other: null };
        }
        responses.push({
          question,
          value,
        });
      }
    } else {
      apiForm[key] = form[key];
    }
  });
  if (responses.length) {
    apiForm.responses = responses;
  }
  return Fetcher.post('/users', apiForm)
    .then(() => {
      FluxDispatcher.dispatch({
        actionType: 'UPDATE_ALERT',
        alert: { bsStyle: 'success', content: 'alert.success.add.user' },
      });
      login({ username: values.email, password: values.plainPassword }, dispatch);
      dispatch(closeRegistrationModal());
    })
    .catch(error => {
      const response = error.response;
      const errors: Object = { _error: 'Registration failed !' };
      if (typeof window.grecaptcha !== 'undefined') {
        window.grecaptcha.reset();
        dispatch(change('registration-form', 'captcha', null));
      }
      if (response.errors) {
        const children = response.errors.children;
        if (children.email.errors && children.email.errors.length > 0) {
          children.email.errors.map(string => {
            if (string === 'already_used_email') {
              errors.email = 'registration.constraints.email.already_used';
            } else if (string === 'check_email.domain') {
              errors.email = 'registration.constraints.email.not_authorized';
            } else {
              errors.email = `registration.constraints.${string}`;
            }
          });
        }
        if (children.captcha.errors && children.captcha.errors.length > 0) {
          errors.captcha = 'registration.constraints.captcha.invalid';
        }
        throw new SubmissionError(errors);
      }
    });
};

export const submitConfirmPasswordForm = (
  { password }: { password: string },
  dispatch: Dispatch,
): void => {
  dispatch(submitConfirmPasswordFormSucceed(password));
  dispatch(closeConfirmPasswordModal());
  setTimeout((): void => {
    dispatch(submit('account'));
  }, 1000);
};

export const cancelEmailChange = (dispatch: Dispatch, previousEmail: string): void => {
  Fetcher.post('/account/cancel_email_change').then(() => {
    dispatch(cancelEmailChangeSucceed());
    dispatch(change('account', 'email', previousEmail));
  });
};

const sendEmail = () => {
  FluxDispatcher.dispatch({
    actionType: UPDATE_ALERT,
    alert: { bsStyle: 'success', content: 'user.confirm.sent' },
  });
};

export const resendConfirmation = (): void => {
  Fetcher.post('/account/resend_confirmation_email')
    .then(sendEmail)
    .catch(sendEmail);
};

export const submitAccountForm = (values: Object, dispatch: Dispatch): Promise<*> => {
  dispatch(startSubmittingAccountForm());
  return Fetcher.put('/users/me', values)
    .then(
      (): void => {
        dispatch(stopSubmittingAccountForm());
        dispatch(userRequestEmailChange(values.email));
      },
    )
    .catch(
      ({
        response: { message, errors },
      }: {
        response: { message: string, errors: { children?: ?Object } },
      }): void => {
        dispatch(stopSubmittingAccountForm());
        if (message === 'You must specify your password to update your email.') {
          throw new SubmissionError({ _error: 'user.confirm.wrong_password' });
        }
        if (message === 'Already used email.') {
          throw new SubmissionError({ _error: 'registration.constraints.email.already_used' });
        }
        if (message === 'Validation Failed.') {
          if (
            errors.children &&
            errors.children.newEmailToConfirm &&
            errors.children.newEmailToConfirm.errors &&
            Array.isArray(errors.children.newEmailToConfirm.errors) &&
            errors.children.newEmailToConfirm.errors[0]
          ) {
            throw new SubmissionError({
              // $FlowFixMe
              _error: `registration.constraints.${errors.children.newEmailToConfirm.errors[0]}`,
            });
          }
        }
        throw new SubmissionError({ _error: 'global.error' });
      },
    );
};

const reorderSuceeded = (questions: Array<Object>): ReorderSucceededAction => ({
  type: 'REORDER_REGISTRATION_QUESTIONS',
  questions,
});
export const reorderRegistrationQuestions = (questions: Array<Object>, dispatch: Dispatch) => {
  Fetcher.patch('/registration_form/questions', { questions });
  dispatch(reorderSuceeded(questions));
};

export const groupAdminUsersUserDeletionSuccessful = (): GroupAdminUsersUserDeletionSuccessfulAction => ({
  type: 'GROUP_ADMIN_USERS_USER_DELETION_SUCCESSFUL',
});
export const groupAdminUsersUserDeletionFailed = (): GroupAdminUsersUserDeletionFailedAction => ({
  type: 'GROUP_ADMIN_USERS_USER_DELETION_FAILED',
});
export const groupAdminUsersUserDeletionReset = (): GroupAdminUsersUserDeletionResetAction => ({
  type: 'GROUP_ADMIN_USERS_USER_DELETION_RESET',
});

export const reducer = (state: State = initialState, action: Action): Exact<State> => {
  switch (action.type) {
    case '@@INIT':
      return { ...initialState, ...state };
    case 'DELETE_REGISTRATION_FIELD_SUCCEEDED': {
      const index = state.registration_form.questions.findIndex(el => el.id === action.id);
      return {
        ...state,
        registration_form: {
          ...state.registration_form,
          questions: [
            ...state.registration_form.questions.slice(0, index),
            ...state.registration_form.questions.slice(index + 1),
          ],
        },
      };
    }
    case 'UPDATE_REGISTRATION_FIELD_SUCCEEDED': {
      const index = state.registration_form.questions.findIndex(el => el.id === action.id);
      return {
        ...state,
        registration_form: {
          ...state.registration_form,
          questions: [
            ...state.registration_form.questions.slice(0, index),
            action.element,
            ...state.registration_form.questions.slice(index + 1),
          ],
        },
      };
    }
    case 'ADD_REGISTRATION_FIELD_SUCCEEDED': {
      return {
        ...state,
        registration_form: {
          ...state.registration_form,
          questions: [...state.registration_form.questions, action.element],
        },
      };
    }
    case 'REORDER_REGISTRATION_QUESTIONS': {
      return {
        ...state,
        registration_form: {
          ...state.registration_form,
          questions: action.questions,
        },
      };
    }
    case 'SHOW_REGISTRATION_MODAL':
      return { ...state, showRegistrationModal: true };
    case 'CLOSE_REGISTRATION_MODAL':
      return { ...state, showRegistrationModal: false };
    case 'SHOW_LOGIN_MODAL':
      return { ...state, showLoginModal: true };
    case 'CLOSE_LOGIN_MODAL':
      return { ...state, showLoginModal: false };
    case 'CANCEL_EMAIL_CHANGE':
      return {
        ...state,
        user: { ...state.user, newEmailToConfirm: null },
        confirmationEmailResent: false,
      };
    case 'SUBMIT_ACCOUNT_FORM':
      return { ...state, isSubmittingAccountForm: true };
    case 'STOP_SUBMIT_ACCOUNT_FORM':
      return { ...state, isSubmittingAccountForm: false };
    case 'USER_REQUEST_EMAIL_CHANGE':
      return { ...state, user: { ...state.user, newEmailToConfirm: action.email } };
    case 'SHOW_CONFIRM_PASSWORD_MODAL':
      return { ...state, showConfirmPasswordModal: true };
    case 'CLOSE_CONFIRM_PASSWORD_MODAL':
      return { ...state, showConfirmPasswordModal: false };
    case 'GROUP_ADMIN_USERS_USER_DELETION_SUCCESSFUL':
      return { ...state, groupAdminUsersUserDeletionSuccessful: true };
    case 'GROUP_ADMIN_USERS_USER_DELETION_FAILED':
      return { ...state, groupAdminUsersUserDeletionFailed: true };
    case 'GROUP_ADMIN_USERS_USER_DELETION_RESET':
      return {
        ...state,
        groupAdminUsersUserDeletionSuccessful: false,
        groupAdminUsersUserDeletionFailed: false,
      };
    default:
      return state;
  }
};
