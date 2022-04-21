// @flow
import React from 'react';
import { IntlMixin } from 'react-intl';
import CloseButton from '../../Form/CloseButton';
import OpinionActions from '../../../actions/OpinionActions';
import { Modal, Row } from 'react-bootstrap';
import Loader from '../../Utils/Loader';
import UserBox from '../../User/UserBox';

const OpinionVotesModal = React.createClass({
  propTypes: {
    opinion: React.PropTypes.object.isRequired,
  },
  mixins: [IntlMixin],

  getInitialState() {
    return {
      showModal: false,
      isLoading: true,
      votes: [],
    };
  },

  componentDidMount() {
    const { opinion } = this.props;
    const opinionId = opinion.parent ? opinion.parent.id : opinion.id;
    const versionId = opinion.parent ? opinion.id : null;
    OpinionActions
      .loadAllVotes(opinionId, versionId)
      .then((votes) => {
        this.setState({
          isLoading: false,
          votes,
        });
      })
    ;
  },

  show() {
    this.setState({ showModal: true });
  },

  close() {
    this.setState({ showModal: false });
  },

  render() {
    const { opinion } = this.props;
    const moreVotes = opinion.votes_total > 5 ? opinion.votes_total - 5 : null;
    if (!moreVotes) {
      return null;
    }

    return (
      <span>
        <span id="opinion-votes-show-all" onClick={this.show} className="opinion__votes__more__link text-center">
          {`+${moreVotes}`}
        </span>
        <Modal
          animation={false}
          show={this.state.showModal}
          onHide={this.close}
          bsSize="large"
          className="opinion__votes__more__modal"
          aria-labelledby="opinion-votes-more-title"
        >
          <Modal.Header closeButton>
            <Modal.Title id="opinion-votes-more-title">
              { this.getIntlMessage('opinion.votes.modal.title') }
            </Modal.Title>
          </Modal.Header>
          <Modal.Body>
            <Loader show={this.state.isLoading}>
              <Row>
                {
                  this.state.votes.map((vote, index) => {
                    return <UserBox key={index} user={vote.user} username={vote.username} className="opinion__votes__userbox" />;
                  })
                }
              </Row>
            </Loader>
          </Modal.Body>
          <Modal.Footer>
            <CloseButton onClose={this.close} label="global.close" />
          </Modal.Footer>
        </Modal>
      </span>
    );
  },

});

export default OpinionVotesModal;
