@event
Feature: Event participants

Scenario: GraphQL client want to get the list of participants of an event
  Given I send a GraphQL POST request:
  """
  {
    "query": "query node ($event: ID!){
      event: node(id: $event) {
        ... on Event {
          participants(first: 5) {
            totalCount
            pageInfo {
              hasNextPage
            }
            edges {
              node {
                ... on User {
                  _id
                }
                ... on NotRegistered {
                  username
                  email
                }
              }
            }
          }
        }
      }
    }",
    "variables": {
      "event": "RXZlbnQ6ZXZlbnQx"
    }
  }
  """
  Then the JSON response should match:
  """
  {
    "data": {
      "event": {
        "participants": {
          "totalCount": 51,
          "pageInfo": {
            "hasNextPage": true
          },
          "edges": [
            { "node": { "_id":"user1" }},
            { "node": { "username": @string@, "email": @string@ }},
            { "node": { "username": @string@, "email": @string@ }},
            { "node": { "username": @string@, "email": @string@ }},
            { "node": { "username": @string@, "email": @string@ }}
          ]
        }
      }
    }
  }
  """

Scenario: GraphQL client want to get the list of participants of an event where there is a registered user
  Given I send a GraphQL POST request:
  """
  {
    "query": "query node ($event: ID!){
      event: node(id: $event) {
        ... on Event {
          participants(first: 5) {
            totalCount
            pageInfo {
              hasNextPage
            }
            edges {
              node {
                ... on User {
                  _id
                }
                ... on NotRegistered {
                  username
                  email
                }
              }
            }
          }
        }
      }
    }",
    "variables": {
      "event": "RXZlbnQ6ZXZlbnQz"
    }
  }
  """
  Then the JSON response should match:
  """
  {
    "data": {
      "event": {
        "participants": {
          "totalCount": 1,
          "pageInfo": {
            "hasNextPage": false
          },
          "edges": [
            { "node": { "_id": "user3" } }
          ]
        }
      }
    }
  }
  """
