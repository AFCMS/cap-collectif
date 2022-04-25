@admin @project @events
Feature: Events features

@database
Scenario: Logged in admin wants to import events
  Given I am logged in as admin
  And I go to the admin event list page
  And I open the import events modal
  And I attach the file "/var/www/features/files/events_to_import.csv" to "events"
  And I wait 2 seconds
  Then I should see 'count-events-found {"num":2}'
  And I can confirm my events import

@database
Scenario: Logged in admin wants to add a new event
  Given I am logged in as admin
  Given features themes, projects are enabled
  And I go to the admin event list page
  Then I click on create button
  And I should be on "/admin/capco/app/event/create"
  And I wait "#event_title" to appear on current page
  And I wait "#confirm-event-create" to appear on current page

@database
Scenario: Logged in admin wants to add a new event
  Given I am logged in as admin
  Given features themes, calendar, projects are enabled
  And I go to the admin event list page
  Then I click on create button
  And I should be on "/admin/capco/app/event/create"
  And I wait "#event_title" to appear on current page
  Then fill in "event_title" with "test"
  And I fill the authors field with name 'adminpro'
  And I fill the address field
  Then I should see "adminpro"
  And I fill in "event_body" with "My body"
  And I fill date field "#event_input_startAt" with value '2030-08-17 12:13:14'
  And I attach the file "/var/www/features/files/image.jpg" to "event_media_field"
  And I fill the theme filter with value "Immobilier"
  And I fill the project filter with value 'Croissance'
  And I fill in "custom.metadescription" with "Common on a react event to speak about how to fire our president"
  And I fill in "custom.customcode" with "<script>console.log('I m an xss fail');</script>"
  Then I click on button "#confirm-event-create"
  And I wait 1 seconds
  Then I visited eventpage with:
    | slug | test |
  And I should see "samedi 17 ao\u00fbt 2030"

@database
Scenario: Logged in admin wants to edit an event
  Given I am logged in as admin
  Given features themes, projects are enabled
  And I go to admin event page with eventId "event10"
  And I wait "#event_title" to appear on current page
  And I wait "#confirm-event-edit" to appear on current page

@database
Scenario: Logged in admin wants to review an event
  Given I am logged in as admin
  Given features themes, projects are enabled
  And I go to admin event page with eventId "eventCreateByAUserReviewAwaiting"
  And I wait "#event_title" to appear on current page
  And I wait "#confirm-event-edit" to appear on current page

@database
Scenario: Logged in admin wants to review an event approved
  Given I am logged in as admin
  Given features themes, projects are enabled
  And I go to admin event page with eventId "eventCreateByAUserReviewApproved"
  And I wait "#event_title" to appear on current page

@database
Scenario: Logged in admin wants to review an event refused
  Given I am logged in as admin
  Given features themes, projects are enabled
  And I go to admin event page with eventId "eventCreateByAUserReviewRefused"
  And I wait "#event_title" to appear on current page

@database
Scenario: Logged in admin wants to edit an event; feature allow_users_to_propose_events is not enabled
  Given I am logged in as admin
  Given features themes, projects are enabled
  And I go to admin event page with eventId "event10"
  And I wait "#event_title" to appear on current page
  Then fill in "event_title" with "edit"
  And I fill the authors field with name 'admin'
  And I fill the address field
  And I fill in "event_body" with "My body"
  And I fill date field "#event_input_startAt" with value '2050-08-17 12:13:14'
  And I attach the file "/var/www/features/files/image.jpg" to "event_media_field"
  And I fill the theme filter with value "Immobilier"
  And I fill the project filter with value 'Croissance'
  And I fill in "custom.metadescription" with "Common on a react event to speak about how to fire our president"
  And I fill in "custom.customcode" with "<script>console.log('I m an xss fail');</script>"
  Then I click on button "#confirm-event-edit"

@database
Scenario: Logged in super admin wants to delete an event
  Given I am logged in as super admin
  Given features themes, projects are enabled
  And I go to admin event page with eventId "event10"
  And I wait "#delete-event" to appear on current page
  When I click on button "#delete-event"
  Then I should see "event.alert.delete"
  And I click on button "#delete-modal-button-delete"
  And I wait 3 seconds
  Then I should be redirected to "/admin/capco/app/event/list"
  And I should not see "event10"

@database
Scenario: Logged in admin wants to delete an event
  Given I am logged in as admin
  Given features themes, projects are enabled
  And I go to admin event page with eventId "event10"
  And I should not see "global.delete"

@database
Scenario: Logged in admin wants to delete his event
  Given I am logged in as admin
  Given features themes, projects are enabled
  And I go to admin event page with eventId "event4"
  And I wait "#delete-event" to appear on current page
  When I click on button "#delete-event"
  Then I should see "event.alert.delete"
  And I click on button "#delete-modal-button-delete"
  And I wait 3 seconds
  Then I should be redirected to "/admin/capco/app/event/list"
  And I should not see "event4"

@database @rabbimq
Scenario: Logged in admin want to approve an awaiting event
  Given I am logged in as admin
  Given features themes, projects, allow_users_to_propose_events, calendar are enabled
  And I go to admin event page with eventId "eventCreateByAUserReviewAwaiting"
  And I wait "#event_title" to appear on current page
  And event fields should be disabled
  Then I click on button "#approved_button"
  Then I click on button "#confirm-event-edit"
  And I wait "global.saved" to appear on current page in "body"
  And I visited "events page"
  And I wait ".eventPreview" to appear on current page
  And I should see "event Create By user with review in awaiting"

@database @rabbimq
Scenario: Logged in admin want to refused an awaiting event
  Given I am logged in as admin
  Given features themes, projects, allow_users_to_propose_events, calendar are enabled
  And I go to admin event page with eventId "eventCreateByAUserReviewAwaiting"
  And I wait "#event_title" to appear on current page
  And event fields should be disabled
  Then I click on button "#refused_button"
  And I wait "#event_refusedReason" to appear on current page
  And I select "spam" as refused reason
  Then I click on button "#confirm-event-edit"
  And I wait "global.saved" to appear on current page in "body"
  And I visited "events page"
  And I wait ".eventPreview" to appear on current page
  And I should not see "event Create By user with review in awaiting"

Scenario: Logged in admin want to moderate a refused event
  Given I am logged in as admin
  Given features themes, projects, allow_users_to_propose_events, calendar are enabled
  And I go to admin event page with eventId "eventCreateByAUserReviewRefused"
  And I wait "#event_title" to appear on current page
  And event fields should be disabled
  And event moderation should be disabled
  And I should not see an "#confirm-event-edit" element

Scenario: Logged in admin want to moderate a accepted event
  Given I am logged in as admin
  Given features themes, projects, allow_users_to_propose_events, calendar are enabled
  And I go to admin event page with eventId "eventCreateByAUserReviewApproved"
  And I wait "#event_title" to appear on current page
  And event fields should be disabled
  And event moderation should be disabled

@database @rabbitmq
Scenario: Logged in super admin want to moderate a accepted event
  Given I am logged in as super admin
  Given features themes, projects, allow_users_to_propose_events, calendar are enabled
  And I go to admin event page with eventId "eventCreateByAUserReviewApproved"
  And I wait "#event_title" to appear on current page
  And I should see an "#confirm-event-edit" element
  Then I click on button "#refused_button"
  And I wait "#event_refusedReason" to appear on current page
  And I select "spam" as refused reason
  Then I click on button "#confirm-event-edit"
  And I wait "global.saved" to appear on current page in "body"
