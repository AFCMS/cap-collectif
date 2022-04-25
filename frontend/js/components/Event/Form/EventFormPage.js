// @flow
import * as React from 'react';
import { FormattedMessage, type IntlShape, injectIntl } from 'react-intl';
import {
  SubmissionError,
  isPristine,
  isValid,
  isInvalid,
  isSubmitting,
  submit,
  hasSubmitSucceeded,
  hasSubmitFailed,
} from 'redux-form';
import { connect } from 'react-redux';
import { createFragmentContainer, graphql } from 'react-relay';
import { Button, ButtonToolbar } from 'react-bootstrap';
import moment from 'moment';
import SubmitButton from '../../Form/SubmitButton';
import EventForm, { formName } from './EventForm';
import type { Dispatch, GlobalState } from '../../../types';
import AddEventMutation from '../../../mutations/AddEventMutation';
import ReviewEventMutation from '../../../mutations/ReviewEventMutation';
import {
  type EventRefusedReason,
  type EventReviewStatus,
} from '~relay/ReviewEventMutation.graphql';
import ChangeEventMutation from '../../../mutations/ChangeEventMutation';
import DeleteEventMutation from '../../../mutations/DeleteEventMutation';
import AlertForm from '../../Alert/AlertForm';
import type { EventFormPage_event } from '~relay/EventFormPage_event.graphql';
import type { EventFormPage_query } from '~relay/EventFormPage_query.graphql';
import DeleteModal from '../../Modal/DeleteModal';
import type { FormValues as CustomFormValues } from '../../Admin/Field/CustomPageFields';

type Props = {|
  intl: IntlShape,
  query: EventFormPage_query,
  event: ?EventFormPage_event,
  pristine: boolean,
  valid: boolean,
  submitting: boolean,
  submitSucceeded: boolean,
  submitFailed: boolean,
  invalid: boolean,
  dispatch: Dispatch,
  isFrontendView: boolean,
  className?: string,
|};

type FormValues = {|
  custom: CustomFormValues,
  title: string,
  body: string,
  author?: { value: string, label: string },
  startAt: string,
  endAt: ?string,
  commentable: boolean,
  guestListEnabled: boolean,
  link: ?string,
  addressJson: ?string,
  address: ?string,
  enabled: boolean,
  media: ?{
    id: string,
    url: string,
  },
  themes: ?[],
  projects: ?[],
  refusedReason: ?EventRefusedReason,
  status: ?EventReviewStatus,
|};

type ReviewEventForm = {|
  comment: ?string,
  refusedReason: ?EventRefusedReason,
  status: EventReviewStatus,
|};

type EditFormValue = {|
  ...FormValues,
  ...ReviewEventForm,
  id: string,
|};

type State = { showDeleteModal: boolean };

const validate = (values: FormValues) => {
  const errors = {};
  const fields = ['title', 'startAt', 'endAt', 'author', 'body'];
  fields.forEach(value => {
    if (value === 'endAt' && values.endAt) {
      if (!values.startAt) {
        errors.startAt = 'fill-field';
      }
      if (values.startAt) {
        if (moment(values.startAt).isAfter(moment(values.endAt))) {
          errors.endAt = {
            id: 'event-before-date-error',
            values: { before: '<i class="cap cap-attention pr-5" />' },
          };
        }
      }
    }
    if (value === 'body' && values[value] && values[value] === '<p><br></p>') {
      errors[value] = 'fill-field';
    }

    if (value !== 'endAt' && (!values[value] || values[value].length === 0)) {
      errors[value] = 'fill-field';
    }
  });
  if (values.guestListEnabled && values.link) {
    errors.link = 'error-alert-choosing-subscription-mode';
  }

  if (values.status === 'REFUSED' && !values.refusedReason) {
    errors.refusedReason = 'fill-field';
  }

  return errors;
};

const onSubmit = (values: FormValues, dispatch: Dispatch, props: Props) => {
  const { intl, isFrontendView } = props;
  const media =
    typeof values.media !== 'undefined' && values.media !== null ? values.media.id : null;
  const guestListEnabled = values.guestListEnabled ? values.guestListEnabled : false;
  const commentable = values.commentable ? values.commentable : false;
  const enabled = values.enabled ? values.enabled : false;
  const addressJson = values.address;
  delete values.address;

  const input = {
    title: values.title,
    body: values.body,
    startAt: moment(values.startAt).format('YYYY-MM-DD HH:mm:ss'),
    endAt: values.endAt ? moment(values.endAt).format('YYYY-MM-DD HH:mm:ss') : null,
    metaDescription: values.custom.metadescription,
    customCode: values.custom.customcode,
    commentable,
    guestListEnabled,
    addressJson,
    enabled,
    media,
    link: values.link,
    themes: values.themes ? values.themes.map(t => t.value) : null,
    projects: values.projects ? values.projects.map(p => p.value) : null,
    author: values.author ? values.author.value : undefined,
  };

  return AddEventMutation.commit({ input })
    .then(response => {
      if (!response.addEvent || !response.addEvent.eventEdge) {
        throw new Error('Mutation "AddEventMutation" failed.');
      }
      if (response?.addEvent?.eventEdge?.node) {
        window.location.href = isFrontendView
          ? response.addEvent.eventEdge.node.url
          : `/admin/capco/app/event/${response.addEvent.eventEdge.node._id}/edit`;
      }
    })
    .catch(response => {
      if (response.response.message) {
        throw new SubmissionError({
          _error: response.response.message,
        });
      } else {
        throw new SubmissionError({
          _error: intl.formatMessage({ id: 'global.error.server.form' }),
        });
      }
    });
};

const updateEvent = (values: EditFormValue, dispatch: Dispatch, props: Props) => {
  const { intl, event, isFrontendView } = props;
  const media = values.media && values.media.id ? values.media.id : null;
  const guestListEnabled = values.guestListEnabled ? values.guestListEnabled : false;
  const commentable = values.commentable ? values.commentable : false;
  const enabled = values.enabled ? values.enabled : false;
  const addressJson = values.address;
  delete values.address;
  const updateInput = {
    id: values.id,
    title: values.title,
    body: values.body,
    startAt: moment(values.startAt).format('YYYY-MM-DD HH:mm:ss'),
    endAt: values.endAt ? moment(values.endAt).format('YYYY-MM-DD HH:mm:ss') : null,
    metaDescription: values.custom.metadescription,
    customCode: values.custom.customcode,
    commentable,
    guestListEnabled,
    addressJson,
    enabled,
    media,
    link: values.link,
    themes: values.themes ? values.themes.map(t => t.value) : null,
    projects: values.projects ? values.projects.map(p => p.value) : null,
    author: values.author ? values.author.value : undefined,
  };

  const reviewInput =
    values.refusedReason !== 'NONE'
      ? {
          id: values.id,
          comment: values.comment,
          status: values.status,
          refusedReason: values.refusedReason,
        }
      : { id: values.id, comment: values.comment, status: values.status };
  return ChangeEventMutation.commit({ input: updateInput })
    .then(response => {
      if (!response.changeEvent || !response.changeEvent.event) {
        throw new Error('Mutation "ChangeEventMutation" failed.');
      }
      if (
        !isFrontendView &&
        event?.review &&
        (event?.review?.status !== values.status ||
          event?.review?.comment !== values.comment ||
          event?.review?.refusedReason !== values.refusedReason)
      ) {
        return ReviewEventMutation.commit({ input: reviewInput })
          .then(reviewResponse => {
            if (!reviewResponse.reviewEvent || !reviewResponse.reviewEvent.event) {
              throw new Error('Mutation "ReviewEventMutation" failed.');
            }
          })
          .catch(reviewResponse => {
            if (reviewResponse.response.message) {
              throw new SubmissionError({
                _error: reviewResponse.response.message,
              });
            } else {
              throw new SubmissionError({
                _error: intl.formatMessage({ id: 'global.error.server.form' }),
              });
            }
          });
      }
      if (isFrontendView) {
        return window.location.reload();
      }
    })
    .catch(response => {
      if (response.response.message) {
        throw new SubmissionError({
          _error: response.response.message,
        });
      } else {
        throw new SubmissionError({
          _error: intl.formatMessage({ id: 'global.error.server.form' }),
        });
      }
    });
};

const onDelete = (eventId: string) =>
  DeleteEventMutation.commit({
    input: {
      eventId,
    },
  }).then(() => {
    window.location.href = `${window.location.protocol}//${window.location.host}/admin/capco/app/event/list`;
  });

export class EventFormPage extends React.Component<Props, State> {
  static defaultProps = {
    isFrontendView: false,
  };

  state = {
    showDeleteModal: false,
  };

  openDeleteModal = () => {
    this.setState({ showDeleteModal: true });
  };

  cancelCloseDeleteModal = () => {
    this.setState({ showDeleteModal: false });
  };

  renderSubmitButton = () => {
    const { pristine, invalid, submitting, dispatch, event, query } = this.props;
    if (!event) {
      return (
        <SubmitButton
          id="confirm-event-create"
          label="global.save"
          isSubmitting={submitting}
          disabled={pristine || invalid || submitting}
          onSubmit={() => {
            dispatch(submit(formName));
          }}
        />
      );
    }
    if (
      query.viewer.isSuperAdmin ||
      event.review === null ||
      (event?.review?.status !== 'APPROVED' &&
        event?.review?.status !== 'REFUSED' &&
        query.viewer.isAdmin)
    ) {
      return (
        <SubmitButton
          id={event ? 'confirm-event-edit' : 'confirm-event-create'}
          label="global.save"
          isSubmitting={submitting}
          disabled={pristine || invalid || submitting}
          onSubmit={() => {
            dispatch(submit(formName));
          }}
        />
      );
    }
  };

  render() {
    const {
      invalid,
      valid,
      submitSucceeded,
      submitFailed,
      submitting,
      event,
      query,
      isFrontendView,
      className,
    } = this.props;
    const { showDeleteModal } = this.state;

    return (
      <>
        <div className={`${!isFrontendView ? 'box box-primary container-fluid' : ''}`}>
          <EventForm
            event={event}
            onSubmit={event ? updateEvent : onSubmit}
            validate={validate}
            query={query}
            className={className}
            isFrontendView={isFrontendView}
          />
          {!isFrontendView && (
            <ButtonToolbar className="mt-45 box-content__toolbar">
              {this.renderSubmitButton()}

              {event && (event.viewerDidAuthor || query.viewer.isSuperAdmin) && (
                <>
                  <DeleteModal
                    closeDeleteModal={this.cancelCloseDeleteModal}
                    showDeleteModal={showDeleteModal}
                    deleteElement={() => {
                      onDelete(event.id);
                    }}
                    deleteModalTitle="event.alert.delete"
                    deleteModalContent="group.admin.parameters.modal.delete.content"
                    buttonConfirmMessage="global.removeDefinitively"
                  />
                  <Button
                    bsStyle="danger"
                    className="ml-5"
                    onClick={this.openDeleteModal}
                    id="delete-event">
                    <i className="fa fa-trash" /> <FormattedMessage id="global.delete" />
                  </Button>
                </>
              )}
              <AlertForm
                valid={valid}
                invalid={invalid}
                submitSucceeded={submitSucceeded}
                submitFailed={submitFailed}
                submitting={submitting}
              />
            </ButtonToolbar>
          )}
        </div>
      </>
    );
  }
}

const mapStateToProps = (state: GlobalState) => ({
  pristine: isPristine(formName)(state),
  valid: isValid(formName)(state),
  invalid: isInvalid(formName)(state),
  submitting: isSubmitting(formName)(state),
  submitSucceeded: hasSubmitSucceeded(formName)(state),
  submitFailed: hasSubmitFailed(formName)(state),
});

export const EventFormCreatePage = connect(mapStateToProps)(injectIntl(EventFormPage));

export default createFragmentContainer(EventFormCreatePage, {
  query: graphql`
    fragment EventFormPage_query on Query {
      ...EventForm_query
      viewer {
        isSuperAdmin
        isAdmin
      }
    }
  `,
  event: graphql`
    fragment EventFormPage_event on Event
      @argumentDefinitions(isAuthenticated: { type: "Boolean!" }) {
      id
      review {
        status
        comment
        refusedReason
      }
      author {
        id
        isAdmin
      }
      viewerDidAuthor @include(if: $isAuthenticated)
      ...EventForm_event
    }
  `,
});
