Feature: MOT Test Survey

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