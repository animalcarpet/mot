Feature: Person name

  @create-user("Freddy Krueger")
  Scenario: A user with permission to change names can update a different person's name
    Given I am logged in as a Scheme Manager
    When I change "Freddy Krueger" name to Joe Bloggs Smith
    Then The person's name should be updated

  Scenario: A user with permission to change names cannot update their own name
    Given I am logged in as a Area Office 1
    When I try change my own name to Joe Bloggs Smith
    Then I am forbidden from changing name

  @create-user("Freddy Krueger")
  Scenario: A user without permission to change names should be forbidden
    Given I am logged in as a Tester
    When I try change "Freddy Krueger" name to Joe Bloggs Smith
    Then I am forbidden from changing name

  @create-user("Freddy Krueger")
  Scenario Outline: Name validation is enforced
    Given I am logged in as an Scheme User
    When I try change "Freddy Krueger" name to "<firstName>" "<middleName>" "<lastName>"
    Then The person's name should not be updated

    Examples:
      | firstName                                      | middleName                                     | lastName                                        |
      |                                                |                                                |                                                 |
      | Joe                                            |                                                |                                                 |
      |                                                |                                                |  Bloggs                                         |
      | Thisnameislongerthan45characterssoitisinvalidl |                                                |                                                 |
      | Joe                                            | Thisnameislongerthan45characterssoitisinvalidl |  Bloggs                                         |
      | Joe                                            |                                                |  Thisnameislongerthan45characterssoitisinvalidl |

  @details-changed-notification
  @create-user("Freddy Krueger")
  Scenario: A user gets a notification when their name is updated by DVSA
    Given I am logged in as a Scheme Manager
    When I change "Freddy Krueger" name to Joe Bloggs Smith
    Then "Freddy Krueger" should receive a notification about the change
