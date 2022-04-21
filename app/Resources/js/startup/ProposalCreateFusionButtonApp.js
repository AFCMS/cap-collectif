import React from 'react';
import { Provider } from 'react-redux';
import ReactOnRails from 'react-on-rails';
import ProposalCreateFusionButton from '../components/Proposal/Create/ProposalCreateFusionButton';

export default props =>
 <Provider store={ReactOnRails.getStore('appStore')}>
   <ProposalCreateFusionButton {...props} />
 </Provider>
;
