// @flow
import type { Store as ReduxStore, Dispatch as ReduxDispatch } from 'redux';
import type { State as ProposalState, ProposalAction } from './redux/modules/proposal';
import type { State as OpinionState, OpinionAction } from './redux/modules/opinion';
import type { State as UserState, UserAction } from './redux/modules/user';
import type { State as ProjectState, ProjectAction } from './redux/modules/project';
import type { State as IdeaState, IdeaAction } from './redux/modules/idea';
import type { State as ReportState, ReportAction } from './redux/modules/report';
import type { State as DefaultState, DefaultAction } from './redux/modules/default';

export type Exact<T> = T;
export type Uuid = string;
export type VoteValue = -1 | 0 | 1;
export type Opinion = { id: Uuid };
export type Version = { id: Uuid, parent: Object };
export type OpinionAndVersion = Opinion | Version;

export type FeatureToggles = {
  blog: boolean,
  calendar: boolean,
  ideas: boolean,
  idea_creation: boolean,
  idea_trash: boolean,
  login_facebook: boolean,
  login_gplus: boolean,
  login_saml: boolean,
  members_list: boolean,
  newsletter: boolean,
  profiles: boolean,
  projects_form: boolean,
  project_trash: boolean,
  search: boolean,
  share_buttons: boolean,
  shield_mode: boolean,
  registration: boolean,
  phone_confirmation: boolean,
  restrict_registration_via_email_domain: boolean,
  reporting: boolean,
  themes: boolean,
  districts: boolean,
  user_type: boolean,
  votes_evolution: boolean,
  export: boolean,
  server_side_rendering: boolean,
  zipcode_at_register: boolean,
  vote_without_account: boolean
};
export type FeatureToggle =
  'blog' |
  'calendar' |
  'ideas' |
  'restrict_registration_via_email_domain' |
  'idea_creation' |
  'idea_trash' |
  'login_facebook' |
  'login_gplus' |
  'login_saml' |
  'members_list' |
  'newsletter' |
  'profiles' |
  'projects_form' |
  'project_trash' |
  'search' |
  'share_buttons' |
  'shield_mode' |
  'registration' |
  'phone_confirmation' |
  'reporting' |
  'themes' |
  'districts' |
  'user_type' |
  'votes_evolution' |
  'export' |
  'server_side_rendering' |
  'zipcode_at_register' |
  'vote_without_account'
;

export type Action =
    ProposalAction
  | OpinionAction
  | UserAction
  | ProjectAction
  | IdeaAction
  | ReportAction
  | DefaultAction
;

export type State = {
  form: Object,
  default: DefaultState,
  idea: IdeaState,
  proposal: ProposalState,
  project: ProjectState,
  report: ReportState,
  user: UserState,
  opinion: OpinionState
};

export type Store = ReduxStore<State, Action>;
export type Dispatch = ReduxDispatch<Action>;
