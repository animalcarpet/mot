Feature: MOT Test
  As a Tester
  I want to Perform an MOT Test
  So that I can verify a Vehicle is Road Worthy

  Scenario Outline: Create MOT with Vehicle Classes 3-7
    Given I am logged in as a Tester
    And I start an Mot Test with a Class <class> Vehicle
    And the Tester adds an Odometer Reading of 1000 mi
    And the Tester adds a Class 3-7 Decelerometer Brake Test
    When the Tester Passes the Mot Test
    Then the MOT Test Status is "PASSED"
  Examples:
    | class |
    | 3     |
    | 4     |
    | 5     |
    | 7     |

  Scenario Outline: Create MOT with Vehicle Classes 1-2
    Given I am logged in as a Tester
    And I start an Mot Test with a Class <class> Vehicle
    And the Tester adds an Odometer Reading of 1000 mi
    And the Tester adds a Class 1-2 Decelerometer Brake Test
    When the Tester Passes the Mot Test
    Then the MOT Test Status is "PASSED"
  Examples:
    | class |
    | 1     |
    | 2     |

  Scenario: Abort a Test
    Given a logged in Tester, starts an MOT Test
    When the Tester Aborts the Mot Test
    Then the MOT Test Status is "ABORTED"


  Scenario Outline: Tester cancels In Progress MOT Test
    Given a logged in Tester, starts an MOT Test
    And the Tester cancels the test with a reason of <cancelReason>
    Then the MOT Test Status is "<testStatus>"

  Examples:
    | cancelReason | testStatus |
    | 5            | ABORTED    |
    | 24           | ABORTED    |
    | 23           | ABANDONED  |
    | 19           | ABANDONED  |
    | 18           | ABANDONED  |
    | 27           | ABORTED    |
    | 20           | ABANDONED  |
    | 14           | ABANDONED  |
    | 6            | ABORTED    |
    | 17           | ABANDONED  |
    | 13           | ABORTED    |
    | 16           | ABANDONED  |
    | 28           | ABORTED    |
    | 29           | ABORTED    |
    | 12           | ABORTED    |
    | 15           | ABANDONED  |
    | 22           | ABANDONED  |
    | 21           | ABANDONED  |
    | 25           | ABORTED    |

  @VM-10358
  Scenario: Tester performs MOT test on vehicle without a manufactured date and first used date
    Given I am logged in as a Tester
    And I attempt to create a MOT Test on a vehicle without a manufactured date and first used date
    Then MOT test should be created successfully

  @create-default-site("Garage for small and big cars")
  Scenario: Tester can not perform a MOT for vehicle on site with no associated classes
    Given I am logged in as a Tester
    When class 4 is removed from site
    Then I can not start an Mot Test for Vehicle with class 4

  @defect
  Scenario: As a Tester performing a normal MOT test I can add and edit reasons for rejection defects
    Given I am logged in as a Tester
    When I start an Mot Test with a Class 3 Vehicle
    Then I can search for Rfr
    And I can list child test items selector
    And I can add PRS to test
    And I can add a Failure to test
    And I can edit previously added Rfr