// @flow
import * as React from 'react';
import { Route, Switch, useRouteMatch } from 'react-router-dom';
import { usePreloadedQuery, graphql } from 'relay-hooks';
import NoContributionsStep from '~/components/Admin/Project/ProjectAdminContributions/NoContributions/NoContributionsStep';
import IndexContributions, { getContributionsPath } from './IndexContributions/IndexContributions';
import AppBox from '~ui/Primitives/AppBox';
import type { ResultPreloadQuery } from '~/types';
import ProjectAdminProposalsPage from '~/components/Admin/Project/ProjectAdminProposalsPage';
import { ProjectAdminProposalsProvider } from '~/components/Admin/Project/ProjectAdminPage.context';
import { ProjectAdminDebateProvider } from './ProjectAdminDebate/ProjectAdminDebate.context';
import ProjectAdminDebate from '~/components/Admin/Project/ProjectAdminContributions/ProjectAdminDebate/ProjectAdminDebate';
import ContributionsPlaceholder from '~/components/Admin/Project/ProjectAdminContributions/IndexContributions/ContributionsPlaceholder';
import Skeleton from '~ds/Skeleton';

type Props = {|
  +dataPrefetch: ResultPreloadQuery,
  +projectId: string,
|};

export const queryContributions = graphql`
  query ProjectAdminContributionsPageQuery(
    # ARGUMENTS OF ProjectAdminProposals
    $projectId: ID!
    $viewerIsAdmin: Boolean!
    $countProposalPagination: Int!
    $cursorProposalPagination: String
    $proposalRevisionsEnabled: Boolean!
    $proposalOrderBy: ProposalOrder!
    $proposalState: ProposalsState!
    $proposalCategory: ID
    $proposalDistrict: ID
    $proposalTheme: ID
    $proposalStatus: ID
    $proposalStep: ID
    $proposalTerm: String
    # ARGUMENTS OF ProjectAdminDebate (DebateArgument)
    $countArgumentPagination: Int!
    $cursorArgumentPagination: String
    $argumentType: ForOrAgainstValue
    $isPublishedArgument: Boolean!
    $isTrashedArgument: Boolean!
    # ARGUMENTS OF ProjectAdminDebate (DebateVote)
    $isPublishedVote: Boolean
    $countVotePagination: Int!
    $cursorVotePagination: String
  ) {
    project: node(id: $projectId) {
      ... on Project {
        firstCollectStep {
          id
        }
        steps {
          id
          __typename
          slug
          ... on DebateStep {
            debate {
              id
              ...ProjectAdminDebate_debate
                @arguments(
                  countArgumentPagination: $countArgumentPagination
                  cursorArgumentPagination: $cursorArgumentPagination
                  argumentType: $argumentType
                  isPublishedArgument: $isPublishedArgument
                  isTrashedArgument: $isTrashedArgument
                  isPublishedVote: $isPublishedVote
                  countVotePagination: $countVotePagination
                  cursorVotePagination: $cursorVotePagination
                )
            }
          }
          ...ProjectAdminDebate_debateStep
        }
        ...NoContributionsStep_project
        ...IndexContributions_project
      }
    }
    viewer {
      ...NoContributionsStep_viewer
    }
    ...ProjectAdminProposalsPage_query
      @arguments(
        projectId: $projectId
        viewerIsAdmin: $viewerIsAdmin
        count: $countProposalPagination
        cursor: $cursorProposalPagination
        proposalRevisionsEnabled: $proposalRevisionsEnabled
        orderBy: $proposalOrderBy
        state: $proposalState
        category: $proposalCategory
        district: $proposalDistrict
        theme: $proposalTheme
        status: $proposalStatus
        step: $proposalStep
        term: $proposalTerm
      )
  }
`;

const ProjectAdminContributionsPage = ({ dataPrefetch, projectId }: Props) => {
  const { props: dataPreloaded } = usePreloadedQuery(dataPrefetch);
  const { path: baseUrl } = useRouteMatch();
  const project = dataPreloaded?.project || null;
  const viewer = dataPreloaded?.viewer || null;

  const collectSteps = project?.steps.filter(step => step.__typename === 'CollectStep') || [];
  const debateSteps = project?.steps.filter(step => step.__typename === 'DebateStep') || [];

  const hasCollectStep = collectSteps.length > 0;
  const hasDebateStep = debateSteps.length > 0;
  const hasAtLeastOneContributionsStep = hasCollectStep || hasDebateStep;

  const hasIndex =
    collectSteps.length > 1 ||
    debateSteps.length > 1 ||
    (collectSteps.length >= 1 && debateSteps.length >= 1);

  return (
    <AppBox m={5}>
      <Switch>
        <Route exact path={baseUrl}>
          <Skeleton isLoaded={!!project} placeholder={<ContributionsPlaceholder />}>
            {!hasAtLeastOneContributionsStep ? (
              <NoContributionsStep project={project} viewer={viewer} />
            ) : (
              <IndexContributions project={project} />
            )}
          </Skeleton>
        </Route>

        <Route path={getContributionsPath(baseUrl, 'CollectStep')}>
          <ProjectAdminProposalsProvider firstCollectStepId={project?.firstCollectStep?.id}>
            <ProjectAdminProposalsPage
              hasContributionsStep={hasIndex}
              projectId={projectId}
              query={dataPreloaded}
              baseUrl={baseUrl}
            />
          </ProjectAdminProposalsProvider>
        </Route>

        <Route
          path={getContributionsPath(baseUrl, 'DebateStep')}
          component={routeProps => (
            <ProjectAdminDebateProvider>
              <ProjectAdminDebate
                hasContributionsStep={hasIndex}
                baseUrl={baseUrl}
                debate={
                  project?.steps.find(({ slug }) => slug === routeProps.match.params.stepSlug)
                    ?.debate || null
                }
                debateStep={
                  project?.steps.find(({ slug }) => slug === routeProps.match.params.stepSlug) ||
                  null
                }
                {...routeProps}
              />
            </ProjectAdminDebateProvider>
          )}
        />
      </Switch>
    </AppBox>
  );
};

export default ProjectAdminContributionsPage;
