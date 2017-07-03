@parallel_suite_2
@create-site
Feature: VTS
  As DVSA I should be able to search for VTS by name/number/town/postcode
  In function to find the details of a Vehicle Testing Station

  Scenario Outline: As a DVSA User I need to be able to search for VTS
    Given I am logged in as <user>
    When I search for a existing Vehicle Testing Station by it's <attribute>
    Then I should see the Vehicle Testing Station result
  Examples:
    | user                  | attribute |
    | an Area Office User   | number    |
    | an Area Office User 2 | name      |
    | a Vehicle Examiner    | town      |
    | a Vehicle Examiner    | postcode  |

  Scenario: As a DVSA User I cannot search for VTS only base on classes
    Given I am logged in as a Vehicle Examiner
    When I search for a Vehicle Testing Station only by a class
    Then the search will return no results

  Scenario: As a DVSA User if the VTS search is not successful
    Given I am logged in as an Area Office User
    When I search for a town with no Vehicle Testing Station
    Then the search will return no results

  Scenario Outline: As a User I want retrieve the information about a VTS
    Given I am logged in as <user>
    When I request information about a VTS
    Then the VTS details are returned
    Examples:
      | user                  |
      | an Area Office User 2 |
      | a DVLA Operative      |
