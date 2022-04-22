// @flow
import * as React from 'react';
import { Modal, Alert } from 'react-bootstrap';
import { QueryRenderer, graphql } from 'react-relay';
import { FormattedMessage } from 'react-intl';
import { connect, type MapStateToProps } from 'react-redux';
import { submit, isSubmitting } from 'redux-form';
import CloseButton from '../../Form/CloseButton';
import SubmitButton from '../../Form/SubmitButton';
import RegistrationForm, { form } from './RegistrationForm';
import LoginSocialButtons from '../Login/LoginSocialButtons';
import { closeRegistrationModal, hideChartModal } from '../../../redux/modules/user';
import type { State, Dispatch } from '../../../types';
import WYSIWYGRender from '../../Form/WYSIWYGRender';
import environment, { graphqlError } from '../../../createRelayEnvironment';
import Loader from "../../Ui/Loader";

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
          <Modal.Body>
            <WYSIWYGRender value={chartBody} />
          </Modal.Body>
          <Modal.Footer>
            <CloseButton label="global.close" onClose={onCloseChart} />
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
              <WYSIWYGRender value={textTop} />
            </Alert>
          )}
          <LoginSocialButtons prefix="registration." />
          <QueryRenderer
            query={graphql`
                query RegistrationModalQuery {
                    registrationForm {
                      ...RegistrationForm_registrationForm
                    }
                }
            `}
            environment={environment}
            variables={{}}
            render={({error, props}) => {
              const { stopSubmit, handleSubmitSuccess } = this
              if (error) {
                console.log(error); // eslint-disable-line no-console
                return graphqlError;
              }
              if (props) {
                if (props.registrationForm) {

                  return <RegistrationForm
                    ref={c => {
                      this.form = c;
                    }}
                    registrationForm={props.registrationForm}
                    // $FlowFixMe
                    onSubmitFail={stopSubmit}
                    // $FlowFixMe
                    onSubmitSuccess={handleSubmitSuccess}
                  />
                }

                return graphqlError;
              }
              return <Loader />;
            }}
          />
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
