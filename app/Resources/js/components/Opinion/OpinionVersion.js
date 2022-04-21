import React from 'react';
import classNames from 'classnames';
import {Row, Col} from 'react-bootstrap';

import OpinionPreview from './OpinionPreview';
import VotePiechart from '../Utils/VotePiechart';

const OpinionVersion = React.createClass({
  propTypes: {
    version: React.PropTypes.object.isRequired,
    rankingThreshold: React.PropTypes.number,
  },

  render() {
    const version = this.props.version;
    const classes = classNames({
      'opinion': true,
      'block--bordered': true,
      'has-chart': true,
      'bg-vip': version.author.vip,
    });
    return (
      <li className={classes}>
        <Row>
          <Col xs={12} sm={8} md={9} lg={10}>
            <OpinionPreview opinion={version} rankingThreshold={this.props.rankingThreshold} />
          </Col>
          <Col sm={4} md={3} lg={2} className="hidden-xs">
            <VotePiechart top={10} height={90} width={145} ok={version.votes_ok} nok={version.votes_nok} mitige={version.votes_mitige} />
          </Col>
        </Row>
      </li>
    );
  },

});

export default OpinionVersion;
