import React from 'react';
import Linkify from 'react-linkify';
import { IntlMixin } from 'react-intl';

const CommentBody = React.createClass({
  propTypes: {
    comment: React.PropTypes.object,
  },
  mixins: [IntlMixin],

  getInitialState() {
    return {
      expanded: false,
    };
  },

  textShouldBeTruncated() {
    const { comment } = this.props;
    return comment.body.length > 400;
  },

  generateText() {
    const { comment } = this.props;
    let text = '';

    if (!this.textShouldBeTruncated() || this.state.expanded) {
      text = comment.body;
    } else {
      text = comment.body.substr(0, 400);
      text = text.substr(0, Math.min(text.length, text.lastIndexOf(' ')));
      if (text.indexOf('.', text.length - 1) === -1) {
        text += '...';
      }
      text += ' ';
    }

    return text.replace(/\r?\n/g, '<br/>');
  },

  expand(expanded) {
    this.setState({
      expanded,
    });
  },

  renderReadMoreOrLess() {
    if (this.textShouldBeTruncated() && !this.state.expanded) {
      return <button className="btn-link" onClick={this.expand.bind(this, true)}>{this.getIntlMessage('global.read_more')}</button>;
    }
  },

  renderTrashedLabel() {
    const { comment } = this.props;
    if (comment.isTrashed) {
      return (
        <span className="label label-default">
          { this.getIntlMessage('comment.trashed.label') }
        </span>
      );
    }
    return null;
  },

  render() {
    return (
      <div className="opinion__text">
        { this.renderTrashedLabel() }
        <Linkify properties={{ className: 'external-link' }}>{this.generateText()}</Linkify>
        { this.renderReadMoreOrLess() }
      </div>
    );
  },

});

export default CommentBody;
