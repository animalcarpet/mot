@parallel_suite_2
Feature: Person Telephone

  @telephone
  @create-user("James Bond")
  Scenario: A user with permission can add a valid telephone number to a user
    Given I am logged in as an Area Office User
    When I change "James Bond" telephone number to '1234567890'
    Then the person's telephone number should be updated

  @telephone
  @create-user("James Bond")
  Scenario: A user with permission cannot add an invalid telephone number to a user
    Given I am logged in as an Area Office User
    When I try change "James Bond" telephone number to '1234567890123456789012345'
    Then the person's telephone number should not be updated

  @telephone
  Scenario: A user can update their telephone number to a valid number
    Given I am logged in as a Tester
    When I change my own telephone number to '1234567890'
    Then my telephone number should be updated

  @telephone
  Scenario: A user cannot update their telephone number to an invalid number
    Given I am logged in as a Tester
    When I try change my own telephone number to '1234567890123456789012345'
    Then my telephone number should not be updated

  @telephone
  @details-changed-notification
  @create-user("James Bond")
  Scenario: A user gets a notification when the DVSA update their telephone number
    Given I am logged in as a Area Office 1
    When I change "James Bond" telephone number to '1234567890'
    Then "James Bond" should receive a notification about the change

  @telephone
  @details-changed-notification
  Scenario: A user does not get a notification when they update their own telephone number
    Given I am logged in as a Tester
    When I change my own telephone number to '1234567890'
    Then the person should not receive a notification about the change
