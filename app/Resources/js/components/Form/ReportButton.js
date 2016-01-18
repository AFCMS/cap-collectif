import LoginStore from '../../stores/LoginStore';
import FeatureStore from '../../stores/FeatureStore';
import LoginOverlay from '../Utils/LoginOverlay';

const Button = ReactBootstrap.Button;

const ReportButton = React.createClass({
  propTypes: {
    url: React.PropTypes.string.isRequired,
    author: React.PropTypes.object,
    hasReported: React.PropTypes.bool,
    className: React.PropTypes.string,
    style: React.PropTypes.object,
  },
  mixins: [ReactIntl.IntlMixin],

  getDefaultProps() {
    return {
      author: null,
      hasReported: false,
      className: '',
      style: {},
    };
  },

  getInitialState() {
    return {
      reporting: FeatureStore.isActive('reporting'),
    };
  },

  componentWillMount() {
    FeatureStore.addChangeListener(this.onChange);
  },

  componentWillUnmount() {
    FeatureStore.removeChangeListener(this.onChange);
  },

  onChange() {
    this.setState({
      reporting: FeatureStore.isActive('reporting'),
    });
  },

  canReport() {
    return this.state.reporting && !this.isTheUserTheAuthor();
  },

  isTheUserTheAuthor() {
    if (this.props.author === null || !LoginStore.isLoggedIn()) {
      return false;
    }
    return LoginStore.user.uniqueId === this.props.author.uniqueId;
  },

  render() {
    if (this.canReport()) {
      const reported = this.props.hasReported;
      const classes = {
        'btn-dark-gray': true,
        'btn--outline': true,
      };

      return (
        <LoginOverlay>
          <Button
            href={this.props.url}
            style={this.props.style}
            active={reported}
            disabled={reported}
            className={classNames(classes, this.props.className)}
          >
            <i className="cap cap-flag-1"></i>
            {reported ? this.getIntlMessage('global.report.reported') : this.getIntlMessage('global.report.submit')}
          </Button>
        </LoginOverlay>
      );
    }
    return null;
  },

});

export default ReportButton;
