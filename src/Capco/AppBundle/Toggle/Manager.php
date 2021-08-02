<?php

namespace Capco\AppBundle\Toggle;

use Capco\AppBundle\Event\ToggleFeatureEvent;
use Qandidate\Toggle\Context;
use Qandidate\Toggle\ContextFactory;
use Qandidate\Toggle\Toggle;
use Qandidate\Toggle\ToggleManager;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class Manager
{
    public const graphql_query_analytics = 'graphql_query_analytics';
    public const new_feature_questionnaire_result = 'new_feature_questionnaire_result';
    public const blog = 'blog';
    public const calendar = 'calendar';
    public const captcha = 'captcha';
    public const consent_external_communication = 'consent_external_communication';
    public const developer_documentation = 'developer_documentation';
    public const public_api = 'public_api';
    public const login_facebook = 'login_facebook';
    public const login_gplus = 'login_gplus';
    public const login_paris = 'login_paris';
    public const login_saml = 'login_saml';
    public const votes_min = 'votes_min';
    public const privacy_policy = 'privacy_policy';
    public const members_list = 'members_list';
    public const newsletter = 'newsletter';
    public const profiles = 'profiles';
    public const projects_form = 'projects_form';
    public const project_trash = 'project_trash';
    public const search = 'search';
    public const share_buttons = 'share_buttons';
    public const shield_mode = 'shield_mode';
    public const registration = 'registration';
    public const restrict_registration_via_email_domain = 'restrict_registration_via_email_domain';
    public const themes = 'themes';
    public const export = 'export';
    public const districts = 'districts';
    public const user_type = 'user_type';
    public const votes_evolution = 'votes_evolution';
    public const server_side_rendering = 'server_side_rendering';
    public const zipcode_at_register = 'zipcode_at_register';
    public const indexation = 'indexation';
    public const consultation_plan = 'consultation_plan';
    public const display_map = 'display_map';
    public const consent_internal_communication = 'consent_internal_communication';
    public const disconnect_openid = 'disconnect_openid';
    public const sso_by_pass_auth = 'sso_by_pass_auth';
    public const allow_users_to_propose_events = 'allow_users_to_propose_events';
    public const login_franceconnect = 'login_franceconnect';
    public const restrict_connection = 'restrict_connection';
    public const secure_password = 'secure_password';
    public const read_more = 'read_more';
    public const remind_user_account_confirmation = 'remind_user_account_confirmation';
    public const display_pictures_in_depository_proposals_list = 'display_pictures_in_depository_proposals_list';
    public const display_pictures_in_event_list = 'display_pictures_in_event_list';
    public const external_project = 'external_project';
    public const app_news = 'app_news';
    public const sentry_log = 'sentry_log';
    public const login_openid = 'login_openid';
    public const versions = 'versions';
    public const multilangue = 'multilangue';
    public const unstable__admin_editor = 'unstable__admin_editor';
    public const unstable__analysis = 'unstable__analysis';
    public const http_redirects = 'http_redirects';
    public const report_browers_errors_to_sentry = 'report_browers_errors_to_sentry';
    public const phone_confirmation = 'phone_confirmation';
    public const reporting = 'reporting';
    public const unstable__remote_events = 'unstable__remote_events';
    public const majority_vote_question = 'majority_vote_question';
    public const unstable__emailing = 'unstable__emailing';
    public const unstable__emailing_parameters = 'unstable__emailing_parameters';
    public const unstable__debate = 'unstable__debate';
    public const proposal_revisions = 'proposal_revisions';
    public const unstable__tipsmeee = 'unstable__tipsmeee';
    public const unstable__new_consultation_page = 'unstable__new_consultation_page';
    public const unstable__new_project_card = 'unstable__new_project_card';
    public const export_legacy_users = 'export_legacy_users';
    public const import_proposals = 'import_proposals';
    public const unstable__analytics_page = 'unstable__analytics_page';
    public const unstable_project_admin = 'unstable_project_admin';

    public const ADMIN_ALLOWED_FEATURES = [
        self::blog,
        self::calendar,
        self::consultation_plan,
        self::privacy_policy,
        self::display_map,
        self::versions,
        self::themes,
        self::districts,
        self::members_list,
        self::profiles,
        self::reporting,
        self::newsletter,
        self::share_buttons,
        self::search,
        self::display_pictures_in_depository_proposals_list,
        self::display_pictures_in_event_list,
        self::external_project,
        self::read_more,
        self::secure_password,
        self::restrict_connection,
        self::public_api,
        self::developer_documentation,
    ];

    public static $toggles = [
        self::graphql_query_analytics,
        self::new_feature_questionnaire_result,
        self::blog,
        self::calendar,
        self::captcha,
        self::consent_external_communication,
        self::developer_documentation,
        self::public_api,
        self::votes_min,
        self::login_facebook,
        self::login_gplus,
        self::login_saml,
        self::login_paris,
        self::login_openid,
        self::privacy_policy,
        self::members_list,
        self::newsletter,
        self::profiles,
        self::projects_form,
        self::project_trash,
        self::search,
        self::share_buttons,
        self::shield_mode,
        self::registration,
        self::phone_confirmation,
        self::reporting,
        self::restrict_registration_via_email_domain,
        self::themes,
        self::export,
        self::districts,
        self::user_type,
        self::votes_evolution,
        self::server_side_rendering,
        self::zipcode_at_register,
        self::indexation,
        self::consultation_plan,
        self::display_map,
        self::consent_internal_communication,
        self::disconnect_openid,
        self::sso_by_pass_auth,
        self::allow_users_to_propose_events,
        self::login_franceconnect,
        self::restrict_connection,
        self::secure_password,
        self::read_more,
        self::remind_user_account_confirmation,
        self::display_pictures_in_depository_proposals_list,
        self::display_pictures_in_event_list,
        self::external_project,
        self::app_news,
        self::sentry_log,
        self::multilangue,
        self::unstable__admin_editor,
        self::unstable__analysis,
        self::http_redirects,
        self::report_browers_errors_to_sentry,
        self::unstable__remote_events,
        self::majority_vote_question,
        self::unstable__emailing,
        self::unstable__emailing_parameters,
        self::unstable__debate,
        self::proposal_revisions,
        self::unstable__tipsmeee,
        self::unstable__new_consultation_page,
        self::unstable__new_project_card,
        self::export_legacy_users,
        self::import_proposals,
        self::unstable__analytics_page,
        self::unstable_project_admin,
    ];

    protected ToggleManager $toggleManager;

    protected Context $context;

    protected array $knownValues = [];

    private EventDispatcherInterface $dispatcher;

    public function __construct(
        ToggleManager $toggleManager,
        ContextFactory $contextFactory,
        EventDispatcherInterface $dispatcher
    ) {
        $this->toggleManager = $toggleManager;
        $this->context = $contextFactory->createContext();
        $this->dispatcher = $dispatcher;
    }

    public function exists(string $name): bool
    {
        return \in_array($name, self::$toggles, true);
    }

    public function activate(string $name): void
    {
        $toggle = $this->createToggle($name, Toggle::ALWAYS_ACTIVE);
        $this->dispatchEvent($toggle);
        $this->toggleManager->add($toggle);
    }

    public function activateAll(): void
    {
        foreach (self::$toggles as $name) {
            $this->activate($name);
        }
    }

    public function all(?bool $state = null): array
    {
        // features are disabled by default
        $return = [];

        foreach (self::$toggles as $name) {
            if (!$state || $state === $this->isActive($name)) {
                $return[$name] = $this->isActive($name);
            }
        }

        return $return;
    }

    public function deactivate(string $name): void
    {
        $toggle = $this->createToggle($name, Toggle::INACTIVE);
        $this->dispatchEvent($toggle);
        $this->toggleManager->add($this->createToggle($name, Toggle::INACTIVE));
    }

    public function deactivateAll(): void
    {
        foreach (self::$toggles as $name) {
            $this->deactivate($name);
        }
    }

    public function isActive(string $name): bool
    {
        if (!isset($this->knownValues[$name])) {
            $this->knownValues[$name] = $this->toggleManager->active($name, $this->context);
        }

        return $this->knownValues[$name];
    }

    public function hasOneActive(array $names): bool
    {
        if (0 === \count($names)) {
            return true;
        }

        foreach ($names as $name) {
            if ($this->isActive($name)) {
                return true;
            }
        }

        return false;
    }

    public function switchValue(string $name): bool
    {
        $value = $this->isActive($name);

        if ($value) {
            $this->deactivate($name);
        } else {
            $this->activate($name);
        }

        return !$value;
    }

    public function containsEnabledFeature(array $features): bool
    {
        if (empty($features)) {
            return true;
        }

        foreach ($features as $feature) {
            if (isset($this->all(true)[$feature])) {
                return true;
            }
        }

        return false;
    }

    public function set(string $name, bool $value): void
    {
        $value ? $this->activate($name) : $this->deactivate($name);
    }

    public function getToggleManager(): ToggleManager
    {
        return $this->toggleManager;
    }

    public function dispatchEvent(Toggle $toggle)
    {
        $event = new ToggleFeatureEvent($toggle);
        $this->dispatcher->dispatch($event::NAME, $event);
    }

    private function createToggle(string $name, int $status, array $conditions = []): Toggle
    {
        $toggle = new Toggle($name, $conditions);

        if (Toggle::INACTIVE === $status) {
            $toggle->deactivate();
        } else {
            $toggle->activate($status);
        }

        return $toggle;
    }
}
