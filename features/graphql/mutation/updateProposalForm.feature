@updateProposalForm @admin
Feature: Update Proposal Form

@database
Scenario: GraphQL client wants to update a proposal form
  Given I am logged in to graphql as admin
  And I send a GraphQL POST request:
  """
  {
    "query": "mutation ($input: UpdateProposalFormInput!) {
          updateProposalForm(input: $input) {
            proposalForm {
              id
              title
              titleHelpText
              allowAknowledge
              description
              descriptionHelpText
              summaryHelpText
              illustrationHelpText
              usingThemes
              themeMandatory
              themeHelpText
              usingDistrict
              districtMandatory
              districtHelpText
              usingCategories
              categoryMandatory
              categoryHelpText
              usingAddress
              addressHelpText
              usingDescription
              usingSummary
              usingIllustration
              descriptionMandatory
              isProposalForm
              proposalInAZoneRequired
              mapCenter {
                lat
                lng
              }
              zoomMap
              commentable
              costable
              categories(order: ALPHABETICAL) {
                id
                name
                categoryImage {
                  id
                  image {
                    url
                    id
                    name
                  }
                }
              }
              districts {
                id
                name (locale: FR_FR)
                geojson
                displayedOnMap
              }
              questions {
                id
                helpText
                private
                required
                title
                type
              }
            }
          }
        }",
    "variables":{
      "input": {
        "proposalFormId": "proposalForm1",
        "title": "New title",
        "titleHelpText": "Title help",
        "description": "New description",
        "descriptionHelpText": "Description help",
        "summaryHelpText": "Summary Help",
        "illustrationHelpText": "Illustration Help",
        "usingThemes": true,
        "themeMandatory": true,
        "themeHelpText": "Theme Help",
        "usingDistrict": true,
        "allowAknowledge": true,
        "districtMandatory": true,
        "districtHelpText": "District Help",
        "usingCategories": true,
        "categoryMandatory": true,
        "proposalInAZoneRequired": true,
        "categoryHelpText": "Category Help",
        "usingAddress": true,
        "addressHelpText": "Address help",
        "usingDescription": true,
        "usingSummary": true,
        "usingIllustration": true,
        "descriptionMandatory": true,
        "isProposalForm": true,
        "mapCenter": "[{\"geometry\":{\"location_type\":\"GEOMETRIC_CENTER\",\"location\":{\"lat\":\"42\",\"lng\":\"0\"}}}]",
        "zoomMap": 0,
        "commentable": true,
        "costable": true,
        "categories": [{
            "id": "pCategory1",
            "name": "Aménagement",
            "categoryImage":	"categoryImage15"
          },
          {
            "id": "pCategory2",
            "name": "Politique"
          },
          {
            "name": "New category",
            "newCategoryImage":	"media5"
          },
          {
            "name": "Vide"
          },
          {
            "name": "Image perso",
            "newCategoryImage":	"media6"
          },
          {
            "name": "Ecole",
            "categoryImage":	"school"
          }
        ],
        "districts": [{
            "displayedOnMap": false,
            "geojson": "",
            "translations":[{"locale":"fr-FR","name":"Beauregard"}]
          },
          {
            "displayedOnMap": true,
            "geojson": "",
            "translations":[{"locale":"fr-FR","name":"autre district"}]
          }
        ],
        "questions": []
      }
    }
  }
  """
  Then the JSON response should match:
  """
{
   "data":{
      "updateProposalForm":{
         "proposalForm":{
            "id":"proposalForm1",
            "title":"New title",
            "titleHelpText":"Title help",
            "allowAknowledge":true,
            "description":"New description",
            "descriptionHelpText":"Description help",
            "summaryHelpText":"Summary Help",
            "illustrationHelpText":"Illustration Help",
            "usingThemes":true,
            "themeMandatory":true,
            "themeHelpText":"Theme Help",
            "usingDistrict":true,
            "districtMandatory":true,
            "districtHelpText":"District Help",
            "usingCategories":true,
            "categoryMandatory":true,
            "categoryHelpText":"Category Help",
            "usingAddress":true,
            "addressHelpText":"Address help",
            "usingDescription":true,
            "usingSummary":true,
            "usingIllustration":true,
            "descriptionMandatory":true,
            "isProposalForm":true,
            "proposalInAZoneRequired":true,
            "mapCenter": {
              "lat": 42,
              "lng": 0
            },
            "zoomMap":0,
            "commentable":true,
            "costable":true,
            "categories":[
               {
                  "id":"pCategory1",
                  "name":"Am\u00e9nagement",
                  "categoryImage":{
                     "id":"categoryImage15",
                     "image":{
                        "url":"https:\/\/capco.test\/media\/default\/0001\/01\/providerReference44.svg",
                        "id":"media-urbanisme",
                        "name":"Media Urbanisme"
                     }
                  }
               },
               {
                  "id": "@string@",
                  "name":"Ecole",
                  "categoryImage":{
                     "id":"school",
                     "image":{
                        "url":"https:\/\/capco.test\/media\/default\/0001\/01\/providerReference41.svg",
                        "id":"media-scolarite",
                        "name":"Media Scolarit\u00e9"
                     }
                  }
               },
               {
                  "id": "@string@",
                  "name":"Image perso",
                  "categoryImage":{
                     "id": "@string@",
                     "image":{
                        "url":"https:\/\/capco.test\/media\/default\/0001\/01\/providerReference7.jpg",
                        "id":"media6",
                        "name":"Titre du m\u00e9dia id\u00e9e 2"
                     }
                  }
               },
               {
                  "id": "@string@",
                  "name":"New category",
                  "categoryImage":{
                     "id": "@string@",
                     "image":{
                        "url":"https:\/\/capco.test\/media\/default\/0001\/01\/providerReference6.jpg",
                        "id":"media5",
                        "name":"Titre du m\u00e9dia id\u00e9e 1"
                     }
                  }
               },
               {
                  "id":"pCategory2",
                  "name":"Politique",
                  "categoryImage":{
                     "id":"@string@",
                     "image":{
                        "url":"https:\/\/capco.test\/media\/default\/0001\/01\/providerReference33.svg",
                        "id":"media-culture",
                        "name":"Media Culture"
                     }
                  }
               },
               {
                  "id": "@string@",
                  "name":"Vide",
                  "categoryImage":null
               }
            ],
            "districts":[
               {
                  "id":"@string@",
                  "name":"Beauregard",
                  "geojson":null,
                  "displayedOnMap":false
               },
               {
                  "id":"@string@",
                  "name":"autre district",
                  "geojson":null,
                  "displayedOnMap":true
               }
            ],
            "questions": []
         }
      }
   }
}
  """

@database
Scenario: GraphQL client wants to update custom fields of a proposal form
  Given I am logged in to graphql as admin
  And I send a GraphQL POST request:
  """
  {
    "query": "mutation ($input: UpdateProposalFormInput!) {
      updateProposalForm(input: $input) {
        proposalForm {
          id
          questions {
            id
            helpText
            private
            required
            title
            type
          }
        }
      }
    }",
    "variables": {
      "input": {
        "proposalFormId": "proposalForm1",
        "questions": [
          {
            "question": {
              "title": "Etes-vous réél ?",
              "helpText": "Peut-être que non...",
              "private": false,
              "required": true,
              "type": "text"
            }
          },
          {
            "question": {
              "title": "Documents à remplir",
              "helpText": "5 fichiers max",
              "private": false,
              "required": true,
              "type": "medias"
            }
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
      "updateProposalForm": {
        "proposalForm": {
          "id": "proposalForm1",
          "questions": [
            {
              "id": @string@,
              "helpText": "Peut-être que non...",
              "private": false,
              "required": true,
              "title": "Etes-vous réél ?",
              "type": "text"
            },
            {
              "id": @string@,
              "helpText": "5 fichiers max",
              "private": false,
              "required": true,
              "title": "Documents à remplir",
              "type": "medias"
            }
          ]
        }
      }
    }
  }
  """

@database
Scenario: GraphQL client wants to delete the first question
  Given I am logged in to graphql as admin
  And I send a GraphQL POST request:
  """
  {
    "query": "mutation ($input: UpdateProposalFormInput!) {
      updateProposalForm(input: $input) {
        proposalForm {
          id
          questions {
            id
            title
            type
          }
        }
      }
    }",
    "variables": {
      "input": {
        "proposalFormId": "proposalForm13",
        "questions": [
          {
            "question": {
              "id": "UXVlc3Rpb246NDg=",
              "title": "Question Multiple?",
              "helpText": null,
              "description": null,
              "type": "radio",
              "private": false,
              "required": false,
              "validationRule": null,
              "choices": [
                {
                  "id": "UXVlc3Rpb25DaG9pY2U6cXVlc3Rpb25jaG9pY2UzMg==",
                  "title": "Oui",
                  "description": null,
                  "color": null,
                  "image": null
                },
                {
                  "id": "UXVlc3Rpb25DaG9pY2U6cXVlc3Rpb25jaG9pY2UzMw==",
                  "title": "Non",
                  "description": null,
                  "color": null,
                  "image": null
                },
                {
                  "id": "UXVlc3Rpb25DaG9pY2U6cXVlc3Rpb25jaG9pY2UzNA==",
                  "title": "Peut être",
                  "description": null,
                  "color": null,
                  "image": null
                }
              ],
              "otherAllowed": false,
              "randomQuestionChoices": false,
              "jumps": []
            }
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
      "updateProposalForm": {
        "proposalForm": {
          "id": "proposalform13",
          "questions": [
            {
              "id": "UXVlc3Rpb246NDg=",
              "title": "Question Multiple?",
              "type": "radio"
            }
          ]
        }
      }
    }
  }
  """

@database
Scenario: GraphQL client wants to delete the first question choice
  Given I am logged in to graphql as admin
  And I send a GraphQL POST request:
  """
  {
    "query": "mutation ($input: UpdateProposalFormInput!) {
      updateProposalForm(input: $input) {
        proposalForm {
          id
          questions {
            id
            title
            type
            ... on MultipleChoiceQuestion {
              choices(allowRandomize: false) {
                edges {
                  node {
                    id
                    title
                    description
                    color
                  }
                }
              }
            }
          }
        }
      }
    }",
    "variables": {
      "input": {
        "proposalFormId": "proposalForm13",
        "questions": [
          {
            "question": {
              "id": "UXVlc3Rpb246MTMxNA==",
              "private": false,
              "required": false,
              "title": "Question simple?",
              "type": "text"
            }
          },
          {
            "question": {
              "id": "UXVlc3Rpb246NDg=",
              "title": "Question Multiple?",
              "helpText": null,
              "description": null,
              "type": "radio",
              "private": false,
              "required": false,
              "validationRule": null,
              "choices": [
                {
                  "id": "UXVlc3Rpb25DaG9pY2U6cXVlc3Rpb25jaG9pY2UzMw==",
                  "title": "Non",
                  "description": null,
                  "color": null,
                  "image": null
                },
                {
                  "id": "UXVlc3Rpb25DaG9pY2U6cXVlc3Rpb25jaG9pY2UzNA==",
                  "title": "Peut être",
                  "description": null,
                  "color": null,
                  "image": null
                }
              ],
              "otherAllowed": false,
              "randomQuestionChoices": false,
              "jumps": []
            }
          }
        ],
        "proposalFormId": "proposalform13"
      }
    }
  }
  """
  Then the JSON response should match:
  """
  {
    "data": {
      "updateProposalForm": {
        "proposalForm": {
          "id": "proposalform13",
          "questions": [
            {
              "id": "UXVlc3Rpb246MTMxNA==",
              "title": "Question simple?",
              "type": "text"
            },
            {
              "id": "UXVlc3Rpb246NDg=",
              "title": "Question Multiple?",
              "type": "radio",
              "choices": {
                "edges": [
                  {
                    "node": {
                      "id": "UXVlc3Rpb25DaG9pY2U6cXVlc3Rpb25jaG9pY2UzMw==",
                      "title": "Non",
                      "description": null,
                      "color": null
                    }
                  },
                  {
                    "node": {
                      "id": "UXVlc3Rpb25DaG9pY2U6cXVlc3Rpb25jaG9pY2UzNA==",
                      "title": "Peut être",
                      "description": null,
                      "color": null
                    }
                  }
                ]
              }
            }
          ]
        }
      }
    }
  }
  """

@database
Scenario: GraphQL client wants to delete the first district
  Given I am logged in to graphql as admin
  And I send a GraphQL POST request:
  """
  {
    "query": "mutation ($input: UpdateProposalFormInput!) {
      updateProposalForm(input: $input) {
        proposalForm {
          id
          districts {
            id
            name (locale: FR_FR)
          }
        }
      }
    }",
    "variables": {
      "input": {
        "proposalFormId": "proposalForm13",
        "districts": [
          {
            "id": "district15",
            "translations":[{"locale":"fr-FR","name":"Quartier 2"}],
            "displayedOnMap": true,
            "geojson": null
          },
          {
            "id": "district16",
            "translations":[{"locale":"fr-FR","name":"Quartier 3"}],
            "displayedOnMap": true,
            "geojson": null
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
      "updateProposalForm": {
        "proposalForm": {
          "id": "proposalform13",
          "districts": [
            {
              "id": "district15",
              "name": "Quartier 2"
            },
            {
              "id": "district16",
              "name": "Quartier 3"
            }
          ]
        }
      }
    }
  }
  """

@database
Scenario: GraphQL client wants to delete the first category
  Given I am logged in to graphql as admin
  And I send a GraphQL POST request:
  """
  {
    "query": "mutation ($input: UpdateProposalFormInput!) {
      updateProposalForm(input: $input) {
        proposalForm {
          id
          categories {
            id
            name
          }
        }
      }
    }",
    "variables": {
      "input": {
        "proposalFormId": "proposalForm13",
        "categories": [
          {
            "id": "pCategory8",
            "name": "Escrime"
          },
          {
            "id": "pCategory7",
            "name": "Water Polo"
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
      "updateProposalForm": {
        "proposalForm": {
          "id": "proposalform13",
          "categories": [
            {
              "id": "pCategory8",
              "name": "Escrime"
            },
            {
              "id": "pCategory7",
              "name": "Water Polo"
            }
          ]
        }
      }
    }
  }
  """

@database
Scenario: GraphQL admin wants to update the view configuration of the form
  Given I am logged in to graphql as admin
  And I send a GraphQL POST request:
  """
  {
    "query": "mutation ($input: UpdateProposalFormInput!) {
      updateProposalForm(input: $input) {
        proposalForm {
          id
          isGridViewEnabled
          isListViewEnabled
          isMapViewEnabled
        }
      }
    }",
    "variables": {
      "input": {
        "proposalFormId": "proposalForm13",
        "isGridViewEnabled": false,
        "isListViewEnabled": true
      }
    }
  }
  """
  Then the JSON response should match:
  """
  {
    "data": {
      "updateProposalForm": {
        "proposalForm": {
          "id": "proposalform13",
          "isGridViewEnabled": false,
          "isListViewEnabled": true,
          "isMapViewEnabled": false
        }
      }
    }
  }
  """

@database
Scenario: GraphQL admin wants to disable all views and fails
  Given I am logged in to graphql as admin
  And I send a GraphQL POST request:
  """
  {
    "query": "mutation ($input: UpdateProposalFormInput!) {
      updateProposalForm(input: $input) {
        proposalForm {
          id
          isGridViewEnabled
          isListViewEnabled
          isMapViewEnabled
        }
      }
    }",
    "variables": {
      "input": {
        "proposalFormId": "proposalForm13",
        "isGridViewEnabled": false,
        "isListViewEnabled": false,
        "isMapViewEnabled": false
      }
    }
  }
  """
  Then the JSON response should match:
  """
  {
    "errors": [
      {
        "message": "No view is active. At least one must be selected",
        "@*@": "@*@"
      }
    ],
    "data": {
      "updateProposalForm": null
    }
  }
  """
