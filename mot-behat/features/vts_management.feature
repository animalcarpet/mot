@parallel_suite_2
Feature: Vehicle Testing Station management
  In order to manage vehicle testing organisations on the system
  As a DVSA Administrator
  I want to edit Site details

  @VM-10407
  @create-site("Popular Garage")
  Scenario: Update test lanes for One person test lane and Two people test lane successfully
    Given I am logged in as an Area Office User
    When I configure "Popular Garage" test lines to:
      | number of one person test lanes | number of two person test lanes |
      | 2                               | 3                               |
    Then site details for "Popular Garage" should be updated

  @create-site("Popular Garage")
  Scenario: Update test lanes for One person test lane and Two people test lane unsuccessfully
    Given I am logged in as an Area Office User
    When I try configure "Popular Garage" test lines to:
      | number of one person test lanes | number of two person test lanes |
      | 0                               | 0                               |
    Then site details for "Popular Garage" should not be updated

  @create-site("Popular Garage")
  Scenario: Update without selecting number of test lanes
    Given I am logged in as an Area Office User
    When I try configure "Popular Garage" test lines to:
      | number of one person test lanes | number of two person test lanes |
      | please select                   | please select                   |
    Then site details for "Popular Garage" should not be updated

  @create-site("Popular Garage")
  Scenario Outline: Change site status
    Given I am logged in as an Area Office User
    When I change the "Popular Garage" site status to <site_status_attribute>
    Then site status for "Popular Garage" should be updated
    Examples:
      | site_status_attribute |
      | Applied               |
      | Approved              |
      | Lapsed                |
      | Rejected              |
      | Retracted             |
      | Extinct               |

  @BL-102
  Scenario: Permitted user can remove all test classes from a site
    Given I am logged in as an Area Office User
    And a "Popular Garage" vehicle testing site exists
    When I remove all test classes from "Popular Garage"
    Then site testing classes for "Popular Garage" should be removed

  @BL-102
  @create-default-site("Popular Garage")
  Scenario: Cannot start MOT test if site has no testing classes
    Given I am logged in as an Area Office User
    And I remove all test classes from "Popular Garage"
    And I am logged in as a Tester
    When I try to start MOT test
    Then I am not permitted to do this