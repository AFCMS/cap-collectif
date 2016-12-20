@projects
Feature: Projects

    @parallel-scenario
    Scenario: API client wants to get all projects
        When I send a GET request to "/api/projects"
        Then the JSON response status code should be 200
        And the JSON response should match:
    """
    {
      "projects": [
        {
          "id": 4,
          "title": @string@,
          "created_at": "@string@.isDateTime()",
          "updated_at": "@string@.isDateTime()",
          "themes": [
            {
                "id": @integer@,
                "title": @string@,
                "enabled": @boolean@,
                "_links": {"show": @string@}
            },
            @...@
          ],
          "author": @...@,
          "video": @null@,
          "participantsCount": @integer@,
          "contributionsCount": @integer@,
          "votesCount": @integer@,
          "projectType": {
            "title": @string@,
            "slug": @string@,
            "color": @string@
          },
          "steps": @array@,
          "_links": {"show": @string@}
        },
        @...@
      ],
      "page": @integer@,
      "pages": @integer@,
      "count": @integer@
    }
    """

    @parallel-scenario
    Scenario: API client wants to get all consultation projects
        When I send a GET request to "/api/projects?orderBy=popularity"
        Then the JSON response status code should be 200
        And the JSON response should match:
    """
    {
      "projects": [
        {
          "id": 1,
          "title": @string@,
          "created_at": "@string@.isDateTime()",
          "updated_at": "@string@.isDateTime()",
          "themes": [
            {
                "id": @integer@,
                "title": @string@,
                "enabled": @boolean@,
                "_links": {"show": @string@}
            },
            @...@
          ],
          "author": @...@,
          "video": @string@,
          "cover": @array@,
          "participantsCount": @integer@,
          "contributionsCount": @integer@,
          "votesCount": @integer@,
          "projectType": {
            "title": @string@,
            "slug": @string@,
            "color": @string@
          },
          "steps": @array@,
          "_links": {"show": @string@}
        },
        @...@
      ],
      "page": @integer@,
      "pages": @integer@,
      "count": @integer@
    }
    """

    @parallel-scenario
    Scenario: API client wants to get all projects sorted by popularity
        When I send a GET request to "/api/projects?type=consultation"
        Then the JSON response status code should be 200
        And the JSON response should match:
    """
    {
      "projects": [
        {
          "id": 4,
          "title": @string@,
          "created_at": "@string@.isDateTime()",
          "updated_at": "@string@.isDateTime()",
          "themes": [
            {
                "id": @integer@,
                "title": @string@,
                "enabled": @boolean@,
                "_links": {"show": @string@}
            },
            @...@
          ],
          "author": @...@,
          "video": @null@,
          "participantsCount": @integer@,
          "contributionsCount": @integer@,
          "votesCount": @integer@,
          "projectType": {
            "title": @string@,
            "slug": @string@,
            "color": @string@
          },
          "steps": @array@,
          "_links": {"show": @string@}
        },
        @...@
      ],
      "page": 1,
      "pages": 1,
      "count": 3
    }
    """

  @security
  Scenario: Anonymous API client wants to create a project
    When I send a POST request to "/api/projects"
    Then the JSON response status code should be 401
    And the JSON response should match:
    """
    {"code":401,"message":"Invalid credentials"}
    """

  @database
  Scenario: Admin API client can create a project
    Given I am logged in to api as admin
    And user "42" doesn't have role "ROLE_ADMIN"
    When I send a POST request to "/api/projects" with json:
    """
    {
        "title": "My new project",
        "Author": "42"
    }
    """
    Then the JSON response status code should be 201
    And the JSON response should match:
    """
    {
      "_links": {
        "show": @string@,
        "external": @null@,
        "admin": @string@
      }
    }
    """
    And project "11" should have author "42"
    And user "admin" should have role "ROLE_ADMIN"
    And project "11" should not be published
    Then 1 mail should be sent

    @parallel-scenario
    Scenario: API client wants to get all project steps
        When I send a GET request to "/api/projects/1/steps"
        Then the JSON response status code should be 200
        And the JSON response should match:
        """
        [
        {
          "projectId": 1,
          "position": 1,
          "open": false,
          "timeless": false,
          "id": 13,
          "title": "Présentation",
          "enabled": true,
          "startAt": @null@,
          "endAt": @null@,
          "body": @string@,
          "statuses": [],
          "step_type": @string@,
          "status": "closed"
        },
        @...@
      ]
    """
