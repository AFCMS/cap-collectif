@proposals_votes
Feature: mutation addOpinionVote

@security
Scenario: Logged in API client wants to vote for a proposal in a step with vote limited but has already reached vote limit
  Given I am logged in to graphql as user
  And I send a GraphQL POST request:
  """
  {
    "query": "mutation ($input: AddOpinionVoteInput!) {
      addOpinionVote(input: $input) {
        vote {
          id
        }
      }
    }",
    "variables": {
      "input": {
        "opinionId": "opinion17"
      }
    }
  }
  """
  Then the JSON response should match:
  """
  {
    "errors": [
      {
        "message": "You have reached the limit of votes.",
        "category": @string@,
        "locations": [{"line":1,"column":47}],
        "path":["addOpinionVote"]
      }
    ],
    "data": {
      "addOpinionVote": null
    }
  }
  """

@security
Scenario: Logged in API client without all requirements wants to vote for a proposal in a step with requirements
  Given I am logged in to graphql as admin
  And I send a GraphQL POST request:
  """
  {
    "query": "mutation ($input: AddOpinionVoteInput!) {
      addOpinionVote(input: $input) {
        vote {
          id
        }
      }
    }",
    "variables": {
      "input": {
        "stepId": "collectstepVoteClassement",
        "proposalId": "proposal24"
      }
    }
  }
  """
  Then the JSON response should match:
  """
  {
    "errors": [
      {
        "message": "You dont meets all the requirements.",
        "category": @string@,
        "locations": [{"line":1,"column":47}],
        "path":["addOpinionVote"]
      }
    ],
    "data": {
      "addOpinionVote": null
    }
  }
  """

@database
Scenario: Logged in API client wants to vote for a proposal
  Given I am logged in to graphql as user
  And I send a GraphQL POST request:
  """
  {
    "query": "mutation ($input: AddOpinionVoteInput!) {
      addOpinionVote(input: $input) {
        vote {
          id
          proposal {
            id
          }
          author {
            id
          }
        }
      }
    }",
    "variables": {
      "input": {
        "stepId": "selectionstep1",
        "proposalId": "proposal2"
      }
    }
  }
  """
  Then the JSON response should match:
  """
  {
    "data": {
      "addOpinionVote": {
        "vote": {
          "id": @string@,
          "proposal": {
            "id": "proposal2"
          },
          "author": {
            "id": "user5"
          }
        }
      }
    }
  }
  """

@database
Scenario: Logged in API client wants to vote for a proposal anonymously
  Given I am logged in to graphql as user
  And I send a GraphQL POST request:
  """
  {
    "query": "mutation ($input: AddOpinionVoteInput!) {
      addOpinionVote(input: $input) {
        vote {
          id
          proposal {
            id
          }
          author {
            id
          }
        }
      }
    }",
    "variables": {
      "input": {
        "anonymously": true,
        "stepId": "selectionstep1",
        "proposalId": "proposal2"
      }
    }
  }
  """
  Then the JSON response should match:
  """
  {
    "data": {
      "addOpinionVote": {
        "vote": {
          "id": @string@,
          "proposal": {
            "id": "proposal2"
          },
          "author": null
        }
      }
    }
  }
  """

@security
Scenario: Logged in API client wants to vote several times for a proposal in a step
  Given I am logged in to graphql as admin
  And I send a GraphQL POST request:
  """
  {
    "query": "mutation ($input: AddOpinionVoteInput!) {
      addOpinionVote(input: $input) {
        vote {
          id
        }
      }
    }",
    "variables": {
      "input": {
        "stepId": "selectionstep1",
        "proposalId": "proposal2"
      }
    }
  }
  """
  Then the JSON response should match:
  """
  {
    "errors": [
      {
        "message": "proposal.vote.already_voted",
        "category": @string@,
        "locations": [{"line":1,"column":47}],
        "path": ["addOpinionVote"]
      }
    ],
    "data": {
      "addOpinionVote": null
    }
  }
  """

@security
Scenario: Logged in API client wants to vote for a proposal in a wrong selection step
  Given I am logged in to graphql as user
  And I send a GraphQL POST request:
  """
  {
    "query": "mutation ($input: AddOpinionVoteInput!) {
      addOpinionVote(input: $input) {
        vote {
          id
        }
      }
    }",
    "variables": {
      "input": {
        "stepId": "selectionstep1",
        "proposalId": "proposal13"
      }
    }
  }
  """
  Then the JSON response should match:
  """
  {
    "errors": [
      {
        "message": "This proposal is not associated to this selection step.",
        "category": @string@,
        "locations": [{"line":1,"column":47}],
        "path": ["addOpinionVote"]
      }
    ],
    "data": {
      "addOpinionVote": null
    }
  }
  """

@security
Scenario: Logged in API client wants to vote for a proposal in a not votable selection step
  Given I am logged in to graphql as user
  And I send a GraphQL POST request:
  """
  {
    "query": "mutation ($input: AddOpinionVoteInput!) {
      addOpinionVote(input: $input) {
        vote {
          id
        }
      }
    }",
    "variables": {
      "input": {
        "stepId": "selectionstep2",
        "proposalId": "proposal2"
      }
    }
  }
  """
  Then the JSON response should match:
  """
  {
    "errors": [
      {
        "message": "This step is not votable.",
        "category": @string@,
        "locations": [{"line":1,"column":47}],
        "path": ["addOpinionVote"]
      }
    ],
    "data": {
      "addOpinionVote": null
    }
  }
  """

@security
Scenario: Logged in API client wants to vote for a proposal in a not votable selection step
  Given I am logged in to graphql as user
  And I send a GraphQL POST request:
  """
  {
    "query": "mutation ($input: AddOpinionVoteInput!) {
      addOpinionVote(input: $input) {
        vote {
          id
        }
      }
    }",
    "variables": {
      "input": {
        "stepId": "selectionstep3",
        "proposalId": "proposal11"
      }
    }
  }
  """
  Then the JSON response should match:
  """
  {
    "errors": [
      {
        "message": "This step is no longer contributable.",
        "category": @string@,
        "locations": [{"line":1,"column":47}],
        "path": ["addOpinionVote"]
      }
    ],
    "data": {
      "addOpinionVote": null
    }
  }
  """

@security
Scenario: Logged in API client wants to vote for a proposal in a not votable selection step
  Given I am logged in to graphql as admin
  And I send a GraphQL POST request:
  """
  {
    "query": "mutation ($input: AddOpinionVoteInput!) {
      addOpinionVote(input: $input) {
        vote {
          id
        }
      }
    }",
    "variables": {
      "input": {
        "stepId": "selectionstep4",
        "proposalId": "proposal8"
      }
    }
  }
  """
  Then the JSON response should match:
  """
  {
    "errors": [
      {
        "message": "proposal.vote.not_enough_credits",
        "category": @string@,
        "locations": [{"line":1,"column":47}],
        "path": ["addOpinionVote"]
      }
    ],
    "data": {
      "addOpinionVote": null
    }
  }
  """
