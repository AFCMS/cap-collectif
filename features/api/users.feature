@users
Feature: Users

  @parallel-scenario
  Scenario: API client wants to know the number of users
    When I send a GET request to "/api/users"
    Then the JSON response should match:
    """
    {
      "count": "@integer@.greaterThan(0)"
    }
    """

  @parallel-scenario
  Scenario: API client wants to know the number of citoyens
    When I send a GET request to "/api/users?type=citoyen"
    Then the JSON response should match:
    """
    {
      "count": "@integer@.greaterThan(0)"
    }
    """

  @parallel-scenario
  Scenario: API client wants to know the number of citoyens who registered since 2011-11-23
    When I send a GET request to "/api/users?type=citoyen&from=2016-11-23T00:00:00"
    Then the JSON response should match:
    """
    {
      "count": 0
    }
    """

  @security
  Scenario: Anonymous API client wants to register but registration is not enabled
    When I send a POST request to "/api/users" with json:
    """
    {
      "username": "user2",
      "email": "user2@test.com",
      "plainPassword": "supersecureuserpass"
    }
    """
    Then the JSON response status code should be 404
    And the JSON response should match:
    """
    {"code":404,"message":"Cette fonction n'est pas activ\u00e9e, veuillez l'activer dans l'espace d'administration !","errors":null}
    """

  # Note: captcha validation is disabled in test environement
  @database
  Scenario: Anonymous API client wants to register
    Given feature "registration" is enabled
    When I send a POST request to "/api/users" with json:
    """
    {
      "username": "user2",
      "email": "user2@test.com",
      "plainPassword": "supersecureuserpass",
      "captcha": "fakekey"
    }
    """
    Then the JSON response status code should be 201

    @security
    Scenario: Anonymous API client wants to register with throwable email
      Given feature "registration" is enabled
      When I send a POST request to "/api/users" with json:
      """
      {
        "username": "user2",
        "email": "user2@yopmail.com",
        "plainPassword": "supersecureuserpass",
        "captcha": "blabla"
      }
      """
      Then the JSON response status code should be 400
      Then the JSON response should match:
      """
      {
        "code":400,
        "message":"Validation Failed",
        "errors":{
          "children":{
            "username":[],
            "email":{
              "errors": ["email.throwable"]
            },
            "plainPassword":[],
            "captcha":[]
          }
        }
      }
      """

    @security
    Scenario: Anonymous API client wants to register with additional data
      Given feature "registration" is enabled
      When I send a POST request to "/api/users" with json:
      """
      {
        "username": "user2",
        "email": "user2@test.com",
        "plainPassword": "supersecureuserpass",
        "captcha": "fakekey",
        "userType": 1,
        "zipcode": "99999"
      }
      """
      Then the JSON response status code should be 400
      Then the JSON response should match:
      """
      {
        "code":400,
        "message": "Validation Failed",
        "errors": {
          "errors": ["Ce formulaire ne doit pas contenir des champs suppl\u00e9mentaires."],
          "children":{
            "username":[],
            "email":[],
            "plainPassword":[],
            "captcha":[]
          }
        }
      }
      """

    @database
    Scenario: Anonymous API client wants to register with zipcode and type
      Given features "registration", "user_type", "zipcode_at_register" are enabled
      When I send a POST request to "/api/users" with json:
      """
      {
        "username": "user2",
        "email": "user2@test.com",
        "plainPassword": "supersecureuserpass",
        "captcha": "fakekey",
        "userType": 1,
        "zipcode": "99999"
      }
      """
      Then the JSON response status code should be 201

    @database @dev
    Scenario: Admin API client can register an other admin
    Given feature "registration" is enabled
    And I am logged in to api as admin
    When I send a POST request to "/api/users" with json:
    """
    {
      "username": "admin2",
      "email": "admin2@test.com",
      "roles": ["USER_ADMIN"]
    }
    """
    Then the JSON response status code should be 201
    Then the JSON response should match:
    """
    {
      "id": @integer@,
      "_links": @...@
    }
    """
    # And "admin2" password should have been generated
    # And 1 mail should be sent
    # And I open mail with subject "Cap-Collectif — "
    # Then I should see "Confirmer mon adresse électronique" in mail
    # Then I should see "/email-confirmation/azertyuiop" in mail

  @security
  Scenario: API client wants to update his phone
    When I send a PUT request to "/api/users/me" with json:
    """
    {
      "phone": "+33628353290"
    }
    """
    Then the JSON response status code should be 401
    Then the JSON response should match:
    """
    {"code":401,"message":"Invalid credentials"}
    """

  @database
  Scenario: API client wants to update his phone
    Given I am logged in to api as user
    When I send a PUT request to "/api/users/me" with json:
    """
    {
      "phone": "+33628353290"
    }
    """
    Then the JSON response status code should be 204
    And "user" phone number should be "+33628353290"
    And "user" should not be sms confirmed

    @security
    Scenario: API client wants to update his phone
      Given I am logged in to api as user
      When I send a PUT request to "/api/users/me" with json:
      """
      {
        "phone": "+33"
      }
      """
      Then the JSON response status code should be 400
      Then the JSON response should match:
      """
      {
        "code":400,
        "message": "Validation Failed",
        "errors": {
          "children":{
            "phone": {"errors": ["Cette valeur n'est pas un numéro de téléphone valide."]}
          }
        }
      }
      """
