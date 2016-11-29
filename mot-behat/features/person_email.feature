@parallel_suite_2
Feature: Person Email

  @email
  Scenario: Successfully update Area Office user email address
    Given I am logged in as an Area Office User
    When I update my email address on my profile
    Then I will see my updated email address

  @email
  Scenario: Successfully update Tester user email address
    Given I am logged in as a Tester
    When I update my email address on my profile
    Then I will see my updated email address

  @email
  Scenario Outline: Area Office user attempts to change email that violates validation
    Given I am logged in as an Area Office User
    When I try update my email address to <email>
    Then my email address will not be updated

    Examples:
      | email |
      | .     |
      | .com  |
      | com   |
      | @     |

  @email
  Scenario Outline: Email validation is enforced on User Profile for Tester
    Given I am logged in as a Tester
    When I try update my email address to <email>
    Then my email address will not be updated

    Examples:
      | email |
      | .     |
      | .com  |
      | com   |
      | @     |

  @email
  @details-changed-notification
  @create-user("Freddy Krueger")
  Scenario: User is notified when their email address is changed by DVSA
    Given I am logged in as an Area Office User
    When I update "Freddy Krueger" email address
    Then the user's email address will be updated
    And "Freddy Krueger" should receive a notification about the change