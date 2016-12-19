@parallel_suite_2
Feature: MOT Test
  As a Tester
  I want to Perform an MOT Test
  So that I can verify a Vehicle is Road Worthy

  @brake-test
  Scenario Outline: As a tester I require the ability to pass Decelerometer test
    Given I am logged in as a Tester
    And I start an Mot Test with a Class 4 Vehicle
    And the Tester adds an Odometer Reading of 1000 mi
    And the Tester adds a Class 3-7 Decelerometer Brake Test Result with custom <scenario>
    And the Tester Passes the Mot Test
    Then the Mot test status should be <status>
    Examples:
      | scenario                                   | status |
      | class4.decelerometer.commercial.valid.high | PASSED |
      | class4.decelerometer.commercial.valid.low  | PASSED |
      | class4.decelerometer.goods.valid.high      | PASSED |
      | class4.decelerometer.valid.high            | PASSED |
      | class4.decelerometer.valid.low             | PASSED |

  @brake-test
  Scenario Outline: As a tester I require the ability to fail Decelerometer test
    Given I am logged in as a Tester
    And I start an Mot Test with a Class 4 Vehicle
    And the Tester adds an Odometer Reading of 1000 mi
    And the Tester adds a Class 3-7 Decelerometer Brake Test Result with custom <scenario>
    And the Tester Fails the Mot Test
    Then the Mot test status should be <status>
    Examples:
      | scenario                               | status |
      | class4.decelerometer.goods.invalid.low | FAILED |
      | class4.decelerometer.invalid.high      | FAILED |
      | class4.decelerometer.invalid.low       | FAILED |

  @brake-test
  Scenario Outline: Submit Reason For Rejection after submission of brake test result
    Given I am logged in as a Tester
    And I start an Mot Test with a Class 4 Vehicle
    And the Tester adds an Odometer Reading of 1000 mi
    And the Tester adds a Class 3-7 Roller Brake Test Result with custom <scenario>
    And the Tester adds a Reason for Rejection
    And the Tester Fails the Mot Test
    Then the Mot test status should be <status>
    Examples:
      | scenario                   | status |
      | class4.roller.invalid.high | FAILED |
      | class4.roller.invalid.low  | FAILED |

  @brake-test
  Scenario Outline: Pass Roller brake test values and check result
    Given I am logged in as a Tester
    And I start an Mot Test with a Class 4 Vehicle
    And the Tester adds an Odometer Reading of 1000 mi
    And the Tester adds a Class 3-7 Roller Brake Test Result with custom <scenario>
    And the Tester Passes the Mot Test
    Then the Mot test status should be <status>
    Examples:
      | scenario                    | status  |
      | class4.roller.valid.high    | PASSED  |
      | class4.roller.valid.low     | PASSED  |

  @brake-test
  Scenario Outline: Fail Roller brake test values and check result
    Given I am logged in as a Tester
    And I start an Mot Test with a Class 4 Vehicle
    And the Tester adds an Odometer Reading of 1000 mi
    And the Tester adds a Class 3-7 Roller Brake Test Result with custom <scenario>
    And the Tester Fails the Mot Test
    Then the Mot test status should be <status>
    Examples:
      | scenario                    | status  |
      | class4.roller.invalid.low   | FAILED  |
      | class4.roller.invalid.high  | FAILED  |

  @brake-test
  Scenario: RFR added if all wheels are under the 30% efficiency threshold and none have locked
    Given I am a Tester performing an MOT Test on a Class 4 Vehicle
    When I submit brake test results with all service brake controls under 30% efficiency and no wheels locked
    Then the "Service brake performance" - "efficiency below requirements" RFR should have been added

  @brake-test
  Scenario: RFR not added if one or more wheels is under the 30% efficiency threshold but it has locked
    Given I am a Tester performing an MOT Test on a Class 4 Vehicle
    When I submit brake test results with all service brake controls under 30% efficiency and wheels locked
    Then the "Service brake performance" - "efficiency below requirements" RFR should not have been added

  @brake-test
  Scenario: Putting vehicle weight in brake test updates vehicle record for MOT test
    Given I am logged in as a Tester
    And I start an MOT Test
    And the Tester adds an Odometer Reading
    When the Tester adds a Class 3-7 Plate Brake Test
    And the Tester Passes the Mot Test
    Then vehicle weight is updated

  @brake-test
  Scenario: Putting vehicle weight in brake test does not update vehicle record for demo test
    Given I am logged in as a Tester
    And I start a Demo MOT Test
    And the Tester adds an Odometer Reading
    When the Tester adds a Class 3-7 Plate Brake Test
    And the Tester Passes the Mot Test
    Then vehicle weight is not updated
