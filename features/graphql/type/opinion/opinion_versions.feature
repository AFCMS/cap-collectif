@opinion_versions
Feature: Versions of an opinion

@read-only
Scenario: Anonymous wants to get versions for an opinion
  Given I send a GraphQL POST request:
  """
  {
    "query": "query ($opinionId: ID!) {
      opinion: node(id: $opinionId) {
          ... on Opinion {
              versions(first: 5) {
                  totalCount
                  edges {
                      node {
                          id
                          published
                      }
                  }
              }
          }
      }
    }",
    "variables": {
      "opinionId": "T3BpbmlvbjpvcGluaW9uNTc="
    }
  }
  """
  Then the JSON response should match:
  """
  {
    "data": {
        "opinion": {
            "versions": {
              "totalCount": 4,
              "edges": [
                {
                  "node": {
                    "id": @string@,
                    "published": true
                  }
                },
                @...@
              ]
            }
        }
    }
  }
  """
