// @flow
import * as React from 'react';
import { graphql, createFragmentContainer } from 'react-relay';
import { FormattedMessage } from 'react-intl';
import Card from '../Ui/Card/Card';
import OpinionTypeLabel from './OpinionTypeLabel';
import type { OpinionPreviewTitle_opinion } from '~relay/OpinionPreviewTitle_opinion.graphql';
import {translateContent} from "~/utils/ContentTranslator";

type Props = {
  opinion: OpinionPreviewTitle_opinion,
  showTypeLabel: boolean,
};

export class OpinionPreviewTitle extends React.Component<Props> {
  render() {
    const { opinion, showTypeLabel } = this.props;

    return (
      <Card.Title tagName="div" firstElement={false}>
        {opinion.trashed && (
          <span className="label label-default mr-5">
            <FormattedMessage id="global.is_trashed" />
          </span>
        )}
        {/* $FlowFixMe */}
        {showTypeLabel ? <OpinionTypeLabel section={opinion.section || null} /> : null}
        {showTypeLabel ? ' ' : null}
        {/* $FlowFixMe */}
        <a href={opinion.url}>{translateContent(opinion.title)}</a>
      </Card.Title>
    );
  }
}

export default createFragmentContainer(OpinionPreviewTitle, {
  opinion: graphql`
    fragment OpinionPreviewTitle_opinion on OpinionOrVersion {
      ... on Opinion {
        url
        title
        trashed
        section {
          ...OpinionTypeLabel_section
        }
      }
      ... on Version {
        url
        title
        section {
          ...OpinionTypeLabel_section
        }
      }
    }
  `,
});
