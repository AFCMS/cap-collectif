<?php

namespace Capco\AdminBundle\Resolver;

use Capco\AppBundle\Helper\EnvHelper;
use Capco\AppBundle\Toggle\Manager;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class FeaturesCategoryResolver
{
    protected static $categories = [
        'pages.homepage' => ['conditions' => [], 'features' => []],
        'pages.blog' => ['conditions' => ['blog'], 'features' => []],
        'pages.events' => [
            'conditions' => ['calendar'],
            'features' => ['allow_users_to_propose_events']
        ],
        'pages.themes' => ['conditions' => ['themes'], 'features' => []],
        'pages.projects' => ['conditions' => [], 'features' => ['projects_form', 'project_trash']],
        'pages.registration' => [
            'conditions' => [],
            'features' => ['user_type', 'zipcode_at_register']
        ],
        'pages.members' => ['conditions' => ['members_list'], 'features' => []],
        'pages.login' => ['conditions' => [], 'features' => []],
        'pages.footer' => ['conditions' => [], 'features' => []],
        'pages.cookies' => ['conditions' => [], 'features' => []],
        'pages.privacy' => ['conditions' => [], 'features' => []],
        'pages.legal' => ['conditions' => [], 'features' => []],
        'pages.charter' => ['conditions' => [], 'features' => []],
        'pages.shield' => ['conditions' => [], 'features' => ['shield_mode']],
        'settings.global' => ['conditions' => [], 'features' => []],
        'settings.performance' => ['conditions' => [], 'features' => []],
        'settings.modules' => [
            'conditions' => [],
            'features' => [
                'ROLE_ADMIN' => [
                    'blog',
                    'calendar',
                    'consultation_plan',
                    'privacy_policy',
                    'display_map',
                    'versions',
                    'themes',
                    'districts',
                    'members_list',
                    'profiles',
                    'reporting',
                    'newsletter',
                    'share_buttons',
                    'search',
                    'display_pictures_in_depository_proposals_list',
                    'external_project',
                    'read_more',
                    'secure_password',
                    'restrict_connection',
                    'login_franceconnect',
                    'public_api',
                    'developer_documentation'
                ],
                'ROLE_SUPER_ADMIN' => [
                    'disconnect_openid',
                    'votes_evolution',
                    'server_side_rendering',
                    'export',
                    'indexation',
                    'new_feature_questionnaire_result',
                    'app_news',
                    'unstable__multilangue',
                    'allow_users_to_propose_events',
                    'login_openid'
                ]
            ]
        ],
        'settings.notifications' => ['conditions' => [], 'features' => []],
        'settings.appearance' => ['conditions' => [], 'features' => []]
    ];

    protected $manager;
    protected $authorizationChecker;

    public function __construct(
        Manager $manager,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->manager = $manager;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function isCategoryEnabled(string $category): bool
    {
        if (!isset(self::$categories[$category])) {
            return false;
        }

        return $this->manager->hasOneActive(self::$categories[$category]['conditions']);
    }

    public function isAdminEnabled(/* User */ $admin): bool
    {
        if (method_exists($admin, 'getFeatures')) {
            return $this->manager->hasOneActive($admin->getFeatures());
        }

        return true;
    }

    public function getTogglesByCategory(string $category): array
    {
        $toggles = [];

        if (isset(self::$categories[$category]) && 'settings.modules' === $category) {
            foreach (self::$categories[$category]['features'] as $access => $features) {
                if ($this->authorizationChecker->isGranted($access)) {
                    foreach ($features as $feature) {
                        $toggles[$feature] = [
                            'active' => $this->manager->isActive($feature),
                            'access' => $access
                        ];
                    }
                }
            }
        } elseif (isset(self::$categories[$category])) {
            foreach (self::$categories[$category]['features'] as $feature) {
                if ($this->authorizationChecker->isGranted('ROLE_ADMIN')) {
                    $toggles[$feature] = [
                        'active' => $this->manager->isActive($feature)
                    ];
                }
            }
        }

        if ('settings.modules' === $category && EnvHelper::get('SYMFONY_LOGIN_SAML_ALLOWED')) {
            $toggles['login_saml'] = $this->manager->isActive('login_saml');
        }

        if ('settings.modules' === $category && EnvHelper::get('SYMFONY_LOGIN_PARIS_ALLOWED')) {
            $toggles['login_paris'] = $this->manager->isActive('login_paris');
        }

        return $toggles;
    }

    public function findCategoryForToggle(string $toggle): ?string
    {
        foreach (self::$categories as $name => $category) {
            if (
                'settings.modules' === $category &&
                \in_array(
                    $toggle,
                    array_merge(
                        $category['features']['ROLE_ADMIN'],
                        $category['features']['ROLE_SUPER_ADMIN']
                    ),
                    true
                )
            ) {
                return $name;
            }
            if (\in_array($toggle, $category['features'], true)) {
                return $name;
            }
        }

        return null;
    }

    public function getEnabledPagesCategories(): array
    {
        $categories = [];
        foreach (self::$categories as $name => $cat) {
            if (
                0 === strrpos($name, 'pages.') &&
                $this->manager->hasOneActive($cat['conditions'])
            ) {
                $categories[] = $name;
            }
        }

        return $categories;
    }

    public function getEnabledSettingsCategories(): array
    {
        $categories = [];
        foreach (self::$categories as $name => $cat) {
            if (
                0 === strrpos($name, 'settings.') &&
                $this->manager->hasOneActive($cat['conditions'])
            ) {
                $categories[] = $name;
            }
        }

        return $categories;
    }

    public function getGroupNameForCategory(string $category): ?string
    {
        if (0 === strrpos($category, 'settings.')) {
            return 'admin.group.parameters';
        }
        if (0 === strrpos($category, 'pages.')) {
            return 'admin.group.pages';
        }

        return null;
    }
}
