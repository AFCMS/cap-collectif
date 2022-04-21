import React, { PropTypes } from 'react';
import { Button } from 'react-bootstrap';
import { IntlMixin } from 'react-intl';
import LoginOverlay from '../../Utils/LoginOverlay';
import { connect } from 'react-redux';

const OpinionSourceVoteButton = React.createClass({
  propTypes: {
    disabled: PropTypes.bool.isRequired,
    hasVoted: PropTypes.bool.isRequired,
    onClick: PropTypes.func.isRequired,
    user: PropTypes.object,
    features: PropTypes.object.isRequired,
  },
  mixins: [IntlMixin],

  getDefaultProps() {
    return {
      user: null,
    };
  },

  render() {
    const { disabled, hasVoted, onClick, user, features } = this.props;
    return (
      <LoginOverlay user={user} features={features}>
        <Button
          disabled={disabled}
          bsStyle={hasVoted ? 'danger' : 'success'}
          className={'source__btn--vote' + (hasVoted ? '' : ' btn--outline')}
          bsSize="xsmall"
          onClick={this.props.user ? onClick : null}
        >
          {hasVoted
            ? <span>{this.getIntlMessage('vote.cancel')}</span>
            : <span>
                <i className="cap cap-hand-like-2"></i>
                {' '}
                {this.getIntlMessage('vote.ok')}
            </span>
          }
        </Button>
      </LoginOverlay>
    );
  },

});

const mapStateToProps = (state) => {
  return {
    user: state.default.user,
    features: state.default.features,
  };
};

export default connect(mapStateToProps)(OpinionSourceVoteButton);
