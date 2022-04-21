// @flow
import React, {Component} from 'react';
import {graphql, createFragmentContainer} from 'react-relay';
import {
  Panel,
  ButtonToolbar,
  Button,
} from 'react-bootstrap';
import {connect, type MapStateToProps} from 'react-redux';
import {
  reduxForm,
  type FormProps,
  Field,
  SubmissionError,
} from 'redux-form';
import {FormattedMessage, injectIntl, type IntlShape} from 'react-intl';
import type Profile_viewer from './__generated__/Profile_viewer.graphql';
import type {Dispatch, State} from '../../../types';
import component from "../../Form/Field";
import AlertForm from "../../Alert/AlertForm";
import UserAvatar from "../UserAvatar";
import UpdateProfilePublicDataMutation from "../../../mutations/UpdateProfilePublicDataMutation";

type RelayProps = { profileForm: Profile_viewer };
type Props = FormProps &
  RelayProps & {
  viewer: Profile_viewer,
  intl: IntlShape,
  initialValues: Object,
  hasValue: Object,
  userTypes: Array<Object>,
};

const formName = 'viewerProfileForm';

const validate = (values: Object) => {
  const errors = {};

  const fields = ['biography', 'website', 'neighborhood', 'linkedIn', 'twitter', 'facebook', 'username'];
  fields.forEach(value => {
    if (value === 'username') {
      if (!values[value] || values[value].length === 0) {
        errors[value] = 'fill-field';
      }
    }
    if (values[value] && values[value].length <= 2) {
      errors[value] = 'two-characters-minimum-required';
    }
    if (value !== 'biography') {
      if (values[value] && values[value].length > 256) {
        errors[value] = '256-characters-maximum-required';
      }
    }
  });

  return errors;
};

const onSubmit = (values: Object, dispatch: Dispatch, props: Props) => {
  const {intl} = props;
  const media = typeof values.media !== 'undefined' && values.media !== null ? values.media.id : null;
  delete values.media;
  const input = {
    ...values,
    media,
    userId: props.viewer.id
  };

  return UpdateProfilePublicDataMutation.commit({input})
    .then(response => {
      if (!response.updateProfilePublicData || !response.updateProfilePublicData.viewer) {
        throw new Error('Mutation "updateProfilePublicData" failed.');
      }
    })
    .catch(response => {
      if (response.response.message) {
        throw new SubmissionError({
          _error: response.response.message,
        });
      } else {
        throw new SubmissionError({
          _error: intl.formatMessage({id: 'global.error.server.form'}),
        });
      }
    });
};

export class Profile extends Component<Props> {
  render() {
    const {
      viewer,
      invalid,
      valid,
      submitSucceeded,
      submitFailed,
      handleSubmit,
      submitting,
      userTypes,
      error,
    } = this.props;

    return (
      <Panel id="capco_horizontal_form">
        <h2 className="page-header">
          <FormattedMessage id="user.edition"/>
        </h2>
        <form onSubmit={handleSubmit} className="form-horizontal">
          <div className="capco_horizontal_field_with_border_top" style={{border: 0}}>
            <label className="col-sm-3 control-label" htmlFor="profile_avatar">
              <FormattedMessage id="form.label_media"/>
            </label>
            <UserAvatar className="col-sm-1" user={viewer}/>
            <div className="clearfix"></div>
            <div className="col-sm-3"></div>
            <Field
              id="profile_avatar"
              name="media"
              component={component}
              type="image"
              divClassName="col-sm-6"
            />
          </div>
          <div className="capco_horizontal_field_with_border_top" style={{border: 0}}>
            <label className="col-sm-3 control-label" htmlFor="profile-form-username">
              <FormattedMessage id="form.label_username"/>
            </label>
            <div>
              <Field
                name="username"
                component={component}
                required
                type="text"
                id="profile-form-username"
                divClassName="col-sm-6"
              />
            </div>
          </div>
          <div className="capco_horizontal_field_with_border_top" style={{border: 0}}>
            <label className="col-sm-3 control-label">
              <FormattedMessage id="registration.type"/>{' '}
              <span className="excerpt">
                <FormattedMessage id="global.form.optional"/>
              </span>
            </label>
            <div>
              <Field
                id="user_type"
                name="userType"
                component={component}
                type="select"
                divClassName="col-sm-6"
              >
                <FormattedMessage id="registration.select.type">
                  {message => <option value="">{message}</option>}
                </FormattedMessage>
                {userTypes.map((type, i) => (
                  <option key={i + 1} value={type.id}>
                    {type.name}
                  </option>
                ))}
              </Field>
            </div>
          </div>
          <div className="capco_horizontal_field_with_border_top" style={{border: 0}}>
            <label className="col-sm-3 control-label">
              <FormattedMessage id="form.label_biography"/>
            </label>
            <div>
              <Field
                name="biography"
                component={component}
                type="textarea"
                id="public-data-form-biography"
                divClassName="col-sm-6"
              />
            </div>
          </div>
          <div className="capco_horizontal_field_with_border_top" style={{border: 0}}>
            <label className="col-sm-3 control-label">
              <FormattedMessage id="form.label_neighborhood"/>
            </label>
            <div>
              <Field
                name="neighborhood"
                component={component}
                type="text"
                id="public-data-form-neighborhood"
                divClassName="col-sm-6"
              />
            </div>
          </div>
          <div className="capco_horizontal_field_with_border_top" style={{border: 0}}>
            <label className="col-sm-3 control-label">
              <FormattedMessage id="form.label_website"/>
            </label>
            <div>
              <Field
                name="website"
                component={component}
                type="text"
                id="public-data-form-website"
                divClassName="col-sm-6"
              />
            </div>
          </div>
          <div className="clearfix"></div>
          <h2>
            <FormattedMessage id="social-medias"/>
          </h2>
          <div className="capco_horizontal_field_with_border_top" style={{border: 0}}>
            <label className="col-sm-3 control-label">
              <FormattedMessage id="user.profile.edit.facebook"/>
            </label>
            <div>
              <Field
                placeholder="https://"
                name="facebookUrl"
                component={component}
                type="text"
                id="public-data-form-username"
                divClassName="col-sm-6"
              />
            </div>
          </div>
          <div className="capco_horizontal_field_with_border_top" style={{border: 0}}>
            <label className="col-sm-3 control-label">
              <FormattedMessage id="user.profile.edit.twitter"/>
            </label>
            <div>
              <Field
                placeholder="https://"
                name="twitterUrl"
                component={component}
                type="text"
                id="public-data-form-twitter"
                divClassName="col-sm-6"
              />
            </div>
          </div>
          <div className="capco_horizontal_field_with_border_top" style={{border: 0}}>
            <label className="col-sm-3 control-label">
              <FormattedMessage id="show.label_linkedin_url"/>
            </label>
            <div>
              <Field
                placeholder="https://"
                name="linkedInUrl"
                component={component}
                type="text"
                id="public-data-form-linkedIn"
                divClassName="col-sm-6"
              />
            </div>
          </div>
          <div className="clearfix"></div>
          <h2>
            <FormattedMessage id="confidentialite.title"/>
          </h2>
          <div className="capco_horizontal_field_with_border_top">
            <div className="col-sm-3"></div>
            <Field
              id="profilePageIndexed"
              name="profilePageIndexed"
              component={component}
              type="checkbox"
              labelClassName="font-weight-normal"
              children={
                <FormattedMessage
                  id="user.profile.edit.profilePageIndexed"
                />
              }
              divClassName="col-sm-8"
            />
          </div>
          <div className="capco_horizontal_field_with_border_top">
            <div className="col-sm-3"></div>
            <ButtonToolbar className="col-sm-6 pl-0">
              <Button
                disabled={invalid || submitting}
                type="submit"
                bsStyle="primary"
                id="profile-form-save"
              >
                <FormattedMessage id={submitting ? 'global.loading' : 'global.save_modifications'}/>
              </Button>
              <AlertForm
                valid={valid}
                invalid={invalid}
                errorMessage={error}
                submitSucceeded={submitSucceeded}
                submitFailed={submitFailed}
                submitting={submitting}
              />
            </ButtonToolbar>
          </div>
        </form>
      </Panel>
    );
  }
}

const form = reduxForm({
  onSubmit,
  validate,
  enableReinitialize: true,
  form: formName,
})(Profile);

const mapStateToProps: MapStateToProps<*, *, *> = (state: State, props: Props) => ({
  initialValues: {
    username: props.viewer.username ? props.viewer.username : null,
    biography: props.viewer.biography ? props.viewer.biography : null,
    website: props.viewer.website ? props.viewer.website : null,
    facebookUrl: props.viewer.facebookUrl ? props.viewer.facebookUrl : null,
    linkedInUrl: props.viewer.linkedInUrl ? props.viewer.linkedInUrl : null,
    twitterUrl: props.viewer.twitterUrl ? props.viewer.twitterUrl : null,
    profilePageIndexed: props.viewer.profilePageIndexed ? props.viewer.profilePageIndexed : null,
    userType: props.viewer.userType ? props.viewer.userType.id : null,
    neighborhood: props.viewer.neighborhood ? props.viewer.neighborhood : null,
    media: props.viewer ? props.viewer.media : undefined,

  },
  userTypes: state.default.userTypes,
});

const container = connect(mapStateToProps)(injectIntl(form));

export default createFragmentContainer(
  container,
  graphql`
    fragment Profile_viewer on User {
      id
      media {
        id
        name
        size
        url
      }
      show_url
      username
      biography
      website
      facebookUrl
      linkedInUrl
      twitterUrl
      profilePageIndexed
      userType {
        id
      }
      neighborhood
    }
  `,
);
