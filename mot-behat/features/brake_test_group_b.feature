Feature: MOT Test
  As a Tester
  I want to Perform an MOT Test
  So that I can verify a Vehicle is Road Worthy

  @brake-test
  Scenario Outline: Submit Decelerometer brake test values for vehicle type and check results
    Given I am logged in as a Tester
    And I start an Mot Test with a Class 4 Vehicle
    And the Tester adds an Odometer Reading of 1000 mi
    And the Tester adds a Class 3-7 Decelerometer Brake Test Result with custom <scenario>
    And the Tester Passes the Mot Test
    Then the Mot test status should be <status>
    Examples:
      | scenario                                      | status |
      | class4.decelerometer.commercial.valid.high    | PASSED |
      | class4.decelerometer.commercial.valid.low     | PASSED |
      | class4.decelerometer.goods.valid.high         | PASSED |
      | class4.decelerometer.goods.invalid.low        | FAILED |

  @brake-test
  Scenario Outline: Submit Decelerometer brake test values and check result
    Given I am logged in as a Tester
    And I start an Mot Test with a Class 4 Vehicle
    And the Tester adds an Odometer Reading of 1000 mi
    And the Tester adds a Class 3-7 Decelerometer Brake Test Result with custom <scenario>
    And the Tester Passes the Mot Test
    Then the Mot test status should be <status>
    Examples:
      | scenario                            | status |
      | class4.decelerometer.valid.high     | PASSED |
      | class4.decelerometer.valid.low      | PASSED |
      | class4.decelerometer.invalid.high   | FAILED |
      | class4.decelerometer.invalid.low    | FAILED |

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
  Scenario Outline: Submit Roller brake test values and check result
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
      | class4.roller.invalid.low   | FAILED  |
      | class4.roller.invalid.high  | FAILED  |

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