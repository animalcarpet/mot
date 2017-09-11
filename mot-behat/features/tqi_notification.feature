@parallel_suite_2
Feature: TQI Notification

  Background:
    Given there is a site associated with Authorised Examiner with following data:
      | siteName           | aeName            | startDate                  |
      | Fast cars garage   | Hot Wheels        | first day of 15 months ago |
      | Slow cars garage   | Test Organisation | first day of 17 months ago |
    And there is a Site Manager assigned to the site with following data:
      | username       | siteName           |
      | Site Manager   | Fast cars garage   |
      | Site Manager2  | Slow cars garage   |
    And There is a tester "John Doe" associated with "Fast cars garage" and "Slow cars garage"
    And there are tests performed at site "Fast cars garage" by "John Doe"
    And Test Quality Cache is updated between "1" and "3" months ago

  @test-quality-information
  Scenario: Get the site manager notifications about TQI report
    When All TQI notifications was sent
    Then being log in as a person i should receive notification that TQI statistics was generated for my site with following data:
      | username      | template                         | siteName         |
      | Site Manager  | Site TQI stats generated         | Fast cars garage |
      | Site Manager2 | Site TQI stats generated         | Slow cars garage |
    And Authorised Examiner Designated Manager received notifications for his site:
      | siteName         |
      | Fast cars garage |
      | Slow cars garage |

  @test-quality-information
  Scenario: Get correct number of TQI notifications for the site manager
    When user Site Manager on site:
      | username       | siteName           |
      | John Snow      | Fast cars garage   |
      | John Snow      | Slow cars garage   |
      | Adam Snow      | Slow cars garage   |
    And All TQI notifications was sent
    Then being log in as a Site Manager with few sites i should receive correct number of notifications
      | username     | notificationCount |
      | John Snow    | 2                 |
      | Adam Snow    | 1                 |

  @test-quality-information
  Scenario: Get correct number of notifications for person with multiple roles
    Given I am logged in as a Customer Service Manager
    Given there is a site associated with Authorised Examiner with following data:
      | siteName            | aeName                | startDate                  |
      | Cars garage         | Testing Organisation  | first day of 16 months ago |
      | Medium Cars garage  | Station Organisation  | first day of 11 months ago |
    And AEDM is also a user with role in his site:
      | siteName           | roleName     |
      | Cars garage        | Site manager |
    And All TQI notifications was sent
    Then Authorised Examiner Designated Manager received notifications for his site:
      | siteName           |
      | Cars garage        |
      | Medium Cars garage |
