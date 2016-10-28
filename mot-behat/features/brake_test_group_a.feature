@transform
Feature: MOT Test
  As a Tester
  I want to Perform an MOT Test
  So that I can verify a Vehicle is Road Worthy

  @brake-test
  Scenario Outline: As a tester I require the ability to pass Roller test on class 1 & 2 vehicles
    Given I am logged in as a Tester
    And I start an Mot Test with a Class 1 Vehicle
    And the Tester adds an Odometer Reading of 1000 mi
    And I add roller brake test data for <scenario>
    And the Tester Passes the Mot Test
    Then the Mot test status should be <result>
    Examples:
      | scenario                     | result  |
      | class1.roller.valid.high     | PASSED  |
      | class1.roller.valid.low      | PASSED  |
      | class1.roller.locks.locked   | PASSED  |
      | class1.roller.locks.ctrl1Pass| PASSED  |
      | class1.roller.locks.ctrl2Pass| PASSED  |

  Scenario Outline: As a tester I require the ability to fail Roller test on class 1 & 2 vehicles
    Given I am logged in as a Tester
    And I start an Mot Test with a Class 1 Vehicle
    And the Tester adds an Odometer Reading of 1000 mi
    And I add roller brake test data for <scenario>
    And the Tester Fails the Mot Test
    Then the Mot test status should be <result>
    Examples:
      | scenario                     | result  |
      | class1.roller.invalid.high   | FAILED  |
      | class1.roller.invalid.low    | FAILED  |

  @brake-test
  Scenario Outline: As a tester I require the ability to pass Plate test on class 1 & 2 vehicles
    Given I am logged in as a Tester
    And I start an Mot Test with a Class 1 Vehicle
    And the Tester adds an Odometer Reading of 1000 mi
    And I add plate brake test data for <scenario>
    And the Tester Passes the Mot Test
    Then the Mot test status should be <result>
    Examples:
      | scenario                     | result  |
      | class1.plate.valid.high     | PASSED  |
      | class1.plate.valid.low      | PASSED  |
      | class1.plate.locks.locked   | PASSED  |
      | class1.plate.locks.ctrl1Pass| PASSED  |
      | class1.plate.locks.ctrl2Pass| PASSED  |

  @brake-test
  Scenario Outline: As a tester I require the ability to fail Plate test on class 1 & 2 vehicles
    Given I am logged in as a Tester
    And I start an Mot Test with a Class 1 Vehicle
    And the Tester adds an Odometer Reading of 1000 mi
    And I add plate brake test data for <scenario>
    And the Tester Fails the Mot Test
    Then the Mot test status should be <result>
    Examples:
      | scenario                     | result  |
      | class1.plate.invalid.high   | FAILED  |
      | class1.plate.invalid.low    | FAILED  |

  @brake-test
  Scenario Outline: As a tester I require the ability to pass Decelerometer test on class 1 & 2 vehicles
    Given I am logged in as a Tester
    And I start an Mot Test with a Class 1 Vehicle
    And the Tester adds an Odometer Reading of 1000 mi
    And the Tester adds a Class 1-2 Decelerometer Brake Test with custom brake data <scenario>
    And the Tester Passes the Mot Test
    Then the Mot test status should be <result>
    Then the controlOne and controlTwo status should be <control1Pass> <control2Pass>
    Examples:
      |scenario                           |control1Pass  |control2Pass |result    |
      |class1.decelerometer.valid.high    |true          |true         |PASSED    |
      |class1.decelerometer.valid.low     |true          |true         |PASSED    |

  @brake-test
  Scenario Outline: As a tester I require the ability to perform Decelerometer test on class 1 & 2 vehicles
    Given I am logged in as a Tester
    And I start an Mot Test with a Class 1 Vehicle
    And the Tester adds an Odometer Reading of 1000 mi
    And the Tester adds a Class 1-2 Decelerometer Brake Test with custom brake data <scenario>
    And the Tester Fails the Mot Test
    Then the Mot test status should be <result>
    Then the controlOne and controlTwo status should be <control1Pass> <control2Pass>
    Examples:
      |scenario                           |control1Pass  |control2Pass |result    |
      |class1.decelerometer.invalid.high  |false         |false        |FAILED    |
      |class1.decelerometer.invalid.low   |false         |false        |FAILED    |

  @brake-test
  Scenario Outline: As a tester I require the ability to pass Gradient test on class 1 & 2 vehicles
    Given I am logged in as a Tester
    And I start an Mot Test with a Class 1 Vehicle
    And the Tester adds an Odometer Reading of 1000 mi
    And I add gradient brake test data for <scenario>
    And the Tester Passes the Mot Test
    Then the controlOne and controlTwo status should be <control1Pass> <control2Pass>
    Then the Mot test status should be <result>
    Examples:
      | scenario                      |control1Pass  |control2Pass  |result     |
      | class1.gradient.valid.high    |true          |true          |PASSED     |
      | class1.gradient.valid.low     |true          |true          |PASSED     |

  @brake-test
  Scenario Outline: As a tester I require the ability to fail Gradient test on class 1 & 2 vehicles
    Given I am logged in as a Tester
    And I start an Mot Test with a Class 1 Vehicle
    And the Tester adds an Odometer Reading of 1000 mi
    And I add gradient brake test data for <scenario>
    And the Tester Fails the Mot Test
    Then the controlOne and controlTwo status should be <control1Pass> <control2Pass>
    Then the Mot test status should be <result>
    Examples:
      | scenario                      |control1Pass  |control2Pass  |result     |
      | class1.gradient.invalid.high  |false         |false         |FAILED     |
      | class1.gradient.invalid.low   |false         |false         |FAILED     |

  @brake-test
  Scenario Outline: As a tester I require the ability to pass Floor test on class 1 & 2 vehicles
    Given I am logged in as a Tester
    And I start an Mot Test with a Class 1 Vehicle
    And the Tester adds an Odometer Reading of 1000 mi
    And I add floor brake test data for <scenario>
    And the Tester Passes the Mot Test
    Then the controlOne and controlTwo status should be <control1Pass> <control2Pass>
    Then the Mot test status should be <generalPass>
    Examples:
      |scenario                     |control1Pass   |control2Pass  |generalPass |
      |class1.floor.valid.high      |true           |true          |PASSED      |
      |class1.floor.valid.low       |true           |true          |PASSED      |

  @brake-test
  Scenario Outline: As a tester I require the ability to fail Floor test on class 1 & 2 vehicles
    Given I am logged in as a Tester
    And I start an Mot Test with a Class 1 Vehicle
    And the Tester adds an Odometer Reading of 1000 mi
    And I add floor brake test data for <scenario>
    And the Tester Fails the Mot Test
    Then the controlOne and controlTwo status should be <control1Pass> <control2Pass>
    Then the Mot test status should be <generalPass>
    Examples:
      |scenario                     |control1Pass   |control2Pass  |generalPass |
      |class1.floor.invalid.high    |false          |false         |FAILED      |
      |class1.floor.invalid.low     |false          |false         |FAILED      |
      |class1.floor.valid.oneLock   |true           |false         |FAILED      |