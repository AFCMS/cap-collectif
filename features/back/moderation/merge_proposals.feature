@admin @proposal @merge_proposals @moderation
Feature: Merge proposals

@database @elasticsearch
Scenario: Logged in admin wants create a proposal from a merge of 2 proposals
  Given I am logged in as admin
  And I go to the admin proposals list page
  When I click the create merge button
  And I fill the proposal merge form
  And I submit the create merge form
  And I wait "#proposal-admin-page-tabs" to appear on current page

# @database @elasticsearch
# Scenario: Logged in admin wants update a fusion
#   Given I am logged in as admin
#   And I go to the admin proposals page
#   When I click the create merge button
#   And I fill the proposal merge form
#   And I submit the create merge form
#   And I wait 2 seconds
#   Then I should be redirected to a merge proposal
