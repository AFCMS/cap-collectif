@proposals
Feature: Proposals

# Collect step : See proposals with filters, sorting and search term
@javascript @elasticsearch
Scenario: Anonymous user wants to see proposals in a collect step and apply filters
  Given features themes, districts are enabled
  And I go to an open collect step
  Then there should be 6 proposals
  And I change the proposals theme filter
  Then there should be 5 proposals

@javascript @elasticsearch
Scenario: Anonymous user wants to see proposals in a private collect step
  Given I go to a private open collect step
  Then there should be 0 proposals

@javascript @elasticsearch
Scenario: Logged in user wants to see its proposals in a private collect step
  Given I am logged in as user
  And I go to a private open collect step
  Then there should be 2 proposals

@javascript @elasticsearch
Scenario: Anonymous user wants to see proposals in a collect step and sort them
  Given I go to an open collect step
  Then proposals should be ordered randomly
  When I sort proposals by date
  Then proposals should be ordered by date
  When I sort proposals by comments
  Then proposals should be ordered by comments

@javascript @elasticsearch
Scenario: Anonymous user wants to see last proposals when he returns on the list of proposals
  Given I go to an open collect step
  Then proposals should be ordered randomly
  When I save current proposals
  Then I go to an open collect step
  When proposals should be ordered randomly
  Then I should see same proposals

@javascript @elasticsearch
Scenario: Anonymous user wants to search a proposal with the random filter
  Given I go to an open collect step
  Then proposals should be ordered randomly
  When I search for proposals with terms "plantation"
  Then I should not see random row

@javascript @elasticsearch
Scenario: Anonymous user wants to see other random proposals
  Given I go to an open collect step
  Then proposals should be ordered randomly
  When I save current proposals
  Then I click the 'a[href="#proposals-list"]' element
  When I should see other proposals

@javascript @elasticsearch
Scenario: Anonymous user wants to see proposals in a collect step and search by term
  Given I go to an open collect step
  Then there should be 6 proposals
  When I search for proposals with terms "proposition"
  Then there should be 2 proposals
  Then proposals should be filtered by terms

@javascript @elasticsearch
Scenario: Anonymous user wants to see proposals in a collect step and search by reference
  Given I go to an open collect step
  Then there should be 6 proposals
  When I search for proposals with terms "1-7"
  Then there should be 1 proposals
  Then proposals should be filtered by references

@javascript @elasticsearch
Scenario: Anonymous user wants to see proposals in a collect step and search by term but find no ones
  Given I go to an open collect step
  Then there should be 6 proposals
  When I search for proposals with terms "toto"
  Then there should be 0 proposals
  Then proposals should have no results

@javascript @elasticsearch
Scenario: Anonymous user combine search, filters and sorting on proposals in a collect step
  Given features themes, districts are enabled
  And I am logged in as user
  And I go to an open collect step
  Then there should be 6 proposals
  When I sort proposals by comments
  And I search for proposals with terms "proposition"
  And I change the proposals theme filter
  Then there should be 2 proposals
  Then proposals should be filtered by theme and terms and sorted by comments

@javascript @elasticsearch
Scenario: Anonymous user wants to see proposals likers
  Given I go to an open collect step
  Then I should see the proposal likers

# CRUD

@database @javascript @elasticsearch
Scenario: Logged in user wants to create a proposal
  Given feature "districts" is enabled
  And I am logged in as user
  And I go to an open collect step
  Then there should be 6 proposals
  When I click the create proposal button
  And I fill the proposal form
  And I attach the file "/var/www/features/files/image.jpg" to "proposal_media_field"
  And I attach the file "/var/www/features/files/document.pdf" to "proposal_custom-11_field"
  And I submit the create proposal form
  Then I wait 3 seconds
  And I should see my new proposal

@database @javascript @elasticsearch
Scenario: Logged in user wants to create a proposal with theme
  Given features themes, districts are enabled
  And I am logged in as user
  And I go to an open collect step
  Then there should be 6 proposals
  When I click the create proposal button
  And I fill the proposal form with a theme
  And I submit the create proposal form
  Then I wait 3 seconds
  And I should see my new proposal

@javascript @security
Scenario: Logged in user wants to create a proposal without providing required response
  Given feature "districts" is enabled
  And I am logged in as user
  And I go to an open collect step
  When I click the create proposal button
  And I fill the proposal form without required response
  And I submit the create proposal form
  Then I should see "proposal.constraints.field_mandatory"

@javascript @security
Scenario: Logged in user wants to create a proposal in closed collect step
  Given I am logged in as user
  And I go to a closed collect step
  Then I should see "step.collect.alert.ended.title"
  Then I should see "step.collect.alert.ended.text"
  And the create proposal button should be disabled

@javascript @security @elasticsearch
Scenario: Anonymous user wants to create a proposal
  Given I go to an open collect step
  When I click the create proposal button
  Then I should see "user.login.popover.body" in the "#main" element

@javascript @database
Scenario: Author of a proposal wants to update it
  Given I am logged in as user
  And I go to a proposal
  When I click the edit proposal button
  And I change the proposal title
  And I submit the edit proposal form
  # Then I should see "Merci ! Votre proposition a bien été modifiée."
  Then the proposal title should have changed

@javascript
Scenario: Non author of a proposal wants to update it
  Given I am logged in as admin
  And I go to a proposal
  Then I should not see the edit proposal button

@javascript @database @elasticsearch
Scenario: Author of a proposal wants to delete it
  Given I am logged in as user
  And I go to an open collect step
  Then there should be 6 proposals
  And I go to a proposal
  When I click the delete proposal button
  And I confirm proposal deletion
  Then there should be 5 proposals
  And I should not see my proposal anymore

@javascript @database
Scenario: Admin should be notified when an user deletes his proposal on an notifiable proposal
  Given I am logged in as user
  And I go to a proposal which is notifiable
  When I click the delete proposal button
  And I confirm proposal deletion
  And I wait 3 seconds
  Then 1 mails should be sent
  And I should see mail with subject 'notification.email.proposal.delete.subject {"%sitename":"Cap-Collectif","%username%":"user","%project%":"Budget Participatif Rennes"}'

@javascript @database
Scenario: Admin should not be notified when an user deletes his proposal on an non notifiable proposal
  Given I am logged in as user
  And I go to a proposal which is not notifiable
  When I click the delete proposal button
  And I confirm proposal deletion
  Then 0 mails should be sent

@javascript
Scenario: Non author of a proposal wants to delete it
  Given I am logged in as admin
  And I go to a proposal
  Then I should not see the delete proposal button

# Proposal page

@javascript @database
Scenario: Anonymous user should not see private fields on a proposal
  Given I go to a proposal
  Then I should not see the proposal private field

@javascript @database
Scenario: Non author should not see private fields on a proposal
  Given I am logged in as drupal
  When I go to a proposal
  Then I should not see the proposal private field

@javascript @database
Scenario: Logged in user should see private fields on his proposal
  Given I am logged in as user
  And I go to a proposal
  Then I should see the proposal private field

@javascript @database
Scenario: Admin should see private fields on a proposal
  Given I am logged in as admin
  And I go to a proposal
  Then I should see the proposal private field

# Reporting

@javascript @database
Scenario: Logged in user wants to report a proposal
  Given feature "reporting" is enabled
  And I am logged in as admin
  And I go to a proposal
  When I click the report proposal button
  And I fill the reporting form
  And I submit the reporting form
  Then I should see "alert.success.report.proposal" in the "#global-alert-box"

# Sharing

@javascript @database
Scenario: Anonymous user wants to share a proposal
  Given feature "share_buttons" is enabled
  And I go to a proposal
  When I click the share proposal button
  Then I should see the share dropdown
  And I click the share link button
  Then I should see the share link modal

# Selection step : See proposals with filters, sorting and search term

@javascript @elasticsearch
Scenario: Anonymous user wants to see proposals in a selection step and apply filters
  Given feature "themes" is enabled
  When I go to a selection step with simple vote enabled
  Then there should be 3 proposals
  And I change the proposals theme filter
  Then there should be 2 proposals

@javascript @elasticsearch
Scenario: Anonymous user wants to see proposals in a selection step and sort them
  Given I go to a selection step with simple vote enabled
  Then proposals should be ordered randomly
  When I sort proposals by date
  Then proposals should be ordered by date
  When I sort proposals by comments
  Then proposals should be ordered by comments

@javascript @elasticsearch
Scenario: Anonymous user wants to see saved proposals when he returns on the selection of proposals
  Given I go to a selection step
  Then proposals should be ordered randomly
  When I save current proposals
  Then I go to a selection step
  When proposals should be ordered randomly
  Then I should see same proposals
