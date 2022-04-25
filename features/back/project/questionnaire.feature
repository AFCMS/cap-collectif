@admin @questionnaire
Feature: Questionnaire admin features

@database
Scenario: Logged in admin create questionnaire
  Given I am logged in as admin
  And I go to the admin questionnaire list page
  And I click on add questionnaire button
  And I fill in the following:
  | title | Questionnaire created with test |
  And I click on button "#type-questionnaire"
  Then I click on button "#confirm-questionnaire-create"
  Then I should be redirected to "/admin/capco/app/questionnaire/list"
  Then I wait "Questionnaire created with test" to appear on current page in ".sonata-ba-list"

@database
Scenario: Logged in admin edit questionnaire
  Given I am logged in as admin
  And I go to the admin questionnaire edit page with id questionnaire2
  And I fill in the following:
  | questionnaire_title | Questionnaire edited with test |
  | proposal_form_description | This is a questionnaire description edited with test |
  And I check "notify_response_create"
  And I check "notify_response_update"
  And I check "notify_response_delete"
  And I click on button "#parameters-submit"
  And I wait ".alert__form_succeeded-message" to appear on current page
  Then I should see "global.saved"

@database
Scenario: Logged in admin edit questionnaire section
  Given I am logged in as admin
  And I go to the admin questionnaire edit page with id questionnaire2
  And I click on button "#js-btn-edit-0"
  And I fill in the following:
    | questions[0].title | Question title edited with test |
    | questions[0].helpText | Question helpText edited with test |
    | questions[0].description | Question description edited with test |
  And I check "questions[0].required"
  And I check "questions[0].private"
  And I click on button "[id='questions[0].submit']"
  Then I should see "Question title edited with test"

@database
Scenario: Logged in admin cancels edit questionnaire modal
  Given I am logged in as admin
  And I go to the admin questionnaire edit page with id questionnaire2
  And I click on button "#js-btn-edit-0"
  And I fill in the following:
    | questions[0].title | Question title edited with test |
  And I check "questions[0].required"
  And I check "questions[0].private"
  And I click on button "[id='questions[0].submit']"
  And I wait "#proposal-form-admin-question-modal-title-lg" to disappear on current page
  And I click on button "#js-btn-edit-0"
  And I fill in the following:
    | questions[0].title | Question title edited 2 |
  And I click on button "[id='questions[0].cancel']"
  Then I should see "Question title edited with test"
