Feature: MOT Test Certificate

  @jasper
  Scenario Outline: Create certificate for a passed MOT test for class 1 - 2
    Given I am logged in as a Tester
    And I pass Mot Test with a Class "<class>" Vehicle
    And requests the certificate
    Then the certificate contains <no_of_pages> pages
    And the certificate contains the text <expected_text>
    Examples:
      | class | no_of_pages | expected_text |
      | 1     | 1           | VT20          |
      | 2     | 1           | VT20          |

  @jasper
  Scenario Outline: Create certificate for a failed MOT test for class 1 - 2
    Given I am logged in as a Tester
    And I fail Mot Test with a Class "<class>" Vehicle
    And requests the certificate
    Then the certificate contains <no_of_pages> pages
    And the certificate contains the text <expected_text>
    Examples:
      | class | no_of_pages | expected_text |
      | 1     | 1           | VT30          |
      | 2     | 1           | VT30          |

  @jasper
  Scenario Outline: Create certificate for an MOT test for class 3 - 7
    Given I am logged in as a Tester
    And I pass Mot Test with a Class "<class>" Vehicle
    And requests the certificate
    Then the certificate contains <no_of_pages> pages
    And the certificate contains the text <expected_text>
    Examples:
      | class | no_of_pages | expected_text |
      | 3     | 1           | VT20          |
      | 4     | 1           | VT20          |
      | 5     | 1           | VT20          |
      | 7     | 1           | VT20          |

  @jasper
  Scenario Outline: Create certificate for a failed MOT test for class 3 - 7
    Given I am logged in as a Tester
    And I fail Mot Test with a Class "<class>" Vehicle
    And requests the certificate
    Then the certificate contains <no_of_pages> pages
    And the certificate contains the text <expected_text>
    Examples:
      | class | no_of_pages | expected_text |
      | 3     | 1           | VT30          |
      | 4     | 1           | VT30          |
      | 5     | 1           | VT30          |
      | 7     | 1           | VT30          |

  Scenario: When I print certificate only odometer readings from 3 historical passed MOT tests and current passed test should be fetched
    Given I am logged in as a Tester
    And I Create a new vehicle
    And 5 passed MOT tests have been created for the same vehicle
    And 2 failed MOT tests have been created for the same vehicle
    And 1 passed MOT tests have been created for the same vehicle
    When I fetch jasper document for test
    Then document has only 4 odometer readings from newest passed tests

  @quarantine
  Scenario: When I print certificate only odometer readings taken before that test should be fetched
    Given I am logged in as a Tester
    And I Create a new vehicle
    And 4 passed MOT tests have been created for the same vehicle
    When I fetch jasper document for test
    Then document has only odometer readings from tests performed in past

  @quarantine
  Scenario: When I print certificate for migrated test only odometer readings taken before that test should be fetched
    Given I am logged in as a Tester
    # hold your horses, it was creating that vehicle under the hood anyway,
    And I Create a new vehicle
    And 4 passed MOT tests have been migrated for the same vehicle
    And print of migrated mot tests is issued
    When I fetch jasper document for test
    Then document has only odometer readings from tests performed in past
