// @flow
import React from 'react';
import { FormattedMessage, injectIntl, type IntlShape } from 'react-intl';
import { graphql, createPaginationContainer, type RelayPaginationProp } from 'react-relay';
import classNames from 'classnames';
import Comment from './Comment';
import type { CommentListViewPaginated_commentable } from './__generated__/CommentListViewPaginated_commentable.graphql';

type Props = {
  relay: RelayPaginationProp,
  intl: IntlShape,
  commentable: CommentListViewPaginated_commentable,
  highlightedComment: ?string,
};

export class CommentListViewPaginated extends React.Component<Props> {
  render() {
    const { intl, commentable, relay, highlightedComment } = this.props;
    if (!commentable.comments || commentable.comments.totalCount === 0) {
      return null;
    }

    const classes = classNames({
      'media-list': true,
      opinion__list: true,
    });

    return (
      <ul id="comments" className={classes}>
        {commentable.comments &&
          commentable.comments.edges &&
          commentable.comments.edges
            .filter(Boolean)
            .map(edge => edge.node)
            .filter(Boolean)
            .map(node => {
              return (
                // $FlowFixMe $refType
                <Comment
                  key={node.id}
                  comment={node}
                  isHighlighted={node.id === highlightedComment}
                />
              );
            })}
        {relay.hasMore() && (
          <button
            id="comments-section-load-more"
            className="btn btn-block btn-secondary"
            data-loading-text={intl.formatMessage({ id: 'global.loading' })}
            onClick={() => {
              relay.loadMore(10);
            }}>
            <FormattedMessage id="comment.more" />
          </button>
        )}
      </ul>
    );
  }
}

export default createPaginationContainer(
  injectIntl(CommentListViewPaginated),
  {
    commentable: graphql`
      fragment CommentListViewPaginated_commentable on Commentable
        @argumentDefinitions(
          count: { type: "Int", defaultValue: 10 }
          cursor: { type: "String" }
          orderBy: { type: "CommentOrder!" }
          isAuthenticated: { type: "Boolean!" }
        ) {
        id
        comments(first: $count, after: $cursor, orderBy: $orderBy)
          @connection(key: "CommentListViewPaginated_comments", filters: ["orderBy"]) {
          totalCount
          edges {
            node {
              id
              ...Comment_comment @arguments(isAuthenticated: $isAuthenticated)
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
      return props.commentable && props.commentable.comments;
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
      };
    },
    query: graphql`
      query CommentListViewPaginatedQuery(
        $commentableId: ID!
        $cursor: String
        $orderBy: CommentOrder!
        $isAuthenticated: Boolean!
        $count: Int
      ) {
        commentable: node(id: $commentableId) {
          ...CommentListViewPaginated_commentable
            @arguments(
              cursor: $cursor
              orderBy: $orderBy
              isAuthenticated: $isAuthenticated
              count: $count
            )
        }
      }
    `,
  },
);
