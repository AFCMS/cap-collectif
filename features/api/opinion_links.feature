@opinion_links
Feature: Opinions Links

  Scenario: API client wants to list links of an opinion
    When I send a GET request to "/api/opinions/60/links"
    Then the JSON response should match:
    """
    {
      "links": [
        {
          "id": @integer@,
          "title": @string@,

          "created_at": "@string@.isDateTime()",
          "updated_at": "@string@.isDateTime()",

          "versionsCount": @integer@,
          "sourcesCount": @integer@,
          "argumentsCount": @integer@,
          "connectionsCount": @integer@,
          "votesCount": @integer@,
          "votes_nok": @integer@,
          "votes_ok": @integer@,
          "votes_mitige": @integer@,

          "author": @...@,

          "type": @...@,

          "user_vote": @null@,
          "has_user_reported": @boolean@,

          "_links": {
            "show": @string@,
            "type": @string@
          }
        },
        @...@
      ]
    }
    """

  @security
  Scenario: logged in API client wants to add an opinion link
    Given I am logged in to api as user
    When I send a POST request to "/api/opinions/1/links" with a valid link opinion json
    Then the JSON response status code should be 400
    And the JSON response should match:
    """
    {
      "code": 400,
      "message": "This opinion type is not enabled.",
      "errors": @null@
    }
    """

  @security
  Scenario: logged in API client wants to add an opinion link
    Given I am logged in to api as user
    When I send a POST request to "/api/opinions/2/links" with a valid link opinion json
    Then the JSON response status code should be 400
    And the JSON response should match:
    """
    {
      "code": 400,
      "message": "This opinion type is not linkable.",
      "errors": @null@
    }
    """

  @database
  Scenario: logged in API client wants to add an opinion link
    Given I am logged in to api as user
    When I send a POST request to "/api/opinions/60/links" with a valid link opinion json
    Then the JSON response status code should be 201
