@parallel_suite_2
Feature: TQI Tester Performance

  Background:
    Given there is a site associated with Authorised Examiner with following data:
      | siteName         | aeName     | startDate                  |
      | Fast cars garage | Hot Wheels | first day of 15 months ago |
      | Slow cars garage | Big Wheels | first day of 11 months ago |
    And There is a tester "John Doe" associated with "Fast cars garage" and "Slow cars garage"
    And there are tests performed at site "Fast cars garage" by "John Doe"
    And Test Quality Cache is updated between "1" and "3" months ago

  @test-quality-information
  Scenario: Get the tester performance statistics at site for 3 previous months
    When I am logged in as a Tester "John Doe"
    Then I should be able to see the tester performance statistics performed last "3" months at site "Fast cars garage"
    And I should be able to see national tester performance statistics for last 3 months
    And there are tester performance statistics performed in last "3" months at site "Slow cars garage" and contains statistics for "1" tester for both groups

  @test-quality-information
  Scenario: Get the tester performance statistics at site for previous month
    When I am logged in as a Tester "John Doe"
    Then I should be able to see the tester performance statistics performed last "1" months at site "Fast cars garage"
    And I should be able to see national tester performance statistics for last 1 months
    And there are tester performance statistics performed in last "1" months at site "Slow cars garage" and contains statistics for "1" tester for both groups


  @test-quality-information
  Scenario: Get the tester performance statistics at site for the previous months after changing AE
    Given site "Fast cars garage" is unlinked from AE "Hot Wheels" on "first day of previous month"
    And site "Fast cars garage" is linked to AE "Big Wheels" on "last day of previous month"
    When I am logged in as a Tester "John Doe"
    And there are tester performance statistics performed in last "3" months at site "Fast cars garage" and contains statistics for "1" tester for both groups

  @test-quality-information
  Scenario: Get the tester performance statistics performed 2 months ago
    Given there are tests performed at site "Slow cars garage" by "John Doe"
    And Test Quality Cache is updated between "1" and "3" months ago
    When I am logged in as a Tester "John Doe"
    Then I should be able to see the tester performance statistics performed "1" months ago
    And I should be able to see the tester performance statistics performed "3" months ago

  @test-quality-information
  Scenario: Get the tester performance statistics after changing AE
    Given site "Fast cars garage" is unlinked from AE "Hot Wheels" on "first day of previous month"
    And site "Fast cars garage" is linked to AE "Big Wheels" on "first day of this month"
    When I am logged in as a Tester "John Doe"
    And there are tester performance statistics performed in last "1" months at site "Fast cars garage" and contains statistics for "1" tester for both groups
