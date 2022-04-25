// @flow
import * as React from 'react';
import { FormattedMessage, injectIntl, type IntlShape } from 'react-intl';
import { connect } from 'react-redux';
import {
  Field,
  FieldArray,
  formValueSelector,
  reduxForm,
  SubmissionError,
  submit,
} from 'redux-form';
import { createFragmentContainer, graphql } from 'react-relay';
// TODO https://github.com/cap-collectif/platform/issues/7774
// eslint-disable-next-line no-restricted-imports
import { Button, ButtonToolbar, ListGroup, ListGroupItem, Panel } from 'react-bootstrap';
import memoize from 'lodash/memoize';
import ChangeProposalContentMutation from '~/mutations/ChangeProposalContentMutation';
import UpdateProposalFusionMutation from '~/mutations/UpdateProposalFusionMutation';
import component from '../../Form/Field';
import AlertForm from '../../Alert/AlertForm';
import ProposalFusionEditModal from './ProposalFusionEditModal';
import type { ProposalAdminContentForm_proposal } from '~relay/ProposalAdminContentForm_proposal.graphql';
import type { ProposalForm_proposalForm } from '~relay/ProposalForm_proposalForm.graphql';
import type { FormValues as FrontendFormValues } from '../Form/ProposalForm';
import type { Dispatch, FeatureToggles, GlobalState, Uuid } from '~/types';
import UserListField from '../../Admin/Field/UserListField';
import SubmitButton from '~/components/Form/SubmitButton';
import type { ResponsesInReduxForm } from '~/components/Form/Form.type';
import validateResponses from '~/utils/form/validateResponses';
import formatInitialResponsesValues from '~/utils/form/formatInitialResponsesValues';
import formatSubmitResponses from '~/utils/form/formatSubmitResponses';
import warnResponses from '~/utils/form/warnResponses';
import renderResponses from '~/components/Form/RenderResponses';

type ProposalForm = ProposalForm_proposalForm;
type FormValues = {|
  media: ?{ id: Uuid },
  responses: ResponsesInReduxForm,
  draft: boolean,
  title?: ?string,
  body?: ?string,
  summary?: ?string,
  author?: { value: Uuid, label: string },
  theme?: ?Uuid,
  addressText?: ?string,
  category?: ?Uuid,
  district?: ?Uuid,
  address?: ?string,
|};

type RelayProps = {|
  +proposal: ProposalAdminContentForm_proposal,
|};

type Props = {|
  ...ReduxFormFormProps,
  ...RelayProps,
  +themes: Array<{ id: Uuid, title: string }>,
  +features: FeatureToggles,
  +intl: IntlShape,
  +isAdmin: boolean,
  +responses: ResponsesInReduxForm,
  +dispatch: Dispatch,
|};

const formName = 'proposal-admin-edit';

const onSubmit = (values: FormValues, dispatch: Dispatch, { proposal, isAdmin }: Props) => {
  const input = {
    title: values.title,
    summary: values.summary,
    body: values.body,
    address: values.address,
    theme: values.theme,
    category: values.category,
    district: values.district,
    draft: values.draft,
    media: typeof values.media !== 'undefined' && values.media !== null ? values.media.id : null,
    responses: formatSubmitResponses(values.responses, proposal.form.questions),
    author: isAdmin && values.author ? values.author.value : undefined,
    id: proposal.id,
  };

  return ChangeProposalContentMutation.commit({ input })
    .then(response => {
      if (!response.changeProposalContent || !response.changeProposalContent.proposal) {
        throw new Error('Mutation "changeProposalContent" failed.');
      }
    })
    .catch(() => {
      throw new SubmissionError({
        _error: 'global.error.server.form',
      });
    });
};

export const checkProposalContent = (
  values: FormValues | FrontendFormValues,
  proposalForm: ProposalForm,
  features: FeatureToggles,
  intl: IntlShape,
  isDraft: boolean,
) => {
  const messages = {};
  if (!values.title || values.title.length <= 2) {
    messages.title = isDraft
      ? 'proposal.constraints.title_for_draft'
      : 'proposal.constraints.title';
  } else if (values.title.length >= 255) {
    messages.title = 'question.title.max_length';
  }
  if (
    proposalForm.usingSummary &&
    values.summary &&
    (values.summary.length > 140 || values.summary.length < 2)
  ) {
    messages.summary = 'proposal.constraints.summary';
  }
  if (
    proposalForm.usingDescription &&
    proposalForm.descriptionMandatory &&
    (!values.body || values.body.length <= 2)
  ) {
    messages.body = 'proposal.constraints.body';
  }

  if (proposalForm.usingAddress && !values.address) {
    messages.addressText = 'proposal.constraints.address';
  }

  if (
    proposalForm.categories.length &&
    proposalForm.usingCategories &&
    proposalForm.categoryMandatory &&
    !values.category
  ) {
    messages.category = 'proposal.constraints.category';
  }
  if (
    features.districts &&
    proposalForm.usingDistrict &&
    proposalForm.districtMandatory &&
    !values.district
  ) {
    messages.district = 'proposal.constraints.district';
  }
  if (features.themes && proposalForm.usingThemes && proposalForm.themeMandatory && !values.theme) {
    messages.theme = 'proposal.constraints.theme';
  }

  return messages;
};

const memoizeAvailableQuestions: any = memoize(() => {});

export const validateProposalContent = (
  values: FormValues | FrontendFormValues,
  // $FlowFixMe
  proposalForm: ProposalForm,
  features: FeatureToggles,
  intl: IntlShape,
  isDraft: boolean,
  availableQuestions: Array<string>,
) => {
  const MIN_LENGTH_TITLE = 2;
  const MAX_LENGTH_TITLE = 255;

  const errors = !isDraft
    ? checkProposalContent(values, proposalForm, features, intl, isDraft)
    : {};

  if (!values.title || values.title.length <= MIN_LENGTH_TITLE) {
    errors.title = isDraft ? 'proposal.constraints.title_for_draft' : 'proposal.constraints.title';
  } else if (values.title.length >= MAX_LENGTH_TITLE) {
    errors.title = 'question.title.max_length';
  }

  const responsesError = validateResponses(
    proposalForm.questions,
    values.responses,
    'proposal',
    intl,
    isDraft,
    availableQuestions,
  );

  if (responsesError.responses && responsesError.responses.length) {
    errors.responses = responsesError.responses;
  }

  return errors;
};

export const warnProposalContent = (
  values: FormValues | FrontendFormValues,
  proposalForm: ProposalForm,
  features: FeatureToggles,
  intl: IntlShape,
  isDraft: boolean,
) => {
  const warnings = checkProposalContent(values, proposalForm, features, intl, isDraft);
  const responsesWarning = warnResponses(
    proposalForm.questions,
    values.responses,
    'proposal',
    intl,
  );
  if (responsesWarning.responses && responsesWarning.responses.length) {
    warnings.responses = responsesWarning.responses;
  }

  return warnings;
};

const validate = (values: FormValues, { proposal, features, intl }: Props) => {
  const availableQuestions: Array<string> = memoizeAvailableQuestions.cache.get(
    'availableQuestions',
  );

  validateProposalContent(values, proposal.form, features, intl, values.draft, availableQuestions);
};

type State = {
  showEditFusionModal: boolean,
};

export class ProposalAdminContentForm extends React.Component<Props, State> {
  constructor() {
    super();

    this.state = {
      showEditFusionModal: false,
    };
  }

  render() {
    const {
      pristine,
      invalid,
      valid,
      submitSucceeded,
      submitFailed,
      proposal,
      features,
      submitting,
      isAdmin,
      themes,
      handleSubmit,
      intl,
      change,
      responses,
      dispatch,
    } = this.props;
    const { form } = proposal;
    const { categories } = proposal.form;
    const optional = (
      <span className="excerpt">
        {' '}
        <FormattedMessage id="global.optional" />
      </span>
    );

    const { showEditFusionModal } = this.state;

    return (
      <div className="box box-primary container-fluid">
        <ProposalFusionEditModal
          onClose={() => {
            this.setState({ showEditFusionModal: false });
          }}
          show={showEditFusionModal}
          proposal={proposal}
        />
        {proposal.mergedIn.length > 0 && (
          <Panel className="mt-30 mb-0 panel_flex">
            <Panel.Heading>
              <FormattedMessage id="grouped-into-a-new-proposal" />
            </Panel.Heading>
            <ListGroup fill>
              {proposal.mergedIn.map(parent => (
                <ListGroupItem key={parent.id}>
                  <a href={parent.adminUrl}>{parent.title}</a>
                  {parent.mergedFrom.length > 2 && (
                    <Button
                      bsStyle="danger"
                      onClick={() => {
                        if (
                          window.confirm(
                            intl.formatMessage({ id: 'are-you-sure-you-want-to-delete-this-item' }),
                          )
                        ) {
                          UpdateProposalFusionMutation.commit({
                            input: {
                              proposalId: parent.id,
                              fromProposals: parent.mergedFrom
                                .map(child => child.id)
                                .filter(id => id !== proposal.id),
                            },
                          });
                        }
                      }}>
                      <FormattedMessage id="global.delete" />
                    </Button>
                  )}
                </ListGroupItem>
              ))}
            </ListGroup>
          </Panel>
        )}
        {proposal.mergedFrom.length > 0 && (
          <Panel
            className="mt-30 mb-0 panel_flex"
            header={
              <div>
                <FormattedMessage id="initial-proposals" />
                <ButtonToolbar>
                  <Button
                    bsStyle="warning"
                    onClick={() => {
                      this.setState({ showEditFusionModal: true });
                    }}>
                    <FormattedMessage id="global.edit" />
                  </Button>
                  <Button
                    bsStyle="danger"
                    onClick={() => {
                      if (
                        window.confirm(
                          intl.formatMessage({ id: 'are-you-sure-you-want-to-delete-this-item' }),
                        )
                      ) {
                        UpdateProposalFusionMutation.commit({
                          input: {
                            proposalId: proposal.id,
                            fromProposals: [],
                          },
                        });
                      }
                    }}>
                    <FormattedMessage id="global.delete" />
                  </Button>
                </ButtonToolbar>
              </div>
            }>
            <ListGroup fill>
              {proposal.mergedFrom.map(child => (
                <ListGroupItem key={child.id}>
                  <a href={child.adminUrl}>{child.title}</a>
                </ListGroupItem>
              ))}
            </ListGroup>
          </Panel>
        )}
        <form onSubmit={handleSubmit}>
          <div className="box-header">
            <h3 className="box-title">
              <FormattedMessage id="global.contenu" />
            </h3>
            <a
              className="pull-right link"
              target="_blank"
              rel="noopener noreferrer"
              href={intl.formatMessage({ id: 'admin.help.link.proposal.body' })}>
              <i className="fa fa-info-circle" /> Aide
            </a>
          </div>
          <div className="box-content box-content__content-form">
            <Field
              name="title"
              component={component}
              type="text"
              id="proposal_title"
              label={<FormattedMessage id="proposal.title" />}
            />
            <Field
              name="summary"
              component={component}
              type="textarea"
              id="global.summary"
              label={
                <span>
                  <FormattedMessage id="global.summary" />
                  {optional}
                </span>
              }
            />
            <UserListField
              disabled={!isAdmin}
              id="proposal-admin-author"
              name="author"
              ariaControls="ProposalAdminContentForm-filter-user-listbox"
              label={<FormattedMessage id="global.author" />}
              labelClassName="control-label"
              inputClassName="fake-inputClassName"
              placeholder={intl.formatMessage({ id: 'global.author' })}
              selectFieldIsObject
              multi={false}
              autoload={false}
              clearable={false}
            />
            {features.themes && form.usingThemes && (
              <Field
                name="theme"
                id="global.theme"
                type="select"
                component={component}
                label={
                  <span>
                    <FormattedMessage id="global.theme" />
                    {!form.themeMandatory && optional}
                  </span>
                }>
                <FormattedMessage id="proposal.select.theme">
                  {(message: string) => <option value="">{message}</option>}
                </FormattedMessage>
                {themes.map(theme => (
                  <option key={theme.id} value={theme.id}>
                    {theme.title}
                  </option>
                ))}
              </Field>
            )}
            {categories.length > 0 && form.usingCategories && (
              <Field
                id="global.category"
                type="select"
                name="category"
                component={component}
                label={
                  <span>
                    <FormattedMessage id="global.category" />
                    {!form.categoryMandatory && optional}
                  </span>
                }>
                <FormattedMessage id="proposal.select.category">
                  {(message: string) => <option value="">{message}</option>}
                </FormattedMessage>
                {categories.map(category => (
                  <option key={category.id} value={category.id}>
                    {category.name}
                  </option>
                ))}
              </Field>
            )}
            {features.districts && form.usingDistrict && form.districts.length > 0 && (
              <Field
                id="proposal_district"
                type="select"
                name="district"
                component={component}
                label={
                  <span>
                    <FormattedMessage id="proposal.district" />
                    {!form.districtMandatory && optional}
                  </span>
                }>
                <FormattedMessage id="proposal.select.district">
                  {(message: string) => <option value="">{message}</option>}
                </FormattedMessage>
                {form.districts.map(district => (
                  <option key={district.id} value={district.id}>
                    {district.name}
                  </option>
                ))}
              </Field>
            )}
            {form.usingAddress && (
              <Field
                id="proposal_address"
                component={component}
                type="address"
                name="addressText"
                formName={formName}
                label={<FormattedMessage id="proposal_form.address" />}
                placeholder="proposal.map.form.placeholder"
                addressProps={{
                  getAddressComplete: addressComplete =>
                    change('address', JSON.stringify(addressComplete)),
                }}
              />
            )}
            <Field
              id="proposal_body"
              type="editor"
              name="body"
              component={component}
              label={<FormattedMessage id="proposal.body" />}
            />
            <FieldArray
              intl={intl}
              name="responses"
              component={renderResponses}
              form={formName}
              questions={form.questions}
              change={change}
              responses={responses}
              memoize={memoizeAvailableQuestions}
            />
            <Field
              id="proposal_media"
              name="media"
              component={component}
              type="image"
              image={proposal && proposal.media ? proposal.media.url : null}
              label={
                <span>
                  <FormattedMessage id="proposal.media" />
                  {optional}
                </span>
              }
            />
            <ButtonToolbar className="box-content__toolbar">
              <SubmitButton
                type="submit"
                id="proposal_admin_content_save"
                bsStyle="primary"
                disabled={pristine || submitting}
                onSubmit={() => {
                  dispatch(submit(formName));
                }}
                label={submitting ? 'global.loading' : 'global.save'}
              />
              <AlertForm
                valid={valid}
                invalid={invalid}
                submitSucceeded={submitSucceeded}
                submitFailed={submitFailed}
                submitting={submitting}
              />
            </ButtonToolbar>
          </div>
        </form>
      </div>
    );
  }
}

const form = reduxForm({
  onSubmit,
  validate,
  enableReinitialize: true,
  form: formName,
})(ProposalAdminContentForm);

const mapStateToProps = (state: GlobalState, { proposal }: RelayProps) => {
  const defaultResponses = formatInitialResponsesValues(
    proposal.form.questions,
    proposal.responses ? proposal.responses : [],
  );
  return {
    isAdmin: !!(
      (state.user.user && state.user.user.roles.includes('ROLE_ADMIN')) ||
      (state.user.user && state.user.user.roles.includes('ROLE_SUPER_ADMIN'))
    ),
    features: state.default.features,
    themes: state.default.themes,
    initialValues: {
      draft: proposal.publicationStatus === 'DRAFT',
      title: proposal.title,
      body: proposal.body,
      summary: proposal.summary,
      author: {
        value: proposal.author.id,
        label: proposal.author.displayName,
      },
      theme:
        state.default.features.themes && proposal.form.usingThemes
          ? proposal.theme
            ? proposal.theme.id
            : null
          : undefined,
      category: proposal.form.usingCategories
        ? proposal.category
          ? proposal.category.id
          : null
        : undefined,
      district:
        state.default.features.districts && proposal.form.usingDistrict
          ? proposal.district
            ? proposal.district.id
            : null
          : undefined,
      address:
        proposal.form.usingAddress && proposal.address?.json ? proposal.address.json : undefined,
      media: proposal.media ? proposal.media : null,
      responses: defaultResponses,
      addressText: proposal.address ? proposal.address.formatted : null,
    },
    responses: formValueSelector(formName)(state, 'responses') || defaultResponses,
  };
};

const container = connect(mapStateToProps)(injectIntl(form));
export default createFragmentContainer(container, {
  proposal: graphql`
    fragment ProposalAdminContentForm_proposal on Proposal {
      ...ProposalFusionEditModal_proposal
      id
      mergedFrom {
        id
        adminUrl
        title
      }
      mergedIn {
        id
        adminUrl
        title
        mergedFrom {
          id
        }
      }
      author {
        id
        displayName
      }
      theme {
        id
      }
      category {
        id
      }
      district {
        id
      }
      title
      body
      summary
      address {
        json
        formatted
      }
      publicationStatus
      responses {
        ...responsesHelper_response @relay(mask: false)
      }
      media {
        id
        url
      }
      form {
        id
        description
        step {
          id
          ...interpellationLabelHelper_step @relay(mask: false)
        }
        districts {
          id
          name
        }
        categories {
          id
          name
        }
        questions {
          id
          ...responsesHelper_adminQuestion @relay(mask: false)
        }
        usingDistrict
        usingDescription
        usingSummary
        descriptionMandatory
        districtMandatory
        districtHelpText
        usingThemes
        themeMandatory
        usingCategories
        categoryMandatory
        categoryHelpText
        usingAddress
        titleHelpText
        summaryHelpText
        themeHelpText
        illustrationHelpText
        usingIllustration
        suggestingSimilarProposals
        isProposalForm
        descriptionHelpText
        addressHelpText
        proposalInAZoneRequired
      }
    }
  `,
});
