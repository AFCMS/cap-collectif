@delete_reply
Feature: Delete reply

@database
Scenario: User can delete his reply
  Given I am logged in to graphql as admin
  And I send a GraphQL POST request:
  """
  {
    "query": "mutation ($input: DeleteReplyInput!) {
      deleteReply(input: $input) {
        questionnaire {
          id
          viewerReplies {
            id
          }
        }
      }
    }",
    "variables": {
      "input": {
        "id": "reply2"
      }
    }
  }
  """
  Then the JSON response should match:
  """
  {
    "data": {
      "deleteReply": {
        "questionnaire": {
          "id": "questionnaire1",
          "viewerReplies": []
        }
      }
    }
  }
  """

@security
Scenario: User can not delete the reply of someone else
  Given I am logged in to graphql as user
  And I send a GraphQL POST request:
  """
  {
    "query": "mutation ($input: DeleteReplyInput!) {
      deleteReply(input: $input) {
        questionnaire {
          id
        }
      }
    }",
    "variables": {
      "input": {
        "id": "reply2"
      }
    }
  }
  """
  Then the JSON response should match:
  """
  {
    "errors": [
      {"message":"You are not the author of this reply","category":@string@,"locations":[{"line":1,"column":43}],"path":["deleteReply"]}
    ],
    "data": {
      "deleteReply": null
    }
  }
  """
