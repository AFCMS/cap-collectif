import React from 'react';
import { IntlMixin, FormattedMessage, FormattedDate } from 'react-intl';
import { ListGroupItem } from 'react-bootstrap';
import moment from 'moment';
import ShowReplyModal from './ShowReplyModal';

const ReplyModalLink = React.createClass({
  propTypes: {
    reply: React.PropTypes.object.isRequired,
    form: React.PropTypes.object.isRequired,
  },
  mixins: [IntlMixin],

  getInitialState() {
    return {
      showModal: false,
    };
  },

  showModal() {
    this.setState({
      showModal: true,
    });
  },

  hideModal() {
    this.setState({
      showModal: false,
    });
  },

  render() {
    const { reply, form } = this.props;

    return (
      <ListGroupItem className="reply" id={'reply-link-' + reply.id } onClick={this.showModal}>
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
        <ShowReplyModal
          show={this.state.showModal}
          onClose={this.hideModal}
          reply={reply}
          form={form}
        />
      </ListGroupItem>
    );
  },

});

export default ReplyModalLink;
