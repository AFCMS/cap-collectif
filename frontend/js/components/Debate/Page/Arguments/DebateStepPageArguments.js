// @flow
import React from 'react';
import { createFragmentContainer, graphql } from 'react-relay';
import type { DebateStepPageArguments_step } from '~relay/DebateStepPageArguments_step.graphql';
import type { DebateStepPageArguments_viewer } from '~relay/DebateStepPageArguments_viewer.graphql';
import MobileDebateStepPageArguments from '~/components/Debate/Page/Arguments/MobileDebateStepPageArguments';
import DesktopDebateStepPageArguments from '~/components/Debate/Page/Arguments/DesktopDebateStepPageArguments';

type Props = {|
  +step: ?DebateStepPageArguments_step,
  +viewer: ?DebateStepPageArguments_viewer,
  +isMobile?: boolean,
|};

export const DebateStepPageArguments = ({ step, viewer, isMobile }: Props) => {
  return isMobile ? (
    <>{step?.debate && <MobileDebateStepPageArguments debate={step.debate} viewer={viewer} />}</>
  ) : (
    // About step => $fragmentRefs is missing in DebateStepPageArguments_step
    // Would be fix if we transform DesktopDebateStepPageArguments in fragment
    // $FlowFixMe
    <DesktopDebateStepPageArguments step={step} viewer={viewer} />
  );
};

export default createFragmentContainer(DebateStepPageArguments, {
  step: graphql`
    fragment DebateStepPageArguments_step on DebateStep {
      noDebate: debate {
        id
        ...DebateStepPageArgumentsPagination_debate
          @arguments(
            isAuthenticated: $isAuthenticated
            value: AGAINST
            orderBy: { field: PUBLISHED_AT, direction: DESC }
          )
      }
      yesDebate: debate {
        id
        ...DebateStepPageArgumentsPagination_debate
          @arguments(
            isAuthenticated: $isAuthenticated
            value: FOR
            orderBy: { field: PUBLISHED_AT, direction: DESC }
          )
      }
      debate {
        id
        arguments(first: 0) {
          totalCount
        }
        ...MobileDebateStepPageArguments_debate @arguments(isAuthenticated: $isAuthenticated)
      }
    }
  `,
  viewer: graphql`
    fragment DebateStepPageArguments_viewer on User {
      ...MobileDebateStepPageArguments_viewer
      ...DesktopDebateStepPageArguments_viewer
    }
  `,
});
