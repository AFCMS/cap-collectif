<?php

namespace Capco\AppBundle\Behat\Traits;

trait AdminProjectTrait
{
    /**
     * @When I go to the admin project list page
     */
    public function iGoToTheAdminProjectListPage()
    {
        $this->iVisitedPage('AdminProjectListPage');
    }

    /**
     * @When I go to the admin project type list page
     */
    public function iGoToTheAdminProjectTypeListPage()
    {
        $this->iVisitedPage('AdminProjectTypePage');
    }

    /**
     * @When I click the project add button
     */
    public function iClickAddProjectButton()
    {
        $this->getCurrentPage()->clickAddButton();
    }

    /**
     * @When I submit the project add modal
     */
    public function iSubmitProjectModal()
    {
        $this->getCurrentPage()->submitModal();
    }

    // TODO: Put each page in a different trait and add specific tests.

    /**
     * @When I go to the admin source list page
     */
    public function iGoToTheAdminSourceListPage()
    {
        $this->iVisitedPage('AdminProjectSourcePage');
    }

    /**
     * @When I go to the admin proposal list page
     */
    public function iGoToTheAdminProposalListPage()
    {
        $this->iVisitedPage('AdminProjectProposalPage');
    }

    /**
     * @When I go to the admin questionnaire list page
     */
    public function iGoToTheAdminQuestionnaireListPage()
    {
        $this->iVisitedPage('AdminProjectListQuestionnairePage');
    }

    /**
     * @When I go to the admin appendix list page
     */
    public function iGoToTheAdminAppendixListPage()
    {
        $this->iVisitedPage('AdminProjectListQuestionnairePage');
    }

    /**
     * @When I go to the admin consultation list page
     */
    public function iGoToTheAdminConsultationListPage()
    {
        $this->iVisitedPage('AdminProjectConsultationPage');
    }

    /**
     * @When I go to the admin consultation creation page
     */
    public function iGoToTheAdminConsultationCreationPage()
    {
        $this->iVisitedPage('AdminProjectConsultationCreationPage');
    }

    /**
     * @When I fill the project authors field with name :username
     */
    public function iFillProjectAuthorsFieldWithName(string $username)
    {
        /** @var DocumentElement $page */
        $page = $this->getCurrentPage();
        $page->find('css', '#project-author .react-select__input input')->setValue($username);
        $this->iWait(3);
        $page->find('css', '#project-author')->click();
        $page->find('css', '.react-select__menu-portal .react-select__option:first-child')->click();
    }
}
