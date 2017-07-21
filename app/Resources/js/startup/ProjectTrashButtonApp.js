import React from 'react';
import { Provider } from 'react-redux';
import ReactOnRails from 'react-on-rails';
import ProjectTrashButton from '../components/Project/ProjectTrashButton';

export default props =>
 <Provider store={ReactOnRails.getStore('appStore')}>
   <ProjectTrashButton {...props} />
 </Provider>
;
