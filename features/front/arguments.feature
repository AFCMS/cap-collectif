@arguments
Feature: Arguments

## CRUD

## Create in opinion

@database @javascript
Scenario: Can create an argument in contribuable opinion
  Given I am logged in as user
  And I go to an opinion
  When I submit an argument
  Then I should see "alert.success.add.argument" in the "#global-alert-box" element
  And I should see my new argument

@javascript @security
Scenario: Argument in opinion must be at least 3 chars long
  Given I am logged in as user
  And I go to an opinion
  When I submit a too short argument
  Then I should see "argument.constraints.min" in the "#main" element

@javascript @security
Scenario: Argument in opinion must be at most 2000 chars long
  Given I am logged in as user
  And I go to an opinion
  When I submit a too long argument
  Then I should see "argument.constraints.max" in the "#main" element

@javascript @security
Scenario: Can not create an argument in opinion when step is closed
  Given I am logged in as user
  And I go to an opinion in a closed step
  Then I should see the argument creation boxes disabled

## Create in version

@database @javascript
Scenario: Can create an argument in contribuable version
  Given I am logged in as user
  And I go to a version
  When I submit an argument
  Then I should see "alert.success.add.argument" in the "#global-alert-box" element
  And I should see my new argument

@javascript @security
Scenario: Argument in a version must be at least 3 chars long
  Given I am logged in as user
  And I go to a version
  When I submit a too short argument
  Then I should see "argument.constraints.min" in the "#main" element

@javascript @security
Scenario: Argument in a version must be at most 2000 chars long
  Given I am logged in as user
  And I go to a version
  When I submit a too long argument
  Then I should see "argument.constraints.max" in the "#main" element

@javascript @security
Scenario: Can not create an argument in a version when step is closed
  Given I am logged in as user
  And I go to an opinion version in a closed step
  Then I should see the argument creation boxes disabled

## Update in opinion

@javascript @database
Scenario: Author of an argument on an opinion looses his votes when updating it
  Given I am logged in as user
  And I go to an opinion
  When I edit my argument
  Then I should see "alert.success.update.argument" in the "#global-alert-box" element
  And my argument should have changed
  And my argument should have lost its votes

@javascript @security
Scenario: Author of an argument on an opinion wants to update it without checking the confirm checkbox
  Given I am logged in as user
  And I go to an opinion
  When I edit my argument without confirming my votes lost
  Then I should see "argument.constraints.confirm" in the "#argument-form" element

@javascript @security
Scenario: Non author of an argument on an opinion wants to update it
  Given I am logged in as admin
  And I go to an opinion
  Then I should not see the argument edit button

@javascript @security
Scenario: Anonymous wants to update an argument on an opinion
  Given I go to an opinion
  Then I should not see the argument edit button

## Update in version

@javascript @database
Scenario: Author of an argument on a version looses his votes when updating it
  Given I am logged in as user
  And I go to a version
  When I edit my argument
  Then I should see "alert.success.update.argument" in the "#global-alert-box" element
  And my argument should have changed
  And my argument should have lost its votes

@javascript @security
Scenario: Author of an argument on a version wants to update it without checking the confirm checkbox
  Given I am logged in as user
  And I go to a version
  When I edit my argument without confirming my votes lost
  Then I should see "argument.constraints.confirm" in the "#argument-form" element

@javascript @security
Scenario: Non author of an argument on a version wants to update it
  Given I am logged in as admin
  And I go to a version
  Then I should not see the argument edit button

@javascript @security
Scenario: Anonymous wants to update an argument on a version
  Given I go to a version
  Then I should not see the argument edit button

## Delete from opinion

@javascript @security @database
Scenario: Author of an argument on an opinion wants to delete it
  Given I am logged in as user
  And I go to an opinion
  When I delete my argument
  Then I should see "alert.success.delete.argument" in the "#global-alert-box" element
  And I should not see my argument anymore

@javascript @security
Scenario: Non author of an argument on an opinion wants to delete it
  Given I am logged in as admin
  And I go to an opinion
  Then I should not see the argument delete button

@javascript @security
Scenario: Anonymous wants to delete an argument on an opinion
  Given I go to an opinion
  Then I should not see the argument delete button

## Delete from version

@javascript @database
Scenario: Author of an argument on a version wants to delete it
  Given I am logged in as user
  And I go to a version
  When I delete my argument
  Then I should see "alert.success.delete.argument" in the "#global-alert-box" element
  And I should not see my argument anymore

@javascript @security
Scenario: Non author of an argument on a version wants to delete it
  Given I am logged in as admin
  And I go to a version
  Then I should not see the argument delete button

@javascript @security
Scenario: Anonymous wants to delete an argument on a version
  Given I go to a version
  Then I should not see the argument delete button

## Votes from opinion

@javascript @database
Scenario: Logged in user wants to vote for an argument on an opinion then delete his vote
  Given I am logged in as admin
  And I go to an opinion
  When I vote for the argument
  Then I should see "alert.success.add.vote" in the "#global-alert-box" element
  When I delete my vote on the argument
  Then I should see "alert.success.delete.vote" in the "#global-alert-box" element

@javascript @security
Scenario: Logged in user wants to vote for his own argument on an opinion
  Given I am logged in as user
  And I go to an opinion
  Then the argument vote button should be disabled

@javascript @security
Scenario: Logged in user wants to vote for an argument on an opinion in a closed step
  Given I am logged in as user
  And I go to an opinion in a closed step
  Then the argument vote button should be disabled

@javascript @security
Scenario: Anonymous user wants to vote for an argument on an opinion
  Given I go to an opinion
  When I click the argument vote button
  Then I should see a "#login-popover" element

## Votes from version

@javascript @database
Scenario: Logged in user wants to vote for an argument on a version then delete his vote
  Given I am logged in as admin
  And I go to a version
  When I vote for the argument
  Then I should see "alert.success.add.vote" in the "#global-alert-box" element
  When I delete my vote on the argument
  Then I should see "alert.success.delete.vote" in the "#global-alert-box" element

@javascript @security
Scenario: Logged in user wants to vote for his own argument on a version
  Given I am logged in as user
  And I go to a version
  Then the argument vote button should be disabled

@javascript @security
Scenario: Logged in user wants to vote for an argument on a version in a closed step
  Given I am logged in as user
  And I go to an opinion version in a closed step
  Then the argument vote button should be disabled

@javascript @security
Scenario: Anonymous user wants to vote for an argument on a version
  Given I go to a version
  When I click the argument vote button
  Then I should see a "#login-popover" element

# Reporting

@javascript @database
Scenario: Non author of an argument can report it
  Given feature "reporting" is enabled
  And I am logged in as admin
  And I go to an opinion
  And I click the argument report button
  And I fill the reporting form
  And I submit the reporting form
  Then I should see "alert.success.report.argument" in the "#global-alert-box" element
