@core
Feature: Homepage

@parallel-scenario
Scenario: Can see sections
  Given I visited "home page"
  Then I should see "Section activée"
  And I should not see "Section désactivée"

Scenario: Can see limited projects
  Given feature "themes" is enabled
  Given I visited "home page"
  And I wait ".project-preview" to appear on current page
  Then I should see 4 ".project-preview" elements