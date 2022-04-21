@emails
Feature: Email

  @security
  Scenario: Registration is disabled and API client wants to resend an email
    When I send a POST request to "/api/account/resend_confirmation_email"
    Then the JSON response status code should be 404
    And the JSON response should match:
    """
    {
      "code":404,
      "message": "Cette fonction n'est pas activ\u00e9e, veuillez l'activer dans l'espace d'administration !",
      "errors": null
    }
    """

  @security
  Scenario: Anonymou API client wants resend an email
    Given feature "registration" is enabled
    When I send a POST request to "/api/account/resend_confirmation_email"
    Then the JSON response status code should be 401
    And the JSON response should match:
    """
    {
      "code": 401,
      "message": "Invalid credentials"
    }
    """

  @security
  Scenario: Confirmed and logged in API client wants resend an email
    Given feature "registration" is enabled
    And I am logged in to api as user
    When I send a POST request to "/api/account/resend_confirmation_email"
    Then the JSON response status code should be 400
    And the JSON response should match:
    """
    {
      "code": 400,
      "message": "Already confirmed.",
      "errors": null
    }
    """

  @database
  Scenario: Not confirmed logged in API client wants resend a confirmation email
    Given feature "registration" is enabled
    And I am logged in to api as user_not_confirmed
    When I send a POST request to "/api/account/resend_confirmation_email"
    Then the JSON response status code should be 201
    Then 1 mail should be sent
    And I purge mails
    When I send a POST request to "/api/account/resend_confirmation_email"
    Then the JSON response status code should be 400
    And the JSON response should match:
    """
    {
      "code": 400,
      "message": "Email already sent less than a minute ago.",
      "errors":null
    }
    """
    And 0 mail should be sent

  @database
  Scenario: Not confirmed logged in API client can receive a new confirmation email
    Given feature "registration" is enabled
    And I am logged in to api as user_not_confirmed
    And I send a POST request to "/api/account/resend_confirmation_email"
    Then the JSON response status code should be 201
    And 1 mail should be sent
    And I open mail with subject "Cap-Collectif — Confirmez votre adresse électronique"
    Then I should see "Confirmer mon adresse électronique" in mail
    Then I should see "/account/email_confirmation/azertyuiop" in mail

  @database @security
  Scenario: Not confirmed logged in API client wants to mass spam confirmation email
    Given feature "registration" is enabled
    And I am logged in to api as user_not_confirmed
    And I send a POST request to "/api/account/resend_confirmation_email"
    And 1 mail should be sent
    And I purge mails
    When I send a POST request to "/api/account/resend_confirmation_email"
    Then the JSON response status code should be 400
    And the JSON response should match:
    """
    {
      "code": 400,
      "message": "Email already sent less than a minute ago.",
      "errors":null
    }
    """
    And 0 mail should be sent

    @database
    Scenario: Logged in API client can update his email
      And I am logged in to api as user
      And I send a PUT request to "/api/users/me" with json:
      """
      {
        "email": "popopo@test.com",
        "password": "user"
      }
      """
      Then the JSON response status code should be 204
      And 1 mail should be sent
      And I open mail with subject "[Cap-Collectif] Veuillez confirmer votre nouvelle adresse électronique"
      Then I should see "Confirmer mon adresse électronique" in mail
      Then I should see "/account/new_email_confirmation/" in mail
