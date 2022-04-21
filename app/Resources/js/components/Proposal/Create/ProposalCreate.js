import React, { PropTypes } from 'react';
import { FormattedMessage } from 'react-intl';
import { connect } from 'react-redux';
import { Modal } from 'react-bootstrap';
import ProposalCreateButton from './ProposalCreateButton';
import SubmitButton from '../../Form/SubmitButton';
import CloseButton from '../../Form/CloseButton';
import ProposalForm from '../Form/ProposalForm';
import {
  submitProposalForm,
  openCreateModal,
  closeCreateModal,
} from '../../../redux/modules/proposal';

const ProposalCreate = React.createClass({
  propTypes: {
    form: PropTypes.object.isRequired,
    categories: PropTypes.array.isRequired,
    showModal: PropTypes.bool.isRequired,
    isSubmitting: PropTypes.bool.isRequired,
    dispatch: PropTypes.func.isRequired,
  },

  render() {
    const { categories, form, showModal, isSubmitting, dispatch } = this.props;
    return (
      <div>
        <ProposalCreateButton
          disabled={!form.isContribuable}
          handleClick={() => dispatch(openCreateModal())}
        />
        <Modal
          animation={false}
          show={showModal}
          onHide={() => {
            if (
              window.confirm(
                <FormattedMessage id="proposal.confirm_close_modal" />,
              )
            ) {
              // eslint-disable-line no-alert
              dispatch(closeCreateModal());
            }
          }}
          bsSize="large"
          aria-labelledby="contained-modal-title-lg">
          <Modal.Header closeButton>
            <Modal.Title id="contained-modal-title-lg">
              {<FormattedMessage id="proposal.add" />}
            </Modal.Title>
          </Modal.Header>
          <Modal.Body>
            <ProposalForm
              form={form}
              isSubmitting={isSubmitting}
              categories={categories}
            />
          </Modal.Body>
          <Modal.Footer>
            <CloseButton onClose={() => dispatch(closeCreateModal())} />
            <SubmitButton
              id="confirm-proposal-create"
              isSubmitting={isSubmitting}
              onSubmit={() => dispatch(submitProposalForm())}
            />
          </Modal.Footer>
        </Modal>
      </div>
    );
  },
});

const mapStateToProps = state => ({
  isSubmitting: state.proposal.isCreating,
  showModal: state.proposal.showCreateModal,
});

export default connect(mapStateToProps)(ProposalCreate);
