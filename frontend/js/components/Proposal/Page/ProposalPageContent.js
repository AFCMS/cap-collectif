// @flow
import * as React from 'react';
import { FormattedMessage } from 'react-intl';
import { connect } from 'react-redux';
import { graphql, createFragmentContainer } from 'react-relay';
import classNames from 'classnames';
import { Map, Marker, TileLayer } from 'react-leaflet';
import ShareButtonDropdown from '../../Utils/ShareButtonDropdown';
import ProposalEditModal from '../Edit/ProposalEditModal';
import ProposalDeleteModal from '../Delete/ProposalDeleteModal';
import EditButton from '../../Form/EditButton';
import DeleteButton from '../../Form/DeleteButton';
import ProposalReportButton from '../Report/ProposalReportButton';
import ProposalPageComments from './ProposalPageComments';
import ProposalResponse from './ProposalResponse';
import ProposalVoteButtonWrapperFragment from '../Vote/ProposalVoteButtonWrapperFragment';
import { openDeleteProposalModal, openEditProposalModal } from '../../../redux/modules/proposal';
import config from '../../../config';
import type { ProposalPageContent_proposal } from '~relay/ProposalPageContent_proposal.graphql';
import type { ProposalPageContent_viewer } from '~relay/ProposalPageContent_viewer.graphql';
import type { ProposalPageContent_step } from '~relay/ProposalPageContent_step.graphql';
import WYSIWYGRender from '../../Form/WYSIWYGRender';
import type { GlobalState } from '../../../types';
import type { MapTokens } from '../../../redux/modules/user';

let L;

type Props = {|
  +viewer: ?ProposalPageContent_viewer,
  +step: ?ProposalPageContent_step,
  +proposal: ProposalPageContent_proposal,
  +mapTokens: MapTokens,
  +className: string,
  +dispatch: Function,
|};

export class ProposalPageContent extends React.Component<Props> {
  static defaultProps = {
    className: '',
  };

  render() {
    const { proposal, step, className, dispatch, viewer, mapTokens } = this.props;
    const { publicToken, styleId, styleOwner } = mapTokens.MAPBOX;

    const classes = {
      proposal__content: true,
      [className]: true,
    };
    if (config.canUseDOM) {
      L = require('leaflet'); // eslint-disable-line
    }

    const { address } = proposal;
    const proposalForm = proposal.form;

    return (
      <div id="ProposalPageContent" className={classNames(classes)}>
        <div className="block">
          {viewer && viewer.id === proposal.author.id && (
            <div className="actions">
              <EditButton
                id="proposal-edit-button"
                author={{ uniqueId: proposal.author.slug }}
                onClick={() => {
                  dispatch(openEditProposalModal());
                }}
                editable={proposalForm.contribuable}
              />
              <DeleteButton
                id="proposal-delete-button"
                author={{ uniqueId: proposal.author.slug }}
                onClick={() => {
                  dispatch(openDeleteProposalModal());
                }}
                style={{ marginLeft: '15px' }}
                deletable={proposalForm.contribuable}
              />
            </div>
          )}
          {proposal.media && (
            <img
              id="proposal-media"
              src={proposal.media.url}
              alt={proposal.title}
              className="img-responsive mb-15"
            />
          )}
          {proposal.summary && <p className="excerpt">{proposal.summary}</p>}
          {proposal.body && (
            <div>
              <h3 className="h3">
                <FormattedMessage id="global.description" />
              </h3>
              <WYSIWYGRender value={proposal.body} />
            </div>
          )}
        </div>
        {address && config.canUseDOM && (
          <div className="block proposal-map__block">
            <h3 className="h3">
              <FormattedMessage id="proposal_form.address" />
            </h3>
            <p>{address.formatted}</p>
            <Map
              center={{
                lat: address.lat,
                lng: address.lng,
              }}
              zoom={16}
              maxZoom={18}
              style={{
                width: '100%',
                height: 175,
              }}>
              <TileLayer
                attribution='&copy; <a href"https://www.mapbox.com/about/maps/">Mapbox</a> &copy; <a href="http://osm.org/copyright">OpenStreetMap</a> <a href"https://www.mapbox.com/map-feedback/#/-74.5/40/10">Improve this map</a>'
                url={`https://api.mapbox.com/styles/v1/${styleOwner}/${styleId}/tiles/256/{z}/{x}/{y}?access_token=${publicToken}`}
              />
              <Marker
                position={[address.lat, address.lng]}
                icon={L.icon({
                  iconUrl: '/svg/marker.svg',
                  iconSize: [40, 40],
                  iconAnchor: [20, 40],
                  popupAnchor: [0, -40],
                })}
              />
            </Map>
          </div>
        )}
        {proposal.responses.map((response, index) => (
          /* $FlowFixMe */
          <ProposalResponse key={index} response={response} />
        ))}
        <div className="block proposal__buttons">
          {proposal.publicationStatus !== 'DRAFT' && (
            <div>
              {step && (
                <ProposalVoteButtonWrapperFragment
                  viewer={viewer}
                  step={step}
                  className="mr-15"
                  proposal={proposal}
                />
              )}
              <ShareButtonDropdown
                id="proposal-share-button"
                url={proposal.url}
                title={proposal.title}
              />
              <ProposalReportButton proposal={proposal} />
            </div>
          )}
        </div>
        <ProposalEditModal proposal={proposal} />
        <ProposalDeleteModal proposal={proposal} />
        {proposal.publicationStatus !== 'DRAFT' && <ProposalPageComments proposal={proposal} />}
      </div>
    );
  }
}

const mapStateToProps = (state: GlobalState) => ({
  mapTokens: state.user.mapTokens,
});

const container = connect(mapStateToProps)(ProposalPageContent);

export default createFragmentContainer(container, {
  step: graphql`
    fragment ProposalPageContent_step on ProposalStep {
      id
      ...ProposalVoteButtonWrapperFragment_step
    }
  `,
  viewer: graphql`
    fragment ProposalPageContent_viewer on User
      @argumentDefinitions(hasVotableStep: { type: "Boolean", defaultValue: true }) {
      id
      ...ProposalVoteButtonWrapperFragment_viewer
        @arguments(stepId: $stepId, isAuthenticated: $isAuthenticated)
        @include(if: $hasVotableStep)
    }
  `,
  proposal: graphql`
    fragment ProposalPageContent_proposal on Proposal {
      id
      ...ProposalDeleteModal_proposal
      ...ProposalEditModal_proposal
      ...ProposalVoteButtonWrapperFragment_proposal
        @arguments(stepId: $stepId, isAuthenticated: $isAuthenticated)
      author {
        id
        slug
      }
      form {
        contribuable
      }
      address {
        formatted
        lat
        lng
      }
      body
      summary
      media {
        url
      }
      ...ProposalPageComments_proposal
      ...ProposalReportButton_proposal @arguments(isAuthenticated: $isAuthenticated)
      publicationStatus
      title
      url
      currentVotableStep {
        id
        open
      }
      responses {
        ...ProposalResponse_response
      }
    }
  `,
});
