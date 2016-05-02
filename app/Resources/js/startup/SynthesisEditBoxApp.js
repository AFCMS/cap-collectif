import React from 'react';
import { Provider } from 'react-redux';
import SynthesisBox from '../components/Synthesis/SynthesisBox';
import ReactOnRails from 'react-on-rails';
import { Router, hashHistory, IndexRoute, Route } from 'react-router';
import ElementsInbox from '../components/Synthesis/Inbox/ElementsInbox';
import ElementsSearch from '../components/Synthesis/ElementsSearch';
import FolderManager from '../components/Synthesis/FolderManager';
import EditElement from '../components/Synthesis/Edit/EditElement';
import Preview from '../components/Synthesis/View/Preview';

// See documentation for https://github.com/reactjs/react-redux.
// This is how you get props from the Rails view into the redux store.
// This code here binds your smart component to the redux store.
const mainNode = (props) => {
  const store = ReactOnRails.getStore('appStore');

  const redirectToDefaultInbox = (nextState, replace) => {
    replace({
      pathname: '/inbox/new',
    });
  };

  const SynthesisBoxWrapper = React.createClass({
    propTypes: {
      children: React.PropTypes.object.isRequired,
    },
    render() {
      return (
        <SynthesisBox
          children={this.props.children}
          {...props}
        />
      );
    },
  });

  return (
    <Provider store={store}>
      <Router history={hashHistory}>
        <Route path="/" component={SynthesisBoxWrapper}>
          <IndexRoute component={SynthesisBoxWrapper} onEnter={redirectToDefaultInbox} />
          <Route path="inbox">
            <IndexRoute component={ElementsInbox} onEnter={redirectToDefaultInbox} />
            <Route path=":type" component={ElementsInbox} />
          </Route>
          <Route path="search/:term" component={ElementsSearch} />
          <Route path="folder-manager" component={FolderManager} />
          <Route path="element/:element_id" component={EditElement} />
          <Route path="preview" component={Preview} />
        </Route>
      </Router>
    </Provider>
 );
};

export default mainNode;
