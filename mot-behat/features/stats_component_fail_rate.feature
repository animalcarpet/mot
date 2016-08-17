@transform
Feature: Stats Component Fail Rate

  Background:
    Given there is a site associated with Authorised Examiner with following data:
      | siteName         | aeName     | startDate                  |
      | Fast cars garage | Hot Wheels | first day of 4 months ago |
      | Slow cars garage | Big Wheels | first day of 4 months ago |
    And There is a tester "Kowalsky" associated with "Fast cars garage" and "Slow cars garage"
    And There is a tester "Sikorsky" associated with "Slow cars garage"
    And there are tests with reason for rejection performed at site "Fast cars garage" by "Kowalsky"

  @test-quality-information
  Scenario: Get the tester performance statistics for the last month
    When I am logged in as a Tester "Kowalsky"
    Then I should be able to see component fail rate statistics performed "1" months ago at site "Fast cars garage" for tester "Sikorsky" and group "A"
    And I should be able to see component fail rate statistics performed "1" months ago at site "Fast cars garage" for tester "Kowalsky" and group "B"
    And I should be able to see national fail rate statistics performed "1" months ago for group "A"
    And I should be able to see national fail rate statistics performed "1" months ago for group "B"


  @test-quality-information
  Scenario: Get the tester performance statistics for the last month after changing AE
    Given site "Fast cars garage" is unlinked from AE "Hot Wheels" on "first day of previous month"
    And site "Fast cars garage" is linked to AE "Big Wheels" on "last day of previous month"
    When I am logged in as a Tester "Kowalsky"
    Then there is no component fail rate statistics performed "1" months ago at site "Fast cars garage" for tester "Kowalsky" and group "A"
    Then there is no component fail rate statistics performed "1" months ago at site "Fast cars garage" for tester "Kowalsky" and group "B"
