@proposal @update_proposal
Feature: Update a proposal

@database @rabbitmq
Scenario: Admin should be notified if GraphQL user modify his proposal
  Given I am logged in to graphql as user
  And I send a GraphQL POST request:
  """
  {
    "query": "mutation ($input: ChangeProposalContentInput!) {
      changeProposalContent(input: $input) {
        proposal {
          id
          title
          body
          publicationStatus
        }
      }
    }",
    "variables": {
      "input": {
        "id": "proposal2",
        "title": "Achetez un DOP à la madeleine",
        "body": "Grâce à ça, on aura des cheveux qui sentent la madeleine !!!!!!!",
        "responses": [
          {
            "question": "1",
            "value": "reponse-1"
          },
          {
            "question": "3",
            "value": "reponse-3"
          },
          {
            "question": "11",
            "medias": ["media1"]
          },
          {
            "question": "12",
            "medias": []
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
      "changeProposalContent": {
        "proposal": {
          "id": "proposal2",
          "title": "Achetez un DOP à la madeleine",
          "body": "Grâce à ça, on aura des cheveux qui sentent la madeleine !!!!!!!",
          "publicationStatus": "PUBLISHED"
        }
      }
    }
  }
  """
  Then the queue associated to "proposal_update" producer has messages below:
  | 0 | {"proposalId": "proposal2"} |
  Then the queue associated to "elasticsearch_indexation" producer has messages below:
  | 1 | ["{"class":"Capco\\AppBundle\\Entity\\Proposal","id":"proposal2"}","{"class":"Capco\\UserBundle\\Entity\\User","id":"user5"}"] |

@database
Scenario: GraphQL client wants to edit his proposal
  Given I am logged in to graphql as user
  And I send a GraphQL POST request:
  """
  {
    "query": "mutation ($input: ChangeProposalContentInput!) {
      changeProposalContent(input: $input) {
        proposal {
          id
          title
          body
          publicationStatus
        }
      }
    }",
    "variables": {
      "input": {
        "id": "proposal2",
        "title": "Acheter un sauna par personne pour Capco",
        "body": "Avec tout le travail accompli, on mérite bien chacun un (petit) cadeau, donc on a choisi un sauna. JoliCode interdit",
        "responses": [
          {
            "question": "1",
            "value": "reponse-1"
          },
          {
            "question": "3",
            "value": "reponse-3"
          },
          {
            "question": "11",
            "medias": ["media1"]
          },
          {
            "question": "12",
            "medias": []
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
      "changeProposalContent": {
        "proposal": {
          "id": "proposal2",
          "title": "Acheter un sauna par personne pour Capco",
          "body": "Avec tout le travail accompli, on mérite bien chacun un (petit) cadeau, donc on a choisi un sauna. JoliCode interdit",
          "publicationStatus": "PUBLISHED"
        }
      }
    }
  }
  """
  And I send a GraphQL POST request:
  """
  {
    "query": "mutation ($input: ChangeProposalContentInput!) {
      changeProposalContent(input: $input) {
        proposal {
          id
          title
          body
          publicationStatus
          responses {
            question {
              id
            }
            ... on ValueResponse {
              value
            }
            ... on MediaResponse {
              medias {
                id
              }
            }
          }
        }
      }
    }",
    "variables": {
      "input": {
        "id": "proposal2",
        "title": "New title",
        "body": "New body",
        "category": "pCategory3",
        "responses": [
          {
            "question": "3",
            "value": "New reponse-3"
          },
          {
            "question": "11",
            "medias": ["media1", "media2"]
          },
          {
            "question": "12",
            "medias": []
          },
          {
            "question": "1",
            "value": "New reponse-1"
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
      "changeProposalContent": {
        "proposal": {
          "id": "proposal2",
          "title": "New title",
          "body": "New body",
          "publicationStatus": "PUBLISHED",
          "responses": [
            { "question": {"id":"1"}, "value": "New reponse-1" },
            { "question": {"id":"3"}, "value": "New reponse-3" },
            { "question": {"id":"11"}, "medias": [{"id": "media1"}, {"id": "media2"}] },
            { "question": {"id":"12"}, "medias": []}
          ]
        }
      }
    }
  }
  """

@database
Scenario: Super Admin GraphQL client wants to update a proposal
  Given features themes, districts are enabled
  And I am logged in to graphql as super admin
  And I send a GraphQL POST request:
  """
  {
    "query": "mutation ($input: ChangeProposalContentInput!) {
      changeProposalContent(input: $input) {
        proposal {
          id
          title
          body
          author {
            id
          }
          theme {
            id
          }
          district {
            id
          }
          category {
            id
          }
          responses {
            question {
              id
            }
            ... on ValueResponse {
              value
            }
            ... on MediaResponse {
              medias {
                id
              }
            }
          }
        }
      }
    }",
    "variables": {
      "input": {
        "title": "NewTitle",
        "body": "NewBody",
        "theme": "theme1",
        "author": "userAdmin",
        "district": "district2",
        "category": "pCategory2",
        "responses": [
          {
            "question": "11",
            "medias": ["media1"]
          },
          {
            "question": "1",
            "value": "reponse-1"
          },
          {
            "question": "3",
            "value": "reponse-3"
          },
          {
            "question": "12",
            "medias": ["media1"]
          }
        ],
        "id": "proposal2"
      }
    }
  }
  """
  Then the JSON response should match:
  """
  {
    "data": {
      "changeProposalContent":{
        "proposal":{
          "id":"proposal2",
          "title":"NewTitle",
          "body":"NewBody",
          "author":{"id":"userAdmin"},
          "theme":{"id":"theme1"},
          "district":{"id":"district2"},
          "category":{"id":"pCategory2"},
          "responses":[
            {"question":{"id":"1"},"value":"reponse-1"},
            {"question":{"id":"3"},"value":"reponse-3"},
            {"question":{"id":"12"},"medias":[{"id":"media1"}]},
            {"question":{"id":"11"},"medias":[{"id":"media1"}]}
          ]
        }
      }
    }
  }
  """

@database
Scenario: GraphQL client wants to edit his proposal without required response
  Given I am logged in to graphql as user
  And I send a GraphQL POST request:
  """
  {
    "query": "mutation ($input: ChangeProposalContentInput!) {
      changeProposalContent(input: $input) {
        proposal {
          id
          title
          body
          publicationStatus
        }
      }
    }",
    "variables": {
      "input": {
        "id": "proposal2",
        "responses": [
          {
            "question": "1",
            "value": "reponse-1"
          },
          {
            "question": "3",
            "value": "reponse-3"
          },
          {
            "question": "11",
            "medias": []
          },
          {
            "question": "12",
            "medias": []
          }
        ]
      }
    }
  }
  """
  Then the JSON response should match:
  """
    "errors":[{"message":"proposal.missing_required_responses {\"missing\":11}","category":@string@,"locations":[{"line":1,"column":53}],"path":[@...@]}],
    "data": { "changeProposalContent": null }

  """
