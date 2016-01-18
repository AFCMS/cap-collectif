import React from 'react';
import {IntlMixin, FormattedMessage} from 'react-intl';
import ProposalStore from '../../stores/ProposalStore';
import ProposalVoteStore from '../../stores/ProposalVoteStore';
import MessageStore from '../../stores/MessageStore';
import ProposalActions from '../../actions/ProposalActions';
import {PROPOSAL_PAGINATION} from '../../constants/ProposalConstants';
import ProposalListFilters from '../Proposal/List/ProposalListFilters';
import ProposalList from '../Proposal/List/ProposalList';
import Loader from '../Utils/Loader';
import Pagination from '../Utils/Pagination';
import FlashMessages from '../Utils/FlashMessages';

const SelectionStepPage = React.createClass({
  propTypes: {
    themes: React.PropTypes.array.isRequired,
    statuses: React.PropTypes.array.isRequired,
    districts: React.PropTypes.array.isRequired,
    types: React.PropTypes.array.isRequired,
    stepId: React.PropTypes.number.isRequired,
    votable: React.PropTypes.bool.isRequired,
    count: React.PropTypes.number.isRequired,
    creditsLeft: React.PropTypes.number.isRequired,
  },
  mixins: [IntlMixin],

  getInitialState() {
    ProposalActions.initProposalVotes(this.props.votable ? this.props.stepId : null, this.props.creditsLeft);
    return {
      proposals: ProposalStore.proposals,
      proposalsCount: this.props.count,
      currentPage: ProposalStore.currentPage,
      creditsLeft: ProposalVoteStore.creditsLeft,
      votableStep: ProposalVoteStore.votableStep,
      isLoading: true,
      messages: {
        'errors': [],
        'success': [],
      },
    };
  },

  componentWillMount() {
    ProposalStore.addChangeListener(this.onChange);
    ProposalVoteStore.addChangeListener(this.onVoteChange);
    MessageStore.addChangeListener(this.onMessageChange);
  },

  componentDidMount() {
    this.loadProposals();
  },

  componentDidUpdate(prevProps, prevState) {
    if (prevState && (prevState.currentPage !== this.state.currentPage)) {
      this.loadProposals();
    }
  },

  componentWillUnmount() {
    ProposalStore.removeChangeListener(this.onChange);
    ProposalVoteStore.removeChangeListener(this.onVoteChange);
    MessageStore.removeChangeListener(this.onMessageChange);
  },

  onMessageChange() {
    this.setState({
      messages: MessageStore.messages,
    });
  },

  onVoteChange() {
    this.setState({
      votableStep: ProposalVoteStore.votableStep,
      creditsLeft: ProposalVoteStore.creditsLeft,
    });
  },

  onChange() {
    if (ProposalStore.isProposalListSync) {
      this.setState({
        proposals: ProposalStore.proposals,
        proposalsCount: ProposalStore.proposalsCount,
        currentPage: ProposalStore.currentPage,
        isLoading: false,
      });
      return;
    }

    this.setState({
      isLoading: true,
    });
    this.loadProposals();
  },

  loadProposals() {
    ProposalActions.load('selectionStep', this.props.stepId);
  },

  handleFilterOrOrderChange() {
    this.setState({isLoading: true});
  },

  selectPage(newPage) {
    this.setState({isLoading: true});
    ProposalActions.changePage(newPage);
  },

  render() {
    const nbPages = Math.ceil(this.state.proposalsCount / PROPOSAL_PAGINATION);
    return (
      <div>
        <h2 className="h2">
          <FormattedMessage
            message={this.getIntlMessage('proposal.count')}
            num={this.state.proposalsCount}
          />
        </h2>
        <ProposalListFilters
          id={this.props.stepId}
          fetchFrom="selectionStep"
          theme={this.props.themes}
          district={this.props.districts}
          type={this.props.types}
          status={this.props.statuses}
          onChange={() => this.handleFilterOrOrderChange()}
          orderByVotes={this.props.votable}
        />
        <br />
        <Loader show={this.state.isLoading}>
          <div>
            <ProposalList proposals={this.state.proposals} selectionStepId={this.state.votableStep} creditsLeft={this.state.creditsLeft} />
            {
              nbPages > 1
              ? <Pagination
                  current={this.state.currentPage}
                  nbPages={nbPages}
                  onChange={this.selectPage}
              />
              : null
            }
          </div>
        </Loader>
        <FlashMessages
          errors={this.state.messages.errors}
          success={this.state.messages.success}
          style={{marginBottom: '0'}}
        />
      </div>
    );
  },

});

export default SelectionStepPage;
