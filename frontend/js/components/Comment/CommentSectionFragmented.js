// @flow
import React from 'react';
import { graphql, createFragmentContainer } from 'react-relay';
import type { CommentSectionFragmented_commentable } from '~relay/CommentSectionFragmented_commentable.graphql';
import CommentSectionView from './CommentSectionView';

type Props = {|
  +commentable: CommentSectionFragmented_commentable,
  +isAuthenticated: boolean,
  +useBodyColor: boolean,
  newDesign?: boolean,
|};

export class CommentSectionFragmented extends React.Component<Props> {
  static defaultProps = {
    useBodyColor: false,
  };

  render() {
    const { isAuthenticated, useBodyColor, commentable, newDesign } = this.props;

    if (!commentable) {
      return null;
    }

    return (
      <div className="comments__section">
        <CommentSectionView
          commentable={commentable}
          isAuthenticated={isAuthenticated}
          useBodyColor={useBodyColor}
          newDesign={newDesign}
        />
      </div>
    );
  }
}

export default createFragmentContainer(CommentSectionFragmented, {
  commentable: graphql`
    fragment CommentSectionFragmented_commentable on Commentable
      @argumentDefinitions(isAuthenticated: { type: "Boolean!" }) {
      id
      allComments: comments(first: 0) {
        totalCountWithAnswers
      }
      ...CommentListView_commentable @arguments(isAuthenticated: $isAuthenticated)
      ...CommentForm_commentable
    }
  `,
});
