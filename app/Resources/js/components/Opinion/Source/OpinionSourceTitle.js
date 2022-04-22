// @flow
import React from 'react';
import { graphql, createFragmentContainer } from 'react-relay';
import { Label } from 'react-bootstrap';
import type { OpinionSourceTitle_source } from './__generated__/OpinionSourceTitle_source.graphql';

type Props = {
  source: OpinionSourceTitle_source,
};

const OpinionSourceTitle = ({ source }: Props) => {
  return (
    <h3 className="opinion__title">
      <Label bsStyle="primary">{source.category.title}</Label>{' '}
      <a className="external-link" href={source.url}>
        {source.title}
      </a>
    </h3>
  );
};

export default createFragmentContainer(
  OpinionSourceTitle,
  graphql`
    fragment OpinionSourceTitle_source on Source {
      id
      title
      category {
        title
      }
      url
    }
  `,
);
