@opinion_votes
Feature: Opinion votes connection

@database
Scenario: Anonymous wants to get votes for an opinion
  Given I send a GraphQL POST request:
  """
  {
    "query": "query ($opinionId: ID!) {
      opinion: node(id: $opinionId) {
        ... on Opinion {
          votes(first: 10) {
            totalCount
            pageInfo {
              hasNextPage
            }
            edges {
              cursor
              node {
                id
                author {
                  id
                }
                value
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
        "votes": {
          "totalCount": 4,
          "pageInfo": {
            "hasNextPage": false
          },
          "edges": [
            {
              "cursor": @string@,
              "node": {
                "id": @string@,
                "author": {
                    "id": @string@
                },
                "value": "NO"
              }
            },
            @...@
          ]
        }
      }
    }
  }
  """

@database
Scenario: Anonymous wants to get votes "YES" for an opinion
  Given I send a GraphQL POST request:
  """
  {
    "query": "query ($opinionId: ID!) {
      opinion: node(id: $opinionId) {
        ... on Opinion {
          votes(first: 10, value: YES) {
            totalCount
            edges {
              cursor
              node {
                id
                author {
                  _id
                }
                value
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
    "data":{
      "opinion":{
        "votes":{
          "totalCount":2,
          "edges":[{
          "cursor":"@string@",
          "node":{
            "id":"@string@",
          "author":{
            "_id":"user3"
          },
          "value":"YES"
          }},
          {
          "cursor":"@string@",
          "node":{
            "id":"@string@",
            "author":{
              "_id":"userAdmin"
            },
            "value":"YES"
          }}]
        }
      }
    }
  }
  """
