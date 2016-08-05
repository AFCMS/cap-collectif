import React, { PropTypes } from 'react';
import { Modal } from 'react-bootstrap';
import { IntlMixin, FormattedDate, FormattedMessage } from 'react-intl';
import moment from 'moment';
import ResponseValue from './ResponseValue';
import ReplyModalButtons from './ReplyModalButtons';
import CloseButton from '../../Form/CloseButton';
import ReplyActions from '../../../actions/ReplyActions';

const ShowReplyModal = React.createClass({
  propTypes: {
    show: PropTypes.bool.isRequired,
    reply: PropTypes.object.isRequired,
    form: PropTypes.object.isRequired,
    onClose: PropTypes.func.isRequired,
  },
  mixins: [IntlMixin],

  onChange() {
    this.props.onClose();
    ReplyActions.loadUserReplies(this.props.form.id);
  },

  render() {
    const { reply, form, show, onClose } = this.props;
    return (
      <Modal
        id={`show-reply-modal-${reply.id}`}
        className="reply__modal--show"
        animation={false}
        onHide={onClose}
        show={show}
        bsSize="large"
        aria-labelledby="contained-modal-title-lg"
      >
        <Modal.Header closeButton>
          <Modal.Title id="contained-modal-title-lg">
            <FormattedMessage
                message={this.getIntlMessage('reply.show.link')}
                date={
                  <FormattedDate
                    value={moment(reply.createdAt)}
                    day="numeric" month="long" year="numeric"
                  />
                }
                time={
                  <FormattedDate
                    value={moment(reply.createdAt)}
                    hour="numeric" minute="numeric"
                  />
                }
            />
          </Modal.Title>
        </Modal.Header>
        <Modal.Body>
          {
            reply.responses.map((response, index) => {
              return (
                <div key={index} className="form-group">
                  <label>
                    {response.field.question}
                  </label>
                  <ResponseValue response={response} />
                </div>
              );
            })
          }
        </Modal.Body>
        <Modal.Footer>
          <ReplyModalButtons
            reply={reply}
            form={form}
            onChange={this.onChange}
            onClose={onClose}
          />
          <CloseButton onClose={onClose} />
        </Modal.Footer>
      </Modal>
    );
  },

});

export default ShowReplyModal;
