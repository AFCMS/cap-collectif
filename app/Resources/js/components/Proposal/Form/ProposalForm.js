import React, { PropTypes } from 'react';
import { IntlMixin, FormattedHTMLMessage, FormattedMessage } from 'react-intl';
import { connect } from 'react-redux';
import { Button, Collapse, Panel } from 'react-bootstrap';
import { debounce } from 'lodash';
import FormMixin from '../../../utils/FormMixin';
import DeepLinkStateMixin from '../../../utils/DeepLinkStateMixin';
import FlashMessages from '../../Utils/FlashMessages';
import ArrayHelper from '../../../services/ArrayHelper';
import Input from '../../Form/Input';
import ProposalPrivateField from '../ProposalPrivateField';
import { submitProposal, updateProposal, cancelSubmitProposal } from '../../../redux/modules/proposal';
import { loadSuggestions } from '../../../actions/ProposalActions';

const ProposalForm = React.createClass({
  propTypes: {
    currentStepId: PropTypes.number.isRequired,
    form: PropTypes.object.isRequired,
    themes: PropTypes.array.isRequired,
    districts: PropTypes.array.isRequired,
    categories: PropTypes.array.isRequired,
    isSubmitting: PropTypes.bool.isRequired,
    dispatch: PropTypes.func.isRequired,
    features: PropTypes.object.isRequired,
    mode: PropTypes.string,
    proposal: PropTypes.object,
  },
  mixins: [IntlMixin, DeepLinkStateMixin, FormMixin],

  getDefaultProps() {
    return {
      mode: 'create',
      proposal: {
        title: '',
        body: '',
        theme: {
          id: -1,
        },
        district: {
          id: -1,
        },
        category: {
          id: -1,
        },
        responses: [],
      },
    };
  },

  getInitialState() {
    const { proposal } = this.props;
    return {
      form: {
        title: proposal.title,
        body: proposal.body,
        theme: proposal.theme ? proposal.theme.id : -1,
        district: proposal.district ? proposal.district.id : null,
        category: proposal.category ? proposal.category.id : -1,
        media: null,
      },
      custom: this.getInitialFormAnswers(),
      errors: {
        title: [],
        body: [],
        theme: [],
        district: [],
        category: [],
        media: [],
      },
      suggestions: [],
    };
  },

  componentWillMount() {
    this.handleTitleChangeDebounced = debounce(this.handleTitleChangeDebounced, 500);
  },

  componentDidMount() {
    const { form } = this.props;
    form.fields.map((field) => {
      const ref = `custom-${field.id}`;
      if (field.required) {
        this.formValidationRules[ref] = {
          notBlank: { message: 'proposal.constraints.field_mandatory' },
        };
      }
    });
    this.updateThemeConstraint();
    this.updateDistrictConstraint();
    this.updateCategoryConstraint();
  },

  componentWillReceiveProps(nextProps) {
    const {
      categories,
      features,
      isSubmitting,
      mode,
      proposal,
      dispatch,
    } = this.props;
    this.updateThemeConstraint();
    this.updateDistrictConstraint();
    this.updateCategoryConstraint();
    if (!isSubmitting && nextProps.isSubmitting) {
      if (this.isValid()) {
        const form = this.state.form;
        const responses = [];
        const custom = this.state.custom;
        Object.keys(custom).map((key) => {
          const question = key.split('-')[1];
          if (typeof custom[key] !== 'undefined' && custom[key].length > 0) {
            responses.push({
              question,
              value: custom[key],
            });
          }
        });
        form.responses = responses;
        if (responses.length === 0) {
          delete form.responses;
        }
        if (!features.themes || !this.props.form.usingThemes || form.theme === -1) {
          delete form.theme;
        }
        if (!features.districts || !form.district || !this.props.form.usingDistrict) {
          delete form.district;
        }
        if (categories.length === 0 || !this.props.form.usingCategories || form.category === -1) {
          delete form.category;
        }
        if (mode === 'edit') {
          updateProposal(dispatch, this.props.form.id, proposal.id, form);
        } else {
          submitProposal(dispatch, this.props.form.id, form);
        }
      } else {
        dispatch(cancelSubmitProposal());
      }
    }
  },

  getInitialFormAnswers() {
    const { form } = this.props;
    const custom = {};
    form.fields.map((field) => {
      custom[`custom-${field.id}`] = this.getResponseForField(field.id);
    });
    return custom;
  },

  getResponseForField(id) {
    const { proposal } = this.props;
    const index = ArrayHelper.getElementIndexFromArray(
      proposal.responses,
      { field: { id } },
      'field',
      'id',
    );
    if (index > -1 && typeof proposal.responses[index].value !== 'undefined') {
      return proposal.responses[index].value;
    }
    return '';
  },

  handleTitleChange(e) {
    e.persist();
    const title = e.target.value;
    this.setState(prevState => ({
      form: { ...prevState.form, title },
    }));
    this.handleTitleChangeDebounced(title);
  },

  handleTitleChangeDebounced(title) {
    this.setState({
      suggestions: [],
    });
    if (title.length > 3) {
      this.setState({ isLoadingSuggestions: true });
      loadSuggestions(this.props.currentStepId, title)
        .then((res) => {
          if (this.state.form.title === title) { // last request only
            this.setState({
              suggestions: res.proposals,
              isLoadingSuggestions: false,
            });
          }
        });
    }
  },

  updateThemeConstraint() {
    const {
      features,
      form,
    } = this.props;
    if (features.themes && form.usingThemes && form.themeMandatory) {
      this.formValidationRules.theme = {
        minValue: { value: 0, message: 'proposal.constraints.theme' },
      };
      return;
    }
    this.formValidationRules.theme = {};
  },

  updateDistrictConstraint() {
    const { features, form } = this.props;
    if (features.districts && form.usingDistrict && form.districtMandatory) {
      this.formValidationRules.district = {
        notBlank: { message: 'proposal.constraints.district' },
      };
      return;
    }
    this.formValidationRules.district = {};
  },

  updateCategoryConstraint() {
    const {
      categories,
      form,
    } = this.props;
    if (categories.length && form.usingCategories && form.categoryMandatory) {
      this.formValidationRules.category = {
        minValue: { value: 0, message: 'proposal.constraints.category' },
      };
      return;
    }
    this.formValidationRules.category = {};
  },

  formValidationRules: {
    title: {
      min: { value: 2, message: 'proposal.constraints.title' },
      notBlank: { message: 'proposal.constraints.title' },
    },
    body: {
      min: { value: 2, message: 'proposal.constraints.body' },
      notBlankHtml: { message: 'proposal.constraints.body' },
    },
  },

  renderFormErrors(field) {
    const errors = this.getErrorsMessages(field);
    if (errors.length === 0) {
      return null;
    }
    return <FlashMessages errors={errors} form />;
  },

  render() {
    const { form, features, themes, districts, categories, proposal } = this.props;
    const optional = <span className="excerpt">{` ${this.getIntlMessage('global.form.optional')}`}</span>;
    const themeLabel = (
      <span>
        {this.getIntlMessage('proposal.theme')}
        {!form.themeMandatory && optional}
      </span>
    );
    const categoryLabel = (
      <span>
        {this.getIntlMessage('proposal.category')}
        {!form.categoryMandatory && optional}
      </span>
    );
    const districtLabel = (
      <span>
        {this.getIntlMessage('proposal.district')}
        {!form.districtMandatory && optional}
      </span>
    );
    const illustration = (
      <span>
        {this.getIntlMessage('proposal.media')}
        {optional}
      </span>
    );
    return (
      <form id="proposal-form">
        {
          form.description &&
            <FormattedHTMLMessage message={form.description} />
        }
        <Input
          id="proposal_title"
          type="text"
          autoComplete="off"
          value={this.state.form.title}
          onChange={this.handleTitleChange}
          label={this.getIntlMessage('proposal.title')}
          groupClassName={this.getGroupStyle('title')}
          errors={this.renderFormErrors('title')}
          addonAfter={this.state.isLoadingSuggestions ? <span className="glyphicon glyphicon-refresh glyphicon-spin" /> : <span className="glyphicon glyphicon-refresh" />}
        />
        <Collapse
          in={this.state.suggestions.length > 0}
        >
          <Panel
            header={
              <FormattedMessage
                message={this.getIntlMessage('proposal.suggest_header')}
                matches={this.state.suggestions.length}
                terms={this.state.form.title.split(' ').length}
              />
            }
          >
            <ul style={{ listStyleType: 'none', padding: 0 }}>
              {
                this.state.suggestions.slice(0, 5).map(suggest =>
                  <li>
                    <a href={suggest._links.show} className="external-link">
                      { suggest.title }
                    </a>
                  </li>,
                )
              }
            </ul>
            <Button onClick={() => { this.setState({ suggestions: [] }); }}>{this.getIntlMessage('global.close')}</Button>
          </Panel>
        </Collapse>
        {
          features.themes && form.usingThemes &&
            <Input
              id="proposal_theme"
              type="select"
              valueLink={this.linkState('form.theme')}
              label={themeLabel}
              groupClassName={this.getGroupStyle('theme')}
              errors={this.renderFormErrors('theme')}
              help={form.themeHelpText}
            >
              <option value={-1} disabled>{this.getIntlMessage('proposal.select.theme')}</option>
              {
                themes.map(theme =>
                  <option key={theme.id} value={theme.id}>
                    {theme.title}
                  </option>,
                )
              }
            </Input>
        }
        {
          categories.length > 0 && form.usingCategories &&
            <Input
              id="proposal_category"
              type="select"
              valueLink={this.linkState('form.category')}
              label={categoryLabel}
              groupClassName={this.getGroupStyle('category')}
              errors={this.renderFormErrors('category')}
              help={form.categoryHelpText}
            >
              <option value={-1} disabled>{this.getIntlMessage('proposal.select.category')}</option>
              {
                categories.map((category) => {
                  return (
                    <option key={category.id} value={category.id}>
                      {category.name}
                    </option>
                  );
                })
              }
            </Input>
        }
        {
          features.districts && form.usingDistrict &&
            <Input
              id="proposal_district"
              type="select"
              valueLink={this.linkState('form.district')}
              label={districtLabel}
              groupClassName={this.getGroupStyle('district')}
              errors={this.renderFormErrors('district')}
              help={form.districtHelpText}
            >
              <option value="">{this.getIntlMessage('proposal.select.district')}</option>
              {
                districts.map(district => (
                  <option key={district.id} value={district.id}>
                    {district.name}
                  </option>
                ))
              }
            </Input>
        }
        <Input
          id="proposal_body"
          type="editor"
          label={this.getIntlMessage('proposal.body')}
          groupClassName={this.getGroupStyle('body')}
          errors={this.renderFormErrors('body')}
          valueLink={this.linkState('form.body')}
          help={form.descriptionHelpText}
        />
        {
          form.fields.map((field) => {
            const key = `custom-${field.id}`;
            const label = (
              <span>
                {field.question}
                {!field.required && optional}
              </span>
            );
            const input = (
              <Input
                key={key}
                id={`proposal_${key}`}
                type={field.type}
                label={label}
                groupClassName={this.getGroupStyle(key)}
                valueLink={this.linkState(`custom.${key}`)}
                help={field.helpText}
                errors={this.renderFormErrors(key)}
              />
            );
            return (
              <ProposalPrivateField
                show={field.private}
                children={input}
              />
            );
          })
        }
        <Input
          id="proposal_media"
          type="image"
          image={proposal && proposal.media ? proposal.media.url : null}
          label={illustration}
          groupClassName={this.getGroupStyle('media')}
          errors={this.renderFormErrors('media')}
          valueLink={this.linkState('form.media')}
        />
      </form>
    );
  },

});

const mapStateToProps = state => ({
  features: state.default.features,
  themes: state.default.themes,
  districts: state.default.districts,
  currentStepId: state.project.currentProjectStepById,
});

export default connect(mapStateToProps)(ProposalForm);
