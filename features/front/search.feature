Feature: Search

Background:
  Given feature "search" is enabled


  @javascript
Scenario: Anonymous wants to search in all types
  Given I visited "search page"
  Then I should see "962 résultats" in the ".search__results-nb" element
  When I fill in the following:
    | q_term | article |
  And I click the ".btn[type='submit']" element
  And I wait 10 seconds
  Then I should see "6 résultats" in the ".search__results-nb" element

 @javascript
 Scenario: Anonymous wants to filter and sort search results
   Given I visited "search page"
   When I fill in the following:
     | q_term | coucou |
   And I click the "#q_type .radio:nth-child(3) label" element
   And I wait 5 seconds
   Then I should see "2 résultats" in the ".search__results-nb" element
   And "Coucou coucou coucou coucou coucou coucou...." should be before "Coucou !..." for selector ".search__results .search-result__preview"
   Then I select "Date" from "q_sort"
   And I wait 5 seconds
   And "Coucou !..." should be before "Coucou coucou coucou coucou coucou coucou...." for selector ".search__results .search-result__preview"

@javascript
Scenario: Anonymous wants to search in members
  Given I visited "search page"
  When I fill in the following:
    | q_term | sfavot |
  And I click the "#q_type .radio:nth-child(9) label" element
  And I wait 5 seconds
  Then I should see "1 résultat" in the ".search__results-nb" element
