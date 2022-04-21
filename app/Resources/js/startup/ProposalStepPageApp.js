import React from 'react';
import { Provider } from 'react-redux';
import ReactOnRails from 'react-on-rails';
import { IntlProvider } from 'react-intl-redux';
import ProposalStepPage from '../components/Page/ProposalStepPage';

const mainNode = props => {
  const store = ReactOnRails.getStore('appStore');

  return (
    <Provider store={store}>
      <IntlProvider>
        <ProposalStepPage {...props} />
      </IntlProvider>
    </Provider>
  );
};

export default mainNode;
