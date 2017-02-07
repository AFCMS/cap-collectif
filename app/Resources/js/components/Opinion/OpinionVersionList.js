import React from 'react';
import { IntlMixin } from 'react-intl';
import OpinionVersion from './OpinionVersion';

const OpinionVersionList = React.createClass({
  propTypes: {
    versions: React.PropTypes.array.isRequired,
    rankingThreshold: React.PropTypes.number,
  },
  mixins: [IntlMixin],

  render() {
    const {
      rankingThreshold,
      versions,
    } = this.props;
    if (versions.length === 0) {
      return (
        <p className="text-center">
          <i className="cap-32 cap-baloon-1"></i>
          <br />
          { this.getIntlMessage('opinion.no_new_version') }
        </p>
      );
    }

    return (
      <ul className="media-list" style={{ marginTop: '20px' }}>
        {
          versions.map((version) => {
            return <OpinionVersion key={version.id} version={version} rankingThreshold={rankingThreshold} />;
          })
        }
      </ul>
    );
  },

});

export default OpinionVersionList;
