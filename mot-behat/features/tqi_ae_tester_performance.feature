Feature: TQI Tester Performance at AE

  Background:
    Given there is a site associated with Authorised Examiner with following data:
      | siteName         | aeName     | startDate                  |
      | Fast cars garage | Hot Wheels | first day of 15 months ago |
      | Slow cars garage | Hot Wheels | first day of 11 months ago |
    And There is a tester "John Doe" associated with "Fast cars garage" and "Slow cars garage"

  @test-quality-information
  Scenario: Get the site list under Test Quality Information for Authorised Examiner
    Given I am logged in as a Scheme Manager
    When I add risk assessment with score "111.11" to site "Fast cars garage" on "Hot Wheels"
    And I add risk assessment with score "222.22" to site "Slow cars garage" on "Hot Wheels"
    Then being log in as an aedm in "Hot Wheels" I can view authorised examiner statistics with data:
      | siteName         | riskScore |
      | Slow cars garage | 222.22    |
      | Fast cars garage | 111.11    |
