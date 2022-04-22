// @flow
import React, { Component } from 'react';
import { ListGroup, ListGroupItem, Button } from 'react-bootstrap';
import { FormattedMessage } from 'react-intl';
import { graphql, createPaginationContainer, type RelayPaginationProp } from 'react-relay';
import type { ArgumentListProfile_argumentList } from './__generated__/ArgumentListProfile_argumentList.graphql';

import ArgumentItem from './ArgumentItem';
import Loader from '../Ui/Loader';

const ARGUMENTS_PAGINATION = 5;

type Props = {
  relay: RelayPaginationProp,
  argumentList: ArgumentListProfile_argumentList,
};

type State = {
  loading: boolean,
};

export class ArgumentListProfile extends Component<Props, State> {
  state = {
    loading: false,
  };

  handleLoadMore = () => {
    this.setState({ loading: true });
    this.props.relay.loadMore(ARGUMENTS_PAGINATION, () => {
      this.setState({ loading: false });
    });
  };

  render() {
    const { argumentList, relay } = this.props;

    return (
      <ListGroup bsClass="media-list">
        {argumentList.arguments &&
          argumentList.arguments.edges &&
          argumentList.arguments.edges
            .filter(Boolean)
            .map(edge => edge.node)
            .filter(Boolean)
            .map(argument => (
              // $FlowFixMe
              <ArgumentItem key={argument.id} argument={argument} isProfile />
            ))}
        {relay.hasMore() && (
          <ListGroupItem style={{ textAlign: 'center' }}>
            {this.state.loading ? (
              <Loader />
            ) : (
              <Button bsStyle="link" onClick={this.handleLoadMore}>
                <FormattedMessage id="global.more" />
              </Button>
            )}
          </ListGroupItem>
        )}
      </ListGroup>
    );
  }
}

export default createPaginationContainer(
  ArgumentListProfile,
  {
    argumentList: graphql`
      fragment ArgumentListProfile_argumentList on User
        @argumentDefinitions(
          cursor: { type: "String" }
          count: { type: "Int!" }
          isAuthenticated: { type: "Boolean!" }
        ) {
        id
        arguments(first: $count, after: $cursor) @connection(key: "ArgumentListProfile_arguments") {
          totalCount
          edges {
            node {
              id
              ...ArgumentItem_argument @arguments(isAuthenticated: $isAuthenticated)
            }
          }
          pageInfo {
            hasPreviousPage
            hasNextPage
            startCursor
            endCursor
          }
        }
      }
    `,
  },
  {
    direction: 'forward',
    getConnectionFromProps(props: Props) {
      return props.argumentList && props.argumentList.arguments;
    },
    getFragmentVariables(prevVars) {
      return {
        ...prevVars,
      };
    },
    getVariables(props: Props, { count, cursor }, fragmentVariables) {
      return {
        ...fragmentVariables,
        count,
        cursor,
        argumentId: props.argumentList.id,
      };
    },
    query: graphql`
      query ArgumentListProfileQuery(
        $userId: ID!
        $cursor: String
        $count: Int
        $isAuthenticated: Boolean!
      ) {
        argumentList: node(id: $userId) {
          id
          ...ArgumentListProfile_argumentList
            @arguments(cursor: $cursor, count: $count, isAuthenticated: $isAuthenticated)
        }
      }
    `,
  },
);
