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
    Then the MOT Test Status is PASSED
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
    Then the MOT Test Status is PASSED
  Examples:
    | class |
    | 1     |
    | 2     |

  Scenario: Abort a Test
    Given I am logged in as a Tester
    And there is a Mot test in progress
    When the Tester Aborts the Mot Test
    Then the MOT Test Status is ABORTED

  Scenario Outline: Tester cancels In Progress MOT Test
    Given I am logged in as a Tester
    And there is a Mot test in progress
    And the Tester cancels the test with a reason of "<cancelReason>"
    Then the MOT Test Status is <testStatus>

  Examples:
    | cancelReason                                             | testStatus |
    | VTS incident                                             | ABORTED    |
    | System unavailability                                    | ABORTED    |
    | For classes I and II the frame is stamped                | ABANDONED  |
    | Smoke issue                                              | ABANDONED  |
    | The vehicle size issue                                   | ABANDONED  |
    | Aborted due to change of ownership via seamless transfer | ABORTED    |
    | Inability to open any device                             | ABANDONED  |
    | The registration document issue                          | ABANDONED  |
    | Incorrect location                                       | ABORTED    |
    | Insecurity of a load                                     | ABANDONED  |
    | Accident or illness of tester                            | ABORTED    |
    | Lack of fuel or oil                                      | ABANDONED  |
    | Vehicle registered in error                              | ABORTED    |
    | Test registered in error                                 | ABORTED    |
    | Test equipment issue                                     | ABORTED    |
    | Dirty vehicle                                            | ABANDONED  |
    | Diesel engine vehicle is suspect                         | ABANDONED  |
    | Inspection may be dangerous or cause damage              | ABANDONED  |
    | Aborted by VE                                            | ABORTED    |

  @VM-10358
  Scenario: Tester performs MOT test on vehicle without a manufactured date and first used date
    Given I am logged in as a Tester
    And I attempt to create a MOT Test on a vehicle without a manufactured date and first used date
    Then MOT test should be created successfully

  Scenario: Tester can not perform a MOT for vehicle on site with no associated classes
    Given I am logged in as a Tester at site "Popular Garage"
    When class 4 is removed from site "Popular Garage"
    Then I can not start an Mot Test for Vehicle with class 4 at site "Popular Garage"

  @defect
  Scenario: As a Tester performing a normal MOT test I can add and edit reasons for rejection defects
    Given I am logged in as a Tester
    When I start an Mot Test with a Class 3 Vehicle
    Then I can search for Rfr
    And I can list child test items selector
    And I can add PRS to test
    And I can add a Failure to test
    And I can edit previously added Rfr

  Scenario Outline: MOT Test defects can be added to an MOT test
    Given I am logged in as a Tester
    And I start an Mot Test with a Class <class> Vehicle
    When I add a <defect type> to the test
    Then the <defect type> is associated with the MOT test

    Examples:
      |class | defect type |
      | 1    | Advisory    |
      | 2    | PRS         |
      | 3    | Failure     |
      | 4    | Advisory    |
      | 5    | PRS         |
      | 7    | Failure     |

  Scenario Outline: MOT defects can be edited within an MOT test
    Given I am logged in as a Tester
    And I start an Mot Test with a Class <class> Vehicle
    And I add a <defect type> to the test
    When When I edit all editable fields for the defect
    Then the edited defect is updated

    Examples:
      |class | defect type |
      | 1    | Advisory    |
      | 2    | PRS         |
      | 3    | Failure     |
      | 4    | Advisory    |
      | 5    | PRS         |
      | 7    | Failure     |

  Scenario Outline: MOT defects can be removed from an MOT test
    Given I am logged in as a Tester
    And I start an Mot Test with a Class <class> Vehicle
    And I add a <defect type> to the test
    When I remove the defect
    Then the defect is not associated with the MOT test

    Examples:
      |class | defect type |
      | 1    | Advisory    |
      | 2    | PRS         |
      | 3    | Failure     |
      | 4    | Advisory    |
      | 5    | PRS         |
      | 7    | Failure     |

  Scenario: Tester can create an MOT with an Advisory and the MOT Test result is pass
    Given I am logged in as a Tester
    And I start an Mot Test with a Class 3 Vehicle
    And I add a Advisory to the test
    And the Tester adds a Class 3-7 Decelerometer Brake Test
    And the Tester adds an Odometer Reading of 1000 mi
    When the Tester Passes the Mot Test
    Then the MOT Test Status is PASSED

  Scenario Outline: Tester can create an MOT with PRS Fail and the MOT Test result is fail
    Given I am logged in as a Tester
    And I start an Mot Test with a Class <class> Vehicle
    And I add a <defect type> to the test
    And the Tester adds a Class 3-7 Decelerometer Brake Test
    And the Tester adds an Odometer Reading of 1000 mi
    When the Tester Fails the Mot Test with PRS
    Then the MOT Test Status is FAILED
    Examples:
    |class |defect type  |
    |3     | Failure PRS |
    |4     | Failure PRS |
    |5     | Failure PRS |
    |7     | Failure PRS |

  Scenario Outline: Tester can create an MOT with Failure and the MOT Test result is fail
    Given I am logged in as a Tester
    And I start an Mot Test with a Class <class> Vehicle
    And I add a <defect type> to the test
    And the Tester adds a Class 3-7 Decelerometer Brake Test
    And the Tester adds an Odometer Reading of 1000 mi
    When the Tester Fails the Mot Test
    Then the MOT Test Status is FAILED
    Examples:
      |class |defect type  |
      |3     | Failure     |
      |4     | Failure     |
      |5     | Failure     |
      |7     | Failure     |