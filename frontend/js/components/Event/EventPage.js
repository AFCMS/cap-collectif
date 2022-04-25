// @flow
import * as React from 'react';
import { Row } from 'react-bootstrap';
import { graphql, QueryRenderer } from 'react-relay';
import { connect } from 'react-redux';
import Loader from '../Ui/FeedbacksIndicators/Loader';
import environment, { graphqlError } from '../../createRelayEnvironment';
import type {
  EventPageQueryResponse,
  EventPageQueryVariables,
} from '~relay/EventPageQuery.graphql';
import config from '../../config';
import EventPageContainer, { getInitialValues } from './EventPageContainer';
import EventPageHeader from './EventPageHeader';
import withColors from '../Utils/withColors';
import type { GlobalState } from '../../types';

type Props = {|
  +eventPageTitle: ?string,
  +eventPageBody: ?string,
  +backgroundColor: ?string,
  +isAuthenticated: boolean,
|};

export class EventPage extends React.Component<Props> {
  render() {
    const { backgroundColor, isAuthenticated } = this.props;

    const initialValues = getInitialValues();
    const { project } = initialValues;
    const isFuture =
      initialValues.status === 'all' ? null : initialValues.status === 'ongoing-and-future';

    return (
      <div className="event-page">
        <QueryRenderer
          environment={environment}
          query={graphql`
            query EventPageQuery(
              $cursor: String
              $count: Int!
              $search: String
              $theme: ID
              $project: ID
              $userType: ID
              $isFuture: Boolean
              $author: ID
              $isRegistrable: Boolean
              $orderBy: EventOrder!
              $isAuthenticated: Boolean!
            ) {
              ...EventPageContainer_query
                @arguments(
                  cursor: $cursor
                  count: $count
                  search: $search
                  theme: $theme
                  project: $project
                  userType: $userType
                  author: $author
                  isRegistrable: $isRegistrable
                  isFuture: $isFuture
                  orderBy: $orderBy
                  isAuthenticated: $isAuthenticated
                )
            }
          `}
          variables={
            ({
              count: config.isMobile ? 25 : 100,
              cursor: null,
              search: null,
              theme: null,
              userType: null,
              project,
              isFuture,
              author: null,
              isRegistrable: null,
              orderBy: { field: 'START_AT', direction: 'ASC' },
              isAuthenticated,
            }: EventPageQueryVariables)
          }
          render={({
            error,
            props,
          }: {
            ...ReactRelayReadyState,
            props: ?EventPageQueryResponse,
          }) => {
            if (error) {
              return graphqlError;
            }
            if (props) {
              const { eventPageTitle, eventPageBody } = this.props;
              return (
                <div>
                  <section className="jumbotron--bg-1 ">
                    <EventPageHeader eventPageTitle={eventPageTitle} />
                  </section>
                  <section className="section--alt">
                    <EventPageContainer
                      query={props}
                      eventPageBody={eventPageBody}
                      backgroundColor={backgroundColor}
                    />
                  </section>
                </div>
              );
            }
            return (
              <Row>
                <Loader />
              </Row>
            );
          }}
        />
      </div>
    );
  }
}

const mapStateToProps = (state: GlobalState) => ({
  isAuthenticated: !!state.user.user,
});
const container = connect(mapStateToProps)(EventPage);

export default withColors(container);
