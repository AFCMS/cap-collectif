// @flow
import * as React from 'react';
import { createFragmentContainer, graphql } from 'react-relay';
import { connect } from 'react-redux';
import { FormattedMessage, useIntl } from 'react-intl';
import type { GlobalState } from '~/types';
import type { User } from '~/redux/modules/user';
import type { AnalysisDashboardHeader_project } from '~relay/AnalysisDashboardHeader_project.graphql';
import { usePickableList } from '~ui/List/PickableList';
import { useAnalysisProposalsContext } from '~/components/Analysis/AnalysisProjectPage/AnalysisProjectPage.context';
import { getAllFormattedChoicesForProject } from '~/components/Analysis/AnalysisProjectPage/AnalysisProjectPage.utils';
import Collapsable from '~ui/Collapsable';
import SearchableDropdownSelect from '~ui/SearchableDropdownSelect';
import DropdownSelect from '~ui/DropdownSelect';
import type {
  ProposalsCategoryValues,
  ProposalsDistrictValues,
  SortValues,
} from '~/components/Analysis/AnalysisProjectPage/AnalysisProjectPage.reducer';
import { ORDER_BY } from '~/components/Analysis/AnalysisProjectPage/AnalysisProjectPage.reducer';
import { TYPE_FILTER } from '~/constants/AnalyseConstants';
import FilterTag from '~ui/Analysis/FilterTag';
import { AnalysisFilterContainer } from '~ui/Analysis/common.style';

type Props = {|
  project: AnalysisDashboardHeader_project,
  user: User,
|};

const getFiltersShown = (
  proposalIdsSelected: string[] = [],
  userId: string,
  proposals: ?$ReadOnlyArray<Object>,
): string[] => {
  const { ANALYST, SUPERVISOR } = TYPE_FILTER;

  if (proposalIdsSelected.length === 0) {
    // all filters
    return ((Object.values(TYPE_FILTER): any): string[]);
  }

  const proposalsSelected = proposals
    ? proposals.filter(({ id }) => proposalIdsSelected.includes(id))
    : [];

  const userRoles = proposalsSelected.reduce(
    (acc, proposal) => {
      const isUserAnalyst =
        proposal.analysts?.length > 0
          ? proposal.analysts.some(analyst => analyst.id === userId)
          : false;
      const isUserSupervisor = proposal.supervisor ? proposal.supervisor.id === userId : false;
      const isUserDecisionMaker = proposal.decisionMaker
        ? proposal.decisionMaker.id === userId
        : false;

      if (isUserAnalyst && acc.isAnalyst !== true) {
        return {
          ...acc,
          isAnalyst: true,
        };
      }

      if (isUserSupervisor && acc.isSupervisor !== true) {
        return {
          ...acc,
          isSupervisor: true,
        };
      }

      if (isUserDecisionMaker && acc.isDecisionMaker !== true) {
        return {
          ...acc,
          isDecisionMaker: true,
        };
      }

      return acc;
    },
    { isAnalyst: false, isSupervisor: false, isDecisionMaker: false },
  );

  // add only filter allowed
  return [
    ...(userRoles.isAnalyst || userRoles.isSupervisor ? [ANALYST] : []),
    ...(userRoles.isDecisionMaker ? [ANALYST, SUPERVISOR] : []),
  ];
};

const AnalysisDashboardHeader = ({ project, user }: $Diff<Props, { relay: * }>) => {
  const intl = useIntl();
  const { proposals: dataProposals } = project;
  const proposals = dataProposals?.edges?.filter(Boolean).map(edge => edge.node);
  const { selectedRows, rowsCount } = usePickableList();
  const { parameters, dispatch } = useAnalysisProposalsContext();
  const { categories, districts } = React.useMemo(() => getAllFormattedChoicesForProject(project), [
    project,
  ]);
  const { DISTRICT, CATEGORY, ANALYST, SUPERVISOR, DECISION_MAKER, SORT } = TYPE_FILTER;
  const filtersShown: string[] = getFiltersShown(selectedRows, user.id, proposals);
  const fakePromise = () => new Promise(resolve => resolve([1, 2, 3]));

  return (
    <>
      <FormattedMessage
        tagName="p"
        id="count-proposal"
        values={{ num: selectedRows.length > 0 ? selectedRows.length : rowsCount }}
      />

      {filtersShown.includes(DISTRICT) && (
        <AnalysisFilterContainer>
          <Collapsable>
            <Collapsable.Button>
              <FormattedMessage id="admin.fields.proposal.map.zone" />
            </Collapsable.Button>
            <Collapsable.Element
              ariaLabel={intl.formatMessage({ id: 'admin.fields.proposal.map.zone' })}>
              <DropdownSelect
                value={parameters.filters.district}
                onChange={newValue => {
                  dispatch({
                    type: 'CHANGE_DISTRICT_FILTER',
                    payload: ((newValue: any): ProposalsDistrictValues),
                  });
                }}
                title={intl.formatMessage({ id: 'admin.fields.proposal.map.zone' })}>
                <DropdownSelect.Choice value="ALL">
                  {intl.formatMessage({ id: 'global.select_districts' })}
                </DropdownSelect.Choice>
                {districts.map(district => (
                  <DropdownSelect.Choice key={district.id} value={district.id}>
                    {district.name}
                  </DropdownSelect.Choice>
                ))}
              </DropdownSelect>
            </Collapsable.Element>
          </Collapsable>
          <FilterTag
            onClose={() => {
              dispatch({ type: 'CLEAR_DISTRICT_FILTER' });
            }}
            icon={<i className="cap cap-marker-1" />}
            show={parameters.filters.district !== 'ALL'}>
            {districts.find(d => d.id === parameters.filters.district)?.name || null}
          </FilterTag>
        </AnalysisFilterContainer>
      )}

      {filtersShown.includes(CATEGORY) && (
        <AnalysisFilterContainer>
          <Collapsable>
            <Collapsable.Button>
              <FormattedMessage id="admin.fields.proposal.category" />
            </Collapsable.Button>
            <Collapsable.Element
              ariaLabel={intl.formatMessage({ id: 'admin.fields.proposal.category' })}>
              <DropdownSelect
                value={parameters.filters.category}
                onChange={newValue => {
                  dispatch({
                    type: 'CHANGE_CATEGORY_FILTER',
                    payload: ((newValue: any): ProposalsCategoryValues),
                  });
                }}
                title={intl.formatMessage({ id: 'admin.fields.proposal.category' })}>
                <DropdownSelect.Choice value="ALL">
                  {intl.formatMessage({ id: 'global.select_categories' })}
                </DropdownSelect.Choice>
                {categories.map(cat => (
                  <DropdownSelect.Choice key={cat.id} value={cat.id}>
                    {cat.name}
                  </DropdownSelect.Choice>
                ))}
              </DropdownSelect>
            </Collapsable.Element>
          </Collapsable>
          <FilterTag
            onClose={() => {
              dispatch({ type: 'CLEAR_CATEGORY_FILTER' });
            }}
            icon={<i className="cap cap-tag-1" />}
            show={parameters.filters.category !== 'ALL'}>
            {categories.find(d => d.id === parameters.filters.category)?.name || null}
          </FilterTag>
        </AnalysisFilterContainer>
      )}

      {filtersShown.includes(ANALYST) && (
        <AnalysisFilterContainer>
          <Collapsable>
            <Collapsable.Button>
              <FormattedMessage id="panel.analysis.subtitle" />
            </Collapsable.Button>
            <Collapsable.Element
              ariaLabel={intl.formatMessage({ id: 'filter.by.assigned.analyst' })}>
              <SearchableDropdownSelect
                searchPlaceholder={intl.formatMessage({ id: 'search.users' })}
                loadOptions={fakePromise}
                title={intl.formatMessage({ id: 'filter.by.assigned.analyst' })}
                noResultsMessage={intl.formatMessage({ id: 'no_result' })}>
                {(result = []) =>
                  result.map((number, idx) => (
                    <DropdownSelect.Choice value="NONE" key={idx}>
                      {intl.formatMessage({ id: 'assigned.to.nobody' })}
                    </DropdownSelect.Choice>
                  ))
                }
              </SearchableDropdownSelect>
            </Collapsable.Element>
          </Collapsable>
        </AnalysisFilterContainer>
      )}

      {filtersShown.includes(SUPERVISOR) && (
        <AnalysisFilterContainer>
          <Collapsable>
            <Collapsable.Button>
              <FormattedMessage id="global.review" />
            </Collapsable.Button>
            <Collapsable.Element
              ariaLabel={intl.formatMessage({ id: 'filter.by.assigned.supervisor' })}>
              <SearchableDropdownSelect
                searchPlaceholder={intl.formatMessage({ id: 'search.users' })}
                loadOptions={fakePromise}
                title={intl.formatMessage({ id: 'filter.by.assigned.supervisor' })}
                noResultsMessage={intl.formatMessage({ id: 'no_result' })}>
                {(result = []) =>
                  result.map((number, idx) => (
                    <DropdownSelect.Choice value="NONE" key={idx}>
                      {intl.formatMessage({ id: 'assigned.to.nobody' })}
                    </DropdownSelect.Choice>
                  ))
                }
              </SearchableDropdownSelect>
            </Collapsable.Element>
          </Collapsable>
        </AnalysisFilterContainer>
      )}

      {filtersShown.includes(DECISION_MAKER) && (
        <AnalysisFilterContainer>
          <Collapsable align="right">
            <Collapsable.Button>
              <FormattedMessage id="global.decision" />
            </Collapsable.Button>
            <Collapsable.Element
              ariaLabel={intl.formatMessage({ id: 'filter.by.assigned.decision-maker' })}>
              <SearchableDropdownSelect
                searchPlaceholder={intl.formatMessage({ id: 'search.users' })}
                loadOptions={fakePromise}
                title={intl.formatMessage({ id: 'filter.by.assigned.decision-maker' })}
                noResultsMessage={intl.formatMessage({ id: 'no_result' })}>
                {(result = []) =>
                  result.map((number, idx) => (
                    <DropdownSelect.Choice value="NONE" key={idx}>
                      {intl.formatMessage({ id: 'assigned.to.nobody' })}
                    </DropdownSelect.Choice>
                  ))
                }
              </SearchableDropdownSelect>
            </Collapsable.Element>
          </Collapsable>
        </AnalysisFilterContainer>
      )}

      {filtersShown.includes(SORT) && (
        <AnalysisFilterContainer>
          <Collapsable align="right">
            <Collapsable.Button>
              <FormattedMessage id="argument.sort.label" />
            </Collapsable.Button>
            <Collapsable.Element ariaLabel={intl.formatMessage({ id: 'sort-by' })}>
              <DropdownSelect
                value={parameters.sort}
                onChange={newValue => {
                  dispatch({ type: 'CHANGE_SORT', payload: ((newValue: any): SortValues) });
                }}
                title={intl.formatMessage({ id: 'sort-by' })}>
                <DropdownSelect.Choice value={ORDER_BY.NEWEST}>
                  {intl.formatMessage({ id: 'global.filter_f_last' })}
                </DropdownSelect.Choice>
                <DropdownSelect.Choice value={ORDER_BY.OLDEST}>
                  {intl.formatMessage({ id: 'global.filter_f_old' })}
                </DropdownSelect.Choice>
              </DropdownSelect>
            </Collapsable.Element>
          </Collapsable>
        </AnalysisFilterContainer>
      )}
    </>
  );
};

const mapStateToProps = (state: GlobalState) => ({
  user: state.user.user,
});

const AnalysisDashboardHeaderConnected = connect(mapStateToProps)(AnalysisDashboardHeader);

export default createFragmentContainer(AnalysisDashboardHeaderConnected, {
  project: graphql`
    fragment AnalysisDashboardHeader_project on Project {
      steps {
        __typename
        ... on ProposalStep {
          form {
            districts {
              id
              name
            }
            categories {
              id
              name
            }
          }
        }
      }
      proposals {
        edges {
          node {
            id
            analysts {
              id
            }
            supervisor {
              id
            }
            decisionMaker {
              id
            }
            ...AnalysisProposalListRole_proposal
          }
        }
      }
    }
  `,
});
