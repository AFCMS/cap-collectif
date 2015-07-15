var UserLink = React.createClass({
  propTypes: {
    user: React.PropTypes.object
  },

  render() {
    if (this.props.user) {
      return (
        <a href={this.props.user._links.profile}>{this.props.user.display_name}</a>
      );
    }
    return <span></span>;
  },

});

export default UserLink;
