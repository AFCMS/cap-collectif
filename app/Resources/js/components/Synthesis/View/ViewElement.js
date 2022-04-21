import React from 'react';
import { IntlMixin, FormattedMessage } from 'react-intl';
import { Tooltip, OverlayTrigger } from 'react-bootstrap';
import ElementTitle from './../Element/ElementTitle';
import UserAvatar from '../../User/UserAvatar';
import VotePiechart from '../../Utils/VotePiechart';
import ChildrenModal from './ChildrenModal';
import SynthesisDisplayRules from '../../../services/SynthesisDisplayRules';
import SynthesisPourcentageTooltip, { calculPourcentage } from './SynthesisPourcentageTooltip';

const ViewElement = React.createClass({
  propTypes: {
    element: React.PropTypes.object.isRequired,
    parent: React.PropTypes.object,
    settings: React.PropTypes.array.isRequired,
    onExpandElement: React.PropTypes.func.isRequired,
  },
  mixins: [IntlMixin],

  getDefaultProps() {
    return {
      parent: null,
    };
  },

  getInitialState() {
    return {
      showChildrenModal: false,
    };
  },

  getPercentageTooltip(contributions, score, percentage) {
    return (
      <Tooltip>
        <FormattedMessage
          message={this.getIntlMessage('synthesis.percentage.tooltip')}
          contributions={contributions}
          scoreSign={score < 0 ? '-' : '+'}
          score={Math.abs(score)}
          percentage={percentage}
        />
      </Tooltip>
    );
  },

  openOriginalContribution() {
    const { element } = this.props;
    window.open(element.linkedDataUrl);
    return false;
  },

  toggleChildrenModal(value) {
    const {
      element,
      onExpandElement,
    } = this.props;
    onExpandElement(element);
    this.setState({
      showChildrenModal: value,
    });
  },

  renderAuthor() {
    const {
      element,
      settings,
    } = this.props;
    if (SynthesisDisplayRules.getValueForRule(settings, 'display', 'author')) {
      return (
        <div className="synthesis__element__author">
          <UserAvatar className="pull-left" style={{ marginRight: '15px', marginTop: '15px' }} />
          <span>
            {element.authorName}
          </span>
        </div>
      );
    }
    return null;
  },

  renderPieChart() {
    const {
      element,
      settings,
    } = this.props;
    const votes = element.votes;
    if (SynthesisDisplayRules.getValueForRule(settings, 'display', 'piechart')) {
      return (
        <div className="synthesis__element__votes">
          <VotePiechart
            top={20}
            height="180px"
            width="100%"
            ok={votes[1] || 0}
            nok={votes[-1] || 0}
            mitige={votes[0] || 0}
          />
          <p style={{ textAlign: 'center' }}>
            <FormattedMessage
              message={this.getIntlMessage('synthesis.vote.total')}
              nb={(votes[-1] || 0) + (votes[0] || 0) + (votes[1] || 0)}
            />
          </p>
        </div>
      );
    }
    return null;
  },

  renderCounters() {
    const {
      element,
      settings,
    } = this.props;
    if (SynthesisDisplayRules.getValueForRule(settings, 'display', 'counters')) {
      return (
        <div className="synthesis__element__counters">
          <FormattedMessage
            message={this.getIntlMessage('synthesis.counter.contributions')}
            nb={element.publishedChildrenCount}
          />
          {element.linkedDataUrl
            ? <a
              style={{ marginLeft: '15px' }}
              href={element.linkedDataUrl}
              onClick={this.openOriginalContribution}
              >
              {this.getIntlMessage('synthesis.counter.link')}
            </a>
            : null
          }
        </div>
      );
    }
    return null;
  },

  renderSubtitle() {
    const {
      element,
      settings,
    } = this.props;
    if (SynthesisDisplayRules.getValueForRule(settings, 'display', 'subtitle') && element.subtitle) {
      return (
        <p className="small excerpt">
          {element.subtitle}
        </p>
      );
    }
    return null;
  },

  renderPercentage() {
    const {
      element,
      parent,
      settings,
    } = this.props;
    if (SynthesisDisplayRules.getValueForRule(settings, 'display', 'percentage') && parent) {
      let percentage = Math.round(
        (element.childrenElementsNb / parent.childrenElementsNb) * 1000,
      ) / 10;
      percentage = percentage > 0 ? percentage : 0;
      const tooltip = this.getPercentageTooltip(
        element.publishedChildrenCount,
        element.childrenScore,
        percentage,
      );
      return (
        <OverlayTrigger placement="top" overlay={tooltip}>
          <span className="small excerpt pull-right">
            {percentage}%
          </span>
        </OverlayTrigger>
      );
    }
    return null;
  },

  renderDescription() {
    const { element } = this.props;
    if (element.description) {
      return (
        <p className="synthesis__element__description">
          {element.description}
        </p>
      );
    }
  },

  renderTitle() {
    const {
      element,
      settings,
    } = this.props;
    const childrenModal = SynthesisDisplayRules.getValueForRule(settings, 'display', 'childrenInModal');
    return (
      <ElementTitle
        className="element__title"
        element={element}
        hasLink={false}
        style={SynthesisDisplayRules.buildStyle(settings)}
        onClick={childrenModal ? this.toggleChildrenModal.bind(null, true) : null}
      />
    );
  },

  renderAsProgressBar() {
    const {
      element,
      parent,
    } = this.props;
    if (parent) {
      return (
        <div className="synthesis__element">
          <OverlayTrigger placement="top" overlay={<SynthesisPourcentageTooltip element={element} parent={parent} />}>
            <div className="synthesis__element__bar">
              <span className="synthesis__element__bar__value" style={{ width: `${calculPourcentage(element, parent)}%` }} />
              {this.renderTitle()}
            </div>
          </OverlayTrigger>
        </div>
      );
    }
    return null;
  },

  render() {
    const {
      element,
      settings,
    } = this.props;
    return (
      <div className="synthesis__element" style={SynthesisDisplayRules.buildStyle(settings, 'containerStyle')}>
        {this.renderAuthor()}
        <div style={SynthesisDisplayRules.buildStyle(settings)}>
          {
            SynthesisDisplayRules.getValueForRule(settings, 'display', 'asProgressBar')
            ? this.renderAsProgressBar()
              : <p>
                {this.renderTitle()}
                {this.renderPercentage()}
              </p>
          }
          {this.renderSubtitle()}
        </div>
        {this.renderCounters()}
        {this.renderPieChart()}
        {this.renderDescription()}
        <ChildrenModal elements={element.children} show={this.state.showChildrenModal} toggle={this.toggleChildrenModal} />
      </div>
    );
  },

});

export default ViewElement;
