// @flow
import * as React from 'react';
import { Panel, Row, Col, ListGroup } from 'react-bootstrap';
import { FormattedMessage } from 'react-intl';
import { QueryRenderer, createFragmentContainer, graphql, type ReadyState } from 'react-relay';
import environment, { graphqlError } from '../../../createRelayEnvironment';
import type {
  OpinionSourceBoxQueryVariables,
  OpinionSourceBoxQueryResponse,
} from './__generated__/OpinionSourceBoxQuery.graphql';
import OpinionSourceListView from './OpinionSourceListView';
import OpinionSourceAdd from './OpinionSourceAdd';
import OpinionSource from './OpinionSource';
import Loader from '../../Ui/Loader';
import Filter from '../../Utils/Filter';
import type { OpinionSourceBox_sourceable } from './__generated__/OpinionSourceBox_sourceable.graphql';

type Props = {
  sourceable: OpinionSourceBox_sourceable,
  isAuthenticated: boolean,
};

type State = {
  order: 'old' | 'last' | 'popular',
};

export class OpinionSourceBox extends React.Component<Props, State> {
  state = {
    order: 'last',
  };

  handleFilterChange = (event: $FlowFixMe) => {
    this.setState({
      order: event.target.value,
    });
  };

  render() {
    const { sourceable, isAuthenticated } = this.props;
    const { order } = this.state;
    const totalCount = sourceable.allSources.totalCount;
    return (
      <div>
        {sourceable.viewerSourcesUnpublished &&
        sourceable.viewerSourcesUnpublished.totalCount > 0 ? (
          <Panel bsStyle="danger">
            <Panel.Heading>
              <Panel.Title>
                <strong>
                  <FormattedMessage
                    id="count-sources"
                    values={{ num: sourceable.viewerSourcesUnpublished.totalCount }}
                  />
                </strong>{' '}
                <FormattedMessage id="awaiting-publication-lowercase" />
              </Panel.Title>
            </Panel.Heading>
            <ListGroup className="list-group-custom">
              {sourceable.viewerSourcesUnpublished.edges &&
                sourceable.viewerSourcesUnpublished.edges
                  .filter(Boolean)
                  .map(edge => edge.node)
                  .filter(Boolean)
                  .map(source => (
                    // $FlowFixMe https://github.com/cap-collectif/platform/issues/4973
                    <OpinionSource key={source.id} source={source} sourceable={sourceable} />
                  ))}
            </ListGroup>
          </Panel>
        ) : null}
        <Panel>
          <Panel.Heading>
            <Row>
              <Col xs={12} sm={6} md={6}>
                <OpinionSourceAdd sourceable={sourceable} />
              </Col>
              {totalCount > 1 && (
                <Col xs={12} sm={6} md={6}>
                  <Filter show value={order} onChange={this.handleFilterChange} />
                </Col>
              )}
            </Row>
          </Panel.Heading>
          <QueryRenderer
            environment={environment}
            query={graphql`
              query OpinionSourceBoxQuery(
                $sourceableId: ID!
                $isAuthenticated: Boolean!
                $count: Int!
                $cursor: String
                $orderBy: SourceOrder!
              ) {
                sourceable: node(id: $sourceableId) {
                  ...OpinionSourceListView_sourceable
                    @arguments(
                      cursor: $cursor
                      orderBy: $orderBy
                      count: $count
                      isAuthenticated: $isAuthenticated
                    )
                }
              }
            `}
            variables={
              ({
                isAuthenticated,
                cursor: null,
                count: 25,
                sourceableId: sourceable.id,
                orderBy: { field: 'PUBLISHED_AT', direction: 'DESC' },
              }: OpinionSourceBoxQueryVariables)
            }
            render={({ error, props }: { props?: ?OpinionSourceBoxQueryResponse } & ReadyState) => {
              if (error) {
                return graphqlError;
              }
              if (props) {
                if (!props.sourceable) {
                  return graphqlError;
                }
                return (
                  // $FlowFixMe
                  <OpinionSourceListView order={order} sourceable={props.sourceable} />
                );
              }
              return <Loader />;
            }}
          />
        </Panel>
      </div>
    );
  }
}

export default createFragmentContainer(OpinionSourceBox, {
  sourceable: graphql`
    fragment OpinionSourceBox_sourceable on Sourceable
      @argumentDefinitions(isAuthenticated: { type: "Boolean!" }) {
      id
      ...OpinionSourceAdd_sourceable
      ...OpinionSource_sourceable
      allSources: sources(first: 0) {
        totalCount
      }
      viewerSourcesUnpublished(first: 100)
        @include(if: $isAuthenticated)
        @connection(key: "OpinionSourceBox_viewerSourcesUnpublished") {
        totalCount
        edges {
          node {
            id
            ...OpinionSource_source
          }
        }
      }
    }
  `,
});
