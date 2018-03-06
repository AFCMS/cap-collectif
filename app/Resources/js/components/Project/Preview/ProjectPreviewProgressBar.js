// @flow
import * as React from 'react';
import { ProgressBar } from 'react-bootstrap';
import { FormattedMessage } from 'react-intl';

type Props = {
  project: Object,
  actualStep: Object,
  isCurrentStep?: ?boolean
};

export class ProjectPreviewProgressBar extends React.Component<Props> {
  getStyle = (stepStatus: string) => {
    const { isCurrentStep } = this.props;

    if (stepStatus === 'open' || isCurrentStep) {
      return 'success';
    }
  };

  getClass = (stepStatus: string) => {
    const { isCurrentStep } = this.props;

    if (stepStatus === 'future') {
      return 'progress_future-step';
    }
    if (stepStatus === 'closed' && !isCurrentStep) {
      return 'progress_closed-step';
    }
  };

  getLabel = (step: Object) => {
    const { isCurrentStep } = this.props;

    if (step.timeless === true) {
      return <FormattedMessage id="step.timeless" />;
    }
    if (step.status === 'open' || isCurrentStep) {
      return <FormattedMessage id="step.status.open" />;
    }
    if (step.status === 'future') {
      return <FormattedMessage id="step.status.future" />;
    }
    if (step.status === 'closed' && !isCurrentStep) {
      return <FormattedMessage id="step.status.closed" />;
    }
  };

  getWidth = (step: Object) => {
    const { isCurrentStep } = this.props;

    if (
      (step.status === 'closed' && !isCurrentStep) ||
      step.status === 'future' ||
      step.timeless === true
    ) {
      return 100;
    }
    if (step.status === 'open' || isCurrentStep) {
      return 50;
    }

    return 0;
  };

  render() {
    const { project, actualStep } = this.props;
    const nbSteps = project.steps.length;

    if (nbSteps > 0) {
      return (
        <div className="thumbnail__steps-bar">
          <ProgressBar
            className={this.getClass(actualStep.status)}
            bsStyle={this.getStyle(actualStep.status)}
            now={this.getWidth(actualStep)}
            label={this.getLabel(actualStep)}
          />
        </div>
      );
    }
    return null;
  }
}

export default ProjectPreviewProgressBar;
