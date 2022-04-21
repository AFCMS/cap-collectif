import React, { PropTypes } from 'react';
import { IntlMixin } from 'react-intl';
import ProposalDetailEstimation from '../Detail/ProposalDetailEstimation';
import ProposalDetailLikers from '../Detail/ProposalDetailLikers';

const ProposalPageMetadata = React.createClass({
  displayName: 'ProposalPageMetadata',
  propTypes: {
    proposal: PropTypes.object.isRequired,
    showDistricts: PropTypes.bool.isRequired,
    showCategories: PropTypes.bool.isRequired,
    showNullEstimation: PropTypes.bool.isRequired,
    showThemes: PropTypes.bool.isRequired,
  },
  mixins: [IntlMixin],

  render() {
    const { proposal, showCategories, showDistricts, showNullEstimation, showThemes } = this.props;
    return (
      <div>
        { ((showCategories && proposal.category) || (showDistricts && proposal.district))
        && <div className="proposal__page__metadata">
          <div className="proposal__infos">
            {
              showThemes && proposal.theme &&
              <div className="proposal__info proposal__info--district ellipsis">
                <i className="cap cap-tag-1-1 icon--blue"></i>{proposal.theme.title}
              </div>
            }
            {
              showCategories && proposal.category
              && <div className="proposal__info proposal__info--category ellipsis">
                <i className="cap cap-tag-1-1 icon--blue"></i>{proposal.category.name}
              </div>
            }
            {
              showDistricts && proposal.district
              && <div className="proposal__info proposal__info--district ellipsis">
                <i className="cap cap-marker-1-1 icon--blue"></i>{proposal.district.name}
              </div>
            }
            <ProposalDetailEstimation
              proposal={proposal}
              showNullEstimation={showNullEstimation}
            />
            <ProposalDetailLikers
              proposal={proposal}
              componentClass="div"
            />
          </div>
        </div>
        }
      </div>
    );
  },

});

export default ProposalPageMetadata;
