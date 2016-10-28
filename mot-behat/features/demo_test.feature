Feature: Demo MOT Test
  As a Trainer
  I want to watch Testers perform a Demo MOT Test
  So that I can see how they interact with the system

  Scenario: Tester creates Demo MOT Test
    Given I am logged in as a Tester
    When I start a Demo MOT Test
    Then an MOT test number should be allocated

  Scenario: Tester tries to start a Demo MOT Test when they already have one in progress
    Given I am logged in as a Tester
    And I have a Demo MOT Test In Progress
    Then I am unable to start a new Demo MOT test

  Scenario Outline: Complete an MOT Test with No Odometer or Reading
    Given I start a Demo MOT test as a Tester
    And the Tester adds an Odometer Reading "<type>"
    And the Tester adds a Class 3-7 Roller Brake Test Result
    When the Tester Passes the Mot Test
    Then the MOT Test Status is "PASSED"
  Examples:
    | type     |
    | NOT READ |
    | NO METER |

  Scenario Outline: Complete MOT Test with Specific Mot Mileage/Kilometre
    Given I am logged in as a Tester
    When I start a Demo MOT Test
    And the Tester adds an Odometer Reading of <distance>
    And the Tester adds a Class 3-7 Roller Brake Test Result
    When the Tester Passes the Mot Test
    Then the MOT Test Status is "PASSED"
  Examples:
    | distance |
    | 499 km   |
    | 501 mi   |

  Scenario: Complete MOT Test with Decelerometer
    Given I am logged in as a Tester
    When I start a Demo MOT Test
    And the Tester adds an Odometer Reading of 1000 mi
    And the Tester adds a Class 3-7 Decelerometer Brake Test
    When the Tester Passes the Mot Test
    Then the MOT Test Status is "PASSED"

  Scenario: Add a Reason for Rejection
    Given I start a Demo MOT test as a Tester
    And the Tester adds an Odometer Reading of 1000 mi
    And the Tester adds a Class 3-7 Decelerometer Brake Test
    And the Tester adds a Reason for Rejection
    When the Tester Fails the Mot Test
    Then the MOT Test Status is "FAILED"

  @defect
  Scenario Outline: Being authorised to perform Demo Test, I can interact with reasons for rejection
    Given I am logged in as a <permitted user>
    When I start a Demo MOT Test
    Then I can search for Rfr
    And I can list child test items selector
    And I can add PRS to test
    And I can add a Failure to test
    And I can edit previously added Rfr
  Examples:
    | permitted user            |
    | Area Office User          |
    | Area Office User 2        |
    | Scheme Manager            |
    | Scheme User               |
    | Customer Service Manager  |
    | Customer Service Operator |
    | Finance User              |
    | DVLA Manager              |
    | DVLA Operative            |
    | GVTSTester                |


