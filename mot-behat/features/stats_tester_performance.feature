Feature: Stats Tester Performance

  @test-quality-information
  Scenario: Get the tester performance statistics for the last month
    Given there is an Authorised Examiner with following data:
    | name       | slots |
    | Hot Wheels | 1001  |
    And there is a site associated with Authorised Examiner with following data:
    | site_name | ae_name    | start_date                |
    | garage1   | Hot Wheels | first day of 4 months ago |
    | garage2   | Hot Wheels | first day of 4 months ago |
    And There is a tester "tester" associated with sites:
      | site    |
      | garage1 |
      | garage2 |
    And There is a tester "tester2" associated with site "garage1"
    And There is a tester "tester3" associated with site "garage1"
    And There is a tester "tester4" associated with site "garage2"
    When there is a test created for vehicle with the following data:
    | status    | type        | testername | site    | started_date                | duration | vehicle_class | date_of_manufacture         |
    | passed    | normal      | tester     | garage1 | first day of previous month | 60       | 1             | first day of 12 months ago  |
    | failed    | normal      | tester     | garage1 | first day of previous month | 50       | 2             | first day of 13 months ago  |
    | passed    | demo        | tester     | garage1 | first day of previous month | 60       | 1             | first day of 24 months ago  |
    | failed    | demo        | tester     | garage1 | first day of previous month | 60       | 2             | first day of 5 months ago   |
    | prs       | normal      | tester     | garage1 | first day of previous month | 60       | 1             | first day of 6 months ago   |
    | abandoned | normal      | tester     | garage1 | first day of previous month | 60       | 1             | first day of 17 months ago  |
    | aborted   | normal      | tester     | garage1 | first day of previous month | 60       | 1             | first day of 18 months ago  |
    | passed    | contingency | tester     | garage1 | first day of previous month | 60       | 1             | first day of 59 months ago  |
    | passed    | normal      | tester     | garage1 | first day of previous month | 18       | 4             | first day of 82 months ago  |
    | passed    | normal      | tester     | garage1 | now                         | 30       | 5             | first day of 3 months ago   |
    | passed    | normal      | tester     | garage1 | first day of 2 months ago   | 20       | 7             | first day of 64 months ago  |
    | passed    | normal      | tester2    | garage1 | last day of previous month  | 12       | 1             | first day of 25 months ago  |
    | failed    | normal      | tester3    | garage1 | last day of 2 months ago    | 2160     | 1             | first day of 36 months ago  |
    | passed    | normal      | tester3    | garage1 | last day of previous month  | 2160     | 1             | first day of 47 months ago  |
    | passed    | normal      | tester     | garage2 | first day of previous month | 60       | 1             | first day of 98 months ago  |
    | failed    | normal      | tester     | garage2 | first day of previous month | 50       | 1             | first day of 99 months ago  |
    Then being log in as a "tester" I can view site tester performance statistics for site "garage1" with data:
    | total | averageTime | percentageFailed | averageVehicleAgeInMonths | isAverageVehicleAgeAvailable | testername | group |
    | 3     | 00:56:40    | 66.67            | 9.33                      | true                         | tester     | A     |
    | 1     | 00:18:00    | 0                | 81                        | true                         | tester     | B     |
    | 1     | 00:12:00    | 0                | 24                        | true                         | tester2    | A     |
    | 1     | 1.12:00:00  | 100              | 35                        | true                         | tester3    | A     |
    And being log in as a "tester4" I can view site tester performance statistics for site "garage2" with data:
    | total | averageTime | percentageFailed | averageVehicleAgeInMonths | isAverageVehicleAgeAvailable | testername | group |
    | 2     | 00:55:00    | 50               | 97.5                      | true                         | tester     | A     |
    And being log in as a "tester" I can view total site tester performance statistics for site "garage1" with data:
    | total | averageTime | percentageFailed | averageVehicleAgeInMonths | isAverageVehicleAgeAvailable | group |
    | 5     | 07:48:24    | 60               | 17                        | true                         | A     |
    | 1     | 00:18:00    | 0                | 81                        | true                         | B     |
    And being log in as a "tester4" I can view total site tester performance statistics for site "garage2" with data:
    | total | averageTime | percentageFailed | averageVehicleAgeInMonths | isAverageVehicleAgeAvailable | group |
    | 2     | 00:55:00    | 50               | 98                        | true                         | A     |
    | 0     | 00:00:00    | 0                | 0                         | false                        | B     |
    And being log in as a "tester" I can view site national statistics with data:
    | total | averageTime | percentageFailed | averageVehicleAgeInMonths | isAverageVehicleAgeAvailable | group |
    | 2     | 05:50:17    | 57.14            | 40                        | true                         | A     |
    | 1     | 00:18:00    | 0                | 81                        | true                         | B     |


  @test-quality-information
  Scenario: Get the tester performance statistics for the last month after changing AE
    Given there is an Authorised Examiner with following data:
      | name       | slots |
      | Hot Wheels | 1001  |
      | Big Wheels | 1001  |
    And there is a site associated with Authorised Examiner with following data:
      | site_name | ae_name    | start_date                |
      | garage1   | Hot Wheels | first day of 4 months ago |
    And There is a tester "tester" associated with site "garage1"
    And there is a test created for vehicle with the following data:
      | status    | type        | testername | site    | started_date                | duration | vehicle_class | date_of_manufacture      |
      | passed    | normal      | tester     | garage1 | first day of previous month | 60       | 1             | first day of 1 years ago |
      | failed    | normal      | tester     | garage1 | first day of previous month | 50       | 4             | first day of 2 years ago |
    When site "garage1" is unlinked from AE "Hot Wheels" on "first day of previous month"
    And site "garage1" is linked to AE "Big Wheels" on "last day of previous month"
    And there is a test created for vehicle with the following data:
      | status    | type        | testername | site    | started_date               | duration | vehicle_class | date_of_manufacture       |
      | passed    | normal      | tester     | garage1 | last day of previous month | 30       | 1             | first day of 40 years ago |
    Then being log in as a "tester" I can view site tester performance statistics for site "garage1" with data:
      | total | averageTime | percentageFailed | averageVehicleAgeInMonths | isAverageVehicleAgeAvailable | testername | group |
      | 1     | 00:30:00    |  0               | 479                       | true                         | tester     | A     |
    And being log in as a "tester" I can view total site tester performance statistics for site "garage1" with data:
      | total | averageTime | percentageFailed | averageVehicleAgeInMonths | isAverageVehicleAgeAvailable | group |
      | 1     | 00:30:00    | 0                | 479                       | true                         | A     |
      | 0     | 00:00:00    | 0                | 0                         | false                        | B     |
    And being log in as a "tester" I can view site national statistics with data:
      | total | averageTime | percentageFailed | averageVehicleAgeInMonths | isAverageVehicleAgeAvailable | group |
      | 2     | 00:45:00    | 0                | 245                       | true                         | A     |
      | 1     | 00:50:00    | 100              | 23                        | true                         | B     |

  @test-quality-information
  Scenario: Get the site list under Test Quality Information for Authorised Examiner
    Given there is an Authorised Examiner with following data:
      | name     | slots |
      | First AE | 200   |
    And there is a site associated with Authorised Examiner with following data:
      | site_name  | ae_name  | start_date                |
      | First VTS  | First AE | first day of 4 months ago |
      | Second VTS | First AE | first day of 4 months ago |
    When I am logged in as a Scheme Manager
    And I add risk assessment with score "111.11" to site "First VTS" on "First AE"
    And I add risk assessment with score "222.22" to site "Second VTS" on "First AE"
    Then being log in as an aedm in "First AE" I can view authorised examiner statistics with data:
      | siteName   | riskScore |
      | Second VTS | 222.22    |
      | First VTS  | 111.11    |