// @flow
import React from 'react';
import { graphql, createFragmentContainer } from 'react-relay';
import { FormattedMessage } from 'react-intl';
import UserAvatarDeprecated from '../User/UserAvatarDeprecated';
import OpinionInfos from './OpinionInfos';
import OpinionPreviewTitle from './OpinionPreviewTitle';
import OpinionPreviewCounters from './OpinionPreviewCounters';
import type { OpinionPreview_opinion } from '~relay/OpinionPreview_opinion.graphql';
import TrashedMessage from '../Trashed/TrashedMessage';
import Media from '../Ui/Medias/Media/Media';

type Props = {|
  +showUpdatedDate: boolean,
  +opinion: OpinionPreview_opinion,
  +rankingThreshold?: ?number,
  +isProfile: boolean,
|};

class OpinionPreview extends React.Component<Props> {
  static defaultProps = {
    showUpdatedDate: false,
    isProfile: false,
  };

  render() {
    const { opinion, rankingThreshold, showUpdatedDate, isProfile } = this.props;

    return (
      <div>
        {opinion.__typename === 'Version' && isProfile && (
          <p>
            <FormattedMessage id="admin.fields.opinion.link" />
            {' : '}
            <a href={opinion.related ? opinion.related.url : ''}>
              {opinion.related ? opinion.related.title : ''}
            </a>
          </p>
        )}
        <Media.Left>
          {/* $FlowFixMe Will be a fragment soon */}
          <UserAvatarDeprecated user={opinion.author} />
        </Media.Left>

        <Media.Body className="opinion__body">
          <OpinionInfos
            rankingThreshold={rankingThreshold}
            opinion={opinion}
            showUpdatedDate={showUpdatedDate}
          />
          <div className="web">
            <TrashedMessage contribution={opinion}>
              <OpinionPreviewTitle opinion={opinion} showTypeLabel={false} />
            </TrashedMessage>
            <OpinionPreviewCounters opinion={opinion} />
          </div>
        </Media.Body>
        <div className="opinion__body mt-10 mobile">
          <TrashedMessage contribution={opinion}>
            <OpinionPreviewTitle opinion={opinion} showTypeLabel={false} />
          </TrashedMessage>
          <OpinionPreviewCounters opinion={opinion} />
        </div>
      </div>
    );
  }
}

export default createFragmentContainer(OpinionPreview, {
  opinion: graphql`
    fragment OpinionPreview_opinion on OpinionOrVersion {
      __typename
      ...TrashedMessage_contribution
      ...OpinionInfos_opinion
      ...OpinionPreviewTitle_opinion
      ...OpinionPreviewCounters_opinion
      ... on Opinion {
        author {
          displayName
          media {
            url
          }
        }
      }
      ... on Version {
        author {
          displayName
          media {
            url
          }
        }
        related {
          ... on Opinion {
            title
            url
          }
        }
      }
    }
  `,
});
