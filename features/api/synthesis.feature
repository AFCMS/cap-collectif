@synthesis
Feature: Synthesis
  As an API client
  I want to manage syntheses

  @database
  Scenario: API client wants to list syntheses
    Given I am logged in to api as admin
    And there is a synthesis with id "42" and elements:
      | 43 |
    And I send a GET request to "/api/syntheses"
    Then the JSON response should match:
    """
    [
      {
        "id": @string@,
        "elements": @array@,
        "_links": {
          "self": { "href": "@string@.startsWith('/api/syntheses/')" },
          "elements": { "href": "@string@.startsWith('/api/syntheses/').endsWith('/elements')" }
        }
      },
      @...@
    ]
    """

  @database
  Scenario: API client wants to get a synthesis
    Given there is a synthesis with id "42" and elements:
      | 43 |
    And I send a GET request to "/api/syntheses/42"
    Then the JSON response should match:
    """
    {
      "id": "42",
      "enabled": true,
      "editable": true,
      "_links": {
        "self": { "href": "/api/syntheses/42" },
        "elements": { "href": "/api/syntheses/42/elements" }
      },
      "elements": [
        {
          "id": "43",
          "title": "Je suis un élément",
          "_links": {
            "self": { "href": "/api/syntheses/42/elements/43" },
            "history": { "href": "/api/syntheses/42/elements/43/history" }
          }
        }
      ]
    }
    """

  @database
  Scenario: API client wants to create a synthesis
    Given I am logged in to api as admin
    And I send a POST request to "/api/syntheses" with json:
    """
    {
      "enabled": true
    }
    """
    Then the JSON response status code should be 201
    And the JSON response should match:
    """
    {
      "id": @string@,
      "enabled": true,
      "editable": true,
      "elements": [],
      "_links": {
        "self": { "href": "@string@.startsWith('/api/syntheses/')" },
        "elements": { "href": "@string@.startsWith('/api/syntheses/').endsWith('/elements')" }
      }
    }
    """

  Scenario: Non admin API client wants to create a synthesis
    Given I am logged in to api as user
    And I send a POST request to "/api/syntheses" with json:
    """
    {
      "enabled": true
    }
    """
    Then the JSON response status code should be 403

  Scenario: Anonymous API client wants to create a synthesis
    Given I send a POST request to "/api/syntheses" with json:
    """
    {
      "enabled": true
    }
    """
    Then the JSON response status code should be 401

  @database
  Scenario: API client wants to create a synthesis from a consultation step
    Given I am logged in to api as admin
    And I send a POST request to "/api/syntheses/from-consultation-step/2" with json:
    """
    {
      "enabled": true
    }
    """
    Then the JSON response status code should be 201
    And the JSON response should match:
    """
    {
      "id": @string@,
      "enabled": true,
      "editable": true,
      "elements": [
        {
            "id": @string@,
            "title": "Le problème constaté",
            "_links": {
              "self": { "href": "@string@.startsWith('/api/syntheses/').contains('/elements')" },
              "history": { "href": "@string@.startsWith('/api/syntheses/').contains('/elements/').endsWith('/history')" }
            }
        },
        {
          "id": @string@,
          "title": "Opinion 52",
          "_links": "@*@"
        },
        {
          "id": @string@,
          "title": "Arguments pour",
          "_links": "@*@"
        },
        {
          "id": @string@,
          "title": "Arguments contre",
          "_links": "@*@"
        },
        {
          "id": @string@,
          "title": @null@,
          "_links": "@*@"
        },
        {
          "id": @string@,
          "title": "Sources",
          "_links": "@*@"
        },
        {
          "id": @string@,
          "title": "Les causes",
          "_links": "@*@"
        },
        {
          "id": @string@,
          "title": "Opinion 51",
          "_links": "@*@"
        },
        {
          "id": @string@,
          "title": "Arguments pour",
          "_links": "@*@"
        },
        {
          "id": @string@,
          "title": "Arguments contre",
          "_links": "@*@"
        },
        {
          "id": @string@,
          "title": @null@,
          "_links": "@*@"
        },
        {
          "id": @string@,
          "title": @null@,
          "_links": "@*@"
        },
        {
          "id": @string@,
          "title": "Sources",
          "_links": "@*@"
        },
        {
          "id": @string@,
          "title": "Opinion 53",
          "_links": "@*@"
        },
        {
          "id": @string@,
          "title": "Arguments pour",
          "_links": "@*@"
        },
        {
          "id": @string@,
          "title": "Arguments contre",
          "_links": "@*@"
        },
        {
          "id": @string@,
          "title": "Sources",
          "_links": "@*@"
        }
      ],
      "_links": {
        "self": { "href": "@string@.startsWith('/api/syntheses/')" },
        "elements": { "href": "@string@.startsWith('/api/syntheses/').endsWith('/elements')" }
      }
    }
    """

  @database
  Scenario: API client wants to get elements count from consultation step synthesis
    Given I am logged in to api as admin
    And there is a synthesis with id "48" based on consultation step 2
    And I send a GET request to "/api/syntheses/48/elements/count?type=all"
    Then the JSON response should match:
    """
    {"count": 17}
    """

  @database
  Scenario: API client wants to update a synthesis
    Given I am logged in to api as admin
    And there is a synthesis with id "42" and elements:
      | 43 |
    And I send a PUT request to "/api/syntheses/42" with json:
    """
    {
      "enabled": false
    }
    """
    Then the JSON response status code should be 200
    And the JSON response should match:
    """
    {
      "id": "42",
      "enabled": false,
      "editable": true,
      "elements": [
        {
          "id": "43",
          "title": "Je suis un élément",
          "_links": {
            "self": { "href": "/api/syntheses/42/elements/43" },
            "history": { "href": "/api/syntheses/42/elements/43/history" }
          }
        }
      ],
      "_links": {
        "self": { "href": "/api/syntheses/42" },
        "elements": { "href": "/api/syntheses/42/elements" }
      }
    }
    """

  @database
  Scenario: Non admin API client wants to update a synthesis
    Given I am logged in to api as user
    And there is a synthesis with id "42" and elements:
      | 43 |
    And I send a PUT request to "/api/syntheses/42" with json:
    """
    {
      "enabled": false
    }
    """
    Then the JSON response status code should be 403

  @database
  Scenario: Anonymous API client wants to update a synthesis
    Given there is a synthesis with id "42" and elements:
      | 43 |
    And I send a PUT request to "/api/syntheses/42" with json:
    """
    {
      "enabled": false
    }
    """
    Then the JSON response status code should be 401

  @database
  Scenario: API client wants to get synthesis elements
    Given I am logged in to api as admin
    And there is a synthesis with id "42" and elements:
      | 43 |
    And I send a GET request to "/api/syntheses/42/elements?type=all"
    Then the JSON response should match:
    """
    {
      "elements": [
        {
          "has_linked_data": false,
          "id": "43",
          "published": false,
          "created_at": "@string@.isDateTime()",
          "updated_at": "@string@.isDateTime()",
          "archived": false,
          "author": {
            "displayName": "sfavot",
            "uniqueId": "sfavot",
            "isAdmin": true,
            "media": {
              "url": @string@
            },
            "vip": true,
            "_links": {
              "profile": @string@,
              "settings": @string@
            }
          },
          "path": "Je suis un élément-43",
          "displayType": "folder",
          "title": "Je suis un élément",
          "body": "blabla",
          "description": @null@,
          "notation": 4,
          "linkedDataCreation": @null@,
          "_links": {
            "self": { "href": "/api/syntheses/42/elements/43" },
            "history": { "href": "/api/syntheses/42/elements/43/history" }
          }
        }
      ],
      "count": 1
    }
    """

  @database
  Scenario: API client wants to get synthesis elements count
    Given I am logged in to api as admin
    And there is a synthesis with id "42" and elements:
      | 43 |
    And I send a GET request to "/api/syntheses/42/elements/count?type=all"
    Then the JSON response should match:
    """
    {"count": 1}
    """

  @database
  Scenario: API client wants to get new synthesis elements
    Given I am logged in to api as admin
    And there is a synthesis with id "42" and elements:
      | 43 |
    And I create an element in synthesis 42 with values:
      | id       | 44   |
      | archived | true |
    And I send a GET request to "/api/syntheses/42/elements?type=new"
    Then the JSON response should match:
    """
    {
      "elements": [
        {
          "has_linked_data": false,
          "id": "43",
          "published": false,
          "created_at": "@string@.isDateTime()",
          "updated_at": "@string@.isDateTime()",
          "archived": false,
          "author": {
            "displayName": "sfavot",
            "uniqueId": "sfavot",
            "isAdmin": true,
            "media": {
              "url": @string@
            },
            "vip": true,
            "_links": {
              "profile": @string@,
              "settings": @string@
            }
          },
          "path": "Je suis un élément-43",
          "displayType": "folder",
          "title": "Je suis un élément",
          "body": "blabla",
          "description": @null@,
          "notation": 4,
          "linkedDataCreation": @null@,
          "_links": {
            "self": { "href": "/api/syntheses/42/elements/43" },
            "history": { "href": "/api/syntheses/42/elements/43/history" }
          }
        }
      ],
      "count": 1
    }
    """

  @database
  Scenario: API client wants to get new synthesis elements count
    Given I am logged in to api as admin
    And there is a synthesis with id "42" and elements:
      | 43 |
    And I create an element in synthesis 42 with values:
      | id       | 44   |
      | archived | true |
    And I send a GET request to "/api/syntheses/42/elements/count?type=new"
    Then the JSON response should match:
    """
    {"count": 1}
    """

  @database
  Scenario: API client wants to get archived synthesis elements
    Given I am logged in to api as admin
    And there is a synthesis with id "42" and elements:
      | 43 |
    And I create an element in synthesis 42 with values:
      | id       | 44   |
      | archived | true |
    And I send a GET request to "/api/syntheses/42/elements?type=archived"
    Then the JSON response should match:
    """
    {
      "elements": [
        {
          "has_linked_data": false,
          "id": "44",
          "published": false,
          "created_at": "@string@.isDateTime()",
          "updated_at": "@string@.isDateTime()",
          "archived": true,
          "author": @null@,
          "path": "-44",
          "displayType": "folder",
          "title": @null@,
          "body": "blabla",
          "description": @null@,
          "notation": @null@,
          "linkedDataCreation": @null@,
          "_links": {
            "self": { "href": "/api/syntheses/42/elements/44" },
            "history": { "href": "/api/syntheses/42/elements/44/history" }
          }
        }
      ],
      "count": 1
    }
    """

  @database
  Scenario: API client wants to get archived synthesis elements count
    Given I am logged in to api as admin
    And there is a synthesis with id "42" and elements:
      | 43 |
    And I create an element in synthesis 42 with values:
      | id       | 44   |
      | archived | true |
    And I send a GET request to "/api/syntheses/42/elements/count?type=archived"
    Then the JSON response should match:
    """
    {"count": 1}
    """

  @database
  Scenario: API client wants to get unpublished synthesis elements
    Given I am logged in to api as admin
    And there is a synthesis with id "42" and elements:
      | 43 |
    And I create an element in synthesis 42 with values:
      | id        | 44    |
      | archived  | true  |
      | published | false |
    And I send a GET request to "/api/syntheses/42/elements?type=unpublished"
    Then the JSON response should match:
    """
    {
      "elements": [
        {
          "has_linked_data": false,
          "id": "44",
          "published": false,
          "created_at": "@string@.isDateTime()",
          "updated_at": "@string@.isDateTime()",
          "archived": true,
          "author": @null@,
          "path": "-44",
          "displayType": "folder",
          "title": @null@,
          "body": "blabla",
          "description": @null@,
          "notation": @null@,
          "linkedDataCreation": @null@,
          "_links": {
            "self": { "href": "/api/syntheses/42/elements/44" },
            "history": { "href": "/api/syntheses/42/elements/44/history" }
          }
        }
      ],
      "count": 1
    }
    """

  @database
  Scenario: API client wants to get unpublished synthesis elements count
    Given I am logged in to api as admin
    And there is a synthesis with id "42" and elements:
      | 43 |
    And I create an element in synthesis 42 with values:
      | id        | 44    |
      | archived  | true  |
      | published | false |
    And I send a GET request to "/api/syntheses/42/elements/count?type=unpublished"
    Then the JSON response should match:
    """
    {"count": 1}
    """

  @database
  Scenario: Anonymous wants to get synthesis elements published tree
    Given there is a synthesis with id "48" based on consultation step 2
    And I send a GET request to "/api/syntheses/48/elements/tree?type=published"
    Then the JSON response should match:
    """
    [
      {
        "id": @string@,
        "level": 0,
        "path": @string@,
        "displayType": "folder",
        "title": "Le problème constaté",
        "body": @string@,
        "description": @null@,
        "childrenCount": 0,
        "votes": [],
        "publishedChildrenCount": 0,
        "publishedParentChildrenCount": 0,
        "childrenScore": 0,
        "parentChildrenScore": 0,
        "childrenElementsNb": 0,
        "parentChildrenElementsNb": 0,
        "linkedDataUrl": @...@,
        "subtitle": @...@,
        "authorName": @...@,
        "children": []
      },
      {
        "id": @string@,
        "level": 0,
        "path": @string@,
        "displayType": "folder",
        "title": "Les causes",
        "body": @string@,
        "description": @null@,
        "childrenCount": 0,
        "votes": [],
        "publishedChildrenCount": 0,
        "publishedParentChildrenCount": 0,
        "childrenScore": 0,
        "parentChildrenScore": 0,
        "childrenElementsNb": 0,
        "parentChildrenElementsNb": 0,
        "linkedDataUrl": @...@,
        "subtitle": @...@,
        "authorName": @...@,
        "children": []
      }
    ]
    """

  @database
  Scenario: API client wants to get not ignored synthesis elements tree
    Given I am logged in to api as admin
    And there is a synthesis with id "48" based on consultation step 2
    And I send a GET request to "/api/syntheses/48/elements/tree?type=notIgnored"
    Then the JSON response should match:
    """
    [
      {
        "id": @string@,
        "level": 0,
        "path": @string@,
        "displayType": @string@,
        "title": @string@,
        "body": @string@,
        "description": @null@,
        "childrenCount": @integer@,
        "children": [
          {
            "id": @string@,
            "level": 1,
            "path": @string@,
            "displayType": @string@,
            "title": @string@,
            "body": @string@,
            "description": @null@,
            "childrenCount": @integer@,
            "children": [@...@]
          }
        ]
      },
      @...@
    ]
    """

  @database
  Scenario: API client wants to get synthesis elements tree
    Given I am logged in to api as admin
    And there is a synthesis with id "48" based on consultation step 2
    And I send a GET request to "/api/syntheses/48/elements/tree?type=all"
    Then the JSON response should match:
    """
    [
      {
        "id": @string@,
        "level": 0,
        "path": @string@,
        "displayType": @string@,
        "title": @string@,
        "body": @string@,
        "description": @null@,
        "childrenCount": @integer@,
        "children": [
          {
            "id": @string@,
            "level": 1,
            "path": @string@,
            "displayType": @string@,
            "title": @string@,
            "body": @string@,
            "description": @null@,
            "childrenCount": @integer@,
            "children": [@...@]
          }
        ]
      },
      @...@
    ]
    """

  @database
  Scenario: API client wants to get a synthesis element
    Given there is a synthesis with id "42" and elements:
      | 43 |
    And I send a GET request to "/api/syntheses/42/elements/43"
    Then the JSON response should match:
    """
    {
      "childrenCount": 0,
      "has_linked_data": false,
      "id": "43",
      "published": false,
      "created_at": "@string@.isDateTime()",
      "updated_at": "@string@.isDateTime()",
      "archived": false,
      "author": {
        "displayName": "sfavot",
        "uniqueId": "sfavot",
        "isAdmin": true,
        "media": {
          "url": @string@
        },
        "vip": true,
        "_links": {
          "profile": @string@,
          "settings": @string@
        }
      },
      "original_division": @null@,
      "division": @null@,
      "level": 0,
      "path": "Je suis un élément-43",
      "parent": @...@,
      "children": [],
      "displayType": "folder",
      "title": "Je suis un élément",
      "body": "blabla",
      "link": @null@,
      "notation": 4,
      "comment": @null@,
      "votes": {"-1": 21, "0":12, "1": 43},
      "linkedDataCreation": @null@,
      "logs": [
        {
          "id": @integer@,
          "action": "update",
          "logged_at": "@string@.isDateTime()",
          "version": 2,
          "sentences": [
            " a mis à jour l'élément"
          ]
        },
        {
          "id": @integer@,
          "action": "create",
          "logged_at": "@string@.isDateTime()",
          "version": 1,
          "sentences": [
            "Création de l'élément"
          ]
        }
      ],
      "_links": {
        "self": { "href": "/api/syntheses/42/elements/43" },
        "history": { "href": "/api/syntheses/42/elements/43/history" }
      }
    }
    """

  @database
  Scenario: API client wants to create a synthesis element
    Given I am logged in to api as admin
    And there is a synthesis with id "42" and elements:
      | 43 |
    And I send a POST request to "/api/syntheses/42/elements" with json:
    """
    {
      "title": "Coucou, je suis un élément.",
      "body": "blabla",
      "notation": 5,
      "archived": true,
      "published": true,
      "parent": "43"
    }
    """
    Then the JSON response status code should be 201
    And the JSON response should match:
    """
    {
      "childrenCount": 0,
      "has_linked_data": false,
      "id": @string@,
      "published": true,
      "created_at": "@string@.isDateTime()",
      "updated_at": "@string@.isDateTime()",
      "archived": true,
      "author": @...@,
      "original_division": @null@,
      "division": @null@,
      "path": @string@,
      "children": [],
      "displayType": "folder",
      "title": "Coucou, je suis un élément.",
      "body": "blabla",
      "link": @null@,
      "notation": 5,
      "comment": @null@,
      "votes": [],
      "linkedDataCreation": @null@,
      "logs": @...@,
      "_links": {
        "self": { "href": "@string@.startsWith('/api/syntheses/42/elements/')" },
        "history": { "href": "@string@.startsWith('/api/syntheses/42/elements/').endsWith('/history')" }
      }
    }
    """
    And there should be a create log on response element

  @database
  Scenario: Non admin API client wants to create a synthesis element
    Given I am logged in to api as user
    And there is a synthesis with id "42" and elements:
      | 43 |
    And I send a POST request to "/api/syntheses/42/elements" with json:
    """
    {
      "title": "Coucou, je suis un élément.",
      "body": "blabla",
      "notation": 5
    }
    """
    Then the JSON response status code should be 403

  @database
  Scenario: Anonymous API client wants to create a synthesis element
    Given there is a synthesis with id "42" and elements:
      | 43 |
    And I send a POST request to "/api/syntheses/42/elements" with json:
    """
    {
      "title": "Coucou, je suis un élément.",
      "body": "blabla",
      "notation": 5
    }
    """
    Then the JSON response status code should be 401

  @database
  Scenario: API client wants to update a synthesis element
    Given I am logged in to api as admin
    And there is a synthesis with id "42" and elements:
      | 43 |
    And I send a PUT request to "/api/syntheses/42/elements/43" with json:
    """
    {
      "published": true,
      "notation": 2,
      "comment": "Cet argument est vraiment nul !"
    }
    """
    Then the JSON response status code should be 200
    And the JSON response should match:
    """
    {
      "childrenCount": 0,
      "has_linked_data": false,
      "id": "43",
      "published": true,
      "created_at": "@string@.isDateTime()",
      "updated_at": "@string@.isDateTime()",
      "archived": false,
      "author": {
        "displayName": "sfavot",
        "uniqueId": "sfavot",
        "isAdmin": true,
        "media": {
          "url": @string@
        },
        "vip": true,
        "_links": {
          "profile": @string@,
          "settings": @string@
        }
      },
      "original_division": @null@,
      "division": @null@,
      "level": 0,
      "path": "Je suis un élément-43",
      "parent": @...@,
      "children": [],
      "displayType": "folder",
      "title": "Je suis un élément",
      "body": "blabla",
      "link": @null@,
      "notation": 2,
      "comment": "Cet argument est vraiment nul !",
      "votes": {"-1": 21, "0":12, "1": 43},
      "linkedDataCreation": @null@,
      "logs": @...@,
      "_links": {
        "self": { "href": "/api/syntheses/42/elements/43" },
        "history": { "href": "/api/syntheses/42/elements/43/history" }
      }
    }
    """

  @database
  Scenario: Non admin API client wants to update a synthesis element
    Given I am logged in to api as user
    And there is a synthesis with id "42" and elements:
      | 43 |
    And I send a PUT request to "/api/syntheses/42/elements/43" with json:
    """
    {
      "published": true,
      "notation": 2
    }
    """
    Then the JSON response status code should be 403

  @database
  Scenario: Anonymous API client wants to update a synthesis element
    Given there is a synthesis with id "42" and elements:
      | 43 |
    And I send a PUT request to "/api/syntheses/42/elements/43" with json:
    """
    {
      "published": true,
      "notation": 2
    }
    """
    Then the JSON response status code should be 401

  # @database
  # Scenario: API client wants to divide a synthesis element
  #   Given I am logged in to api as admin
  #   And there is a synthesis with id "42" and elements:
  #     | 43 |
  #   And I send a PUT request to "/api/syntheses/42/elements/43" with json:
  #   """
  #   {
  #     "archived": true,
  #     "published": false,
  #     "division": {
  #       "elements": [
  #         {
  #           "title": "Coucou, je suis un élément.",
  #           "body": "blabla",
  #           "notation": 5,
  #           "archived": true,
  #           "published": true
  #         },
  #         {
  #           "title": "Coucou, je suis un autre élément.",
  #           "body": "blabla",
  #           "notation": 3,
  #           "archived": true,
  #           "published": true
  #         },
  #         {
  #           "title": "Coucou, je suis le dernier élément.",
  #           "body": "blabla",
  #           "notation": 2,
  #           "archived": true,
  #           "published": true
  #         }
  #       ]
  #     }
  #   }
  #   """
  #   Then the JSON response status code should be 200
  #   And the JSON response should match:
  #   """
  #   {
  #     "has_linked_data": false,
  #     "id": "43",
  #     "published": false,
  #     "created_at": "@string@.isDateTime()",
  #     "updated_at": "@string@.isDateTime()",
  #     "archived": true,
  #     "author": @...@,
  #     "original_division": @null@,
  #     "division": {
  #       "id": @string@,
  #       "original_element": "43",
  #       "elements": [
  #         {
  #           "has_linked_data": false,
  #           "id": @string@,
  #           "published": true,
  #           "created_at": "@string@.isDateTime()",
  #           "updated_at": "@string@.isDateTime()",
  #           "archived": true,
  #           "author": @...@,
  #           "original_division": @string@,
  #           "division": @null@,
  #           "parent": @null@,
  #           "children": [],
  #           "displayType": "folder",
  #           "title": "Coucou, je suis un élément.",
  #           "body": "blabla",
  #           "link": @null@,
  #           "notation": 5,
  #           "votes": [],
  #           "linkedDataCreation": @null@,
  #           "_links": {
  #             "self": { "href": "/api/syntheses/42/elements/43" },
  #             "history": { "href": "/api/syntheses/42/elements/43/history" }
  #           }
  #         },
  #         @...@
  #       ]
  #     },
  #     "parent": @null@,
  #     "children": [],
  #     "displayType": "folder",
  #     "title": "Je suis un élément",
  #     "body": "blabla",
  #     "link": @null@,
  #     "notation": @null@,
  #     "comment": @null@,
  #     "votes": {"-1": 21, "0":12, "1": 43},
  #     "linkedDataCreation": @null@,
  #     "logs": @...@,
  #     "_links": {
  #       "self": { "href": "/api/syntheses/42/elements/43" },
  #       "history": { "href": "/api/syntheses/42/elements/43/history" }
  #     }
  #   }
  #   """
  #   And there should be a log on element 43 with sentence "admin a divisé l'élément"

  @database
  Scenario: Non admin API client wants to divide a synthesis element
    Given I am logged in to api as user
    And there is a synthesis with id "42" and elements:
      | 43 |
    And I send a PUT request to "/api/syntheses/42/elements/43" with json:
    """
    {
      "division": {
        "elements": [
          {
            "title": "Coucou, je suis un élément.",
            "body": "blabla",
            "notation": 5
          },
          {
            "title": "Coucou, je suis un autre élément.",
            "body": "blabla",
            "notation": 3
          },
          {
            "title": "Coucou, je suis le dernier élément.",
            "body": "blabla",
            "notation": 2
          }
        ]
      }
    }
    """
    Then the JSON response status code should be 403

  @database
  Scenario: Anonymous API client wants to divide a synthesis element
    Given there is a synthesis with id "42" and elements:
      | 43 |
    And I send a PUT request to "/api/syntheses/42/elements/43" with json:
    """
    {
      "division": {
        "elements": [
          {
            "title": "Coucou, je suis un élément.",
            "body": "blabla",
            "notation": 5
          },
          {
            "title": "Coucou, je suis un autre élément.",
            "body": "blabla",
            "notation": 3
          },
          {
            "title": "Coucou, je suis le dernier élément.",
            "body": "blabla",
            "notation": 2
          }
        ]
      }
    }
    """
    Then the JSON response status code should be 401

  @database
  Scenario: API client wants to get a synthesis element history
    Given I am logged in to api as admin
    And there is a synthesis with id "42" and elements:
      | 43 |
    And I send a GET request to "/api/syntheses/42/elements/43/history"
    Then the JSON response should match:
    """
    [
      {
        "id": @integer@,
        "action": "update",
        "logged_at": "@string@.isDateTime()",
        "version": 2,
        "sentences": [
          " a mis à jour l'élément"
        ]
      },
      {
        "id": @integer@,
        "action": "create",
        "logged_at": "@string@.isDateTime()",
        "version": 1,
        "sentences": [
          "Création de l'élément"
        ]
      }
    ]
    """

  @database
  Scenario: After updating an element, there should be an 'update' log
    Given I am logged in to api as admin
    And there is a synthesis with id "42" and elements:
      | 43 |
    And I send a PUT request to "/api/syntheses/42/elements/43" with json:
    """
    {
      "title": "Coucou, je suis un élément avec un titre modifié."
    }
    """
    Then there should be a log on element 43 with sentence "admin a mis à jour l'élément"

  @database
  Scenario: After changing an element's parent, there should be a 'move' log
    Given I am logged in to api as admin
    And there is a synthesis with id "42" and elements:
      | 43 |
    And I create an element in synthesis 42 with values:
      | id       | 47                          |
    And I send a PUT request to "/api/syntheses/42/elements/43" with json:
    """
    {
      "parent": 47
    }
    """
    Then there should be a log on element 43 with sentence "admin a déplacé l'élément"

  @database
  Scenario: After publishing an element, there should be a 'publish' log
    Given I am logged in to api as admin
    And there is a synthesis with id "42" and elements:
      | 43 |
    And I create an element in synthesis 42 with values:
      | id         | 47                          |
      | published  | false                       |
    And I send a PUT request to "/api/syntheses/42/elements/47" with json:
    """
    {
      "published": true
    }
    """
    Then there should be a log on element 47 with sentence "admin a publié l'élément"

  @database
  Scenario: After unpublishing an element, there should be an 'unpublish' log
    Given I am logged in to api as admin
    And there is a synthesis with id "42" and elements:
      | 43 |
    And I create an element in synthesis 42 with values:
      | id        | 47    |
      | published | true |
    And I send a PUT request to "/api/syntheses/42/elements/47" with json:
    """
    {
      "published": false
    }
    """
    Then there should be a log on element 47 with sentence "admin a dépublié l'élément"

  @database
  Scenario: After archiving an element, there should be an 'archive' log
    Given I am logged in to api as admin
    And there is a synthesis with id "42" and elements:
      | 43 |
    And I send a PUT request to "/api/syntheses/42/elements/43" with json:
    """
    {
      "archived": true
    }
    """
    Then there should be a log on element 43 with sentence "admin a marqué l'élément comme traité"

  @database
  Scenario: After noting an element, there should be a 'note' log
    Given I am logged in to api as admin
    And there is a synthesis with id "42" and elements:
      | 43 |
    And I send a PUT request to "/api/syntheses/42/elements/43" with json:
    """
    {
      "notation": 1
    }
    """
    Then there should be a log on element 43 with sentence "admin a modifié la note de l'élément"

  @database
  Scenario: After commenting an element, there should be a 'comment' log
    Given I am logged in to api as admin
    And there is a synthesis with id "42" and elements:
      | 43 |
    And I send a PUT request to "/api/syntheses/42/elements/43" with json:
    """
    {
      "comment": "Super contribution !"
    }
    """
    Then there should be a log on element 43 with sentence "admin a commenté l'élément"

  @database
  Scenario: After updating an opinion, I want to get the updated synthesis
    Given there is a synthesis with id "48" based on consultation step 2
    And I do nothing for 2 seconds
    When I update opinion 51 with values:
      | title | Je suis le nouveau titre |
    And I send a GET request to "api/syntheses/48/updated"
    Then the JSON response should match:
    """
    {
      "id": "48",
      "enabled": true,
      "editable": true,
      "elements": [
        @...@,
        {
          "id": @string@,
          "title": "Je suis le nouveau titre",
          "_links": {
            "self": { "href": "@string@.startsWith('/api/syntheses/48/elements/')" },
            "history": { "href": "@string@.startsWith('/api/syntheses/48/elements/').endsWith('/history')" }
          }
        },
        @...@
      ],
      "_links": {
        "self": { "href": "/api/syntheses/48" },
        "elements": { "href": "/api/syntheses/48/elements" }
      }
    }
    """
