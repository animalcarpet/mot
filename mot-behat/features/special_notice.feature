Feature: Special Notices
  Admin users should be able to post special notices
  Users should be able to read any special notices sent to them

  @special-notice
  Scenario: Broadcast a Special Notice
    Given I am logged in as a Special Notice broadcast user
    When I send a new Special Notice broadcast
    Then I will see the broadcast was successful

  @special-notice
  Scenario Outline: Scheme User user creates new internal Special Notice
    Given I am logged in as a Scheme User
    And site with dvsa and vts users roles exists
    And I create a Special Notice with data:
      | targetRoles    | internalPublishDate   | externalPublishDate |
      | <target_roles> | <internal_date>       | <external_date>     |
    And I publish Special Notice
    When the Special Notice is broadcast
    Then users received Special Notice
    Examples:
      | target_roles              | internal_date   | external_date   |
      | DVSA, VTS                 | now             | tomorrow        |
      | DVSA, VTS                 | tomorrow        | tomorrow        |
      | DVSA, TESTER-CLASS-1      | now             | tomorrow        |
      | DVSA, TESTER-CLASS-1      | tomorrow        | tomorrow        |
      | DVSA                      | now             | tomorrow        |
      | DVSA, VTS, TESTER-CLASS-1 | now             | now             |
      | VTS, TESTER-CLASS-1       | tomorrow        | now             |
      | VTS, TESTER-CLASS-1       | tomorrow        | tomorrow        |
      | VTS, TESTER-CLASS-2       | tomorrow        | now             |
      | VTS, TESTER-CLASS-2       | tomorrow        | tomorrow        |

  @special-notice
  @create-tester("John Smith")
  Scenario: Remove a Special Notice
  Given Special Notice has been broadcast to testers
  When Schemeuser removes Special Notice
  Then "John Smith" does not see Special Notice