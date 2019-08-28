// @flow
/* eslint-env jest */
import { type FormProps } from 'redux-form';
import { type IntlShape } from 'react-intl';
import type { RelayPaginationProp, RelayRefetchProp } from 'react-relay';

export const $refType: any = null;
export const $fragmentRefs: any = null;

export const intlMock: IntlShape = {
  locale: 'fr-FR',
  formats: {},
  messages: {},
  now: () => 0,
  // $FlowFixMe
  formatHTMLMessage: (message: string) => String(message),
  formatPlural: (message: string) => String(message),
  formatNumber: (message: string) => String(message),
  formatRelative: (message: string) => String(message),
  formatTime: (message: string) => String(message),
  formatDate: (message: string) => String(message),
  // $FlowFixMe
  formatMessage: (message: string) => String(message.id),
};

export const formMock: FormProps = {
  anyTouched: false,
  array: {
    insert: jest.fn(),
    move: jest.fn(),
    pop: jest.fn(),
    push: jest.fn(),
    remove: jest.fn(),
    removeAll: jest.fn(),
    shift: jest.fn(),
    splice: jest.fn(),
    swap: jest.fn(),
    unshift: jest.fn(),
  },
  asyncValidate: jest.fn(),
  asyncValidating: false,
  autofill: jest.fn(),
  blur: jest.fn(),
  change: jest.fn(),
  clearAsyncError: jest.fn(),
  clearSubmit: jest.fn(),
  destroy: jest.fn(),
  dirty: false,
  dispatch: jest.fn(),
  error: null,
  form: 'formName',
  handleSubmit: jest.fn(),
  initialize: jest.fn(),
  initialized: true,
  initialValues: {},
  invalid: false,
  pristine: true,
  reset: jest.fn(),
  submitting: false,
  submitFailed: false,
  submitSucceeded: false,
  touch: jest.fn(),
  untouch: jest.fn(),
  valid: true,
  warning: null,
};

const environment = {
  applyMutation: jest.fn(),
  sendMutation: jest.fn(),
  lookup: jest.fn(),
  sendQuery: jest.fn(),
  subscribe: jest.fn(),
  streamQuery: jest.fn(),
  retain: jest.fn(),
  unstable_internal: {
    areEqualSelectors: jest.fn(),
    createFragmentSpecResolver: jest.fn(),
    createOperationDescriptor: jest.fn(),
    getDataIDsFromFragment: jest.fn(),
    getDataIDsFromObject: jest.fn(),
    getFragment: jest.fn(),
    getPluralSelector: jest.fn(),
    getRequest: jest.fn(),
    getSelector: jest.fn(),
    getSelectorsFromObject: jest.fn(),
    getSingularSelector: jest.fn(),
    getVariablesFromFragment: jest.fn(),
    getVariablesFromObject: jest.fn(),
    getVariablesFromPluralFragment: jest.fn(),
  },
  applyUpdate: jest.fn(),
  check: jest.fn(),
  commitPayload: jest.fn(),
  commitUpdate: jest.fn(),
  execute: jest.fn(),
  executeMutation: jest.fn(),
  executeWithSource: jest.fn(),
  getNetwork: jest.fn(),
  getStore: jest.fn(),
  areEqualSelectors: jest.fn(),
  createFragmentSpecResolver: jest.fn(),
  createOperationDescriptor: jest.fn(),
  getDataIDsFromFragment: jest.fn(),
  getDataIDsFromObject: jest.fn(),
  getFragment: jest.fn(),
  getPluralSelector: jest.fn(),
  getRequest: jest.fn(),
  getSelector: jest.fn(),
  getSelectorsFromObject: jest.fn(),
  getSingularSelector: jest.fn(),
  getVariablesFromFragment: jest.fn(),
  getVariablesFromObject: jest.fn(),
  getVariablesFromPluralFragment: jest.fn(),
};

export const relayPaginationMock: RelayPaginationProp = {
  environment,
  hasMore: () => false,
  isLoading: () => false,
  loadMore: jest.fn(),
  refetchConnection: jest.fn(),
};

export const relayRefetchMock: RelayRefetchProp = {
  environment,
  refetch: jest.fn(),
};
