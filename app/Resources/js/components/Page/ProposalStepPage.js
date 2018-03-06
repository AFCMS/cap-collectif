// @flow
import React, { PropTypes } from 'react';
import { FormattedMessage } from 'react-intl';
import { connect, type MapStateToProps } from 'react-redux';
import { VOTE_TYPE_DISABLED, PROPOSAL_PAGINATION } from '../../constants/ProposalConstants';
import ProposalListFilters from '../Proposal/List/ProposalListFilters';
import ProposalListRandomRow from '../Proposal/List/ProposalListRandomRow';
import ProposalList from '../Proposal/List/ProposalList';
import DraftProposalList from '../Proposal/List/DraftProposalList';
import Loader from '../Utils/Loader';
import Pagination from '../Utils/Pagination';
import CollectStepPageHeader from './CollectStepPageHeader';
import SelectionStepPageHeader from './SelectionStepPageHeader';
import StepPageHeader from '../Steps/Page/StepPageHeader';
import VisibilityBox from '../Utils/VisibilityBox';
import LeafletMap from '../Proposal/Map/LeafletMap';
import { loadProposals, changePage } from '../../redux/modules/proposal';
import type { State } from '../../types';

export const ProposalStepPage = React.createClass({
  propTypes: {
    step: PropTypes.object.isRequired,
    count: PropTypes.number.isRequired,
    queryCount: PropTypes.number,
    countFusions: PropTypes.number,
    defaultSort: PropTypes.string,
    form: PropTypes.object.isRequired,
    statuses: PropTypes.array.isRequired,
    categories: PropTypes.array.isRequired,
    proposals: PropTypes.array.isRequired,
    currentPage: PropTypes.number.isRequired,
    randomOrder: PropTypes.bool.isRequired,
    isLogged: PropTypes.bool.isRequired,
    isLoading: PropTypes.bool.isRequired,
    dispatch: PropTypes.func.isRequired,
    selectedViewByStep: PropTypes.string.isRequired
  },

  componentDidMount() {
    this.props.dispatch(loadProposals());
  },

  render() {
    const {
      proposals,
      categories,
      form,
      statuses,
      step,
      count,
      defaultSort,
      queryCount,
      countFusions,
      currentPage,
      dispatch,
      isLoading,
      isLogged,
      randomOrder,
      selectedViewByStep
    } = this.props;
    const total = queryCount || count;
    const nbPages = Math.ceil(total / PROPOSAL_PAGINATION);
    const showPagination = nbPages > 1 && !randomOrder;

    let geoJsons = [];
    try {
      geoJsons = form.districts
        .filter(d => d.geojson && d.displayedOnMap)
        .map(d => JSON.parse(d.geojson));
    } catch (e) {
      // eslint-disable-next-line no-console
      console.error("Can't parse your geojsons !", e);
    }

    return (
      <div className="proposal__step-page">
        <StepPageHeader step={step} />
        {isLogged && <DraftProposalList step={step} />}
        {step.type === 'collect' ? (
          <CollectStepPageHeader
            total={count}
            countFusions={countFusions}
            form={form}
            categories={categories}
          />
        ) : (
          <SelectionStepPageHeader total={count} />
        )}
        <ProposalListFilters
          statuses={statuses}
          categories={categories}
          districts={form.districts}
          defaultSort={defaultSort}
          orderByVotes={step.voteType !== VOTE_TYPE_DISABLED}
          orderByComments={form.commentable}
          orderByCost={form.costable}
          showThemes={form.usingThemes}
          showDistrictFilter={form.usingDistrict}
          showCategoriesFilter={form.usingCategories}
          showToggleMapButton={form.usingAddress && !step.isPrivate}
        />
        <Loader show={isLoading}>
          <LeafletMap
            geoJsons={geoJsons}
            defaultMapOptions={{
              center: { lat: form.latMap, lng: form.lngMap },
              zoom: form.zoomMap
            }}
            visible={selectedViewByStep === 'map' && !step.isPrivate}
          />
          {selectedViewByStep === 'mosaic' && (
            <div>
              {proposals.length === 0 && !step.isPrivate ? (
                <p className={{ 'p--centered': true }} style={{ marginBottom: '40px' }}>
                  {<FormattedMessage id="proposal.empty" />}
                </p>
              ) : (
                <VisibilityBox enabled={step.isPrivate}>
                  <ProposalList
                    proposals={proposals}
                    step={step}
                    showThemes={form.usingThemes}
                    showComments={form.commentable}
                    id="proposals-list"
                  />
                </VisibilityBox>
              )}
              {showPagination &&
                selectedViewByStep === 'mosaic' && (
                  <Pagination
                    current={currentPage}
                    nbPages={nbPages}
                    onChange={newPage => {
                      dispatch(changePage(newPage));
                      dispatch(loadProposals());
                    }}
                  />
                )}
              {randomOrder &&
                proposals.length > 3 &&
                selectedViewByStep === 'mosaic' && (
                  <ProposalListRandomRow orderByVotes={step.voteType !== VOTE_TYPE_DISABLED} />
                )}
            </div>
          )}
        </Loader>
      </div>
    );
  }
});

const mapStateToProps: MapStateToProps<*, *, *> = (state: State, props: Object) => ({
  stepId: undefined,
  isLogged: state.user.user !== null,
  step:
    state.project.currentProjectById &&
    state.project.projectsById[state.project.currentProjectById].stepsById[props.stepId],
  proposals: state.proposal.proposalShowedId.map(
    proposal => state.proposal.proposalsById[proposal]
  ),
  queryCount: state.proposal.queryCount,
  currentPage: state.proposal.currentPaginationPage,
  randomOrder: state.proposal.order === 'random',
  isLoading: state.proposal.isLoading,
  selectedViewByStep: state.proposal.selectedViewByStep || 'mosaic'
});
export default connect(mapStateToProps)(ProposalStepPage);
