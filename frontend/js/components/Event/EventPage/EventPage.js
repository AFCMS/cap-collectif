// @flow
import * as React from 'react';
import { graphql, QueryRenderer } from 'react-relay';
import { connect } from 'react-redux';
import { Flex, Skeleton } from '@cap-collectif/ui';
import EventPageHeader from './EventPageHeader';
import EventPageContent from './EventPageContent';
import environment, { graphqlError } from '~/createRelayEnvironment';
import type {
  EventPageQueryResponse,
  EventPageQueryVariables,
} from '~relay/EventPageQuery.graphql';
import type { Uuid, State } from '~/types';
import EventPagePlaceholder from './EventPagePlaceholder';
import Deleted from './EventDeletedInfo';

type Props = {|
  eventId: Uuid,
  userConnectedId: ?Uuid,
  isAuthenticated: boolean,
  isDeleted?: ?boolean,
|};

const EventPage = ({ eventId, isAuthenticated, isDeleted }: Props) => (
  <QueryRenderer
    environment={environment}
    query={graphql`
      query EventPageQuery($eventId: ID!, $isAuthenticated: Boolean!) {
        ...EventPageHeader_query @arguments(isAuthenticated: $isAuthenticated, eventId: $eventId)
        ...EventPageContent_query @arguments(isAuthenticated: $isAuthenticated, eventId: $eventId)
      }
    `}
    variables={
      ({
        eventId,
        isAuthenticated,
      }: EventPageQueryVariables)
    }
    render={({
      error,
      props,
      retry,
    }: {
      ...ReactRelayReadyState,
      props: ?EventPageQueryResponse,
    }) => {
      if (isDeleted) return <Deleted />;
      if (error) return graphqlError;

      return (
        <Flex direction="column" id="EventPage" maxWidth="100%" p={0}>
          <Skeleton
            isLoaded={!!props}
            placeholder={<EventPagePlaceholder fetchData={retry} hasError={!!error} />}>
            <EventPageHeader queryRef={props} />
            <EventPageContent queryRef={props} />
          </Skeleton>
        </Flex>
      );
    }}
  />
);

const mapStateToProps = (state: State) => ({
  isAuthenticated: !!state.user.user,
});

export default connect<any, any, _, _, _, _>(mapStateToProps)(EventPage);
