@updateReply
Feature: Update Reply

@database
Scenario: User wants to update a reply
  Given I am logged in to graphql as admin
  And I send a GraphQL POST request:
   """
   {
    "query": "mutation ($input: UpdateReplyInput!) {
      updateReply(input: $input) {
        reply {
          id
          published
          responses {
            question {
              id
            }
            ... on ValueResponse {
              value
            }
          }
        }
      }
    }",
    "variables": {
      "input": {
        "replyId": "reply2",
        "responses": [
            {
              "question": "2",
              "value": "Je pense que c'est la ville parfaite pour organiser les JO"
            },
            {
              "question": "13",
              "value": "{\"labels\":[\"Athlétisme\",\"Sports collectifs\"],\"other\":\"Embêter Maxime\"}"
            }
        ]
      }
    }
  }
  """
  Then the JSON response should match:
  """
  {
    "data": {
      "updateReply": {
          "reply": {
              "id": "reply2",
              "published": true,
              "responses": [
                {"question": {"id":"2" }, "value": "Je pense que c\u0027est la ville parfaite pour organiser les JO"},
                {"question": {"id":"13"}, "value": "{\"labels\":[\"Athl\u00e9tisme\",\"Sports collectifs\"],\"other\":\"Emb\u00eater Maxime\"}"},
                {"question": {"id":"14"}, "value": "{\"labels\":[\"Maxime Arrouard\"],\"other\":null}"},
                {"question": {"id":"301"}, "value": @null@},
                {"question": {"id":"302"}, "value": @null@},
                {"question": {"id":"15"}, "value": @null@ },
                {"question": {"id":"16"}, "value": "{\"labels\":[],\"other\":null}"},
                {"question": {"id":"18"}, "value": "{\"labels\":[],\"other\":null}"},
                {"question": {"id":"19"}, "value": "{\"labels\":[],\"other\":null}"}
              ]
          }
       }
     }
  }
  """
