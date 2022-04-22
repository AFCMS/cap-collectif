// @flow
import { graphql, type RecordSourceSelectorProxy } from 'react-relay';
import { ConnectionHandler } from 'relay-runtime';
import environment from '../createRelayEnvironment';
import commitMutation from './commitMutation';
import type {
  DeleteArgumentMutationVariables,
  DeleteArgumentMutationResponse,
} from './__generated__/DeleteArgumentMutation.graphql';

const mutation = graphql`
  mutation DeleteArgumentMutation($input: DeleteArgumentInput!) {
    deleteArgument(input: $input) {
      argumentable {
        id
      }
      deletedArgumentId
    }
  }
`;

const commit = (
  variables: DeleteArgumentMutationVariables,
  type: 'FOR' | 'AGAINST',
  published: boolean,
): Promise<DeleteArgumentMutationResponse> =>
  commitMutation(environment, {
    mutation,
    variables,
    configs: [
      {
        type: 'NODE_DELETE',
        deletedIDFieldName: 'deletedArgumentId',
      },
    ],
    updater: (store: RecordSourceSelectorProxy) => {
      const payload = store.getRootField('deleteArgument');
      if (!payload) return;
      const argumentable = payload.getLinkedRecord('argumentable');
      if (!argumentable) return;

      const id = argumentable.getValue('id');
      if (!id || typeof id !== 'string') {
        return;
      }

      const argumentableProxy = store.get(id);
      if (!argumentableProxy) return;

      // We update the "FOR" or "AGAINST" row arguments totalCount
      if (published) {
        const connection = ConnectionHandler.getConnection(
          argumentableProxy,
          'ArgumentList_allArguments',
          {
            type,
          },
        );
        connection.setValue(connection.getValue('totalCount') - 1, 'totalCount');

        const allArgumentsProxy = argumentableProxy.getLinkedRecord('arguments', { first: 0 });
        if (!allArgumentsProxy) return;
        const previousValue = parseInt(allArgumentsProxy.getValue('totalCount'), 10);
        allArgumentsProxy.setValue(previousValue - 1, 'totalCount');
      }

      if (!published) {
        const connection = ConnectionHandler.getConnection(
          argumentableProxy,
          'UnpublishedArgumentList_viewerUnpublishedArguments',
          {
            type,
          },
        );
        connection.setValue(connection.getValue('totalCount') - 1, 'totalCount');
      }
    },
  });

export default { commit };
