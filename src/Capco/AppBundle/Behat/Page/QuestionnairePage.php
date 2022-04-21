<?php

namespace Capco\AppBundle\Behat\Page;

use Capco\AppBundle\Behat\PageTrait;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

class QuestionnairePage extends Page
{
    use PageTrait;

    /**
     * @var string
     */
    protected $path = '/project/{projectSlug}/questionnaire/{stepSlug}';

    protected $elements = [
        'questionnaire form' => '#create-reply-form',
        'submit reply button' => '#submit-create-reply',
        'user replies' => '#user-replies',
        'user reply' => '#user-replies .reply',
        'user reply modal' => '.reply__modal--show',
        'user first reply link' => '#user-replies .reply:first-child',
        'reply buttons' => '.reply__buttons',
        'edit reply button' => '.reply__edit-btn',
        'submit edited reply button' => '.reply__confirm-edit-btn',
        'delete reply button' => '.reply__delete-btn',
        'confirm delete reply button' => '.reply__confirm-delete-btn',
        'first ranking choice right arrow' => '.ranking__pick-box__choices .ranking__spot:first-child .ranking__item__arrow--right',
    ];

    public function submitReply()
    {
        $this->getElement('submit reply button')->click();
    }

    public function clickFirstRankingChoiceRightArrow()
    {
        $this->getElement('first ranking choice right arrow')->click();
    }

    public function getSubmitReplyButtonSelector()
    {
        return $this->getSelector('submit reply button');
    }

    public function submitEditedReply()
    {
        $this->getElement('submit edited reply button')->click();
    }

    public function clickEditReplyButton()
    {
        $this->getElement('edit reply button')->click();
    }

    public function getReplyButtonsSelector()
    {
        return $this->getSelector('reply buttons');
    }

    public function getDeleteReplyButtonSelector()
    {
        return $this->getSelector('delete reply button');
    }

    public function clickDeleteReplyButton()
    {
        $this->getElement('delete reply button')->click();
    }

    public function clickConfirmDeleteReplyButton()
    {
        $this->getElement('confirm delete reply button')->click();
    }

    public function getSelectorForUserReply()
    {
        return $this->getSelector('user reply');
    }

    public function clickFirstUserReply()
    {
        $this->getElement('user first reply link')->click();
    }

    public function getReplyModalSelector()
    {
        return $this->getSelector('user reply modal');
    }
}
