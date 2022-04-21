import React from 'react';
import { IntlMixin } from 'react-intl';
import SynthesisElementActions from '../../../actions/SynthesisElementActions';
import SynthesisElementStore from '../../../stores/SynthesisElementStore';
import NotationButtons from './../Edit/NotationButtons';
import ElementsFinder from './../ElementsFinder';
import { Button, Modal, Input } from 'react-bootstrap';
import DeepLinkStateMixin from '../../../utils/DeepLinkStateMixin';
import { hashHistory } from 'react-router';

const PublishModal = React.createClass({
  propTypes: {
    synthesis: React.PropTypes.object.isRequired,
    element: React.PropTypes.object.isRequired,
    show: React.PropTypes.bool.isRequired,
    toggle: React.PropTypes.func.isRequired,
    process: React.PropTypes.func,
  },
  mixins: [IntlMixin, DeepLinkStateMixin],

  getDefaultProps() {
    return {
      process: null,
    };
  },

  getInitialState() {
    const element = this.props.element;
    return {
      notation: element ? element.notation : null,
      parent: element ? element.parent : null,
      comment: element ? element.comment : null,
      title: element ? element.title : null,
      description: element ? element.description : null,
      elements: [],
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
    this.fetchElements();
  },

  componentWillReceiveProps(nextProps) {
    const { element } = this.props;
    if (nextProps.element) {
      if (!element || element.id !== nextProps.element.id) {
        this.setState({
          notation: nextProps.element.notation,
          parent: nextProps.element.parent,
          comment: nextProps.element.comment,
          title: nextProps.element.title,
          description: nextProps.element.description,
          expanded: this.getExpandedBasedOnElement(),
        });
      }
    }
  },

  componentWillUnmount() {
    SynthesisElementStore.removeChangeListener(this.onChange);
  },

  onChange() {
    this.fetchElements();
  },

  getExpandedBasedOnElement() {
    const expanded = {
      root: true,
    };
    const element = this.props.element;
    if (this.state.elements && element && element.id !== 'root') {
      expanded[element.id] = true;
      if (element) {
        element.path.split(',').map((id) => {
          expanded[id] = true;
        });
      }
    }
    return expanded;
  },

  setNotation(notation) {
    this.setState({
      notation,
    });
  },

  setParent(element) {
    if (element) {
      const value = element !== 'root' ? element : null;
      this.setState({
        parent: value,
      });
    }
  },

  hide() {
    const { toggle } = this.props;
    toggle(false);
  },

  show() {
    const { toggle } = this.props;
    toggle(true);
  },

  publish() {
    const {
      process,
      synthesis,
    } = this.props;
    this.hide();
    const data = {
      archived: true,
      published: true,
      notation: this.state.notation ? this.state.notation : 0,
      parent: this.state.parent,
      comment: this.state.comment,
      description: this.state.description,
      title: this.state.title,
    };
    if (typeof process === 'function') {
      const element = this.props.element;
      element.archived = data.archived;
      element.published = data.published;
      element.notation = data.notation;
      element.parent = data.parent;
      element.comment = data.comment;
      element.description = data.description;
      element.title = data.title;
      process(element);
      return;
    }
    SynthesisElementActions.update(synthesis.id, this.props.element.id, data);
    hashHistory.push('inbox', { type: 'new' });
  },

  expandItem(element) {
    const expanded = this.state.expanded;
    expanded[element.id] = !this.state.expanded[element.id];
    this.setState({
      expanded,
    });
  },

  fetchElements() {
    this.setState({
      elements: SynthesisElementStore.elements.notIgnoredTree,
      expanded: SynthesisElementStore.expandedItems.nav,
      selectedId: SynthesisElementStore.selectedNavItem,
      isLoading: false,
    });
  },

  loadElementsTreeFromServer() {
    const { synthesis } = this.props;
    SynthesisElementActions.loadElementsTreeFromServer(
      synthesis.id,
      'notIgnored'
    );
  },

  renderTitle() {
    return (
      <div className="modal__action">
        <h2 className="h4">
          {` ${this.getIntlMessage('synthesis.edition.action.publish.field.title')}`}
        </h2>
        <Input type="text" id="publish_element_title" name="publish_element[title]" className="publish-element__title" valueLink={this.linkState('title')} />
      </div>
    );
  },

  renderParent() {
    return (
      <div className="modal__action">
        <h2 className="h4">
          {` ${this.getIntlMessage('synthesis.edition.action.publish.field.parent')}`}
        </h2>
        {this.renderParentFinder()}
      </div>
    );
  },

  renderParentFinder() {
    const {
      element,
      synthesis,
    } = this.props;
    const parentId = this.state.parent ? this.state.parent.id : 'root';
    return (
      <ElementsFinder
        synthesis={synthesis}
        type="notIgnored"
        elements={this.state.elements}
        expanded={this.state.expanded}
        selectedId={parentId}
        onSelect={this.setParent}
        onExpand={this.expandItem}
        hiddenElementId={element.id}
      />
    );
  },

  renderNotation() {
    return (
      <div className="modal__action">
        <h2 className="h4">
          {` ${this.getIntlMessage('synthesis.edition.action.publish.field.notation')}`}
          <span className="small excerpt action__title-right">{`\t${this.getIntlMessage('synthesis.edition.action.publish.optional')}`}</span>
        </h2>
        <NotationButtons notation={this.state.notation} onChange={this.setNotation} block />
        <p className="small excerpt action__help">{this.getIntlMessage('synthesis.edition.action.publish.help.notation')}</p>
      </div>
    );
  },

  renderComment() {
    return (
      <div className="modal__action">
        <h2 className="h4">
          {` ${this.getIntlMessage('synthesis.edition.action.publish.field.comment')}`}
          <span className="small excerpt action__title-right">{`\t${this.getIntlMessage('synthesis.edition.action.publish.optional')}`}</span>
        </h2>
        <form id="publish_element" name="publish_element">
          <Input type="textarea" id="publish_element_comment" name="publish_element[comment]" className="publish-element__comment" valueLink={this.linkState('comment')} />
        </form>
      </div>
    );
  },

  renderDescription() {
    return (
      <div className="modal__action">
        <h2 className="h4">
          {` ${this.getIntlMessage('synthesis.edition.action.publish.field.description')}`}
          <span className="small excerpt action__title-right">{`\t${this.getIntlMessage('synthesis.edition.action.publish.optional')}`}</span>
        </h2>
        <form id="publish_element" name="publish_element">
          <Input type="textarea" id="publish_element_description" name="publish_element[description]" className="publish-element__description" valueLink={this.linkState('description')} />
        </form>
      </div>
    );
  },

  render() {
    const { show } = this.props;
    return (
      <Modal show={show} onHide={this.hide} animation={false} dialogClassName="modal--publish">
        <Modal.Header closeButton>
          <Modal.Title>{this.getIntlMessage('synthesis.edition.action.publish.title')}</Modal.Title>
        </Modal.Header>
        <Modal.Body>
          {this.renderTitle()}
          {this.renderDescription()}
          {this.renderParent()}
          {this.renderNotation()}
          {this.renderComment()}
        </Modal.Body>
        <Modal.Footer>
          <Button type="button" onClick={this.hide}>{this.getIntlMessage('synthesis.edition.action.publish.btn_cancel')}</Button>
          <Button bsStyle="primary" type="submit" onClick={this.publish}>{this.getIntlMessage('synthesis.edition.action.publish.btn_submit')}</Button>
        </Modal.Footer>
      </Modal>
    );
  },

});

export default PublishModal;
