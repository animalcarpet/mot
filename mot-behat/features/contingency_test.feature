Feature: Contingency Test
  In order to record tests that were performed when the system was off-line
  As a Tester
  I wish to enter Contingency Tests results on the system (VM-4506)

  Scenario: Tester starts a Contingency MOT Test
    Given I am logged in as a Tester
    And I called the helpdesk to ask for a daily contingency code
    When I start a Contingency MOT test
    Then I should receive the MOT test number
    And the MOT Test Number should be 12 digits long

  Scenario Outline: Tester submits Contingency test details
    Given I am logged in as a Tester
    When I create a new contingency test with reason <reason>
    Then I should receive an emergency log id

  Examples:
    | reason |
    | CP     |
    | OT     |
    | CP     |
    | OT     |

  Scenario: Unauthenticated Tester attempts to submit Contingency test details
    Given I am not logged in
    When I attempt to create a new contingency test
    Then I should receive an Unauthorised response

  Scenario Outline: Tester attempts to submit Contingency test details with invalid contingency code
    Given I am logged in as a Tester
    When I attempt to create a new contingency test with a <contingencyCode>
    Then I should receive an Unprocessable Entity response

  Examples:
    | contingencyCode |
    | 1               |
    | A               |
    | $               |
    |                 |
    | 1               |
    | A               |
    | $               |
    |                 |

  Scenario Outline: Invalid users cannot submit Contingency test details for Retest test type
    Given I'm authenticated with my username and password <username> DEFAULT
    When I attempt to create a new contingency test with a DEFAULT
    Then I should receive a Forbidden response

  Examples:
    | username        |
    | areaoffice1user |
    | ae              |
    | areaadmin       |
    | schememgt       |
    | aedm            |
    | aed1            |
    | site-manager    |
    | inactivetester  |
    | ft-enf-tester   |
    | financeuser     |
    | csco            |
    | areaoffice1user |
    | ae              |
    | areaadmin       |
    | schememgt       |
    | aedm            |
    | aed1            |
    | site-manager    |
    | inactivetester  |
    | ft-enf-tester   |
    | financeuser     |
    | csco            |

  Scenario: Tester Completes a Contingency - PASSED MOT Test
    Given I am logged in as a Tester
    And I start a Contingency MOT test
    And the Tester adds an Odometer Reading of 658 mi
    And the Tester adds a Class 3-7 Decelerometer Brake Test
    When the Tester Passes the Mot Test
    Then the MOT Test Status is "PASSED"
    And the Contingency Test is Logged
  
  @quarantine
  Scenario Outline: Tester Completes a Contingency expecting a specific response
    Given I am logged in as a Tester
    When I record a Contingency Test with <date> at <time>
    Then I should receive the response code <code>
    And I should receive a validation error "<key>" "<message>"

  Examples:
    | date                | time     | code | key  | message                        |
    | today               | now      | 200  |      |                                |
    | -1 day              | now      | 200  |      |                                |
    | -3 months +1 minute | now      | 200  |      |                                |
#    temporary timeframe extended to infinity
#    | -3 months -1 minute | now      | 422  | date | must be less than 3 months ago |
    | +1 days             | now      | 422  | date | must not be in the future      |

  Scenario: Tester Completes a Contingency - FAILED MOT Test
    Given I am logged in as a Tester
    And I start a Contingency MOT test
    And the Tester adds an Odometer Reading of 120000 mi
    And the Tester adds a Class 3-7 Decelerometer Brake Test
    And the Tester adds a Reason for Rejection
    When the Tester Fails the Mot Test
    Then the MOT Test Status is "FAILED"
    And the Contingency Test is Logged

  Scenario: Tester Completes a Contingency - ABORTED MOT Test
    Given I am logged in as a Tester
    And I start a Contingency MOT test
    When the Tester Aborts the Mot Test
    Then the MOT Test Status is "ABORTED"
    And the Contingency Test is Logged
