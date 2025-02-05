// @flow
import React from 'react';
import { connect } from 'react-redux';
import { graphql, QueryRenderer } from 'react-relay';
import environment, { graphqlError } from '~/createRelayEnvironment';
import type { State } from '~/types';
import OpinionPageLogic from '~/components/Opinion/New/OpinionPageLogic';
import type { NewOpinionPageQueryResponse } from '~relay/NewOpinionPageQuery.graphql';

export type Props = {|
  +opinionId?: string,
  +versionId?: string,
  +isAuthenticated: boolean,
|};

export const NewOpinionPage = ({ opinionId, versionId, isAuthenticated }: Props) => {
  const id = opinionId ?? versionId;
  if (!id) {
    return null;
  }
  return (
    <QueryRenderer
      environment={environment}
      query={graphql`
        query NewOpinionPageQuery($opinionId: ID!, $isAuthenticated: Boolean!) {
          ...OpinionPageLogic_query
            @arguments(opinionId: $opinionId, isAuthenticated: $isAuthenticated)
        }
      `}
      variables={{
        opinionId: id,
        isAuthenticated,
      }}
      render={({
        error,
        props,
      }: {
        ...ReactRelayReadyState,
        props: ?NewOpinionPageQueryResponse,
      }) => {
        if (error) {
          console.log(error); // eslint-disable-line no-console
          return graphqlError;
        }
        return <OpinionPageLogic query={props} isAuthenticated={isAuthenticated} />;
      }}
    />
  );
};

const mapStateToProps = (state: State) => ({
  isAuthenticated: state.user.user !== null,
});

export default connect<any, any, _, _, _, _>(mapStateToProps)(NewOpinionPage);
