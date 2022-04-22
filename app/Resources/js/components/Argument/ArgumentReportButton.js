// @flow
import React from 'react';
import { connect } from 'react-redux';
import { graphql, createFragmentContainer } from 'react-relay';
import ReportBox from '../Report/ReportBox';
import { submitArgumentReport } from '../../redux/modules/report';
import type { ArgumentReportButton_argument } from './__generated__/ArgumentReportButton_argument.graphql';

type Props = {
  dispatch: Function,
  argument: ArgumentReportButton_argument,
};

class ArgumentReportButton extends React.Component<Props> {
  handleReport = (data: Object) => {
    const { argument, dispatch } = this.props;
    if (!argument.related) {
      return;
    }
    return submitArgumentReport(argument.related, argument.id, data, dispatch);
  };

  render() {
    const { argument } = this.props;
    return (
      <ReportBox
        id={`argument-${argument.id}`}
        reported={argument.viewerHasReport}
        onReport={this.handleReport}
        author={{ uniqueId: argument.author.slug }}
        buttonBsSize="xs"
        buttonClassName="argument__btn--report"
      />
    );
  }
}

const container = connect()(ArgumentReportButton);
export default createFragmentContainer(
  container,
  graphql`
    fragment ArgumentReportButton_argument on Argument
      @argumentDefinitions(isAuthenticated: { type: "Boolean", defaultValue: true }) {
      author {
        id
        slug
      }
      related {
        id
      }
      id
      viewerHasReport @include(if: $isAuthenticated)
    }
  `,
);
