import React from 'react';
import { IntlMixin } from 'react-intl';
import { Col, Button } from 'react-bootstrap';
import classNames from 'classnames';
import IdeaVoteBox from '../Vote/IdeaVoteBox';

const IdeaSidebar = React.createClass({
  propTypes: {
    idea: React.PropTypes.object.isRequired,
    expanded: React.PropTypes.bool.isRequired,
    onToggleExpand: React.PropTypes.func.isRequired,
  },
  mixins: [IntlMixin],

  render() {
    const { idea } = this.props;
    if (!idea.canContribute) {
      return null;
    }
    const wrapperClassName = classNames({
      'sidebar-hideable': true,
      'sidebar-hidden-small': !this.props.expanded,
    });

    return (
      <Col xs={12} sm={3} className="sidebar" id="sidebar">
        <div className={wrapperClassName}>
          <IdeaVoteBox
            idea={idea}
            className="block block--bordered box"
            formWrapperClassName="sidebar__form"
          />
        </div>
        <Button
          block
          className="sidebar-toggle sidebar-hideable sidebar-hidden-large btn--no-radius"
          bsStyle={idea.userHasVote || this.props.expanded ? 'danger' : 'success'}
          bsSize="large"
          onClick={this.props.onToggleExpand}
        >
          {
            this.getIntlMessage(
              this.props.expanded
                ? 'idea.vote.hide'
                : idea.userHasVote
                  ? 'idea.vote.delete'
                  : 'idea.vote.add'
            )
          }
        </Button>
      </Col>
    );
  },

});

export default IdeaSidebar;
