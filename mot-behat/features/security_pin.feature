Feature: User requests new security Pin

  Scenario: Tester user requests new security Pin
    Given I am logged in as a Tester
    When I request a new security pin
    Then the generated pin should be 6 digits long
    And the generated pin should be a number

  Scenario: Area Admin user requests new security Pin
    Given I am logged in as an Area Office User
    When I request a new security pin
    Then the generated pin should be 6 digits long
    And the generated pin should be a number

  Scenario: Unauthenticated user cannot receive a new security Pin
    Given I am not logged in
    When I request a new security pin
    Then I should not see any data in the response body
    And I should receive an Unauthorised response
    
  Scenario Outline: Tester user should not be able to request new security Pin for other users
    Given I am logged in as a Tester
    When I request a new security pin for a <role> user
    Then I should not receive a new security pin
    And I should receive a Bad Request response

  Examples:
    | role   |
    | ao1    |
    | ao2    |
    | tester |
    | csco   |