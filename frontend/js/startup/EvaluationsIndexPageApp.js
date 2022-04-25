// @flow
import * as React from 'react';
import { Provider } from 'react-redux';
import ReactOnRails from 'react-on-rails';
import IntlProvider from './IntlProvider';
import EvaluationsIndexPage from '../components/Evaluation/EvaluationsIndexPage';

/**
 * @deprecated This is our legacy evaluation tool.
 */
export default (props: Object) => (
  <Provider store={ReactOnRails.getStore('appStore')}>
    <IntlProvider>
      <EvaluationsIndexPage {...props} />
    </IntlProvider>
  </Provider>
);
