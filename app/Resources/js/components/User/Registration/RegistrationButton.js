import React, { PropTypes } from 'react';
import { Button } from 'react-bootstrap';
import { IntlMixin } from 'react-intl';
import { connect } from 'react-redux';
import RegistrationModal from './RegistrationModal';

const RegistrationButton = React.createClass({
  propTypes: {
    features: PropTypes.object.isRequired,
  },
  mixins: [IntlMixin],

  getInitialState() {
    return {
      show: false,
    };
  },

  handleClick() {
    this.setState({ show: true });
  },

  handleClose() {
    this.setState({ show: false });
  },

  render() {
    if (!this.props.features.registration) {
      return null;
    }
    return (
      <span>
        <Button
          onClick={this.handleClick}
          bsStyle="primary"
          className="navbar-btn btn--registration"
        >
          { this.getIntlMessage('global.registration') }
        </Button>
        <RegistrationModal
          show={this.state.show}
          onClose={this.handleClose}
        />
      </span>
    );
  },

});

const mapStateToProps = (state) => {
  return { features: state.features };
};

export default connect(mapStateToProps)(RegistrationButton);
