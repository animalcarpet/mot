@wip
@mystery-shopper-test
Feature: Mystery Shopper Test
  As a Vehicle Examiner (VE)
  I want a tester to carry out a mystery shopper MOT on a vehicle
  So that I can compare this test with the associated non-MOT inspection

  Scenario: Create Masked Vehicle and complete MOT test
    Given I am logged in as a Tester
    And I start an Mot Test with a Masked Vehicle
    And the Tester adds an Odometer Reading of 1000 mi
    And the Tester adds a Class 3-7 Decelerometer Brake Test
    When the Tester Passes the Mystery Shopper Test
    Then the MOT Test Status is "PASSED"