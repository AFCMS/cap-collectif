// @flow
import * as React from 'react';
import { connect } from 'react-redux';
import { injectIntl, type IntlShape } from 'react-intl';
import { Field, SubmissionError, reduxForm, formValueSelector, change } from 'redux-form';
import Fetcher from '../../../services/Fetcher';
import select from '../../Form/Select';
import CreateProposalFusionMutation, {
  type CreateProposalFusionMutationResponse,
} from '../../../mutations/CreateProposalFusionMutation';
import { closeCreateFusionModal } from '../../../redux/modules/proposal';
import type { State, Dispatch, Uuid } from '../../../types';

export const formName = 'create-proposal-fusion';

type FormValues = {
  project: ?Uuid,
  fromProposals: $ReadOnlyArray<{ value: Uuid }>,
};

type Props = {
  proposalForm: Object,
  projects: Array<Object>,
  currentCollectStep: Object,
  onProjectChange: (form: string, field: string, value: any) => void,
  intl: IntlShape,
};

const validate = (values: FormValues, props: Props) => {
  const { intl } = props;
  const errors = {};
  if (!values.project) {
    errors.project = intl.formatMessage({ id: 'please-select-a-participatory-project' });
  }
  if (!values.fromProposals || values.fromProposals.length < 2) {
    errors.fromProposals = intl.formatMessage({ id: 'please-select-at-least-2-proposals' });
  }
  return errors;
};

const onSubmit = (values: FormValues, dispatch: Dispatch) => {
  return CreateProposalFusionMutation.commit({
    input: { fromProposals: values.fromProposals.map(proposal => proposal.value) },
  })
    .then((response: CreateProposalFusionMutationResponse) => {
      if (!response.createProposalFusion || !response.createProposalFusion.proposal) {
        throw new Error('Mutation "createProposalFusion" failed.');
      }
      const createdProposal = response.createProposalFusion.proposal;
      dispatch(closeCreateFusionModal());
      window.location.href = createdProposal.adminUrl;
    })
    .catch(() => {
      throw new SubmissionError({
        _error: 'global.error.server.form',
      });
    });
};

export class ProposalFusionForm extends React.Component<Props> {
  render() {
    const { currentCollectStep, projects, onProjectChange, intl } = this.props;
    return (
      <form>
        <Field
          name="project"
          id="project"
          label={intl.formatMessage({ id: 'admin.fields.proposal.project' })}
          placeholder={intl.formatMessage({ id: 'select-a-participatory-project' })}
          isLoading={projects.length === 0}
          component={select}
          clearable={false}
          onChange={() => onProjectChange(formName, 'fromProposals', [])}
          options={projects.map(p => ({ value: p.id, label: p.title }))}
        />
        {currentCollectStep && (
          <Field
            name="fromProposals"
            id="fromProposals"
            multi
            label={intl.formatMessage({ id: 'initial-proposals' })}
            autoload
            help={intl.formatMessage({ id: '2-proposals-minimum' })}
            placeholder={intl.formatMessage({ id: 'select-proposals' })}
            component={select}
            filterOptions={(options, filter, currentValues) =>
              options
                .filter(o => o.stepId === currentCollectStep.id) // If step has changed, we hide previous steps
                .filter(o => !currentValues.includes(o))
            }
            loadOptions={input =>
              Fetcher.postToJson(`/collect_steps/${currentCollectStep.id}/proposals/search`, {
                terms: input,
              }).then(res => ({
                options: res.proposals.map(p => ({
                  value: p.id,
                  label: p.title,
                  stepId: currentCollectStep.id,
                })),
              }))
            }
          />
        )}
      </form>
    );
  }
}

const getBudgetProjects = (projects: { [id: Uuid]: Object }): Array<Object> => {
  return Object.keys(projects)
    .map(key => projects[key])
    .filter(p => p.steps.filter(s => s.type === 'collect').length > 0);
};

const getSelectedProjectId = (state: State): Uuid => {
  return formValueSelector(formName)(state, 'project');
};

const getCurrentCollectStep = (state: State): ?Object => {
  const id = getSelectedProjectId(state);
  if (!id) {
    return null;
  }
  const project = state.project.projectsById[id];
  if (!project) {
    return null;
  }
  return Object.keys(project.steps)
    .map(k => project.steps[k])
    .filter(s => s.type === 'collect')[0];
};

const mapStateToProps = (state: State) => ({
  projects: getBudgetProjects(state.project.projectsById),
  currentCollectStep: getCurrentCollectStep(state),
});

const form = reduxForm({
  form: formName,
  destroyOnUnmount: false,
  validate,
  onSubmit,
})(ProposalFusionForm);

export default connect(mapStateToProps, { onProjectChange: change })(injectIntl(form));
