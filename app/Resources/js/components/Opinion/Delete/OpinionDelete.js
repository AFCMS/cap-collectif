// @flow
import React from 'react';
import { FormattedHTMLMessage, FormattedMessage } from 'react-intl';
import { connect, type MapStateToProps } from 'react-redux';
import { Modal, Button } from 'react-bootstrap';
import OpinionActions from '../../../actions/OpinionActions';
import CloseButton from '../../Form/CloseButton';
import SubmitButton from '../../Form/SubmitButton';

type Props = {
  opinion: Object,
  user?: Object,
};

type State = {
  showModal: boolean,
  isSubmitting: boolean,
};

class OpinionDelete extends React.Component<Props, State> {
  static defaultProps = {
    user: null,
  };

  state = {
    showModal: false,
    isSubmitting: false,
  };

  showModal = () => {
    this.setState({ showModal: true });
  };

  hideModal = () => {
    this.setState({ showModal: false });
  };

  isVersion = () => {
    const { opinion } = this.props;
    return !!opinion.parent;
  };

  delete = () => {
    const { opinion } = this.props;
    this.setState({ isSubmitting: true });
    if (this.isVersion()) {
      OpinionActions.deleteVersion(opinion.id, opinion.parent.id).then(() => {
        window.location.href = opinion._links.parent;
      });
    } else {
      OpinionActions.deleteOpinion(opinion.id).then(() => {
        window.location.href = opinion._links.type;
      });
    }
  };

  isTheUserTheAuthor = () => {
    const { opinion, user } = this.props;
    if (opinion.author === null || !user) {
      return false;
    }
    return user.uniqueId === opinion.author.uniqueId;
  };

  render() {
    if (this.isTheUserTheAuthor()) {
      const { showModal, isSubmitting } = this.state;
      return (
        <div>
          <Button
            id="opinion-delete"
            className="pull-right btn--outline btn-danger"
            onClick={this.showModal}
            style={{ marginLeft: '5px' }}>
            <i className="cap cap-bin-2" /> {<FormattedMessage id="global.remove" />}
          </Button>
          <Modal
            animation={false}
            show={showModal}
            onHide={this.hideModal}
            bsSize="large"
            aria-labelledby="contained-modal-title-lg">
            <Modal.Header closeButton>
              <Modal.Title id="contained-modal-title-lg">
                {<FormattedMessage id="global.removeMessage" />}
              </Modal.Title>
            </Modal.Header>
            <Modal.Body>
              <p>
                <FormattedHTMLMessage id="opinion.delete.confirm" />
              </p>
            </Modal.Body>
            <Modal.Footer>
              <CloseButton onClose={this.hideModal} />
              <SubmitButton
                id="confirm-opinion-delete"
                isSubmitting={isSubmitting}
                onSubmit={this.delete}
                label="global.removeDefinitively"
                bsStyle="danger"
              />
            </Modal.Footer>
          </Modal>
        </div>
      );
    }

    return null;
  }
}

const mapStateToProps: MapStateToProps<*, *, *> = state => {
  return {
    user: state.user.user,
  };
};

export default connect(mapStateToProps)(OpinionDelete);
