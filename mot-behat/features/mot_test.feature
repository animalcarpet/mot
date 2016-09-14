Feature: MOT Test
  As a Tester
  I want to Perform an MOT Test
  So that I can verify a Vehicle is Road Worthy

  Scenario: Mot info retrieval with valid VRM and test number
    Given I am logged in as a Tester
    And I start an Mot Test with a Class 3 Vehicle
    And the Tester adds an Odometer Reading of 1000 mi
    And the Tester adds a Class 3-7 Decelerometer Brake Test
    And the Tester Passes the Mot Test
    When I search for an MOT test
    Then the MOT test data is returned

  Scenario: MOT info retrieval with VRM missing
    Given I am logged in as a Tester
    And I start an Mot Test with a Class 3 Vehicle
    And the Tester adds an Odometer Reading of 1000 mi
    And the Tester adds a Class 3-7 Decelerometer Brake Test
    And the Tester Passes the Mot Test
    When I search for an MOT test with missing VRM
    Then the search is failed with error "Invalid search. One of site number, tester, vehicle, vrm or vin id must be passed."

  Scenario: MOT info retrieval with Mot test number missing
    Given I am logged in as a Tester
    And I start an Mot Test with a Class 3 Vehicle
    And the Tester adds an Odometer Reading of 1000 mi
    And the Tester adds a Class 3-7 Decelerometer Brake Test
    And the Tester Passes the Mot Test
    When I search for an MOT test with invalid Mot test number
    Then the search is failed with error "Invalid search. One of site number, tester, vehicle, vrm or vin id must be passed."


  Scenario: Mot info retrieval with non-existing VRM
    Given I am logged in as a Tester
    And I start an Mot Test with a Class 3 Vehicle
    And the Tester adds an Odometer Reading of 1000 mi
    And the Tester adds a Class 3-7 Decelerometer Brake Test
    And the Tester Passes the Mot Test
    When I search for an MOT test with non-existing VRM
    Then the search will return no mot test

  Scenario: Mot info retrieval with non-existing mot test number
    Given I am logged in as a Tester
    And I start an Mot Test with a Class 3 Vehicle
    And the Tester adds an Odometer Reading of 1000 mi
    And the Tester adds a Class 3-7 Decelerometer Brake Test
    And the Tester Passes the Mot Test
    When I search for an MOT test with non-existing mot test number
    Then the search will return no mot test

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

  @jasper
  Scenario Outline: Create certificate for a passed MOT test for class 1 - 2
    Given I am logged in as a Tester
    And I start an Mot Test with a Class <class> Vehicle
    And the Tester adds an Odometer Reading of 1000 mi
    And the Tester adds a Class 1-2 Decelerometer Brake Test
    And the Tester Passes the Mot Test
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
    And I start an Mot Test with a Class <class> Vehicle
    And the Tester adds an Odometer Reading of 1000 mi
    And the Tester adds a Class 1-2 Decelerometer Brake Test
    And the Tester adds a Reason for Rejection
    And the Tester Fails the Mot Test
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
    And I start an Mot Test with a Class <class> Vehicle
    And the Tester adds an Odometer Reading of 1000 mi
    And the Tester adds a Class 3-7 Decelerometer Brake Test
    And the Tester Passes the Mot Test
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
    And I start an Mot Test with a Class <class> Vehicle
    And the Tester adds an Odometer Reading of 1000 mi
    And the Tester adds a Class 3-7 Decelerometer Brake Test
    And the Tester adds a Reason for Rejection
    And the Tester Fails the Mot Test
    And requests the certificate
    Then the certificate contains <no_of_pages> pages
    And the certificate contains the text <expected_text>
  Examples:
    | class | no_of_pages | expected_text |
    | 3     | 1           | VT30          |
    | 4     | 1           | VT30          |
    | 5     | 1           | VT30          |
    | 7     | 1           | VT30          |

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

  @wip
  Scenario: Tester reprints MOT Test Certificate (Pass result)
    Given I am logged in as a Tester
    When I reprint an MOT Test Certificate
    Then the certificate is returned

  @wip
  @clientip
  Scenario: Complete a minimal MOT Test ensuring client IP is recorded
    Given I am logged in as a Tester
    And I start an Mot Test with a Class 4 Vehicle
    And the Tester adds an Odometer Reading of 20000 mi
    And the Tester adds a Class 3-7 Decelerometer Brake Test
    When the Tester Passes the Mot Test from IP "192.111.149.222"
    Then the MOT Test Status is "PASSED"
    Then the recorded IP is "192.111.149.222"



  @VM-10358
  Scenario: Tester performs MOT test on vehicle without a manufactured date and first used date
    Given I am logged in as a Tester
    And I attempt to create a MOT Test on a vehicle without a manufactured date and first used date
    Then MOT test should be created successfully

  @quarantine
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

  Scenario Outline: Tester can not perform a MOT for vehicle on site with no associated classes
    Given I am logged in as a Tester to new site
    When class <class> is removed from site
    Then I can not start an Mot Test for Vehicle with class <class>
    Examples:
      | class |
      | 4     |

  @survey
  Scenario Outline: Tester submits a survey response
    Given I am logged in as a Tester
    And I have passed an MOT test
    And a survey token has been generated
    When I submit a survey response of <response>
    Then The survey response is saved
    Examples:
      | response |
      | 1        |
      | 2        |
      | 3        |
      | 4        |
      | 5        |

  @survey
  @survey_report
  Scenario Outline: Scheme user or scheme manager wants to generate a survey report
    Given There exist survey responses of <1> <2> <3> <4> <5>
    And I am logged in as a Scheme Manager
    And I want to generate a survey report
    Then I can download the report

    Examples:
    | 1 | 2 | 3 | 4 | 5 |
    | 1 | 2 | 3 | 4 | 5 |

  @survey
  Scenario: Survey will not be displayed if user has completed survey too recently
    Given A survey has been completed
    And I start an MOT Test
    And the Tester adds an Odometer Reading
    When the Tester adds a Class 3-7 Plate Brake Test
    And the Tester Passes the Mot Test
    Then the survey will not be displayed to the user

  @survey
  Scenario: Survey will not be displayed if user completes a non-normal MOT test
    Given A survey has been completed
    And I am logged in as a Tester
    And I start a Demo MOT Test
    And the Tester adds an Odometer Reading
    When the Tester adds a Class 3-7 Plate Brake Test
    And the Tester Passes the Mot Test
    Then the survey will not be displayed to the user

  @survey
  Scenario: Tester should be redirected HTTP status 400 if submitting a survey with an invalid token
    Given I am logged in as a Tester
    And I have passed an MOT test
    When I submit a survey response using an invalid token
    Then a BadRequestException will be thrown

  @survey
  Scenario: Tester should be redirected HTTP status 400 if submitting a survey with a null token
    Given I am logged in as a Tester
    And I have passed an MOT test
    When I submit a survey response with a null token
    Then a BadRequestException will be thrown

  @survey
  Scenario: Tester should be redirected HTTP status 400 if submitting a survey with a duplicate token
    Given I am logged in as a Tester
    And I have passed an MOT test
    When I submit a survey response with a duplicate token
    Then a BadRequestException will be thrown
