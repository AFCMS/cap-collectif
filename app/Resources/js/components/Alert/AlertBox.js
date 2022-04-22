// @flow
import React from 'react';
import { FormattedMessage } from 'react-intl';
import AlertStore from '../../stores/AlertStore';
import AlertAutoDismissable from './AlertAutoDismissable';

type Props = {};

type State = {
  alert: ?Object,
};

class AlertBox extends React.Component<Props, State> {
  state = {
    alert: null,
  };

  componentWillMount() {
    AlertStore.addChangeListener(this.onChange);
  }

  componentWillUnmount() {
    AlertStore.removeChangeListener(this.onChange);
  }

  onChange = () => {
    this.setState({
      alert: AlertStore.alert,
    });
  };

  handleDismiss = () => {
    this.setState({
      alert: null,
    });
  };

  render() {
    const { alert } = this.state;
    if (!alert) {
      return null;
    }

    return (
      <AlertAutoDismissable onDismiss={this.handleDismiss} bsStyle={alert.bsStyle}>
        <FormattedMessage id={alert.content} />
      </AlertAutoDismissable>
    );
  }
}

export default AlertBox;
