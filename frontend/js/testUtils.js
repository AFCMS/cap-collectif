// @flow
import * as React from 'react';
import ReactDOM from 'react-dom';
import { ThemeProvider } from 'styled-components';
import { Provider } from 'react-redux';
import { IntlProvider } from 'react-intl-redux';
import { RelayEnvironmentProvider } from 'react-relay';
import { theme } from '~/styles/theme';
import appStore from '~/stores/AppStore';
import type { FeatureFlagType } from '~relay/useFeatureFlagQuery.graphql';

export const mockRandomValues = () => {
  global.Math.random = () => 0.5;
};

export const enableFeatureFlags = (flags: [FeatureFlagType]) => {
  global.mockFeatureFlag.mockImplementation((flag: FeatureFlagType) => {
    if (flags.includes(flag)) {
      return true;
    }
    return false;
  });
};

export const disableFeatureFlags = () => {
  global.mockFeatureFlag.mockImplementation(() => false);
};

export const mockUrl = (url: string) => {
  // https://stackoverflow.com/questions/54021037/how-to-mock-window-location-href-with-jest-vuejs
  // $FlowFixMe assign new URL(...) to window.location because property location is not writable
  delete window.location;
  // $FlowFixMe assign new URL(...) to window.location because property location is not writable
  window.location = new URL(url);
};

export const addsSupportForPortals = () => {
  // See: https://github.com/facebook/react/issues/11565
  // $FlowFixMe
  ReactDOM.createPortal = jest.fn(element => {
    return element;
  });
};

export const clearSupportForPortals = () => {
  // $FlowFixMe
  ReactDOM.createPortal.mockClear();
};

type Props = {| +children: React.Node, +store?: any |};

export const MockProviders = ({ children, store = {} }: Props) => {
  return (
    <Provider store={appStore(store)}>
      <IntlProvider>
        <ThemeProvider theme={theme}>{children}</ThemeProvider>
      </IntlProvider>
    </Provider>
  );
};

type RelaySuspensFragmentTestProps = {| environment: any, ...Props |};

export const RelaySuspensFragmentTest = ({
  children,
  environment,
  store = {},
}: RelaySuspensFragmentTestProps) => {
  return (
    <MockProviders store={store}>
      <RelayEnvironmentProvider environment={environment}>
        <React.Suspense>{children}</React.Suspense>
      </RelayEnvironmentProvider>
    </MockProviders>
  );
};

export default MockProviders;
