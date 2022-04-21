Feature: Statuses
  As an API client

  Scenario: Anonymous API client wants to get all statuses
    When I send a GET request to "/api/collect_steps/16/statuses"
    Then the JSON response should match:
"""
[
  {
    "id": @integer@,
    "name": @string@,
    "color": @string@
  },
  @...@
]
"""
