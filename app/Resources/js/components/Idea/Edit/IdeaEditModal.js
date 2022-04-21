// @flow
import React, { PropTypes } from 'react';
import { IntlMixin } from 'react-intl';
import { connect } from 'react-redux';
import { submit, isSubmitting } from 'redux-form';
import { Modal } from 'react-bootstrap';
import SubmitButton from '../../Form/SubmitButton';
import CloseButton from '../../Form/CloseButton';
import IdeaEditForm, { formName } from './IdeaEditForm';
import { hideIdeaEditModal } from '../../../redux/modules/idea';

const IdeaEditModal = React.createClass({
  propTypes: {
    idea: PropTypes.object.isRequired,
    show: PropTypes.bool.isRequired,
    dispatch: PropTypes.func.isRequired,
    submitting: PropTypes.bool.isRequired,
  },
  mixins: [IntlMixin],

  render() {
    const { idea, show, dispatch, submitting } = this.props;
    return (
      <div>
        <Modal
          animation={false}
          show={show}
          onHide={() => {
            dispatch(hideIdeaEditModal());
          }}
          bsSize="large"
          aria-labelledby="contained-modal-title-lg">
          <Modal.Header closeButton>
            <Modal.Title id="contained-modal-title-lg">
              {this.getIntlMessage('global.edit')}
            </Modal.Title>
          </Modal.Header>
          <Modal.Body>
            <IdeaEditForm idea={idea} />
          </Modal.Body>
          <Modal.Footer>
            <CloseButton
              onClose={() => {
                dispatch(hideIdeaEditModal());
              }}
            />
            <SubmitButton
              id="confirm-idea-edit"
              isSubmitting={submitting}
              onSubmit={() => {
                dispatch(submit(formName));
              }}
            />
          </Modal.Footer>
        </Modal>
      </div>
    );
  },
});

export default connect((state: State, props) => ({
  show: state.idea.showEditModal === props.idea.id,
  submitting: isSubmitting(formName)(state),
}))(IdeaEditModal);
