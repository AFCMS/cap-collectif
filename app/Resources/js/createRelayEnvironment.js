// @flow
import { Environment, Network, RecordSource, Store } from 'relay-runtime';
import { createHeaders } from './services/Fetcher';
import { graphQLUrl } from './config';

function fetchQuery(operation, variables) {
  return fetch(graphQLUrl, {
    method: 'POST',
    headers: createHeaders(),
    body: JSON.stringify({
      operationName: operation.name,
      query: operation.text,
      variables,
    }),
  }).then(response => {
    return response.json();
  });
}

export default new Environment({
  network: Network.create(fetchQuery),
  store: new Store(new RecordSource()),
});
