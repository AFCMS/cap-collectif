@arguments
Feature: Arguments

## List

  @parallel-scenario
  Scenario: API client wants to list arguments of an opinion
    When I send a GET request to "/api/opinions/2/arguments"
    Then the JSON response status code should be 200
    And the JSON response should match:
    """
    {
      "arguments": [
        {
          "id": @integer@,
          "body": @string@,
          "type": @integer@,
          "created_at": "@string@.isDateTime()",
          "updated_at": "@string@.isDateTime()",

          "votesCount": @integer@,
          "is_trashed": @boolean@,
          "is_enabled": @boolean@,
          "isContribuable": @boolean@,

          "author": {
            "username": @string@,
            "displayName": @string@,
            "uniqueId": @string@,
            "isAdmin": @boolean@,
            "vip": @boolean@,
            "media": @...@,
            "_links": {
              "profile": @string@,
              "settings": @string@
            }
          },
          "_links": {
            "show": @string@
          },
          "hasUserVoted": @boolean@,
          "hasUserReported": @boolean@
        },
        @...@
      ],
      "count": @integer@
    }
    """

  @parallel-scenario
  Scenario: API client wants to list arguments of an opinion version
    When I send a GET request to "/api/opinions/57/versions/1/arguments"
    Then the JSON response status code should be 200
    And the JSON response should match:
    """
    {
      "arguments": [
        {
          "id": @integer@,
          "body": @string@,
          "type": @integer@,
          "created_at": "@string@.isDateTime()",
          "updated_at": "@string@.isDateTime()",

          "votesCount": @integer@,
          "is_trashed": @boolean@,
          "is_enabled": @boolean@,
          "isContribuable": @boolean@,

          "author": {
            "username": @string@,
            "displayName": @string@,
            "uniqueId": @string@,
            "isAdmin": @boolean@,
            "vip": @boolean@,
            "media": @...@,
            "_links": {
              "profile": @string@,
              "settings": @string@
            }
          },
          "_links": {
            "show": @string@
          },
          "hasUserVoted": @boolean@,
          "hasUserReported": @boolean@
        },
        @...@
      ],
      "count": @integer@
    }
    """

## CRUD

  ## Create on opinion

  @security
  Scenario: Anonymous API client wants to add an argument to an opinion
    When I send a POST request to "/api/opinions/57/arguments" with a valid argument json
    Then the JSON response status code should be 401

  @database
  Scenario: Logged in API client wants to add an argument to an opinion
    Given I am logged in to api as user
    When I send a POST request to "/api/opinions/57/arguments" with a valid argument json
    Then the JSON response status code should be 201

  ## Create on version

  @security
  Scenario: Anonymous API client wants to add an argument to an opinion version
    When I send a POST request to "/api/opinions/57/versions/1/arguments" with a valid argument json
    Then the JSON response status code should be 401

  @database
  Scenario: Logged in API client wants to add an argument to an opinion version
    Given I am logged in to api as user
    When I send a POST request to "/api/opinions/57/versions/1/arguments" with a valid argument json
    Then the JSON response status code should be 201

  ## Update on opinion

  @security
  Scenario: Anonymous API client wants to update an argument on an opinion
    When I send a PUT request to "/api/opinions/2/arguments/1" with a valid argument update json
    Then the JSON response status code should be 401

  @security
  Scenario: Logged in API client wants to update an argument on an opinion but is not the author
    Given I am logged in to api as admin
    When I send a PUT request to "/api/opinions/2/arguments/1" with a valid argument update json
    Then the JSON response status code should be 403

  @database
  Scenario: Logged in API client wants to update his argument on an opinion
    Given I am logged in to api as user
    When I send a PUT request to "/api/opinions/2/arguments/1" with a valid argument update json
    Then the JSON response status code should be 200

  ## Update on version

  @security
  Scenario: Anonymous API client wants to update an argument on a version
    When I send a PUT request to "/api/opinions/57/versions/1/arguments/2" with a valid argument update json
    Then the JSON response status code should be 401

  @security
  Scenario: Logged in API client wants to update an argument on a version but is not the author
    Given I am logged in to api as admin
    When I send a PUT request to "/api/opinions/57/versions/1/arguments/2" with a valid argument update json
    Then the JSON response status code should be 403

  @database
  Scenario: Logged in API client wants to update his argument on a version
    Given I am logged in to api as user
    When I send a PUT request to "/api/opinions/57/versions/1/arguments/2" with a valid argument update json
    Then the JSON response status code should be 200

  ## Delete from opinion

  @security
  Scenario: Anonymous API client wants to delete an argument from an opinion
    When I send a DELETE request to "/api/opinions/2/arguments/1"
    Then the JSON response status code should be 401

  @security
  Scenario: Logged in API client wants to delete an argument from an opinion but is not the author
    Given I am logged in to api as admin
    When I send a DELETE request to "/api/opinions/2/arguments/1"
    Then the JSON response status code should be 403

  @database
  Scenario: Logged in API client wants to delete his argument from an opinion
    Given I am logged in to api as user
    When I send a DELETE request to "/api/opinions/2/arguments/1"
    Then the JSON response status code should be 204

  ## Delete from version

  @security
  Scenario: Anonymous API client wants to delete an argument from a version
    When I send a DELETE request to "/api/opinions/57/versions/1/arguments/2"
    Then the JSON response status code should be 401

  @security
  Scenario: Logged in API client wants to delete an argument from a version but is not the author
    Given I am logged in to api as admin
    When I send a DELETE request to "/api/opinions/57/versions/1/arguments/2"
    Then the JSON response status code should be 403

  @database
  Scenario: Logged in API client wants to delete his argument from a version
    Given I am logged in to api as user
    When I send a DELETE request to "/api/opinions/57/versions/1/arguments/2"
    Then the JSON response status code should be 204


## Vote

  @parallel-scenario
  Scenario: Anonymous API client wants to vote for an argument
    When I send a POST request to "/api/arguments/1/votes" with json:
    """
    {}
    """
    Then the JSON response status code should be 401

  @database
  Scenario: logged in API client wants to vote for an argument then delete the vote
    Given I am logged in to api as user
    When I send a POST request to "/api/arguments/1/votes" with json:
    """
    {}
    """
    Then the JSON response status code should be 201
    When I send a DELETE request to "/api/arguments/1/votes"
    Then the JSON response status code should be 204

  @database
  Scenario: logged in API client wants to delete a non-existent vote
    Given I am logged in to api as user
    When I send a DELETE request to "/api/arguments/3/votes"
    Then the JSON response status code should be 400
    And the JSON response should match:
    """
    {
      "code": 400,
      "message": "You have not voted for this argument.",
      "errors": @null@
    }
    """

## Report

  # Report from opinion

  @database
  Scenario: Anonymous API client wants to report an argument from an opinion
    When I send a POST request to "/api/opinions/2/arguments/1/reports" with a valid report json
    Then the JSON response status code should be 401

  @database
  Scenario: Logged in API client wants to report his own argument from an opinion
    Given I am logged in to api as user
    When I send a POST request to "/api/opinions/2/arguments/1/reports" with a valid report json
    Then the JSON response status code should be 403

  @database
  Scenario: Logged in API client wants to report an argument from an opinion
    Given I am logged in to api as admin
    When I send a POST request to "/api/opinions/2/arguments/1/reports" with a valid report json
    Then the JSON response status code should be 201

  # Report from a version

  @database
  Scenario: Anonymous API client wants to report an argument from a version
    When I send a POST request to "/api/opinions/57/versions/1/arguments/2/reports" with a valid report json
    Then the JSON response status code should be 401

  @database
  Scenario: Logged in API client wants to report his own argument from a version
    Given I am logged in to api as user
    When I send a POST request to "/api/opinions/57/versions/1/arguments/2/reports" with a valid report json
    Then the JSON response status code should be 403

  @database
  Scenario: Logged in API client wants to report an argument from a version
    Given I am logged in to api as admin
    When I send a POST request to "/api/opinions/57/versions/1/arguments/2/reports" with a valid report json
    Then the JSON response status code should be 201
