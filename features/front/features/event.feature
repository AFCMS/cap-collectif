#TODO tests to display/hide map, test on pagination
@core @events
Feature: Events

Background:
  Given feature "calendar" is enabled

@parallel-scenario
Scenario: Anonymous wants to list events
  Given I visited "events page"
  And I wait ".eventPreview" to appear on current page
  Then I should see 11 ".eventPreview" elements

Scenario: Events can be filtered by projects
  Given feature "projects_form" is enabled
  And I visited "events page"
  And I wait ".eventPreview" to appear on current page
  And I click the "#event-button-filter" element
  And I select "UHJvamVjdDpwcm9qZWN0MQ==" from react "#SelectProject-filter-project"
  And I wait 1 seconds
  Then I should see 3 ".eventPreview" elements
  And I click the "#event-status-filter-button-desktop" element
  And I click the "#finished-events" element
  And I wait 2 seconds
  Then I should see 1 ".eventPreview" elements

Scenario: Events can be filtered by theme
  Given feature "themes" is enabled
  And I visited "events page"
  And I wait ".eventPreview" to appear on current page
  And I click the "#event-button-filter" element
  And I select "Justice" from react "#SelectTheme-filter-theme"
  And I wait 1 seconds
  Then I should see 1 ".eventPreview" elements
  And I should see "Event with registrations"
  And I should not see "ParisWeb2015"

Scenario: Archived events can be filtered by theme
  Given feature "themes" is enabled
  And I visited "events page"
  And I wait ".eventPreview" to appear on current page
  And I click the "#event-button-filter" element
  And I select "Justice" from react "#SelectTheme-filter-theme"
  And I click the "#event-status-filter-button-desktop" element
  And I click the "#finished-events" element
  And I wait ".loader" to appear on current page
  And I wait ".eventPreview" to appear on current page
  Then I should see 1 ".eventPreview" elements
  And I should see "evenementPasseSansDateDeFin"
  And I should not see "PHPTourDuFuture"

Scenario: Events can be filtered by title
  Given I visited "events page"
  And I wait ".eventPreview" to appear on current page
  When I fill in the following:
    | event-search-input | without |
  And I wait 1 seconds
  Then I should see 1 ".eventPreview" elements
  And I should see "Event without registrations"
  And I should not see "Event with registrations"

Scenario: Archived events can be filtered by title
  Given I visited "events page"
  And I wait ".eventPreview" to appear on current page
  And I click the "#event-status-filter-button-desktop" element
  And I click the "#finished-events" element
  When I fill in the following:
    | event-search-input | ParisWeb2014 |
  And I wait 1 seconds
  Then I should see 1 ".eventPreview" elements
  And I should see "ParisWeb2014"
  And I should not see "evenementPasseSansDateDeFin"

@database
Scenario: Anonymous wants to comment an event
  Given I visited eventpage with:
    | slug | event-with-registrations |
  And I wait "#CommentForm" to appear on current page
  And I fill in the following:
    | body        | J'ai un truc à dire |
  And I fill in the following:
    | authorName  | Naruto              |
    | authorEmail | naruto72@gmail.com  |
  When I press "comment.submit"
  And I wait 2 seconds
  Then I should see "J'ai un truc à dire" in the "#CommentListViewPaginated" element

@database
Scenario: Logged in user wants to comment an event
  Given I am logged in as user
  And I visited eventpage with:
    | slug | event-with-registrations |
  And I wait "#CommentForm" to appear on current page
  And I fill in the following:
    | body        | J'ai un truc à dire |
  And I should not see "comment.with_my_account"

@database
Scenario: Anonymous wants to comment an event without email
  Given I visited eventpage with:
    | slug | event-with-registrations |
  And I wait "#CommentForm" to appear on current page
  And I fill in the following:
    | body        | J'ai un truc à dire |
  And I fill in the following:
    | authorName  | Naruto              |
  When I press "comment.submit"
  And I wait 2 seconds
  Then I should not see "J'ai un truc à dire" in the "#CommentListViewPaginated" element

Scenario: Anonymous wants to create an event
  Given feature "allow_users_to_propose_events" is enabled
  And I visited "events page"
  And I wait "#btn-create-event" to appear on current page
  When I click on button "#btn-create-event"
  Then I should see a "#login-popover" element

Scenario: Logged in user wants to create an event (TODO : finish this test when back office ready [check redirection and event status])
  Given I am logged in as user
  And feature "allow_users_to_propose_events" is enabled
  And I visited "events page"
  And I wait "#btn-create-event" to appear on current page
  When I click on button "#btn-create-event"
  Then I should see a "#confirm-event-submit" element

Scenario: Feature allow users to propose events is disabled
  Given feature "allow_users_to_propose_events" is disabled
  And I visited "events page"
  Then I should not see "event-proposal"
