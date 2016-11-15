Feature: Record a new event
  As a DVSA User
  I want the ability to record a manual event
  So that key activities can be stored against an entity to be viewed again

  @story @VM-11545
  Scenario Outline: Record a new event for a person
    Given I am logged in as <permitted_user>
    And I create an event for "Mr Smith"
    And I select the event type CONVC
    And I supply a valid date
    And I select the event outcome NADAE
    And I supply a blank description
    When I submit the event
    Then an event is generated for "Mr Smith" of "Convictions: MOT, motor trade, criminal"

  Examples:
    | permitted_user        |
    | an Area Office User   |
    | an Area Office User 2 |
    | a Vehicle Examiner    |

  @story @VM-11545
  Scenario Outline: Record a new event for a site
    Given I am logged in as <permitted_user>
    And I create an event for a site
    And I select the event type SA
    And I supply a valid date
    And I select the event outcome NFA
    And I supply a blank description
    When I submit the event
    Then a site event is generated for the site of "Site Assessment"

  Examples:
    | permitted_user        |
    | an Area Office User   |
    | an Area Office User 2 |
    | a Vehicle Examiner    |

  @story @VM-11545
  Scenario Outline: Record a new event for an organisation
    Given I am logged in as <permitted_user>
    And I create an event for an organisation
    And I select the event type SA
    And I supply a valid date
    And I select the event outcome NFA
    And I supply a blank description
    When I submit the event
    Then an organisation event is generated for the organisation of "Site Assessment"

  Examples:
    | permitted_user        |
    | an Area Office User   |
    | an Area Office User 2 |
    | a Vehicle Examiner    |