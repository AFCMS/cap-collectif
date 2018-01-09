@proposals
Feature: Edit a proposal

@database @elasticsearch
Scenario: Logged in admin wants edit a proposal content
  Given I am logged in as admin
  And I go to the admin proposal page with proposalid "proposal10"
  And I fill in the following:
    | title | Proposition pas encore votable |
    | summary | "Un super résumé" |
    | proposal_body | "Look, just because I don't be givin' no man a foot massage don't make it right for Marsellus to throw Antwone into a glass motherfucking' house, fucking' up the way the nigger talks. Motherfucker do that shit to me, he better paralyze my ass, 'cause I'll kill the motherfucker, know what I'm sayin'?" |
    | responses[1]  | HAHAHA |
  And I fill the proposal content address with "5 Allée Rallier-du-Baty 35000 Rennes"
  And I change the proposals Category
  And I attach the file "/var/www/features/files/image.jpg" to "proposal_media_field"
  And I attach the file "/var/www/features/files/document.pdf" to "responses[2]_field"
  And I wait 3 seconds
  Then I save current admin content proposal
  And I wait 1 seconds
  Then I should see "global.saved"

@database @elasticsearch
Scenario: Logged in admin wants edit a proposal advancement tab
  Given I am logged in as admin
  And I go to the admin proposal page with proposalid "proposal10"
  Then I go to the admin proposal advancement tab
  And I toggle a proposal advancement "proposal advancement selection"
  And I wait 3 seconds
  And I change the proposal advancement select "proposal advancement selection status" with option "Soumis au vote"
  Then I save current proposal admin advancement
  And I wait 1 seconds
  Then I should see "global.saved"

@database @elasticsearch
Scenario:Logged in admin, wants to edit a proposal evaluation (adding analyst groupes)
  Given I am logged in as admin
  And I go to the admin proposal page with proposalid "proposal10"
  Then I go to the admin proposal evaluation tab
  And I fill "ag" and "Utilisateurs" to the analyst select
  And I save the current proposal evaluation analysts groupes
  And I wait 1 seconds
  Then I should see "global.saved"

@database @elasticsearch
Scenario:Logged in admin, wants to edit a proposal evaluation (evaluate) with custom form
  Given I am logged in as admin
  And I go to the admin proposal page with proposalid "proposal10"
  Then I go to the admin proposal evaluation tab
  And I fill the element "proposal evaluation evaluate" with value "Bonne"
  And I fill the element "proposal evaluation evaluate more information" with value "C'est génial cette appli, les gens sont investit l'évaluation marche super bien !"
  And I pick Comment trouvez-vous cette présentation with value Au top
  And I check "Incohérente" in the proposal definition evaluation
  And  I check "Je dis oui" in the proposal definition resume
  And I save the custom evaluation
  And I wait 1 seconds
  Then I should see "global.saved"
