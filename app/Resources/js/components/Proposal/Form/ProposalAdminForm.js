import React, { PropTypes } from 'react';
import { IntlMixin } from 'react-intl';
import { connect } from 'react-redux';
import { Field, reduxForm } from 'redux-form';
import Select from 'react-select';
// import Fetcher from '../../../services/Fetcher';
import renderInput from '../../Form/Field';

const formName = 'proposal';

const renderSelect = ({ name, label, input }) => { // eslint-disable-line
  // noResultsText
  if (typeof input.loadOptions === 'function') {
    return <Select.Async {...input} name={name} label={label} onBlur={() => { input.onBlur(input.value); }} />;
  }
  return <Select {...input} name={name} label={label} onBlur={() => { input.onBlur(input.value); }} />;
};

const validate = (values) => {
  console.log(values);
};

let ProposalAdminForm = React.createClass({
  propTypes: {
    proposalForm: PropTypes.object.isRequired,
    user: PropTypes.object.isRequired,
    districts: PropTypes.array.isRequired,
    themes: PropTypes.array.isRequired,
    features: PropTypes.object.isRequired,
  },
  mixins: [IntlMixin],

  render() {
    const { districts, themes, features, user, proposalForm } = this.props;
    const optional = <span className="excerpt">{` ${this.getIntlMessage('global.form.optional')}`}</span>;
    // const illustration = (
    //   <span>
    //     {this.getIntlMessage('proposal.media')}
    //     {optional}
    //   </span>
    // );
    return (
      <form>
        <Field
          name="author"
          label="Auteur"
          options={[{ label: user.displayName, value: user.id }, { label: 'lol', value: 899 }]}
          component={renderSelect}
          clearable={false}
          autoload={false}
          loadOptions={() => Promise.resolve({ options: [{ label: user.displayName, value: user.id }, { label: 'lol', value: 899 }] })}
        />
        <Field
          name="title"
          label="Titre"
          type="text"
          autoComplete="off"
          component={renderInput}
        />
        <Field
          name="body"
          type="editor"
          component={renderInput}
          label={this.getIntlMessage('proposal.body')}
          help={proposalForm.descriptionHelpText}
        />
        {
          proposalForm.usingCategories && proposalForm.categories.length > 0 &&
            <Field
              name="category"
              label={
                <span>
                  {this.getIntlMessage('proposal.category')}
                  {!proposalForm.categoryMandatory && optional}
                </span>
              }
              component={renderSelect}
              help={proposalForm.categoryHelpText}
              placeholder={this.getIntlMessage('proposal.select.category')}
              options={proposalForm.categories.map(c => ({ value: c.id, label: c.name }))}
            />
        }
        {
          features.themes && proposalForm.usingThemes &&
            <Field
                name="theme"
                placeholder={this.getIntlMessage('proposal.select.theme')}
                options={themes.map(theme => ({ value: theme.id, label: theme.title }))}
                component={renderSelect}
                label={
                  <span>
                    {this.getIntlMessage('proposal.theme')}
                    {!proposalForm.themeMandatory && optional}
                  </span>
                }
                help={proposalForm.themeHelpText}
            />
        }
        {
          features.districts && proposalForm.usingDistrict &&
            <Field
              name="district"
              placeholder={this.getIntlMessage('proposal.select.district')}
              component={renderSelect}
              label={
                <span>
                  {this.getIntlMessage('proposal.district')}
                  {!proposalForm.districtMandatory && optional}
                </span>
              }
              help={proposalForm.districtHelpText}
              options={districts.map(district => ({ value: district.id, label: district.name }))}
            />
        }
        {
          // <FieldArray name="responses" component={renderMembers}/>
        //   proposalForm.fields.map(field =>
        //       <Field
        //         name={`responses${field.id}`}
        //         type={field.type}
        //         component={renderInput}
        //         /* label={(
        //           <span>
        //             {field.question}
        //             {!field.required && optional}
        //           </span>
        //         )}*/
        //         // help={field.helpText}
        //       />
        //   )
        }
              {/* <ProposalPrivateField
                show={field.private}
                children={input}
              /> */}
      </form>
    );
  },
});

ProposalAdminForm = reduxForm({
  form: formName,
  destroyOnUnmount: false,
  validate,
})(ProposalAdminForm);

ProposalAdminForm = connect(state => ({
  initialValues: { author: state.default.user.id },
  user: state.default.user,
  features: state.default.features,
  themes: state.default.themes,
  districts: state.default.districts,
}))(ProposalAdminForm);
export default ProposalAdminForm;
