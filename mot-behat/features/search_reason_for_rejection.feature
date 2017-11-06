
Feature: Search Reasons For Rejection

  Scenario Outline: Search rfr
    Given I am logged in as a Tester
    When I search for reason for rejection by "<searchTerm>" for vehicle class 4
    Then Any of the returned reasons for rejection contains the exact "<expectedTerm>" term
    Examples:
      | searchTerm                   | expectedTerm |
      | 834                          | 834          |
      | brake                        | brake        |
      | brxke                        | brake        |
      | 1.4.A.2a                     | 1.4.A.2a     |
      | Brxke p3rformance not testED | Brake        |
      | 7.3                          | 7.3          |

  Scenario Outline: Search rfr by manual reference number
    Given I am logged in as a Tester
    When I search for reason for rejection by "<searchTerm>" for vehicle class 4
    Then Any of the returned reasons for rejection contains the exact "<expectedTerm>" term
    Examples:
      | searchTerm                   | expectedTerm |
      | 7.3                          | 7.3.D        |

  Scenario Outline: User does not find rfr
    Given I am logged in as a Tester
    When I search for reason for rejection by "<searchTerm>" for vehicle class 4
    Then Reason for rejection is not returned
    Examples:
      | searchTerm                      |
      | 123456789098834555              |
      | brake xxxzzzyyy                 |
      | Brake performance not xxxzzzyyy |

  Scenario Outline: User finds synonymed rfrs
    Given I am logged in as a Tester
    When I search for reason for rejection by "<baseTerm>" for vehicle class 4
    Then The first returned element contains "<synonymTerm>" but not "<baseTerm>"
    Examples:
      | baseTerm        | synonymTerm      |
      | rusted          | corroded         |
      | stiff           | tight            |
