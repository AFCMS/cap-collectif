// @flow
import * as React from 'react';
import { Modal, Alert } from 'react-bootstrap';
import { FormattedMessage } from 'react-intl';
import { connect, type MapStateToProps } from 'react-redux';
import { submit, isSubmitting } from 'redux-form';
import CloseButton from '../../Form/CloseButton';
import SubmitButton from '../../Form/SubmitButton';
import RegistrationForm, { form } from './RegistrationForm';
import LoginSocialButtons from '../Login/LoginSocialButtons';
import { closeRegistrationModal, hideChartModal } from '../../../redux/modules/user';
import type { State, Dispatch } from '../../../types';

type Props = {
  show: boolean,
  onClose: Function,
  textTop?: string,
  textBottom?: string,
  submitting: boolean,
  onSubmit: Function,
  displayChartModal: boolean,
  onCloseChart: Function,
  chartBody: string,
};

export class RegistrationModal extends React.Component<Props> {
  form: ?React.Component<*>;

  render() {
    const {
      submitting,
      onSubmit,
      onClose,
      show,
      textTop,
      textBottom,
      displayChartModal,
      onCloseChart,
      chartBody,
    } = this.props;

    if (displayChartModal) {
      return (
        <Modal
          animation={false}
          show={displayChartModal}
          autoFocus
          onHide={onCloseChart}
          bsSize="medium"
          aria-labelledby="contained-modal-title-lg"
          enforceFocus={false}>
          <Modal.Header closeButton>
            <Modal.Title id="contained-modal-title-lg">
              {<FormattedMessage id="charter" />}
            </Modal.Title>
          </Modal.Header>
          <Modal.Body dangerouslySetInnerHTML={{ __html: chartBody }} />
          <Modal.Footer>
            <CloseButton label={'global.close'} onClose={onCloseChart} />
          </Modal.Footer>
        </Modal>
      );
    }
    return (
      <Modal
        animation={false}
        show={show}
        autoFocus
        onHide={onClose}
        bsSize="small"
        aria-labelledby="contained-modal-title-lg"
        enforceFocus={false}>
        <Modal.Header closeButton>
          <Modal.Title id="contained-modal-title-lg">
            {<FormattedMessage id="global.register" />}
          </Modal.Title>
        </Modal.Header>
        <Modal.Body>
          {textTop && (
            <Alert bsStyle="info" className="text-center">
              <div dangerouslySetInnerHTML={{ __html: textTop }} />
            </Alert>
          )}
          <LoginSocialButtons prefix="registration." />
          <RegistrationForm
            ref={c => {
              this.form = c;
            }}
            // $FlowFixMe
            onSubmitFail={this.stopSubmit}
            // $FlowFixMe
            onSubmitSuccess={this.handleSubmitSuccess}
          />
          {textBottom && (
            <div
              className="text-center small excerpt"
              style={{ marginTop: '15px' }}
              dangerouslySetInnerHTML={{ __html: textBottom }}
            />
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
    );
  }
}

const mapStateToProps: MapStateToProps<*, *, *> = (state: State) => ({
  textTop: state.user.registration_form.topTextDisplayed
    ? state.user.registration_form.topText
    : null,
  textBottom: state.user.registration_form.bottomTextDisplayed
    ? state.user.registration_form.bottomText
    : null,
  show: state.user.showRegistrationModal,
  displayChartModal: state.user.displayChartModal,
  submitting: isSubmitting(form)(state),
});

const mapDispatchToProps = (dispatch: Dispatch) => ({
  onClose: () => {
    dispatch(closeRegistrationModal());
  },
  onSubmit: () => {
    dispatch(submit(form));
  },
  onCloseChart: () => {
    dispatch(hideChartModal());
  },
});

export default connect(
  mapStateToProps,
  mapDispatchToProps,
)(RegistrationModal);
