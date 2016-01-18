import LoginStore from '../../stores/LoginStore';

const OverlayTrigger = ReactBootstrap.OverlayTrigger;
const Popover = ReactBootstrap.Popover;
const Button = ReactBootstrap.Button;

const LoginOverlay = React.createClass({
  propTypes: {
    children: React.PropTypes.element.isRequired,
    isRegistrationEnabled: React.PropTypes.bool,
  },
  mixins: [ReactIntl.IntlMixin],

  getDefaultProps() {
    return {
      isRegistrationEnabled: true,
    };
  },

  // We add Popover if user is not connected
  render() {
    if (!this.props.children) {
      return null;
    }
    if (LoginStore.isLoggedIn()) {
      return this.props.children;
    }

    return (
      <OverlayTrigger rootClose trigger="click" placement="top" overlay={
          <Popover id="login-popover" title={this.getIntlMessage('vote.popover.title')}>
            <p>
              { this.getIntlMessage('vote.popover.body') }
            </p>
            {this.props.isRegistrationEnabled
              ? <p>
                  <Button href="/register" className="center-block">
                    { this.getIntlMessage('vote.popover.signin') }
                  </Button>
                </p>
              : null
            }
            <p>
              <Button href="/login" bsStyle="success" className="center-block">
                { this.getIntlMessage('vote.popover.login') }
              </Button>
            </p>
          </Popover>}
      >
        { this.props.children }
      </OverlayTrigger>
    );
  },

});

export default LoginOverlay;
