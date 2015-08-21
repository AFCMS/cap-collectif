import ArgumentActions from '../../actions/ArgumentActions';
import LoginStore from '../../stores/LoginStore';
import LoginOverlay from '../Utils/LoginOverlay';

const Button = ReactBootstrap.Button;

const OpinionArgumentButtons = React.createClass({
  propTypes: {
    argument: React.PropTypes.object.isRequired,
  },
  mixins: [ReactIntl.IntlMixin],

  getInitialState() {
    return {
      hasInitiallyVoted: this.props.argument.has_user_voted,
      hasVoted: false,
    };
  },

  renderVoteButton() {
    if (this.state.hasInitiallyVoted || this.state.hasVoted) {
      return (
        <Button bsStyle="danger" bsSize="xsmall" className="btn--outline" onClick={!LoginStore.isLoggedIn() ? null : this.deleteVote.bind(null, this)}>
          { this.getIntlMessage('vote.cancel') }
        </Button>
      );
    }
    return (
      <Button bsStyle="success" bsSize="xsmall" className="btn--outline" onClick={!LoginStore.isLoggedIn() ? null : this.vote.bind(null, this)}>
        <i className="cap-hand-like-2"></i> { this.getIntlMessage('vote.ok') }
      </Button>
    );
  },

  renderReportButton() {
    if (!this.isTheUserTheAuthor()) {
      const reported = this.props.argument.has_user_reported;
      return (
        <LoginOverlay children={
          <Button
            href={reported ? null : this.props.argument._links.report}
            bsSize="xsmall"
            active={reported}
            className="btn-dark-gray btn--outline"
          >
            <i className="cap cap-flag-1"></i>
            {reported ? this.getIntlMessage('global.report.reported') : this.getIntlMessage('global.report.submit')}
          </Button>
        } />
      );
    }
  },

  renderEditButton() {
    if (this.isTheUserTheAuthor()) {
      return (
        <Button href={this.props.argument._links.edit} bsSize="xsmall" className="btn-dark-gray btn--outline">
          <i className="cap cap-pencil-1"></i>
          {this.getIntlMessage('global.edit')}
        </Button>
      );
    }
  },

  render() {
    const argument = this.props.argument;
    return (
      <div>
        <form style={{display: 'inline-block'}}>
          <LoginOverlay children={ this.renderVoteButton() } />
        </form>
        { ' ' }
        <span className="opinion__votes-nb">
          { argument.votes_count + (this.state.hasVoted ? 1 : 0)}
        </span>
        { ' ' }

        { this.renderReportButton() }
        { this.renderEditButton() }
      </div>
    );
  },

  vote() {
    this.setState({hasVoted: true});
    ArgumentActions.addVote(this.props.argument.id);
  },

  deleteVote() {
    this.setState({hasVoted: false});
    ArgumentActions.deleteVote(this.props.argument.id);
  },

  isTheUserTheAuthor() {
    if (this.props.argument.author === null || !LoginStore.isLoggedIn()) {
      return false;
    }
    return LoginStore.user.unique_id === this.props.argument.author.unique_id;
  },


});

export default OpinionArgumentButtons;
