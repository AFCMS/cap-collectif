@replies
Feature: Reply Restful Api
  As an API client

  @parallel-scenario @elasticsearch
  Scenario: Anonymous API client wants to get one reply
    When I send a GET request to "/api/questionnaires/1/replies/1"
    Then the JSON response should match:
    """
    {
      "id": @integer@,
      "enabled": @boolean@,
      "createdAt": "@string@.isDateTime()",
      "updatedAt": "@string@.isDateTime()",
      "author": {
        "username": @string@,
        "displayName": @string@,
        "uniqueId": @string@,
        "isAdmin": @boolean@,
        "media": @...@,
        "user_type": {
          "id": @integer@,
          "name": @string@,
          "slug": @string@
        },
        "vip": @boolean@,
        "_links": {
          "profile": @string@,
          "settings": @string@
        }
      },
      "responses": [
        {
          "id": @integer@,
          "value": @string@,
          "field": {
            "id": @integer@,
            "question": @string@,
            "type": @string@,
            "helpText": @...@,
            "slug": @string@,
            "required": @boolean@
          },
          "updated_at": "@string@.isDateTime()"
        },
        @...@
      ],
      "private": @boolean@
    }
    """

  @parallel-scenario @elasticsearch
  Scenario: Logged in API client wants to get his replies
    Given I am logged in to api as admin
    When I send a GET request to "/api/questionnaires/1/replies"
    Then the JSON response should match:
    """
    {
      "replies": [
        {
          "id": @integer@,
          "enabled": @boolean@,
          "createdAt": "@string@.isDateTime()",
          "updatedAt": "@string@.isDateTime()",
          "author": {
            "username": @string@,
            "displayName": @string@,
            "uniqueId": @string@,
            "isAdmin": @boolean@,
            "media": @...@,
            "user_type": {
              "id": @integer@,
              "name": @string@,
              "slug": @string@
            },
            "vip": @boolean@,
            "_links": {
              "profile": @string@,
              "settings": @string@
            }
          },
          "responses": [
            {
              "id": @integer@,
              "value": @string@,
              "field": {
                "id": @integer@,
                "question": @string@,
                "type": @string@,
                "helpText": @...@,
                "slug": @string@,
                "required": @boolean@
              },
              "updated_at": "@string@.isDateTime()"
            },
            @...@
          ],
          "private": @boolean@
        }
      ]
    }
    """

  @database @elasticsearch
  Scenario: Logged in API client wants to add a reply
    Given I am logged in to api as user
    When I send a POST request to "/api/questionnaires/1/replies" with json:
    """
    {
      "responses": [
        {
          "question": 2,
          "value": "Je pense que c'est la ville parfaite pour organiser les JO"
        },
        {
          "question": 6,
          "value": [2, 3]
        }
      ]
    }
    """
    Then the JSON response status code should be 201

  @database @elasticsearch
  Scenario: Logged in API client wants to add an anonymous reply
    Given I am logged in to api as user
    When I send a POST request to "/api/questionnaires/1/replies" with json:
    """
    {
      "responses": [
        {
          "question": 2,
          "value": "Je pense que c'est la ville parfaite pour organiser les JO"
        },
        {
          "question": 6,
          "value": [2, 3]
        }
      ],
      "private": true
    }
    """
    Then the JSON response status code should be 201

  @security @elasticsearch
  Scenario: Anonymous API client wants to add a reply
    Given I send a POST request to "/api/questionnaires/1/replies" with json:
    """
    {
      "responses": [
        {
          "question": 2,
          "value": "Je pense que c'est la ville parfaite pour organiser les JO"
        },
        {
          "question": 6,
          "value": [2, 3]
        }
      ]
    }
    """
    Then the JSON response status code should be 401

  @security @elasticsearch
  Scenario: Logged in API client wants to add a reply without a required response
    Given I am logged in to api as user
    When I send a POST request to "/api/questionnaires/1/replies" with json:
    """
    {
      "responses": [
        {
          "question": 6,
          "value": [2, 3]
        }
      ]
    }
    """
    Then the JSON response status code should be 400
    And the JSON response should match:
    """
    {
      "code": 400,
      "message": "Validation Failed",
      "errors": {
        "errors": ["Veuillez répondre à toutes les questions obligatoires pour soumettre votre réponse."],
        "children": @...@
      }
    }
    """

  @security @elasticsearch
  Scenario: Logged in API client wants to add a reply with not enough choices for field with validation rules
    Given I am logged in to api as user
    When I send a POST request to "/api/questionnaires/1/replies" with json:
    """
    {
      "responses": [
        {
          "question": 6,
          "value": [2]
        }
      ]
    }
    """
    Then the JSON response status code should be 400
    And the JSON response should match:
    """
    {
      "code": 400,
      "message": "Validation Failed",
      "errors": {
        "errors": ["Vous devez sélectionner au moins 3 réponses."],
        "children": @...@
      }
    }
    """

  @security @elasticsearch
  Scenario: Logged in API client wants to add a reply to closed questionnaire step
    Given I am logged in to api as user
    And I send a POST request to "/api/questionnaires/3/replies" with json:
    """
    {
      "responses": [
        {
          "question": 2,
          "value": "Je pense que c'est la ville parfaite pour organiser les JO"
        },
        {
          "question": 6,
          "value": [2, 3]
        }
      ]
    }
    """
    Then the JSON response status code should be 400
    And the JSON response should match:
    """
    {
      "code": 400,
      "message": "You can no longer contribute to this questionnaire step.",
      "errors": @null@
    }
    """

  @database @elasticsearch
  Scenario: Logged in API client wants to add another reply when multiple replies is allowed
    Given I am logged in to api as admin
    When I send a POST request to "/api/questionnaires/1/replies" with json:
    """
    {
      "responses": [
        {
          "question": 2,
          "value": "Je pense que c'est la ville parfaite pour organiser les JO"
        },
        {
          "question": 6,
          "value": [2, 3]
        }
      ]
    }
    """
    Then the JSON response status code should be 201

  @security @elasticsearch
  Scenario: Logged in API client wants to add another reply when multiple replies is not allowed
    Given I am logged in to api as admin
    Given I send a POST request to "/api/questionnaires/2/replies" with json:
    """
    {
      "responses": [
        {
          "question": 2,
          "value": "Je pense que c'est la ville parfaite pour organiser les JO"
        },
        {
          "question": 6,
          "value": [2, 3]
        }
      ]
    }
    """
    Then the JSON response status code should be 400
    And the JSON response should match:
    """
    {
      "code": 400,
      "message": "Only one reply by user is allowed for this questionnaire.",
      "errors": @null@
    }
    """

  @database
  Scenario: logged in API client wants to edit a reply
    Given I am logged in to api as admin
    When I send a PUT request to "api/questionnaires/1/replies/2" with json:
    """
    {
      "responses": [
        {
          "question": 2,
          "value": "En fait c'est nul, je ne veux pas des JO à Paris"
        },
        {
          "question": 6,
          "value": [2, 3]
        }
      ]
    }
    """
    Then the JSON response status code should be 200

  @security
  Scenario: logged in API client wants to edit a reply when he is not the author
    Given I am logged in to api as user
    When I send a PUT request to "api/questionnaires/1/replies/2" with json:
    """
    {
      "responses": [
        {
          "question": 2,
          "value": "En fait c'est nul, je ne veux pas des JO à Paris"
        },
        {
          "question": 6,
          "value": [2, 3]
        }
      ]
    }
    """
    Then the JSON response status code should be 403

  @security
  Scenario: logged in API client wants to edit a reply when edition is not allowed
    Given I am logged in to api as admin
    When I send a PUT request to "api/questionnaires/2/replies/3" with json:
    """
    {
      "responses": [
        {
          "question": 2,
          "value": "En fait c'est nul, je ne veux pas des JO à Paris"
        },
        {
          "question": 6,
          "value": [2, 3]
        }
      ]
    }
    """
    Then the JSON response status code should be 400
    And the JSON response should match:
    """
    {
      "code": 400,
      "message": "Reply modification is not allowed for this questionnaire.",
      "errors": @null@
    }
    """

  @security
  Scenario: Logged in API client wants to edit a reply in a closed questionnaire step
    Given I am logged in to api as admin
    And I send a PUT request to "/api/questionnaires/3/replies/3" with json:
    """
    {
      "responses": [
        {
          "question": 2,
          "value": "Je pense que c'est la ville parfaite pour organiser les JO"
        },
        {
          "question": 6,
          "value": [2, 3]
        }
      ]
    }
    """
    Then the JSON response status code should be 400
    And the JSON response should match:
    """
    {
      "code": 400,
      "message": "This reply is no longer editable.",
      "errors": @null@
    }
    """

  @database @elasticsearch
  Scenario: logged in API client wants to remove a reply
    Given I am logged in to api as admin
    When I send a DELETE request to "api/questionnaires/1/replies/2"
    Then the JSON response status code should be 204

  @security @elasticsearch
  Scenario: logged in API client wants to remove a reply when he is not the author
    Given I am logged in to api as user
    When I send a DELETE request to "api/questionnaires/1/replies/2"
    Then the JSON response status code should be 403
