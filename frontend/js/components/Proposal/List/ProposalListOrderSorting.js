// @flow
import * as React from 'react';
import { injectIntl, type IntlShape } from 'react-intl';
import { connect } from 'react-redux';
import LocalStorage from '../../../services/LocalStorageService';
import { changeOrder } from '~/redux/modules/proposal';
import { PROPOSAL_AVAILABLE_ORDERS } from '~/constants/ProposalConstants';
import type { Dispatch, State } from '~/types';
import Select from '../../Ui/Form/Select/Select';
import SelectOption from '../../Ui/Form/Select/SelectOption';

type Props = {
  orderByPoints?: boolean,
  orderByVotes?: boolean,
  orderByComments?: boolean,
  orderByCost?: boolean,
  dispatch: Dispatch,
  order?: string,
  defaultSort?: ?string,
  stepId?: string,
  intl: IntlShape,
  canDisplayBallot: boolean,
};

type ComponentState = {
  displayedOrders: Array<string>,
};

export class ProposalListOrderSorting extends React.Component<Props, ComponentState> {
  static defaultProps = {
    orderByPoints: false,
    orderByVotes: false,
    orderByComments: false,
    orderByCost: false,
  };

  constructor(props: Props) {
    super(props);
    this.state = {
      displayedOrders: PROPOSAL_AVAILABLE_ORDERS.concat(props.orderByComments ? ['comments'] : [])
        .concat(props.orderByCost ? ['expensive', 'cheap'] : [])
        .concat(props.orderByVotes && props.canDisplayBallot ? ['votes', 'least-votes'] : [])
        .concat(props.orderByPoints && props.canDisplayBallot ? ['points', 'least-points'] : []),
    };
  }

  componentDidMount() {
    const { dispatch, defaultSort, stepId, order } = this.props;
    const savedSort = LocalStorage.get('proposal.orderByStep');
    if (!savedSort || !Object.prototype.hasOwnProperty.call(savedSort, stepId)) {
      if (defaultSort && !order) {
        dispatch(changeOrder(defaultSort));
      }
    } else {
      dispatch(changeOrder(savedSort[stepId]));
    }
  }

  render() {
    const { order, dispatch, intl } = this.props;
    const { displayedOrders } = this.state;

    return (
      <div>
        <Select
          label={
            order
              ? intl.formatMessage({ id: `global.filter_f_${order}` })
              : intl.formatMessage({ id: 'global.filter' })
          }
          id="proposal-filter-sorting-button">
          {displayedOrders.map(choice => (
            <SelectOption
              key={choice}
              onClick={e => dispatch(changeOrder(e.currentTarget.value))}
              isSelected={choice === order}
              value={choice}>
              {intl.formatMessage({ id: `global.filter_f_${choice}` })}
            </SelectOption>
          ))}
        </Select>
      </div>
    );
  }
}

const mapStateToProps = (state: State) => ({
  order: state.proposal.order,
  stepId: state.project.currentProjectStepById || null,
});

const container = injectIntl(ProposalListOrderSorting);

export default connect<any, any, _, _, _, _>(mapStateToProps)(container);
