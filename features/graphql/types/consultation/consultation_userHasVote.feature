@consultation_userHasVote
Feature: consultation_userHasVote

@read-only
Scenario: API client wants to know if a user has voted on the consultation
  Given I send a GraphQL POST request:
  """
  {
    "query": "query ($consultationId: ID!, $loginA: String!, $loginB: String!) {
      consultation: node(id: $consultationId) {
          ... on Consultation {
              spylHasVote: userHasVote(login: $loginA)
              lbrunetHasVote: userHasVote(login: $loginB)
          }
      }
    }",
    "variables": {
      "consultationId": "cstep1",
      "loginA": "aurelien@cap-collectif.com",
      "loginB": "lbrunet@jolicode.com"
    }
  }
  """
  Then the JSON response should match:
  """
  {
    "data": {
        "consultation": {
            "spylHasVote": false,
            "lbrunetHasVote": true
        }
    }
  }
  """
  