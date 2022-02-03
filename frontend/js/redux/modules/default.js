// @flow
import ToggleFeatureMutation from '~/mutations/ToggleFeatureMutation';
import type {
  Exact,
  Action,
  Dispatch,
  FeatureToggle,
  FeatureToggles,
  ReduxStoreSSOConfiguration,
} from '../../types';

type ShowNewFieldModalAction = { type: 'default/SHOW_NEW_FIELD_MODAL' };
type HideNewFieldModalAction = { type: 'default/HIDE_NEW_FIELD_MODAL' };
type ToggleFeatureSucceededAction = {
  type: 'default/TOGGLE_FEATURE_SUCCEEDED',
  feature: string,
  enabled: boolean,
};
type ShowUpdateFieldModalAction = {
  type: 'default/SHOW_UPDATE_FIELD_MODAL',
  id: number,
};
type HideUpdateFieldModalAction = { type: 'default/HIDE_UPDATE_FIELD_MODAL' };

export type DefaultAction =
  | ToggleFeatureSucceededAction
  | ShowNewFieldModalAction
  | ShowUpdateFieldModalAction
  | HideUpdateFieldModalAction
  | HideNewFieldModalAction;
export type State = {|
  +showNewFieldModal: boolean,
  +themes: Array<Object>,
  +images: ?{
    +avatar: string,
    +logoUrl: string,
  },
  +instanceName: string,
  +features: Exact<FeatureToggles>,
  +userTypes: Array<Object>,
  +parameters: Object,
  +updatingRegistrationFieldModal: ?number,
  +ssoList: Array<ReduxStoreSSOConfiguration>,
|};

export const features: FeatureToggles = {
  report_browers_errors_to_sentry: false,
  unstable__remote_events: false,
  login_saml: false,
  login_cas: false,
  login_paris: false,
  disconnect_openid: false,
  votes_min: false,
  blog: false,
  calendar: false,
  login_facebook: false,
  privacy_policy: false,
  members_list: false,
  captcha: false,
  beta__admin_editor: false,
  beta__questionnaire_result: false,
  consent_external_communication: false,
  consent_internal_communication: false,
  newsletter: false,
  profiles: false,
  projects_form: false,
  project_trash: false,
  search: false,
  share_buttons: false,
  shield_mode: false,
  registration: false,
  phone_confirmation: false,
  reporting: false,
  themes: false,
  districts: false,
  user_type: false,
  votes_evolution: false,
  restrict_registration_via_email_domain: false,
  export: false,
  server_side_rendering: false,
  zipcode_at_register: false,
  consultation_plan: false,
  display_map: false,
  sso_by_pass_auth: false,
  allow_users_to_propose_events: false,
  secure_password: false,
  restrict_connection: false,
  login_franceconnect: false,
  read_more: false,
  display_pictures_in_depository_proposals_list: false,
  external_project: false,
  multilangue: false,
  display_pictures_in_event_list: false,
  beta__emailing: false,
  beta__emailing_parameters: false,
  proposal_revisions: false,
  unstable__tipsmeee: false,
  unstable__new_consultation_page: false,
  new_project_card: false,
  import_proposals: false,
  beta__analytics_page: false,
  http_redirects: false,
  unstable__project_admin: false,
  noindex_on_profiles: false,
  unstable__anonymous_questionnaire: false,
  twilio: false,
  unstable__paper_vote: false,
};

export const initialState: State = {
  themes: [],
  images: null,
  showNewFieldModal: false,
  instanceName: '',
  features,
  userTypes: [],
  parameters: {},
  updatingRegistrationFieldModal: null,
  ssoList: [],
};

export const toggleFeatureSucceeded = (
  feature: FeatureToggle,
  enabled: boolean,
): ToggleFeatureSucceededAction => ({
  type: 'default/TOGGLE_FEATURE_SUCCEEDED',
  feature,
  enabled,
});

export const toggleFeature = async (
  dispatch: Dispatch,
  feature: FeatureToggle,
  enabled: boolean,
): Promise<*> => {
  await ToggleFeatureMutation.commit({
    input: {
      type: feature,
      enabled,
    },
  });
  dispatch(toggleFeatureSucceeded(feature, enabled));
};

export const reducer = (state: State = initialState, action: Action): Exact<State> => {
  switch (action.type) {
    case '@@INIT':
      return { ...initialState, ...state };
    case 'default/SHOW_UPDATE_FIELD_MODAL':
      return { ...state, updatingRegistrationFieldModal: action.id };
    case 'default/HIDE_UPDATE_FIELD_MODAL':
      return { ...state, updatingRegistrationFieldModal: null };
    case 'default/SHOW_NEW_FIELD_MODAL':
      return { ...state, showNewFieldModal: true };
    case 'default/HIDE_NEW_FIELD_MODAL':
      return { ...state, showNewFieldModal: false };
    case 'default/TOGGLE_FEATURE_SUCCEEDED':
      return {
        ...state,
        features: { ...state.features, [action.feature]: action.enabled },
      };
    default:
      return state;
  }
};

export const loginWithOpenID = (ssoList: Array<ReduxStoreSSOConfiguration>): boolean => {
  return ssoList.length > 0 && ssoList.filter(sso => sso.ssoType === 'oauth2').length > 0;
};
