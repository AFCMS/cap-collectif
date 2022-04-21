import React from 'react';
import { IntlMixin } from 'react-intl';
import classNames from 'classnames';
import UserAvatar from '../../User/UserAvatar';
import IdeaPageHeaderInfos from './IdeaPageHeaderInfos';

const IdeaPageHeader = React.createClass({
  propTypes: {
    idea: React.PropTypes.object.isRequired,
    className: React.PropTypes.string,
  },
  mixins: [IntlMixin],

  getDefaultProps() {
    return {
      className: '',
    };
  },

  render() {
    const { idea } = this.props;

    const classes = {
      'idea__header': true,
    };
    classes[this.props.className] = true;

    return (
      <div className={classNames(classes)}>
        <h1 className="h1" id="idea-title">
          {idea.title}
        </h1>
        <div className="media">
          <UserAvatar className="pull-left" user={idea.author} />
          <div className="media-body">
            <IdeaPageHeaderInfos idea={idea} />
          </div>
        </div>
      </div>
    );
  },

});

export default IdeaPageHeader;
