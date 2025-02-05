// @flow
import * as React from 'react';
import { FormattedMessage } from 'react-intl';
import { createFragmentContainer, graphql } from 'react-relay';
import { type ResponseValue_response } from '~relay/ResponseValue_response.graphql';
import getValueFromResponse from '~/utils/form/getValueFromResponse';
import WYSIWYGRender from '../../Form/WYSIWYGRender';

type Props = {
  response: ResponseValue_response,
};

export class ResponseValue extends React.Component<Props> {
  render() {
    const { response } = this.props;
    const responseValue = response.value
      ? getValueFromResponse(response.question.type, response.value)
      : null;

    if (
      (!responseValue || (Array.isArray(responseValue) && !responseValue.length)) &&
      !response.medias
    ) {
      return (
        <p>
          <FormattedMessage id="reply.show.response.no_value" />
        </p>
      );
    }
    if (response.question.type === 'editor') {
      // Here, we know that responseValue is a string and not maybe { label: string, value: string }
      // as react-select set it, so we force cast it
      return <WYSIWYGRender value={((responseValue: any): ?string | number)} />;
    }
    if (response.question.type === 'medias' && response.medias) {
      return (
        <ol>
          {response.medias.map((media: Object) => (
            <li>
              <a href={media.url} download>
                {media.name}
              </a>
            </li>
          ))}
        </ol>
      );
    }
    if (response.question.type === 'ranking') {
      return Array.isArray(responseValue) && responseValue.length > 0 ? (
        <ol>
          {responseValue.map((label, index) => (
            <li key={index}>{label}</li>
          ))}
        </ol>
      ) : (
        <p>
          <FormattedMessage id="reply.show.response.no_value" />
        </p>
      );
    }

    if (
      responseValue &&
      typeof responseValue === 'object' &&
      typeof responseValue.labels !== 'undefined'
    ) {
      const { labels } = responseValue;

      if (labels && responseValue.other) {
        labels.push(responseValue.other);
      }

      return labels && labels.length > 0 ? (
        <p>{labels.join(', ')}</p>
      ) : (
        <p>
          <FormattedMessage id="reply.show.response.no_value" />
        </p>
      );
    }
    // Here, responseValue must either be a string or a number
    return <p>{((responseValue: any): ?string | number)}</p>;
  }
}

export default createFragmentContainer(ResponseValue, {
  response: graphql`
    fragment ResponseValue_response on Response {
      question {
        id
        type
      }
      ... on ValueResponse {
        value
      }
      ... on MediaResponse {
        medias {
          name
          url
        }
      }
    }
  `,
});
