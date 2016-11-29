@parallel_suite_2
Feature: Person address

  @address
  @create-user("Rocky Balboa")
  Scenario: A user with permission to edit other persons' addresses can update a different person's address
    Given I am logged in as an Scheme User
    When I change "Rocky Balboa" address to "1 Some Street", "Some Building", "Some Area", "Nottingham", "UK", "NG1 6LP"
    Then The person's address is updated

  @address
  Scenario: Any user can change their own address
    Given I am logged in as a Tester
    When I change my own address to "1 Some Street", "Some Building", "Some Area", "Nottingham", "UK", "NG1 6LP"
    Then The person's address is updated

  @address
  @create-user("Rocky Balboa")
  Scenario: A user without permission to edit other persons' addresses cannot update a different person's address
    Given I am logged in as a Tester
    When I try change "Rocky Balboa" address to "1 Some Street", "Some Building", "Some Area", "Nottingham", "UK", "NG1 6LP"
    Then I am forbidden from changing address

  @address
  Scenario Outline: Address validation is enforced
    Given I am logged in as a Finance User
    When I try change my own address to "<firstLine>", "<secondLine>", "<thirdLine>", "<townOrCity>", "<country>", "<postcode>"
    Then The person's address should not be updated

    Examples:
      | firstLine  | secondLine  | thirdLine  | townOrCity | country | postcode |
      |            | Second Line | Third Line | Nottingham | UK      | NG1 6LP  |
      | First Line | Second Line | Third Line |            | UK      | NG1 6LP  |
      | First Line |             | Third Line | Belfast    | NI      |          |
      | First Line | Second Line |            | Bristol    | UK      | xxxxx    |

  @address
  @details-changed-notification
  @create-user("Rocky Balboa")
  Scenario: A user gets a notification when the DVSA update their address
    Given I am logged in as a Area Office 1
    When I change "Rocky Balboa" address to "1 Some Street", "Some Building", "Some Area", "Nottingham", "UK", "NG1 6LP"
    Then "Rocky Balboa" should receive a notification about the change

  @address
  @details-changed-notification
  Scenario: A user does not get a notification when they update their own address
    Given I am logged in as a Tester
    When I change my own address to "1 Some Street", "Some Building", "Some Area", "Nottingham", "UK", "NG1 6LP"
    Then the person should not receive a notification about the change
