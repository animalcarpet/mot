Feature: TQI Tester Performance

  Background:
    Given there is a site associated with Authorised Examiner with following data:
      | siteName         | aeName     | startDate                  |
      | Fast cars garage | Hot Wheels | first day of 15 months ago |
      | Slow cars garage | Big Wheels | first day of 11 months ago |
    And There is a tester "John Doe" associated with "Fast cars garage" and "Slow cars garage"
    And there are tests performed at site "Fast cars garage" by "John Doe"

  @test-quality-information
  Scenario: Get the tester performance statistics at site for the previous months
    When I am logged in as a Tester "John Doe"
    Then I should be able to see the tester performance statistics performed "2" months ago at site "Fast cars garage"
    And I should be able to see national tester performance statistics for performed 2 months ago
    But there is no tester performance statistics performed "1" months ago at site "Slow cars garage"

  @test-quality-information
  Scenario: Get the tester performance statistics at site for the previous months after changing AE
    Given site "Fast cars garage" is unlinked from AE "Hot Wheels" on "first day of previous month"
    And site "Fast cars garage" is linked to AE "Big Wheels" on "last day of previous month"
    When I am logged in as a Tester "John Doe"
    Then there is no tester performance statistics performed "2" months ago at site "Fast cars garage"

  @test-quality-information
  Scenario: Get the tester performance statistics performed 2 months ago
    Given there are tests performed at site "Slow cars garage" by "John Doe"
    When I am logged in as a Tester "John Doe"
    Then I should be able to see the tester performance statistics performed "2" months ago
    But there is no tester performance statistics performed "5" months ago

  @test-quality-information
  Scenario: Get the tester performance statistics after changing AE
    Given site "Fast cars garage" is unlinked from AE "Hot Wheels" on "first day of previous month"
    And site "Fast cars garage" is linked to AE "Big Wheels" on "last day of previous month"
    When I am logged in as a Tester "John Doe"
    Then there is no tester performance statistics performed "2" months ago
