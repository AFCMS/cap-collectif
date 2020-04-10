// @flow
import uniqBy from 'lodash/uniqBy';
import lodashFilter from 'lodash/filter';
import type { ProjectAdminProposals_project } from '~relay/ProjectAdminProposals_project.graphql';
import type { Uuid } from '~/types';
import type { StepsChangedProposal } from './ProjectAdminPage.reducer';
import { ICON_NAME } from '~ui/Icons/Icon';
import type { UserSearchDropdownChoice_user } from '~relay/UserSearchDropdownChoice_user.graphql';
import type { ProjectAdminAnalysis_project } from '~relay/ProjectAdminAnalysis_project.graphql';
import {
  getEntriesIds,
  type Analyst,
  type Supervisor,
  type DecisionMaker,
  type RowType,
} from '~/components/Analysis/AnalysisProjectPage/AnalysisProjectPage.utils';

type DistrictFilter = {|
  +id: Uuid,
  +name: string,
|};

type StepStatusFilter = {|
  +id: Uuid,
  +color: ?string,
  +name: string,
|};

export type StepFilter = {|
  +id: Uuid,
  +title: string,
  +url: string,
|};

type CategoryFilter = {|
  +id: Uuid,
  +name: string,
|};

export type ProposalsSelected = {|
  +id: Uuid,
  +steps: Uuid[],
  +status: ?Uuid,
|};

export const FILLING: {
  EMPTY: 'EMPTY',
  AT_LEAST_ONE: 'AT_LEAST_ONE',
  FULL: 'FULL',
} = {
  EMPTY: 'EMPTY',
  AT_LEAST_ONE: 'AT_LEAST_ONE',
  FULL: 'FULL',
};

export type StepFilling = {|
  ...StepFilter,
  +icon: ?$Values<typeof ICON_NAME>,
  +filling: $Values<typeof FILLING>,
|};

type StatusFilling = {|
  ...$Diff<StepStatusFilter, { color: * }>,
  icon: ?$Values<typeof ICON_NAME>,
|};

const SHOWING_STEP_TYPENAME = ['CollectStep', 'SelectionStep'];

export const getFormattedCategoriesChoicesForProject = (
  project: ProjectAdminProposals_project,
): $ReadOnlyArray<DistrictFilter> => {
  const flattened = ((project.steps
    .filter(Boolean)
    .filter(step => SHOWING_STEP_TYPENAME.includes(step.__typename))
    .reduce(
      (acc, step) => [
        ...acc,
        // Flow does not know the type of step in runtime unless we put a bunch of
        // if conditions. For code readability, it is preferable to put here the $FlowFixMe
        // here, but in the end I force cast it to `$ReadOnlyArray<CategoryFilter>`
        // $FlowFixMe
        step.form.categories.map(cat => ({
          id: cat.id,
          name: cat.name,
        })),
      ],
      [],
    )
    .flat(): any): $ReadOnlyArray<CategoryFilter>);
  return uniqBy(flattened, 'id');
};

export const getFormattedDistrictsChoicesForProject = (
  project: ProjectAdminProposals_project,
): $ReadOnlyArray<DistrictFilter> => {
  const flattened = ((project.steps
    .filter(Boolean)
    .filter(step => SHOWING_STEP_TYPENAME.includes(step.__typename))
    .reduce(
      (acc, step) => [
        ...acc,
        // $FlowFixMe
        step.form.districts.map(district => ({
          id: district.id,
          name: district.name,
        })),
      ],
      [],
    )
    .flat(): any): $ReadOnlyArray<CategoryFilter>);
  return uniqBy(flattened, 'id');
};

export const getFormattedStepsChoicesForProject = (
  project: ProjectAdminProposals_project,
): $ReadOnlyArray<StepFilter> => {
  return ((project.steps
    .filter(Boolean)
    .filter(step => SHOWING_STEP_TYPENAME.includes(step.__typename))
    .map(step => ({
      id: step.id,
      title: step.title,
      url: step.url,
    })): any): $ReadOnlyArray<StepFilter>);
};

export const getFormattedCollectStepsForProject = (
  project: ProjectAdminProposals_project,
): $ReadOnlyArray<Uuid> => {
  return ((project.steps
    .filter(Boolean)
    .filter(step => step.__typename === 'CollectStep')
    .map(step => step.id): any): $ReadOnlyArray<Uuid>);
};

export const getFormattedProposalsSelected = (
  project: ProjectAdminProposals_project,
  selectedProposals: Uuid[],
): $ReadOnlyArray<ProposalsSelected> => {
  return ((project.proposals?.edges
    ?.filter(Boolean)
    .map(edge => edge.node)
    .filter(proposal => proposal && selectedProposals.includes(proposal.id))
    .map(proposal => {
      const collectStep: ?string = proposal?.form?.step?.id;
      const selectionSteps: Array<?string> = proposal?.selections?.map(({ step }) => step.id);
      const steps: string[] = [collectStep, ...selectionSteps].filter(Boolean);
      const status: ?string = proposal?.status?.id;

      return { id: proposal.id, steps, status };
    }): any): $ReadOnlyArray<ProposalsSelected>);
};

export const getFormattedStepsFilling = (
  proposalsSteps: $ReadOnlyArray<ProposalsSelected>,
  steps: $ReadOnlyArray<StepFilter>,
  stepsChangedProposal: StepsChangedProposal,
  selectedRows: string[],
): $ReadOnlyArray<StepFilling> => {
  const stepsProposal = proposalsSteps.map(proposalStep => proposalStep.steps).flat();

  // Need this in the case where a step is already in a proposal that is part of our selected proposals,
  // then we clear the count for this step
  const stepsAddedFilter = stepsChangedProposal.stepsAdded.map(stepAdded => ({
    id: stepAdded.id,
    count:
      stepAdded.count - stepsProposal.filter(stepProposal => stepProposal === stepAdded.id).length,
  }));

  const allStepsIds = [
    ...stepsProposal,
    ...stepsAddedFilter.map(step => Array(step.count).fill(step.id)).flat(),
  ];

  const allStepsIdsFilter = allStepsIds.filter(
    stepId => !stepsChangedProposal.stepsRemoved.some(step => step.id === stepId),
  );

  return ((steps.map(step => {
    const countStep = allStepsIdsFilter.filter(stepId => stepId === step.id).length;
    let filling = FILLING.EMPTY;
    let icon = null;

    if (countStep === selectedRows.length) {
      filling = FILLING.FULL;
      icon = ICON_NAME.check;
    } else if (countStep > 0) {
      filling = FILLING.AT_LEAST_ONE;
      icon = ICON_NAME.plus;
    }

    return {
      id: step.id,
      title: step.title,
      icon,
      filling,
    };
  }): any): $ReadOnlyArray<StepFilling>);
};

export const getFormattedStatusFilling = (
  proposalsSteps: $ReadOnlyArray<ProposalsSelected>,
  statuses: $ReadOnlyArray<StepStatusFilter>,
  selectedRows: string[],
): $ReadOnlyArray<StatusFilling> => {
  const allStatusesIds = proposalsSteps.map(proposalStep => proposalStep.status);

  return ((statuses.map(status => {
    const countStatus = allStatusesIds.filter(statusId => statusId === status.id).length;
    let icon = null;

    if (countStatus === selectedRows.length) icon = ICON_NAME.check;
    else if (countStatus > 0) icon = ICON_NAME.plus;

    return {
      id: status.id,
      name: status.name,
      icon,
    };
  }): any): $ReadOnlyArray<StatusFilling>);
};

export const getFormattedStatusesChoicesForProjectStep = (
  project: ProjectAdminProposals_project,
  stepId: ?Uuid,
): $ReadOnlyArray<StepStatusFilter> => {
  return ((project.steps
    .filter(Boolean)
    .filter(step => SHOWING_STEP_TYPENAME.includes(step.__typename))
    .filter(step => stepId && step.id === stepId)
    .filter(step => step.statuses && step.statuses.length > 0)
    .reduce(
      (acc, step) => [
        ...acc,
        // $FlowFixMe,
        step.statuses?.map(status => ({
          id: status.id,
          name: status.name,
          color: status.color,
        })),
      ],
      [],
    )
    .flat(): any): $ReadOnlyArray<StepStatusFilter>);
};

type AllFormattedChoicesReturn = {|
  +categories: $ReadOnlyArray<CategoryFilter>,
  +districts: $ReadOnlyArray<DistrictFilter>,
  +steps: $ReadOnlyArray<StepFilter>,
  +stepStatuses: $ReadOnlyArray<StepStatusFilter>,
  +proposalsSelected: $ReadOnlyArray<ProposalsSelected>,
  +stepsFilling: $ReadOnlyArray<StepFilling>,
  +statusesFilling: $ReadOnlyArray<StatusFilling>,
|};

export const getAllFormattedChoicesForProject = (
  project: ProjectAdminProposals_project,
  stepId: ?Uuid,
  stepsChangedProposal: StepsChangedProposal,
  selectedRows: string[],
): AllFormattedChoicesReturn => {
  const steps = getFormattedStepsChoicesForProject(project);
  const proposalsSelected = getFormattedProposalsSelected(project, selectedRows);
  const stepStatuses = getFormattedStatusesChoicesForProjectStep(project, stepId);

  return {
    categories: getFormattedCategoriesChoicesForProject(project),
    districts: getFormattedDistrictsChoicesForProject(project),
    steps,
    stepStatuses,
    proposalsSelected,
    stepsFilling: getFormattedStepsFilling(
      proposalsSelected,
      steps,
      stepsChangedProposal,
      selectedRows,
    ),
    statusesFilling: getFormattedStatusFilling(proposalsSelected, stepStatuses, selectedRows),
  };
};

export const getSelectedSupervisorsByProposals = (
  project: ProjectAdminAnalysis_project,
  proposalIds: $ReadOnlyArray<Uuid>,
): $ReadOnlyArray<Supervisor> => {
  const entries = Object.entries(
    proposalIds.reduce((acc, id) => {
      acc[id] =
        project.firstAnalysisStep?.proposals.edges?.find(edge => edge?.node.id === id)?.node
          .supervisor || null;
      return acc;
    }, {}),
  );
  return ((uniqBy(
    entries.filter(e => !!e[1]).map(e => e[1]),
    'id',
  ): any): $ReadOnlyArray<Supervisor>);
};

export const getSelectedDecisionMakersByProposals = (
  project: ProjectAdminAnalysis_project,
  proposalIds: $ReadOnlyArray<Uuid>,
): $ReadOnlyArray<DecisionMaker> => {
  const entries = Object.entries(
    proposalIds.reduce((acc, id) => {
      acc[id] =
        project.firstAnalysisStep?.proposals.edges?.find(edge => edge?.node.id === id)?.node
          .decisionMaker || null;
      return acc;
    }, {}),
  );
  return ((uniqBy(
    entries.filter(e => !!e[1]).map(e => e[1]),
    'id',
  ): any): $ReadOnlyArray<DecisionMaker>);
};

export const getSelectedAnalystsByProposals = (
  project: ProjectAdminAnalysis_project,
  proposalIds: $ReadOnlyArray<Uuid>,
): $ReadOnlyArray<Analyst> => {
  const entries = Object.entries(
    proposalIds.reduce((acc, id) => {
      acc[id] =
        project.firstAnalysisStep?.proposals.edges?.find(edge => edge?.node.id === id)?.node
          .analysts || [];
      return acc;
    }, {}),
  );
  return ((uniqBy(
    entries
      .filter(e => ((e[1]: any): $ReadOnlyArray<Analyst>).length > 0)
      .map(e => e[1])
      .flat(),
    'id',
  ): any): $ReadOnlyArray<Analyst>);
};

export const getCommonSupervisorIdWithinProposalIds = (
  project: ProjectAdminAnalysis_project,
  proposalIds: $ReadOnlyArray<Uuid>,
) => {
  const selectedSupervisorsByProposals = getSelectedSupervisorsByProposals(project, proposalIds);

  return selectedSupervisorsByProposals.length === 1 && selectedSupervisorsByProposals[0].id
    ? selectedSupervisorsByProposals[0].id
    : null;
};

export const getCommonDecisionMakerIdWithinProposalIds = (
  project: ProjectAdminAnalysis_project,
  proposalIds: $ReadOnlyArray<Uuid>,
) => {
  const selectedDecisionMakerByProposals = getSelectedDecisionMakersByProposals(
    project,
    proposalIds,
  );

  return selectedDecisionMakerByProposals.length === 1 && selectedDecisionMakerByProposals[0].id
    ? selectedDecisionMakerByProposals[0].id
    : null;
};

export const getCommonAnalystIdsWithinProposalIds = (
  project: ProjectAdminAnalysis_project,
  proposalIds: $ReadOnlyArray<Uuid>,
): $ReadOnlyArray<Uuid> => {
  const selectedAnalystsByProposals = getSelectedAnalystsByProposals(project, proposalIds);

  return selectedAnalystsByProposals.map(analyst => analyst.id);
};

const getSelectedEntries = (
  project: ProjectAdminAnalysis_project,
  proposalIds: $ReadOnlyArray<string>,
  type: RowType,
) => {
  switch (type) {
    case 'analyst':
      return getSelectedAnalystsByProposals(project, proposalIds);
    case 'supervisor':
      return getSelectedSupervisorsByProposals(project, proposalIds);
    case 'decision-maker':
      return getSelectedDecisionMakersByProposals(project, proposalIds);
    default:
      throw new Error(`Unknow row type: ${type}`);
  }
};

export const isRowIndeterminate = (
  user: UserSearchDropdownChoice_user,
  project: ProjectAdminAnalysis_project,
  proposalIds: $ReadOnlyArray<Uuid>,
  type: RowType,
): boolean => {
  const entries = Object.entries(
    proposalIds.reduce((acc, id) => {
      if (type === 'supervisor') {
        acc[id] =
          project.firstAnalysisStep?.proposals.edges?.find(edge => edge?.node.id === id)?.node
            .supervisor || null;
      } else if (type === 'analyst') {
        acc[id] =
          project.firstAnalysisStep?.proposals.edges?.find(edge => edge?.node.id === id)?.node
            .analysts || [];
      } else if (type === 'decision-maker') {
        acc[id] =
          project.firstAnalysisStep?.proposals.edges?.find(edge => edge?.node.id === id)?.node
            .decisionMaker || null;
      }
      return acc;
    }, {}),
  );
  const selectedEntries = getSelectedEntries(project, proposalIds, type);
  const userIds = selectedEntries.map(u => u.id);
  const entriesIds = getEntriesIds(entries, type);

  if (userIds.includes(user.id)) {
    for (const userId of userIds) {
      if ((type === 'supervisor' || type === 'decision-maker') && entriesIds.includes(userId)) {
        return !entriesIds.every(eid => eid === userId);
      }
      if (type === 'analyst') {
        const duplicates = lodashFilter(
          entriesIds,
          v => lodashFilter(entriesIds, v1 => v1 === v).length > 1,
        );
        return (
          !!entries.find(e => ((e[1]: any): $ReadOnlyArray<Uuid>).length === 0) ||
          (duplicates.length > 0 &&
            proposalIds.length > duplicates.filter(id => id === user.id).length)
        );
      }
    }
  }

  return false;
};
