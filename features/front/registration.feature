@registration
Feature: Registration

@database @javascript
Scenario: Anonymous wants to register with user type and zipcode
  Given features "registration", "user_type", "zipcode_at_register" are enabled
  And I visited "home page"
  When I press "global.registration"
  And I fill in the following:
  | username             | Naruto42             |
  | email                | naruto42@gmail.com   |
  | password             | narutoisthebest      |
  | zipcode              | 94123                |
  | dynamic-6            | plop                 |
  And I select "Citoyen" from "user_type"
  And I select "Sangohan" from "dynamic-17"
  And I check "charte"
  And I press "global.register"
  Then I wait 5 seconds
  Then I can see I am logged in as "Naruto42"

@database @javascript
Scenario: Anonymous wants to register
  Given feature "registration" is enabled
  And I visited "home page"
  When I press "global.registration"
  And I fill in the following:
    | username             | Naruto42             |
    | email                | naruto42@gmail.com   |
    | password             | narutoisthebest      |
    | dynamic-6            | plop                 |
  And I select "Sangohan" from "dynamic-17"
  And I check "charte"
  And I press "global.register"
  Then I wait 5 seconds
  Then I can see I am logged in as "Naruto42"

@javascript @security
Scenario: Anonymous wants to register with every possible errors
  Given features "registration", "user_type", "zipcode_at_register" are enabled
  And I visited "home page"
  When I press "global.registration"
  And I fill in the following:
  | username             | p                    |
  | email                | poupouil.com         |
  | password             | 1234                 |
  | zipcode              | 94                   |
  And I press "global.register"
  Then I should see "registration.constraints.username.min"
  And I should see "registration.constraints.email.invalid"
  And I should see "registration.constraints.password.min"

@database @javascript
Scenario: Anonymous wants to register with the consent of external communication
  Given feature "registration" is enabled
  Given feature "consent_external_communication" is enabled
  And I visited "home page"
  When I press "global.registration"
  And I fill in the following:
    | username             | Naruto42             |
    | email                | naruto42@gmail.com   |
    | password             | narutoisthebest      |
    | dynamic-6            | plop                 |
  And I select "Sangohan" from "dynamic-17"
  And I check "charte"
  And I check "consentExternalCommunication"
  And I press "global.register"
  Then I wait 5 seconds
  Then I can see I am logged in as "Naruto42"
