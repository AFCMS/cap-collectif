@admin
Feature: Admin features

Scenario: Logged in admin wants to tests all admin list page
  Given I am logged in as admin
  And I go to the admin general list page
  And I should not see "error.500"
  Then I go to the admin opinion list page
  And I should not see "error.500"

Scenario: Logged in admin wants to test admin dashboard
  Given I am logged in as admin
  And I go to the admin dashboard page
  And I should not see "error.500"

Scenario: Non-generated fonts can be found
  Given I am logged in as admin
  And I go to "/fonts/Nantaise-Bold.otf"
  Then I should not see "error.404"

# TODO: Make E2E test specific to each page
Scenario: Logged in admin wants to test admin project
  Given I am logged in as admin
  And I go to the admin project list page
  And I should not see "error.500"
  And I go to the admin appendix list page
  And I should not see "error.500"
  And I go to the admin source list page
  And I should not see "error.500"
  And I go to the admin consultation list page
  And I should not see "error.500"
  And I go to the admin project type list page
  And I should not see "error.500"
  And I go to the admin proposal list page
  And I should not see "error.500"
  And I go to the admin questionnaire list page
  And I should not see "error.500"

Scenario: Logged in admin wants to test admin contributions
  Given I am logged in as admin
  And I go to the admin argument list page
  And I should not see "error.500"

Scenario: Logged in admin wants to see home / sections page list.
  Given I am logged in as admin
  When I go to the admin section list page
  Then I should not see "error.500"
  And I should see "Section List"

Scenario: Logged in admin wants to edit a section
  Given I am logged in as admin
  When I go to the admin section page with sectionId 5
  Then I should not see "error.500"
  And I should not see "error.404"
  And I should see an ".content" element
  