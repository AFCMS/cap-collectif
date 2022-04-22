// @flow
import React from 'react';
import { graphql, createFragmentContainer } from 'react-relay';
import { FormattedMessage } from 'react-intl';
import { ListGroup, ListGroupItem } from 'react-bootstrap';
import OpinionPreview from './OpinionPreview';
import OpinionAnswer from './OpinionAnswer';
import OpinionButtons from './OpinionButtons';
import OpinionAppendices from './OpinionAppendices';
import OpinionBody from './OpinionBody';
import OpinionVotesBox from './Votes/OpinionVotesBox';
import type { OpinionBox_opinion } from './__generated__/OpinionBox_opinion.graphql';

type Props = {
  +opinion: OpinionBox_opinion,
  rankingThreshold: number,
  opinionTerm: number,
};

export class OpinionBox extends React.Component<Props> {
  getBoxLabel = () => {
    const { opinionTerm } = this.props;
    return this.props.opinion.__typename === 'Version'
      ? 'opinion.header.version'
      : opinionTerm === 0
        ? 'opinion.header.opinion'
        : 'opinion.header.article';
  };

  render() {
    const { opinion, opinionTerm, rankingThreshold } = this.props;
    if (!opinion.section) return null;
    const color = opinion.section.color;
    if (!opinion.section) return null;
    const parentTitle =
      opinion.__typename === 'Version' ? opinion.parent.title : opinion.section.title;
    const headerTitle = this.getBoxLabel();
    if (!opinion.section) return null;

    const backLink = opinion.__typename === 'Version' ? opinion.parent.url : opinion.section.url;
    const colorClass = `opinion opinion--${color} opinion--current`;

    return (
      <div className="block block--bordered opinion__details">
        <div className={colorClass}>
          <div className="opinion__header opinion__header--centered" style={{ height: 'auto' }}>
            <a className="pull-left btn btn-default opinion__header__back" href={backLink}>
              <i className="cap cap-arrow-1-1" />
              <span className="hidden-xs hidden-sm">
                {' '}
                <FormattedMessage id="opinion.header.back" />
              </span>
            </a>
            <div className="opinion__header__title" />
            <h2 className="h4 opinion__header__title">
              <FormattedMessage id={headerTitle} />
              <p className="small excerpt" style={{ marginTop: '5px' }}>
                {parentTitle}
              </p>
            </h2>
          </div>
          <ListGroup className="list-group-custom mb-0">
            <ListGroupItem className="list-group-item__opinion no-border">
              <div className="left-block">
                {/* $FlowFixMe */}
                <OpinionPreview
                  rankingThreshold={rankingThreshold}
                  opinionTerm={opinionTerm}
                  opinion={opinion}
                  link={false}
                />
              </div>
            </ListGroupItem>
          </ListGroup>
        </div>
        {/* $FlowFixMe */}
        <OpinionAppendices opinion={opinion} />
        <div className="opinion__description">
          <p className="h4" style={{ marginTop: '0' }}>
            {opinion.title}
          </p>
          {/* $FlowFixMe */}
          <OpinionBody opinion={opinion} />
          <div
            className="opinion__buttons"
            style={{ marginTop: '15px', marginBottom: '15px' }}
            aria-label={<FormattedMessage id="vote.form" />}>
            <OpinionButtons opinion={opinion} />
          </div>
          {/* $FlowFixMe */}
          <OpinionVotesBox opinion={opinion} />
        </div>
        {/* $FlowFixMe */}
        <OpinionAnswer opinion={opinion} />
      </div>
    );
  }
}

export default createFragmentContainer(OpinionBox, {
  opinion: graphql`
    fragment OpinionBox_opinion on OpinionOrVersion
      @argumentDefinitions(isAuthenticated: { type: "Boolean!" }) {
      ...OpinionPreview_opinion
      ...OpinionAnswer_opinion
      ...OpinionVotesBox_opinion @arguments(isAuthenticated: $isAuthenticated)
      ...OpinionButtons_opinion @arguments(isAuthenticated: $isAuthenticated)
      ...OpinionBody_opinion
      ...OpinionAppendices_opinion
      ... on Opinion {
        __typename
        title
        section {
          title
          color
          url
        }
      }
      ... on Version {
        __typename
        title
        section {
          title
          color
          url
        }
        parent {
          title
          url
        }
      }
    }
  `,
});
