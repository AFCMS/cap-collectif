@project
Feature: Project

Scenario: Can not sort or filter if feature projects_form is disabled
  Given I visited "projects page"
  Then I should not see "project-theme"

@javascript @elasticsearch
Scenario: Project can be sorted by published date
  Given feature "projects_form" is enabled
  And I visited "projects page"
  And I wait 1 seconds
  And I select "global.filter_f_last" from "project-sorting"
  And I wait 1 seconds
  Then "Projet vide" should be before "Dépot avec selection vote budget" for selector "#project-preview .card__title a"

@javascript @elasticsearch
Scenario: Project can be sorted by contributions number
  Given feature "projects_form" is enabled
  And I visited "projects page"
  And I select "global.filter_f_popular" from "project-sorting"
  And I wait 1 seconds
  Then "Croissance, innovation, disruption" should be before "Projet de loi Renseignement" for selector "#project-preview .card__title a"

@javascript
Scenario: Project can be filtered by theme
  Given feature "themes" is enabled
  And feature "projects_form" is enabled
  And I visited "projects page"
  And I wait 1 seconds
  Then I should see 16 "#project-preview" elements
  And I select "Transport" from "project-theme"
  And I wait 1 seconds
  Then I should see 9 "#project-preview" elements
  And I should see "Projet vide"
  And I should see "Dépot avec selection vote budget"
  And I should not see "Croissance, innovation, disruption"

@javascript
Scenario: Project can be filtered by theme and sorted by contributions number at the same time
  Given feature "themes" is enabled
  And feature "projects_form" is enabled
  And I visited "projects page"
  And I select "Transport" from "project-theme"
  And I wait 1 seconds
  And I select "global.filter_f_popular" from "project-sorting"
  And I wait 1 seconds
  Then I should see 9 "#project-preview" elements
  And I should see "Projet de loi Renseignement"
  And I should see "Budget Participatif Rennes"
  And I should not see "Croissance, innovation, disruption"
  And "Stratégie technologique de l'Etat et services publics" should be before "Projet vide" for selector "#project-preview .card__title a"

@javascript
Scenario: Project can be filtered by type and sorted by contributions number at the same time
  And feature "projects_form" is enabled
  And I visited "projects page"
  And I select "project.types.consultation" from "project-type"
  And I wait 1 seconds
  And I select "global.filter_f_popular" from "project-sorting"
  And I wait 1 seconds
  Then I should see 5 "#project-preview" elements
  And I should see "Projet de loi Renseignement"
  And I should see "Stratégie technologique de l'Etat et services publics"
  And I should not see "Croissance, innovation, disruption"
  And "Stratégie technologique de l'Etat et services publics" should be before "Projet vide" for selector "#project-preview .card__title a"

@javascript
Scenario: Project can be filtered by title
  Given feature "projects_form" is enabled
  And I visited "projects page"
  When I fill in the following:
    | project-search-input | innovation |
  And I click the "#project-search-button" element
  And I wait 1 seconds
  Then I should see 1 "#project-preview" elements
  And I should see "Croissance, innovation, disruption"
  And I should not see "Stratégie technologique de l'Etat et services publics"
  And I should not see "Projet vide"

@javascript
Scenario: Presentation step should display correct number of element
  Given feature "calendar" is enabled
  And feature "blog" is enabled
  And I visited "consultation page" with:
    | projectSlug | croissance-innovation-disruption |
    | stepSlug    | collecte-des-avis                |
  And I follow "Présentation"
  Then I should see 2 ".media--news" elements
  And I should see 2 ".event" elements

@javascript
Scenario: Events menu for project should display correct number of events
  Given feature "calendar" is enabled
  And I visited "consultation page" with:
    | projectSlug | croissance-innovation-disruption |
    | stepSlug    | collecte-des-avis                |
  And I follow "Présentation"
  And I follow "project-events"
  And I should see 3 ".event" elements

@javascript
Scenario: Posts menu for project should display correct number of posts
  Given feature "blog" is enabled
  And I visited "consultation page" with:
    | projectSlug | croissance-innovation-disruption |
    | stepSlug    | collecte-des-avis                |
  And I follow "Présentation"
  And I follow "project-posts"
  And I should see 5 ".media--news" elements

@javascript
Scenario: Project header should display correct number of votes
  Given I visited "consultation page" with:
    | projectSlug | croissance-innovation-disruption |
    | stepSlug    | collecte-des-avis                |
  Then I should see "6 project.show.meta.votes_count"

Scenario: Can not have access to download if export is disabled
  Given I visited "consultation page" with:
    | projectSlug   | strategie-technologique-de-letat-et-services-publics |
    | stepSlug      | collecte-des-avis-pour-une-meilleur-strategie        |
  Then I should not see "project.download.button" in the "#main" element

@javascript
Scenario: Can not download a project if export is disabled
  Given I visited "home page"
  When I try to download "projets/strategie-technologique-de-letat-et-services-publics/projet/collecte-des-avis-pour-une-meilleur-strategie/download/xls"
  Then I should see "error.404.title"

@javascript
Scenario: Can not access trash if feature is disabled
  Given I am logged in as user
  And I visited "consultation page" with:
    | projectSlug | croissance-innovation-disruption |
    | stepSlug    | collecte-des-avis                |
  Then I should not see "project.show.trashed.short_name" in the "#main" element

@javascript
Scenario: Project trash display correct numbers of elements
  Given feature "project_trash" is enabled
  And I am logged in as user
  And I visited "consultation page" with:
    | projectSlug | croissance-innovation-disruption |
    | stepSlug    | collecte-des-avis                |
  When I should see "project.show.trashed.short_name" in the "#main" element
  When I click the "#trash-link" element
  Then I should see 75 ".opinion__list .opinion" elements
  And I should see "75 project.show.meta.total_count" in the "h3" element

@javascript
Scenario: Users can't see privates project
  Given feature "projects_form" is enabled
  And I visited "projects page"
  Then I should not see "Qui doit conquérir le monde ? | Visible par les admins seulement"

@javascript
Scenario: Anonymous can't access to a private project
  Given feature "projects_form" is enabled
  And I visited "collect page" with:
    | projectSlug | qui-doit-conquerir-le-monde-visible-par-les-admins-seulement |
    | stepSlug    | collecte-des-propositions-pour-conquerir-le-monde            |
  Then I should see "unauthorized-access"
  And I should see "restricted-access"
  When I follow "error.to_homepage"
  Then I should be redirected to "/"

@javascript
Scenario: Anonymous try to access to a wrong page
  Given feature "projects_form" is enabled
  And I visited "collect page" with:
    | projectSlug | qui-doit-conquerir-fautedefrappe-visible-par-les-admins-seulement |
    | stepSlug    | collecte-des-propositions-pour-conquerir-le-monde            |
  Then I should see "error.404.title"

@javascript
Scenario: Users can't access to a private project
  Given feature "projects_form" is enabled
  And I am logged in as user
  And I visited "collect page" with:
    | projectSlug | qui-doit-conquerir-le-monde-visible-par-les-admins-seulement |
    | stepSlug    | collecte-des-propositions-pour-conquerir-le-monde            |
  And I wait 1 seconds
  Then I should see 'restricted-access'
  When I follow "error.report"
  Then I should be redirected to "/contact"

@javascript
Scenario: Super Admin can access to all private projects
  Given feature "projects_form" is enabled
  And I am logged in as super admin
  And I visited "collect page" with:
    | projectSlug | qui-doit-conquerir-le-monde-visible-par-les-admins-seulement |
    | stepSlug    | collecte-des-propositions-pour-conquerir-le-monde            |
  And I wait 1 seconds
  Then I should see "Collecte des propositions pour conquérir le monde"
  And I should see "only-visible-by-administrators"
  When I visited "collect page" with:
    | projectSlug | project-pour-la-creation-de-la-capcobeer-visible-par-admin-seulement |
    | stepSlug    | collecte-des-propositions-pour-la-capcobeer                          |
  Then I should see "Collecte des propositions pour la capcoBeer"
  And I should see "global.draft.only_visible_by_you"
  When I visited "collect page" with:
    | projectSlug | project-pour-la-force-visible-par-mauriau-seulement |
    | stepSlug    | collecte-des-propositions-pour-la-force             |
  And I wait 1 seconds
  Then I should see "Collecte des propositions pour La Force"
  And I should see "global.draft.only_visible_by_you"

@javascript
Scenario: Admin can't access to a private project of other admin
  Given feature "projects_form" is enabled
  And I am logged in as admin
  When I visited "collect page" with:
    | projectSlug | project-pour-la-force-visible-par-mauriau-seulement |
    | stepSlug    | collecte-des-propositions-pour-la-force             |
  Then I should see 'restricted-access'

@javascript
Scenario: Admin access to his project and click to edit it
  Given feature "projects_form" is enabled
  And I am logged in as admin
  When I visited "collect page" with:
    | projectSlug | project-pour-la-creation-de-la-capcobeer-visible-par-admin-seulement |
    | stepSlug    | collecte-des-propositions-pour-la-capcobeer                          |
  Then I should see "Collecte des propositions pour la capcoBeer"
  And I should see "project.show.published_by admin"
  And I should see "global.draft.only_visible_by_you"
  Then I follow "action_edit"
  And I should be redirected to "/admin/capco/app/project/ProjectAccessibleForMeOnlyByAdmin/edit"
  Then I wait 2 seconds
  And I should see 'title_edit {"%name%":"Project pour la..."}'
