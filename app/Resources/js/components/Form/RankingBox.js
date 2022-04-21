import React, { PropTypes } from 'react';
import { IntlMixin } from 'react-intl';
import classNames from 'classnames';
import RankingSpot from './RankingSpot';
import RankingItem from './RankingItem';

const RankingBox = React.createClass({
  propTypes: {
    items: PropTypes.array.isRequired,
    spotsNb: PropTypes.number.isRequired,
    listType: PropTypes.oneOf(['pickBox', 'choiceBox']),
    fieldId: PropTypes.number.isRequired,
    moveItem: PropTypes.func.isRequired,
    disabled: PropTypes.bool,
  },
  mixins: [IntlMixin],

  getDefaultProps() {
    return {
      disabled: false,
    };
  },

  render() {
    const { spotsNb, items, listType, moveItem, fieldId, disabled } = this.props;
    const className = classNames({
      'ranking__choice-box__choices': listType === 'choiceBox',
      'ranking__pick-box__choices': listType === 'pickBox',
    });
    return (
      <div className={className}>
        {
          [...Array(spotsNb)].map((x, i) => {
            const item = items[i];
            const arrowFunctions = (listType === 'pickBox')
            ? {
              right: it => moveItem('choiceBox', spotsNb, it),
            }
            : {
              up: i > 0 ? it => moveItem('choiceBox', i - 1, it) : null,
              down: i < (items.length - 1) ? it => moveItem('choiceBox', i + 1, it) : null,
              left: it => moveItem('pickBox', spotsNb, it),
            };
            return (
              <RankingSpot
                key={i}
                onDrop={it => moveItem(listType, i, it)}
              >
                {
                  item
                  ? <RankingItem
                    key={item.id}
                    item={item}
                    id={`reply-${fieldId}_choice-${item.id}`}
                    arrowFunctions={arrowFunctions}
                    disabled={disabled}
                    position={listType === 'choiceBox' ? i + 1 : null}
                  />
                  : null
                }
              </RankingSpot>
            );
          })
        }
      </div>
    );
  },

});

export default RankingBox;
