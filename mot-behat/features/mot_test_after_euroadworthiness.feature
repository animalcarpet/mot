Feature: MOT test after EU Roadworthiness changes
  As a MOT test user
  I can use the new EU Roadworthiness categories and Reasons for Rejection for an MOT test
  So that I can verify a vehicle is road worthy

  Scenario Outline: MOT defects can be edited within an MOT test
    Given I am logged in as a Tester
    And I start an Mot Test with a Class <class> Vehicle
    And I add an new EU <defect type> to the test
    When I edit the defect
    Then the edited defect is updated

    Examples:
      |class | defect type |
      | 3    |	Major      |
      | 4    |	Dangerous  |
      | 5    |  Minor      |
      | 7    |	Advisory   |

  Scenario Outline: MOT defects can be removed from an MOT test
    Given I am logged in as a Tester
    And I start an Mot Test with a Class <class> Vehicle
    And I add an new EU <defect type> to the test
    When I remove the defect
    Then the defect is not associated with the MOT test

    Examples:
      |class | defect type |
      | 3    |	Major      |
      | 4    |	Dangerous  |
      | 5    |  Minor      |
      | 7    |	Advisory   |

  Scenario Outline: Tester can create an MOT with Major or Dangerous and fail
    Given I am logged in as a Tester
    And I start an Mot Test with a Class 3 Vehicle
    And I add an new EU <defect type> to the test
    And the Tester adds a Class 3-7 Decelerometer Brake Test
    And the Tester adds an Odometer Reading of 1000 mi
    When the Tester Fails the Mot Test
    Then the MOT Test Status is FAILED
      Examples:
      | defect type |
      | Major       |
      | Dangerous   |

  Scenario Outline: Tester can create an MOT with Advisory or prs and pass
    Given I am logged in as a Tester
    And I start an Mot Test with a Class 3 Vehicle
    And I add an new EU <defect type> to the test
    And the Tester adds a Class 3-7 Decelerometer Brake Test
    And the Tester adds an Odometer Reading of 1000 mi
    When the Tester Passes the Mot Test
    Then the MOT Test Status is PASSED
    Examples:
      | defect type |
      | Advisory    |
      | Minor       |
