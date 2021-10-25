@emailingCampaign @admin
Feature: emailingCampaign

Scenario: GraphQL client wants to get all campaigns
  Given I am logged in to graphql as admin
  And I send a GraphQL POST request:
  """
  {
    "query": "{
      emailingCampaigns {
        totalCount
        edges {
          node {
            name
            owner {
              username
            }
            senderEmail
            senderName
            object
            content
            unlayerConf
            sendAt
            status
            mailingInternal
            mailingList {
              name
            }
            preview
          }
        }
      }
    }"
  }
  """
  Then the JSON response should match:
  """
  {
    "data": {
      "emailingCampaigns": {
        "totalCount":4,
        "edges":[
          {
            "node": {
              "name": "Campagne pour remercier les inscrits confirmés",
              "owner": null,
              "senderEmail": "dev@cap-collectif.com",
              "senderName": "Les devs de capco",
              "object": "Merci",
              "content": "<p>C'est gentil d'avoir participé.</p>",
              "unlayerConf": null,
              "sendAt": "2021-01-01 00:00:00",
              "status": "PLANNED",
              "mailingInternal": "CONFIRMED",
              "mailingList": null,
              "preview": @string@
            }
          },
          {
            "node": {
              "name": "Campagne pour rappeler aux utilisateurs de confirmer",
              "owner": null,
              "senderEmail": "dev@cap-collectif.com",
              "senderName": "Les devs de capco",
              "object": "Veuillez confirmer votre email",
              "content": "<p>Pourquoi vous l'avez toujours pas fait ?</p>",
              "unlayerConf": null,
              "sendAt": "2020-10-01 00:00:00",
              "status": "SENT",
              "mailingInternal": "NOT_CONFIRMED",
              "mailingList": null,
              "preview": @string@
            }
          },
          {
            "node": {
              "name": "On envoie un message à ceux qui ont participé au projet Solidarité COVID-19",
              "owner": null,
              "senderEmail": "dev@cap-collectif.com",
              "senderName": "Les devs de capco",
              "object": "Second confinement",
              "content": "<p>Je pense qu'on va devoir relancer le projet.</p>",
              "unlayerConf": "{\"who\":\"agui\"}",
              "sendAt": null,
              "status": "DRAFT",
              "mailingInternal": null,
              "mailingList": {
                "name": "liste Solidarit\u00e9 COVID-19"
              },
              "preview": @string@
            }
          },
          {
            "node": {
              "name": "Campagne pour le projet avec propriétaire",
              "owner": {
                "username": "Théo QP"
              },
              "senderEmail": "assistance@cap-collectif.com",
              "senderName": "Théo qui assiste",
              "object": "",
              "content": "",
              "unlayerConf": null,
              "sendAt": null,
              "status": "DRAFT",
              "mailingInternal": null,
              "mailingList": {
                "name": "Liste du projet de Théo"
              },
              "preview": @string@
            }
          }
        ]
      }
    }
  }
  """

Scenario: GraphQL client wants to get planned campaigns
  Given I am logged in to graphql as admin
  And I send a GraphQL POST request:
  """
  {
    "query": "{
      emailingCampaigns(status: PLANNED) {
        totalCount
        edges {
          node {
            name
            sendAt
            status
          }
        }
      }
    }"
  }
  """
  Then the JSON response should match:
  """
  {
    "data": {
      "emailingCampaigns": {
        "totalCount":1,
        "edges":[
          {
            "node": {
              "name": "Campagne pour remercier les inscrits confirmés",
              "sendAt": "2021-01-01 00:00:00",
              "status": "PLANNED"
            }
          }
        ]
      }
    }
  }
  """

Scenario: GraphQL client wants to sort campaigns by sendDate
  Given I am logged in to graphql as admin
  And I send a GraphQL POST request:
  """
  {
    "query": "{
      emailingCampaigns(
        orderBy: {
        field: SEND_AT
          direction: ASC
        }
      ) {
        totalCount
        edges {
          node {
            name
            sendAt
          }
        }
      }
    }"
  }
  """
  Then the JSON response should match:
  """
  {
    "data": {
      "emailingCampaigns": {
        "totalCount":4,
        "edges":[
          {
            "node": {
              "name": "On envoie un message à ceux qui ont participé au projet Solidarité COVID-19",
              "sendAt": null
            }
          },
          {
            "node": {
              "name": "Campagne pour le projet avec propriétaire",
              "sendAt": null
            }
          },
          {
            "node": {
              "name": "Campagne pour rappeler aux utilisateurs de confirmer",
              "sendAt": "2020-10-01 00:00:00"
            }
          },
          {
            "node": {
              "name": "Campagne pour remercier les inscrits confirmés",
              "sendAt": "2021-01-01 00:00:00"
            }
          }
        ]
      }
    }
  }
  """

Scenario: GraphQL client wants to get search a campaign
  Given I am logged in to graphql as admin
  And I send a GraphQL POST request:
  """
  {
    "query": "{
      emailingCampaigns(term: \"COVID-19\") {
        totalCount
        edges {
          node {
            name
            object
          }
        }
      }
    }"
  }
  """
  Then the JSON response should match:
  """
  {
    "data": {
      "emailingCampaigns": {
        "totalCount":1,
        "edges":[
          {
            "node": {
              "name": "On envoie un message à ceux qui ont participé au projet Solidarité COVID-19",
              "object": "Second confinement"
            }
          }
        ]
      }
    }
  }
  """
