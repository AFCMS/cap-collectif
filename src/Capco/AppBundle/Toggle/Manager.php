<?php

namespace Capco\AppBundle\Toggle;

use Qandidate\Toggle\Toggle;
use Qandidate\Toggle\ToggleManager;
use Qandidate\Toggle\ContextFactory;

class Manager
{
    protected $toggleManager;

    protected $prefix;

    protected static $toggles = array(
        'blog',
        'calendar',
        'newsletter',
        'ideas',
        'themes',
        'registration',
        'login_facebook',
        'login_gplus',
        'login_twitter',
        'shield_mode',
        'user_type',
        'members_list',
        'consultations_form',
        'share_buttons',
        'idea_creation',
        'consultation_trash',
        'idea_trash',
    );

    protected static $categories = array(
        'pages.blog' => ['blog'],
        'pages.events' => ['calendar'],
        'pages.ideas' => [
            'ideas',
            'idea_creation'
        ],
        'pages.themes' => ['themes'],
        'pages.consultations' => ['consultations_form'],
        'pages.registration' => [
            'registration',
            'user_type',
        ],
        'pages.members' => ['members_list'],
        'pages.login' => [
            'login_facebook',
            'login_gplus',
            'login_twitter',
        ],
        'settings.global' => [
            'newsletter',
            'share_buttons',
        ],
        'settings.shield_mode' => ['shield_mode']
    );

    public function __construct(ToggleManager $toggleManager, ContextFactory $contextFactory, $prefix)
    {
        $this->toggleManager = $toggleManager;
        $this->context = $contextFactory->createContext();
        $this->prefix = $prefix;
    }

    protected function getPrefixedName($name)
    {
        if (null != $this->prefix && !(0 === strpos($name, $this->prefix))) {
            return $this->prefix.'__'.$name;
        }

        return $name;
    }

    public function activate($name)
    {
        $this->toggleManager->add($this->createToggle($name, Toggle::ALWAYS_ACTIVE));
    }

    public function activateAll()
    {
        foreach (self::$toggles as $name) {
            $this->activate($name);
        }
    }

    public function all($state = null)
    {
        // features are disabled by default
        $return = array();

        foreach (self::$toggles as $name) {
            if (null == $state || $state == $this->isActive($name)) {
                $return[$name] = $this->isActive($name);
            }
        }

        return $return;
    }

    public function deactivate($name)
    {
        $this->toggleManager->add($this->createToggle($name, Toggle::INACTIVE));
    }

    public function deactivateAll()
    {
        foreach (self::$toggles as $name) {
            $this->deactivate($name);
        }
    }

    public function isActive($name)
    {
        return $this->toggleManager->active($this->getPrefixedName($name), $this->context);
    }

    public function hasOneActive($names)
    {
        if (count($names) === 0) {
            return true;
        }

        foreach ($names as $name) {
            if ($this->isActive($name)) {
                return true;
            }
        }

        return false;
    }

    public function switchValue($name)
    {
        $value = $this->isActive($name);

        if ($value) {
            $this->deactivate($name);
        } else {
            $this->activate($name);
        }

        return !$value;
    }

    private function createToggle($name, $status, array $conditions = array())
    {
        $toggle = new Toggle($this->getPrefixedName($name), $conditions);

        if ($status === Toggle::INACTIVE) {
            $toggle->deactivate();
        } else {
            $toggle->activate($status);
        }

        return $toggle;
    }

    /**
     * @param $features
     *
     * @return bool
     */
    public function containsEnabledFeature($features)
    {
        if (empty($features)) {
            return true;
        }

        foreach ($features as $feature) {
            if (in_array($feature, array_keys($this->all(true)))) {
                return true;
            }
        }

        return false;
    }

    public function getTogglesByCategory($category)
    {
        $toggles = [];
        if (array_key_exists($category, self::$categories)) {
            foreach (self::$categories[$category] as $name) {
                $toggles[$name] = $this->isActive($name);
            }
        }

        return $toggles;
    }

    public function findCategoryForToggle ($toggle)
    {
        foreach (self::$categories as $category => $toggles) {
            if (in_array($toggle, $toggles)) {
                return $category;
            }
        }

        return null;
    }
}
