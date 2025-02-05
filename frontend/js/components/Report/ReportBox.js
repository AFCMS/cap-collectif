// @flow
import React from 'react';
import { connect } from 'react-redux';
import ReportModal from './ReportModal';
import ReportButton from './ReportButton';
import { openModal } from '../../redux/modules/report';
import type { State, Dispatch } from '../../types';

type Props = {
  id: $FlowFixMe,
  dispatch: Dispatch,
  showModal: boolean,
  reported: boolean,
  onReport: Function,
  features: Object,
  user: Object,
  author?: Object,
  buttonStyle: ?Object,
  buttonBsSize?: ?string,
  buttonClassName?: ?string,
  disabled?: boolean,
  newDesign?: boolean,
};

export class ReportBox extends React.Component<Props> {
  static displayName = 'ReportBox';

  static defaultProps = {
    buttonStyle: {},
    author: null,
    buttonBsSize: null,
    buttonClassName: '',
    user: null,
  };

  render() {
    const {
      id,
      onReport,
      dispatch,
      showModal,
      reported,
      buttonBsSize,
      buttonClassName,
      user,
      author,
      features,
      buttonStyle,
      disabled,
      newDesign,
    } = this.props;
    if (features.reporting && (!user || !author || user.uniqueId !== author.uniqueId)) {
      return (
        <span>
          <ReportButton
            id={id}
            disabled={disabled}
            reported={reported}
            onClick={() => dispatch(openModal(id))}
            bsSize={buttonBsSize}
            style={buttonStyle}
            className={buttonClassName}
            newDesign={newDesign}
          />
          <ReportModal id={id} show={showModal} onSubmit={onReport} />
        </span>
      );
    }
    return null;
  }
}

const mapStateToProps = (state: State, props) => ({
  features: state.default.features,
  user: state.user.user,
  showModal: state.report.currentReportingModal === props.id,
});

export default connect<any, any, _, _, _, _>(mapStateToProps)(ReportBox);
