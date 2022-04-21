Feature: Events comments

## List Comments

  Scenario: API client wants to list comments of an event
    When I send a GET request to "/api/events/3/comments"
    Then the JSON response should match:
    """
    {
      "comments_and_answers_count": @integer@,
      "comments_count": @integer@,
      "comments":
      [
        {
          "can_contribute": @boolean@,
          "id": @integer@,
          "body": @string@,
          "created_at": "@string@.isDateTime()",
          "updated_at": "@string@.isDateTime()",
          "vote_count": @integer@,
          "author": {
            "username": @string@,
            "displayName": @string@,
            "uniqueId": @string@,
            "isAdmin": @boolean@,
            "media": @...@,
            "_links": {
              "profile": @string@,
              "settings": @string@
            }
          },
          "answers": [],
          "author_email": @null@,
          "author_name": @null@,
          "is_trashed": @boolean@,
          "_links": {
            "vote": @string@,
            "edit": @string@,
            "report": @string@
          },
          "has_user_reported": @boolean@,
          "has_user_voted": @boolean@,
          "can_edit": @boolean@
        },
        @...@
      ],
      "is_reporting_enabled": @boolean@
    }
    """

  Scenario: API client wants to find the first comment of an event
    When I send a GET request to "/api/events/3/comments?limit=1"
    Then the JSON response should match:
    """
    {
      "comments_and_answers_count": @integer@,
      "comments_count": @integer@,
      "comments":
      [
        {
          "can_contribute": @boolean@,
          "id": @integer@,
          "body": @string@,
          "created_at": "@string@.isDateTime()",
          "updated_at": "@string@.isDateTime()",
          "vote_count": @integer@,
          "author": @...@,
          "answers": @...@,
          "author_email": @null@,
          "author_name": @null@,
          "is_trashed": @boolean@,
          "_links": @...@,
          "has_user_reported": @boolean@,
          "has_user_voted": @boolean@,
          "can_edit": @boolean@
        }
      ],
      "is_reporting_enabled": @boolean@
    }
    """

  Scenario: API client wants to find popular comments of an event
    When I send a GET request to "/api/events/3/comments?filter=popular"
    Then the JSON response should match:
    """
    {
      "comments_and_answers_count": "@integer@",
      "comments_count": "@integer@",
      "comments":
      [
        @...@
      ],
      "is_reporting_enabled": @boolean@
    }
    """
    And the comments should be ordered by popularity


## Create Comments

  ### Anonymous

  @database
  Scenario: Anonymous API client wants to add a comment
    When I send a POST request to "/api/events/3/comments" with json:
    """
    {
      "authorName": "Kéké",
      "authorEmail": "vivele94@gmail.com",
      "body": "Vive moi qui suis plus fort que www.google.fr !"
    }
    """
    Then the JSON response status code should be 201

  @database
  Scenario: Anonymous API client wants to add an answer to a comment
    When I send a POST request to "/api/events/3/comments" with json:
    """
    {
      "parent": 223,
      "authorName": "Kéké",
      "authorEmail": "vivele94@gmail.com",
      "body": "Ma super réponse"
    }
    """
    Then the JSON response status code should be 201

  @security
  Scenario: Anonymous API client wants to add a comment without user informations
    When I send a POST request to "/api/events/3/comments" with json:
    """
    {
      "body": "Vive moi qui suis plus fort que www.google.fr !"
    }
    """
    Then the JSON response status code should be 400

  ### Logged

  @database
  Scenario: logged in API client wants to add a comment
    Given I am logged in to api as user
    When I send a POST request to "/api/events/3/comments" with json:
    """
    {
      "body": "Vive moi user ! Réponds à ça si tu l'oses."
    }
    """
    Then the JSON response status code should be 201

  @database
  Scenario: logged in API client wants to add an answer to a comment
    Given I am logged in to api as user
    When I send a POST request to "/api/events/3/comments" with json:
    """
    {
      "parent": 223,
      "body": "Oh oui j'ose :-P"
    }
    """
    Then the JSON response status code should be 201

  @security
  Scenario: logged in API client wants to add a comment by hacking
    Given I am logged in to api as user
    When I send a POST request to "/api/events/3/comments" with json:
    """
    {
      "parent": 1,
      "body": "Pr0 Hacker"
    }
    """
    Then the JSON response status code should be 404
    And the JSON response should match:
    """
    {
      "code": 404,
      "message": "Not Found",
      "errors": @null@
    }
    """
