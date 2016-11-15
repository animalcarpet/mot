Feature: Record a new event
  As a DVSA User
  I want the ability to record events for certain actions a user will take
  So that key activities can be stored against an entity to be viewed again

  @story
  @non-manual-event
  @create-user("Marty McFly")
  Scenario: Record a new security card event for a persons
    Given I am logged in as an Customer Service Operator
    When I submit "create security card order" non manual event for "Marty McFly"
    Then an event is generated for "Marty McFly" of "Security Card Order"