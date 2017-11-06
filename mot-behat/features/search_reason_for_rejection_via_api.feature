
Feature: Search Reasons For Rejection via api

  Scenario Outline: Search rfr
    Given I am logged in as a Tester
    When I search for reason for rejection by "<searchTerm>" for vehicle class 4 via api
    Then All returned reasons for rejection contain the exact "<expectedTerm>" term
    Examples:
      | searchTerm                   | expectedTerm |
      | brake                        | brake        |
      | 1.4.A.2a                     | 1.4.A.2a     |
      | brake xxxxx                  | brake        |

  Scenario Outline: User does not find rfr
    Given I am logged in as a Tester
    When I search for reason for rejection by "<searchTerm>" for vehicle class 4 via api
    Then Reason for rejection is not returned
    Examples:
      | searchTerm                      |
      | 123456789098834555              |
      | xxxx xxxzzzyyy                  |
      | asdasd asdsd dd xxxzzzyyy       |