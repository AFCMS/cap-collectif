// @flow
import * as React from 'react';
import { Alert, Modal } from 'react-bootstrap';
import { FormattedMessage } from 'react-intl';
import { connect } from 'react-redux';
import { isSubmitting, submit } from 'redux-form';
import { createFragmentContainer, graphql } from 'react-relay';
import CloseButton from '../../Form/CloseButton';
import SubmitButton from '../../Form/SubmitButton';
import RegistrationForm, { form } from './RegistrationForm';
import LoginSocialButtons from '../Login/LoginSocialButtons';
import { closeRegistrationModal, hideChartModal } from '~/redux/modules/user';
import type { State } from '~/types';
import WYSIWYGRender from '../../Form/WYSIWYGRender';
import type { RegistrationModal_query } from '~relay/RegistrationModal_query.graphql';

type StateProps = {|
  +show: boolean,
  +textTop: ?string,
  +textBottom: ?string,
  +submitting: boolean,
  +displayChartModal: boolean,
  +charterBody?: ?string,
|};

type DispatchProps = {|
  +onClose: () => typeof closeRegistrationModal,
  +onSubmit: () => typeof submit,
  +onCloseChart: () => typeof hideChartModal,
|};

type Props = {|
  ...StateProps,
  ...DispatchProps,
  query: RegistrationModal_query,
|};

export const RegistrationModal = ({
  submitting,
  onSubmit,
  onClose,
  show,
  textTop,
  textBottom,
  displayChartModal,
  onCloseChart,
  charterBody,
  query,
}: Props) => (
  <>
    <Modal
      animation={false}
      show={displayChartModal}
      autoFocus
      onHide={onCloseChart}
      bsSize="medium"
      aria-labelledby="contained-modal-title-lg"
      enforceFocus={false}>
      <Modal.Header closeButton>
        <Modal.Title id="contained-modal-title-lg" componentClass="h1">
          <FormattedMessage id="charter" />
        </Modal.Title>
      </Modal.Header>
      <Modal.Body>
        <WYSIWYGRender value={charterBody} />
      </Modal.Body>
      <Modal.Footer>
        <CloseButton label="global.close" onClose={onCloseChart} />
      </Modal.Footer>
    </Modal>
    <Modal
      animation={false}
      show={show}
      autoFocus
      onHide={onClose}
      bsSize="small"
      aria-labelledby="contained-modal-title-lg"
      enforceFocus={false}>
      <Modal.Header closeButton>
        <Modal.Title id="contained-modal-title-lg" componentClass="h1">
          <FormattedMessage id="global.register" />
        </Modal.Title>
      </Modal.Header>
      <Modal.Body>
        {textTop && (
          <Alert bsStyle="info" className="text-center">
            <WYSIWYGRender value={textTop} />
          </Alert>
        )}
        <LoginSocialButtons prefix="registration." />

        <RegistrationForm query={query} />

        {textBottom && (
          <WYSIWYGRender className="text-center small excerpt mt-15" value={textBottom} />
        )}
      </Modal.Body>
      <Modal.Footer>
        <CloseButton onClose={onClose} />
        <SubmitButton
          id="confirm-register"
          label="global.register"
          isSubmitting={submitting}
          onSubmit={onSubmit}
        />
      </Modal.Footer>
    </Modal>
  </>
);

const mapStateToProps = state => ({
  textTop: state.user.registration_form.topTextDisplayed
    ? state.user.registration_form.topText
    : null,
  textBottom: state.user.registration_form.bottomTextDisplayed
    ? state.user.registration_form.bottomText
    : null,
  show: state.user.showRegistrationModal,
  displayChartModal: state.user.displayChartModal,
  submitting: isSubmitting(form)(state),
  charterBody: state.default.parameters['charter.body'],
});

const mapDispatchToProps = dispatch => ({
  onClose: () => dispatch(closeRegistrationModal()),
  onSubmit: () => dispatch(submit(form)),
  onCloseChart: () => dispatch(hideChartModal()),
});

const RegistrationModalConnected = connect<Props, State, _, StateProps, _, _>(
  mapStateToProps,
  mapDispatchToProps,
)(RegistrationModal);

export default createFragmentContainer(RegistrationModalConnected, {
  query: graphql`
    fragment RegistrationModal_query on Query {
      ...RegistrationForm_query
    }
  `,
});
