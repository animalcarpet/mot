Feature: Stats Component Fail Rate

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
    And There is a tester "tester2" associated with site "garage2"
    When there is a test created for vehicle with the following data:
      | status    | type        | rfr                   | testername | site    | started_date                | duration | vehicle_class | date_of_manufacture       |
      | passed    | normal      |                       | tester     | garage1 | first day of previous month | 60       | 1             | first day of 2 months ago |
      | failed    | normal      | Body condition        | tester     | garage1 | first day of previous month | 50       | 3             | first day of 1 years ago  |
      | passed    | demo        |                       | tester     | garage1 | first day of previous month | 60       | 2             | first day of 9 months ago |
      | failed    | demo        | Body condition        | tester     | garage1 | first day of previous month | 60       | 4             | first day of 2 years ago  |
      | prs       | normal      |                       | tester     | garage1 | first day of previous month | 60       | 5             | first day of 6 months ago |
      | failed    | normal      | Body condition        | tester     | garage2 | first day of previous month | 50       | 7             | first day of 6 months ago |
      | failed    | normal      | Body condition        | tester2    | garage2 | first day of previous month | 50       | 4             | first day of 2 years ago  |
    Then being log in as a "tester" I can view component fail rate statistics for tester "tester" and group "A" and site "garage1" with no data
    And being log in as a "tester" I can view component group performance statistics for tester "tester" and group "A" and site "garage1" with data:
      | total | averageTime | percentageFailed | averageVehicleAgeInMonths | isAverageVehicleAgeAvailable |
      | 1     | 01:00:00    | 0                | 1                         | true                         |
    And being log in as a "tester" I can view component fail rate statistics for tester "tester" and group "B" and site "garage1" with data:
      | componentName                     | percentageFailed |
      | Body, Structure and General Items | 100              |
    And being log in as a "tester" I can view component group performance statistics for tester "tester" and group "B" and site "garage1" with data:
      | total | averageTime | percentageFailed | averageVehicleAgeInMonths | isAverageVehicleAgeAvailable |
      | 2     | 00:55:00    | 100              | 8                         | true                         |
    And being log in as a "tester" I can view national fail rate statistics for group "A" with no data
    And being log in as a "tester" I can view national fail rate statistics for group "B" with data:
      | componentName                     | percentageFailed |
      | Body, Structure and General Items | 80               |
    And being log in as a "tester2" I can view component fail rate statistics for tester "tester2" and group "A" and site "garage2" with no data
    And being log in as a "tester2" I can view component fail rate statistics for tester "tester2" and group "B" and site "garage2" with data:
      | componentName                     | percentageFailed |
      | Body, Structure and General Items | 100              |


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
      | status    | type        | rfr                   | testername | site    | started_date                | duration | vehicle_class | date_of_manufacture       |
      | failed    | normal      | Performance, Gradient | tester     | garage1 | first day of previous month | 60       | 1             | first day of 2 months ago |
      | failed    | normal      | Body condition        | tester     | garage1 | first day of previous month | 50       | 4             | first day of 2 months ago |
    When site "garage1" is unlinked from AE "Hot Wheels" on "first day of previous month"
    And site "garage1" is linked to AE "Big Wheels" on "last day of previous month"
    And there is a test created for vehicle with the following data:
      | status    | type        | rfr                   | testername | site    | started_date               | duration | vehicle_class | date_of_manufacture       |
      | failed    | normal      | Performance, Gradient | tester     | garage1 | last day of previous month | 30       | 1             | first day of 8 months ago |
    Then being log in as a "tester" I can view component fail rate statistics for tester "tester" and group "A" and site "garage1" with data:
      | componentName     | percentageFailed |
      | Motorcycle brakes | 100              |
    And being log in as a "tester" I can view component fail rate statistics for tester "tester" and group "B" and site "garage1" with no data
    And being log in as a "tester" I can view component group performance statistics for tester "tester" and group "A" and site "garage1" with data:
      | total | averageTime | percentageFailed | averageVehicleAgeInMonths | isAverageVehicleAgeAvailable |
      | 1     | 00:30:00    | 100              | 7                         | true                         |
    And being log in as a "tester" I can view component group performance statistics for tester "tester" and group "B" and site "garage1" with data:
      | total | averageTime | percentageFailed | averageVehicleAgeInMonths | isAverageVehicleAgeAvailable |
      | 0     | 00:00:00    | 0                | 0                         | false                        |
    And being log in as a "tester" I can view national fail rate statistics for group "A" with data:
      | componentName     | percentageFailed |
      | Motorcycle brakes | 100              |
    And being log in as a "tester" I can view national fail rate statistics for group "B" with data:
      | componentName                     | percentageFailed |
      | Body, Structure and General Items | 100              |
