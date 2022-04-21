import React, { PropTypes } from 'react';
import { FormattedMessage } from 'react-intl';
import { connect } from 'react-redux';
import { Jumbotron } from 'react-bootstrap';
import classNames from 'classnames';
import LoginButton from '../User/Login/LoginButton';

export const VisibilityBox = React.createClass({
  propTypes: {
    user: PropTypes.object,
    children: PropTypes.element.isRequired,
    enabled: PropTypes.bool
  },

  getDefaultProps() {
    return {
      user: null,
      enabled: false
    };
  },

  render() {
    const { user, children, enabled } = this.props;

    if (!enabled) {
      return children;
    }

    if (enabled && !user) {
      return (
        <Jumbotron className={{ 'p--centered': true }}>
          <p>
            <FormattedMessage id="proposal.private.show_login" />
          </p>
          <p>
            <LoginButton bsStyle="primary" />
          </p>
        </Jumbotron>
      );
    }

    const rootClasses = classNames({ PrivateList: true });
    const boxClasses = classNames({ PrivateList__box: true });

    return (
      <div className={rootClasses}>
        <p id="privateInfo">
          <i className="glyphicon glyphicon-lock" />{' '}
          <strong>
            <FormattedMessage id="proposal.private.message" />
          </strong>
        </p>
        <div className={boxClasses}>{children}</div>
      </div>
    );
  }
});

const mapStateToProps = state => {
  return {
    user: state.user.user
  };
};

export default connect(mapStateToProps)(VisibilityBox);
