Feature: Person
  As a Tester
  I want to get information Person Information
  So that I can get information related to myself and others

  Scenario: Classes for Person
    Given I am logged in as a Tester
    When I get Information about my MOT Classes
    Then I will see my Available Classes

  Scenario: Dashboard for Person
    Given I am logged in as a Tester
    When I get Information about my Dashboard
    Then I will see my Dashboard Information

  Scenario: Check Tester has MOT in Progress
    Given I am logged in as a Tester
    When I have an MOT Test In Progress
    And I get Information about my Dashboard
    Then my Dashboard will return the MotTestNumber

  @wip
  @non-mot-test
  Scenario: Check Vehicle Examiner has non-MOT in progress
    Given I am logged in as a Vehicle Examiner
    When I start a non-MOT Test
    Then my Dashboard should show I have a non-MOT Test in progress

  Scenario: Check Tester details in Profile
    Given I am logged in as a Tester
    When I get my Profile details
    Then I will see my username in my Profile
    And I will see my user id in my Profile

  Scenario: Get my profile details for valid user
    Given I am logged in as a Tester
    When I get my Profile details
    Then the my profile details are returned

  Scenario Outline: Tester performance dashboard daily stats are calculated
    Given I am logged in as a Tester
    When I pass <passedNormalTests> normal tests
    And I fail <failedNormalTests> normal tests
    And I perform <retests> retests
    And I perform <demoTests> demotests
    And I start and abort <abortedTests> tests
    And I get my person stats
    Then person stats show <conductedTests> conducted tests <passedNormalTests> passed tests and <resultFailedTests> failed tests

    Examples:
      | passedNormalTests | failedNormalTests | retests | demoTests | abortedTests | resultFailedTests | conductedTests |
      | 0                 | 1                 | 0       | 0         | 0            | 1                 | 1              |
      | 0                 | 0                 | 0       | 1         | 0            | 0                 | 0              |
      | 1                 | 1                 | 0       | 0         | 0            | 1                 | 2              |
      #creating retest implies failing mot test before doing retest, so in that case we have 3 initial tests
      | 1                 | 1                 | 1       | 0         | 0            | 2                 | 3              |
      | 0                 | 0                 | 0       | 0         | 1            | 0                 | 0              |
