// @flow
import React from 'react';
import OpinionTypeLabel from './OpinionTypeLabel';

export default class OpinionPreviewTitle extends React.Component {
  getType() {
    const opinion = this.props.opinion;
    if (opinion.parent) {
      return opinion.parent.type;
    }
    return opinion.type;
  }

  render() {
    const { link, opinion, showTypeLabel } = this.props;
    let url = '';
    if (link) {
      url = opinion._links ? opinion._links.show : opinion.url;
    }
    return (
      <h3 className="opinion__title">
        {showTypeLabel ? <OpinionTypeLabel type={this.getType()} /> : null}
        {showTypeLabel ? ' ' : null}
        {link ? <a href={url}>{opinion.title}</a> : opinion.title}
      </h3>
    );
  }
}

OpinionPreviewTitle.propTypes = {
  link: React.PropTypes.bool.isRequired,
  opinion: React.PropTypes.object.isRequired,
  showTypeLabel: React.PropTypes.bool.isRequired,
};
