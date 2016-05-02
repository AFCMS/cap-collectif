import React from 'react';
import SynthesisElementActions from '../../../actions/SynthesisElementActions';
import { Button, Modal } from 'react-bootstrap';
import { IntlMixin, FormattedMessage } from 'react-intl';
import { hashHistory } from 'react-router';

const IgnoreButton = React.createClass({
  propTypes: {
    synthesis: React.PropTypes.object,
    element: React.PropTypes.object,
  },
  mixins: [IntlMixin],

  getInitialState() {
    return {
      showConfirmModal: false,
    };
  },

  componentWillUnmount() {
    this.hideConfirmModal();
  },

  showConfirmModal() {
    this.setState({
      showConfirmModal: true,
    });
  },

  hideConfirmModal() {
    this.setState({
      showConfirmModal: false,
    });
  },

  ignore() {
    this.hideConfirmModal();
    const data = {
      'archived': true,
      'published': false,
    };
    SynthesisElementActions.update(this.props.synthesis.id, this.props.element.id, data);
    hashHistory.push('inbox', { 'type': 'new' });
  },

  renderConfirmButton() {
    return (
      <Button bsSize="large" type="button" className="element__action-ignore" onClick={this.showConfirmModal}>
        <i className="cap cap-delete-2"></i>
      </Button>
    );
  },

  renderConfirmModal() {
    return (
      <Modal show={this.state.showConfirmModal} onHide={this.hideConfirmModal} animation={false} dialogClassName="modal--confirm">
        <Modal.Header closeButton>
          <Modal.Title>
          <FormattedMessage message={this.getIntlMessage('synthesis.edition.action.confirm_ignore.title')} name={this.props.element.title} />
          </Modal.Title>
        </Modal.Header>
        <Modal.Body>
          {this.getIntlMessage('synthesis.edition.action.confirm_ignore.body')}
        </Modal.Body>
        <Modal.Footer>
          <Button type="button" className="modal--confirm__cancel" onClick={this.hideConfirmModal}>{this.getIntlMessage('synthesis.edition.action.confirm_ignore.btn_cancel')}</Button>
          <Button className="modal--confirm__submit" bsStyle="primary" type="submit" onClick={this.ignore}>{this.getIntlMessage('synthesis.edition.action.confirm_ignore.btn_submit')}</Button>
        </Modal.Footer>
      </Modal>
    );
  },

  render() {
    return (
      <div className="element__action">
        {this.renderConfirmButton()}
        {this.renderConfirmModal()}
      </div>
    );
  },

});

export default IgnoreButton;
