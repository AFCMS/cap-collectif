@contact
Feature: Contact

@javascript
Scenario: User wants to send a message via contact page
  Given I visited "contact page"
  When I fill in the following:
    | contact_name         | Mwa                                      |
    | contact_email        | rpz91300@msn.com                         |
    | contact_message      | Hello ! Votre site il est trop swag ! ^^ Pourquoi vous m'avez pas invité au hackaton :'( ? Je suis votre plus grand fan ! Maxime tu me reçois ? |
  And I press "contact.submit"
  Then I should see "contact.email.sent_success" in the "#main" element

@javascript
Scenario: User wants to send a message via contact page with wrong email
  Given I visited "contact page"
  When I fill in the following:
    | contact_name         | Mwa                                      |
    | contact_email        | coucou je suis un imbécile               |
    | contact_message      | Hello ! Votre site il est trop swag ! ^^ |
  And I press "contact.submit"
  Then I should see "comment.constraints.author_email" in the "#main" element

@javascript
Scenario: User wants to send a message via contact page without filling fields
  Given I visited "contact page"
  When I press "contact.submit"
  Then I should see "contact.no_name" in the "#main" element
  And I should see "contact.no_email" in the "#main" element
  And I should see "contact.no_message" in the "#main" element
