// @flow
import React from 'react';
import { Tab, Nav, NavItem } from 'react-bootstrap';
import { IntlMixin, FormattedMessage } from 'react-intl';
import ArgumentStore from '../../stores/ArgumentStore';
import {
  COMMENT_SYSTEM_SIMPLE,
  COMMENT_SYSTEM_BOTH,
} from '../../constants/ArgumentConstants';
import ArgumentsBox from '../Argument/ArgumentsBox';
import OpinionVersionsBox from './OpinionVersionsBox';
import OpinionSourceBox from './Source/OpinionSourceBox';
import VoteLinechart from '../Utils/VoteLinechart';
import OpinionLinksBox from './Links/OpinionLinksBox';
import OpinionSourceStore from '../../stores/OpinionSourceStore';
import { scrollToAnchor } from '../../services/ScrollToAnchor';

const OpinionTabs = React.createClass({
  propTypes: {
    opinion: React.PropTypes.object.isRequired,
  },
  mixins: [IntlMixin],

  getInitialState() {
    const { opinion } = this.props;
    return {
      sourcesCount: opinion.sourcesCount,
      argumentsCount: opinion.argumentsCount,
    };
  },

  componentWillMount() {
    OpinionSourceStore.addChangeListener(this.onSourceChange);
    ArgumentStore.addChangeListener(this.onArgumentChange);
  },

  componentDidMount() {
    setTimeout(scrollToAnchor, 20); // We use setTimeout to interact with DOM in componentDidMount (see React documentation)
  },

  componentWillUnmount() {
    ArgumentStore.removeChangeListener(this.onArgumentChange);
    OpinionSourceStore.removeChangeListener(this.onChange);
  },

  onSourceChange() {
    this.setState({
      sourcesCount: OpinionSourceStore.count,
    });
  },

  onArgumentChange() {
    this.setState({
      argumentsCount: ArgumentStore.count,
    });
  },

  getHashKey(hash: string) {
    let key = null;
    if (hash.indexOf('arg') !== -1) {
      key = 'arguments';
    }
    if (hash.indexOf('version') !== -1) {
      key = 'versions';
    }
    if (hash.indexOf('source') !== -1) {
      key = 'sources';
    }
    if (hash.indexOf('votesevolution') !== -1) {
      key = 'votesevolution';
    }
    return key;
  },

  getCommentSystem() {
    const opinion = this.props.opinion;
    return opinion.parent
      ? opinion.parent.type.commentSystem
      : opinion.type.commentSystem;
  },

  getArgumentsTrad() {
    return this.getCommentSystem() === COMMENT_SYSTEM_BOTH
      ? this.getIntlMessage('global.arguments')
      : this.getIntlMessage('global.simple_arguments');
  },

  getDefaultKey() {
    const hash = window.location.hash;
    if (hash) {
      return this.getHashKey(hash);
    }

    return this.isVersionable()
      ? 'versions'
      : this.isCommentable()
          ? 'arguments'
          : this.isSourceable() ? 'sources' : null;
  },

  getType() {
    const { opinion } = this.props;
    return opinion.parent ? opinion.parent.type : opinion.type;
  },

  isLinkable() {
    const type = this.getType();
    return this.isVersion() ? false : type.linkable;
  },

  isSourceable() {
    const type = this.getType();
    return type !== 'undefined' ? type.sourceable : false;
  },

  isCommentable() {
    return (
      this.getCommentSystem() === COMMENT_SYSTEM_SIMPLE ||
      this.getCommentSystem() === COMMENT_SYSTEM_BOTH
    );
  },

  isVersionable() {
    const opinion = this.props.opinion;
    return (
      !this.isVersion() &&
      opinion.type !== 'undefined' &&
      opinion.type.versionable
    );
  },

  isVersion() {
    const { opinion } = this.props;
    return !!opinion.parent;
  },

  hasStatistics() {
    const { opinion } = this.props;
    return !!opinion.history;
  },

  isContribuable() {
    const { opinion } = this.props;
    return opinion.isContribuable;
  },

  renderVersionsContent() {
    const { opinion } = this.props;
    return (
      <OpinionVersionsBox
        isContribuable={this.isContribuable()}
        opinionId={opinion.id}
        opinionBody={opinion.body}
      />
    );
  },

  render() {
    const { opinion } = this.props;

    if (
      this.isSourceable() +
        this.isCommentable() +
        this.isVersionable() +
        this.hasStatistics() +
        this.isLinkable() >
      1
    ) {
      // at least two tabs

      const marginTop = { 'margin-top': '20px' };

      return (
        <Tab.Container
          id="opinion-page-tabs"
          defaultActiveKey={this.getDefaultKey()}
          animation={false}>
          <div>
            <Nav bsStyle="tabs">
              {this.isVersionable() &&
                <NavItem eventKey="versions" className="opinion-tabs">
                  <FormattedMessage
                    message={this.getIntlMessage('global.versions')}
                    num={opinion.versionsCount}
                  />
                </NavItem>}
              {this.isCommentable() &&
                <NavItem className="opinion-tabs" eventKey="arguments">
                  <FormattedMessage
                    message={this.getArgumentsTrad()}
                    num={this.state.argumentsCount}
                  />
                </NavItem>}
              {this.isSourceable() &&
                <NavItem className="opinion-tabs" eventKey="sources">
                  <FormattedMessage
                    message={this.getIntlMessage('global.sources')}
                    num={this.state.sourcesCount}
                  />
                </NavItem>}
              {this.hasStatistics() &&
                <NavItem className="opinion-tabs" eventKey="votesevolution">
                  <FormattedMessage
                    message={this.getIntlMessage('vote.evolution.tab')}
                  />
                </NavItem>}
              {this.isLinkable() &&
                <NavItem className="opinion-tabs" eventKey="links">
                  <FormattedMessage
                    message={this.getIntlMessage('global.links')}
                    num={opinion.connections_count}
                  />
                </NavItem>}
            </Nav>
            <Tab.Content animation={false}>
              {this.isVersionable() &&
                <Tab.Pane eventKey="versions" style={marginTop}>
                  {this.renderVersionsContent()}
                </Tab.Pane>}
              {this.isCommentable() &&
                <Tab.Pane eventKey="arguments" style={marginTop}>
                  <ArgumentsBox {...this.props} />
                </Tab.Pane>}
              {this.isSourceable() &&
                <Tab.Pane eventKey="sources" style={marginTop}>
                  <OpinionSourceBox {...this.props} />
                </Tab.Pane>}
              {this.hasStatistics() &&
                <Tab.Pane eventKey="votesevolution" style={marginTop}>
                  <VoteLinechart
                    top={20}
                    height={300}
                    width={847}
                    history={opinion.history.votes}
                  />
                </Tab.Pane>}
              {this.isLinkable() &&
                <Tab.Pane eventKey="links" style={{ 'margin-top': '20px' }}>
                  <OpinionLinksBox {...this.props} />
                </Tab.Pane>}
            </Tab.Content>
          </div>
        </Tab.Container>
      );
    }

    if (this.isSourceable()) {
      return <OpinionSourceBox {...this.props} />;
    }
    if (this.isVersionable()) {
      return this.renderVersionsContent();
    }
    if (this.isCommentable()) {
      return <ArgumentsBox {...this.props} />;
    }
    if (this.hasStatistics()) {
      return (
        <VoteLinechart
          top={20}
          height={300}
          width={847}
          history={opinion.history.votes}
        />
      );
    }

    if (this.isLinkable()) {
      return <OpinionLinksBox {...this.props} />;
    }

    return null;
  },
});

export default OpinionTabs;
