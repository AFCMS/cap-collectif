import React from 'react';
import { IntlMixin } from 'react-intl';
import { Nav, NavItem } from 'react-bootstrap';
import { LinkContainer } from 'react-router-bootstrap';

import SynthesisElementStore from '../../../stores/SynthesisElementStore';
import SynthesisElementActions from '../../../actions/SynthesisElementActions';

import CreateModal from './../Create/CreateModal';
import ElementsFinder from './../ElementsFinder';
import Loader from '../../Utils/Loader';

const SideMenu = React.createClass({
  propTypes: {
    synthesis: React.PropTypes.object,
  },
  contextTypes: {
    router: React.PropTypes.object.isRequired,
  },
  mixins: [IntlMixin],

  getInitialState() {
    return {
      showCreateModal: false,
      navItems: [],
      selectedId: 'root',
      expanded: {
        root: true,
      },
      isLoading: true,
    };
  },

  componentWillMount() {
    SynthesisElementStore.addChangeListener(this.onChange);
  },

  componentDidMount() {
    this.loadElementsTreeFromServer();
  },

  componentWillUnmount() {
    this.toggleCreateModal(false);
    SynthesisElementStore.removeChangeListener(this.onChange);
  },

  onChange() {
    this.setState({
      navItems: SynthesisElementStore.elements.notIgnoredTree,
      expanded: SynthesisElementStore.expandedItems.nav,
      selectedId: SynthesisElementStore.selectedNavItem,
      isLoading: false,
    });
  },

  toggleExpand(element) {
    SynthesisElementActions.expandTreeItem('nav', element.id, !this.state.expanded[element.id]);
  },

  selectItem(element) {
    SynthesisElementActions.selectNavItem(element.id);
    if (element.id !== 'root') {
      this.context.router.push(`element/${element.id}`);
    }
  },

  showCreateModal() {
    this.toggleCreateModal(true);
  },

  hideCreateModal() {
    this.toggleCreateModal(false);
  },

  toggleCreateModal(value) {
    this.setState({
      showCreateModal: value,
    });
  },

  loadElementsTreeFromServer() {
    SynthesisElementActions.loadElementsTreeFromServer(
      this.props.synthesis.id,
      'notIgnored'
    );
  },

  renderContributionsButton() {
    return (
      <LinkContainer to="/folder_manager">
        <NavItem className="menu__link" bsStyle="link">
          <i className="cap cap-baloon"></i> {this.getIntlMessage('synthesis.edition.sideMenu.contributions')}
        </NavItem>
      </LinkContainer>
    );
  },

  renderTree() {
    if (this.state.isLoading) {
      return <Loader show={this.state.isLoading} />;
    }
    return (
      <ElementsFinder
        synthesis={this.props.synthesis}
        elements={this.state.navItems}
        expanded={this.state.expanded}
        selectedId={this.state.selectedId}
        onExpand={this.toggleExpand}
        onSelect={this.selectItem}
        type="notIgnored"
        itemClass="menu__link"
      />
    );
  },

  renderCreateButton() {
    return (
      <NavItem className="menu__link menu__action" onClick={this.showCreateModal.bind(null, this)}>
          <i className="cap cap-folder-add"></i> {this.getIntlMessage('synthesis.edition.action.create.label')}
      </NavItem>
    );
  },

  renderManageButton() {
    return (
      <LinkContainer to="/folder-manager">
        <NavItem className="menu__link menu__action">
          <i className="cap cap-folder-edit"></i> {this.getIntlMessage('synthesis.edition.action.manage.label')}
        </NavItem>
      </LinkContainer>
    );
  },

  render() {
    return (
      <div className="synthesis__side-menu">
        <div className="menu__tree">
          {this.renderTree()}
        </div>
        <Nav stacked className="menu__actions menu--fixed">
          {this.renderCreateButton()}
          {this.renderManageButton()}
        </Nav>
        <CreateModal synthesis={this.props.synthesis} show={this.state.showCreateModal} toggle={this.toggleCreateModal} elements={this.state.navItems} selectedId={this.state.selectedId} />
      </div>
    );
  },

});

export default SideMenu;
