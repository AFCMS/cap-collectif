// @flow
import moment from 'moment';
import ReactOnRails from 'react-on-rails';
import ProjectsListApp from '../js/startup/ProjectsListAppClient';
import ProposalStepPageApp from '../js/startup/ProposalStepPageApp';
import NavbarApp from '../js/startup/NavbarAppClient';
import EmailNotConfirmedApp from '../js/startup/EmailNotConfirmedAppClient';
import NewOpinionApp from '../js/startup/NewOpinionAppClient';
import AccountProfileApp from '../js/startup/AccountProfileApp';
import ProjectTrashButtonApp from '../js/startup/ProjectTrashButtonApp';
import OpinionPageApp from '../js/startup/OpinionPageApp';
import CommentSectionApp from '../js/startup/CommentSectionApp';
import SynthesisViewBoxApp from '../js/startup/SynthesisViewBoxApp';
import SynthesisEditBoxApp from '../js/startup/SynthesisEditBoxApp';
import ProposalPageApp from '../js/startup/ProposalPageApp';
import QuestionnaireStepPageApp from '../js/startup/QuestionnaireStepPageApp';
import ProjectStatsPageApp from '../js/startup/ProjectStatsPageApp';
import ProposalVoteBasketWidgetApp
  from '../js/startup/ProposalVoteBasketWidgetApp';
import AlertBoxApp from '../js/startup/AlertBoxApp';
import ConsultationPageApp from '../js/startup/ConsultationPageApp';
import ProposalListApp from '../js/startup/ProposalListApp';
import ProposalsUserVotesPageApp from '../js/startup/ProposalsUserVotesPageApp';
import ShareButtonDropdownApp from '../js/startup/ShareButtonDropdownApp';
import ProposalCreateFusionButtonApp from '../js/startup/ProposalCreateFusionButtonApp';
import ProposalFormCreateButtonApp from '../js/startup/ProposalFormCreateButtonApp';
import ProjectListPageApp from '../js/startup/ProjectListPageApp';
import ProposalAdminPageApp from '../js/startup/ProposalAdminPageApp';
import ProposalFormAdminPageApp from '../js/startup/ProposalFormAdminPageApp';
import ShieldApp from '../js/startup/ShieldApp';
import RegistrationAdminApp from '../js/startup/RegistrationAdminApp';
import AdminModalsApp from '../js/startup/AdminModalsApp';
import GroupAdminPageApp from '../js/startup/GroupAdminPageApp';
import EvaluationsIndexPageApp from '../js/startup/EvaluationsIndexPageApp';
import ChooseAUsernameApp from '../js/startup/ChooseAUsernameApp';
import appStore from '../js/stores/AppStore';
import AccountProfileFollowingsApp from "../js/startup/AccountProfileFollowingsApp";
import ParisUserNotValidApp from "../js/startup/ParisUserNotValidApp";
import EditProfileApp from "../js/startup/EditProfileApp";
import CookieApp from "../js/startup/CookieApp";

const emptyFunction = () => {};

global.clearTimeout = global.clearTimeout || emptyFunction;
global.setTimeout = global.setTimeout || emptyFunction;
global.setInterval = global.setInterval || emptyFunction;
global.locale = global.locale || 'fr-FR';

moment.locale(global.locale);

ReactOnRails.registerStore({ appStore });
ReactOnRails.register({
  AccountProfileFollowingsApp,
  AdminModalsApp,
  RegistrationAdminApp,
  ShieldApp,
  ChooseAUsernameApp,
  ProposalFormCreateButtonApp,
  ProjectListPageApp,
  ProposalCreateFusionButtonApp,
  ProposalAdminPageApp,
  ProjectsListApp,
  EvaluationsIndexPageApp,
  ProposalStepPageApp,
  NavbarApp,
  EmailNotConfirmedApp,
  NewOpinionApp,
  ProjectTrashButtonApp,
  OpinionPageApp,
  CommentSectionApp,
  SynthesisViewBoxApp,
  SynthesisEditBoxApp,
  ProposalPageApp,
  QuestionnaireStepPageApp,
  ProjectStatsPageApp,
  ProposalVoteBasketWidgetApp,
  AlertBoxApp,
  ConsultationPageApp,
  ProposalListApp,
  ProposalsUserVotesPageApp,
  AccountProfileApp,
  ShareButtonDropdownApp,
  ProposalFormAdminPageApp,
  GroupAdminPageApp,
  ParisUserNotValidApp,
  EditProfileApp,
  CookieApp,
});
