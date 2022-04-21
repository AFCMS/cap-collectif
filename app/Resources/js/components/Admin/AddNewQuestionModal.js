// @flow
import React, { PropTypes } from 'react';
import { Modal, Button } from 'react-bootstrap';
import { IntlMixin } from 'react-intl';
import { connect } from 'react-redux';
import { submit, isSubmitting } from 'redux-form';
import CloseButton from '../Form/CloseButton';
import { hideNewFieldModal } from '../../redux/modules/default';
import type { Dispatch, State } from '../../types';
import AddNewQuestionForm, { formName } from './AddNewQuestionForm';

export const AddNewQuestionModal = React.createClass({
  propTypes: {
    submitting: PropTypes.bool.isRequired,
    show: PropTypes.bool.isRequired,
    onClose: PropTypes.func.isRequired,
    onSubmit: PropTypes.func.isRequired,
  },
  mixins: [IntlMixin],

  render() {
    const {
      submitting,
      show,
      onClose,
      onSubmit,
    } = this.props;
    return (
      <Modal
        animation={false}
        show={show}
        onHide={onClose}
        autoFocus
        bsSize="small"
        aria-labelledby="contained-modal-title-lg"
      >
        <Modal.Header closeButton>
          <Modal.Title id="contained-modal-title-lg">
            Modifier le champ supplémentaire
          </Modal.Title>
        </Modal.Header>
        <Modal.Body>
          <AddNewQuestionForm />
        </Modal.Body>
        <Modal.Footer>
          <CloseButton onClose={onClose} />
          <Button
            id="confirm-new-question"
            type="submit"
            disabled={submitting}
            onClick={onSubmit}
            bsStyle="primary"
          >
            {
                submitting
              ? this.getIntlMessage('global.loading')
              : this.getIntlMessage('global.save')
            }
          </Button>
        </Modal.Footer>
      </Modal>
    );
  },
});

const mapStateToProps = (state: State) => ({
  submitting: isSubmitting(formName)(state),
  show: state.default.showNewFieldModal,
});
const mapDispatchToProps = (dispatch: Dispatch) => ({
  onSubmit: (e: Event) => {
    e.preventDefault();
    dispatch(submit(formName));
  },
  onClose: () => { dispatch(hideNewFieldModal()); },
});

const connector = connect(mapStateToProps, mapDispatchToProps);
export default connector(AddNewQuestionModal);
