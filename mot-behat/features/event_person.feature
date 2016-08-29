Feature: Record a new event
  As a DVSA User
  I want the ability to record events for certain actions a user will take
  So that key activities can be stored against an entity to be viewed again

  @story
  @non-manual-event
  Scenario: Record a new security card event for a person
    Given I am logged in as an Customer Service Operator
    And I create an event for a person
    And I select the event type "SCO"
    When I submit the non manual event
    Then an event is generated for the user of "Security Card Order"