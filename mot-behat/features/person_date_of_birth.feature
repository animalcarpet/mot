@parallel_suite_2
Feature: Person Date Of Birth

  @create-user("Apollo Creed")
  Scenario: A user with permission to change date of birth can update date of birth of a different person
    Given I am logged in as a Area Office 1
    When I change "Apollo Creed" date of birth to "20-10-1970"
    Then The person's date of birth should be updated

  Scenario: A user with permission to change date of birth cannot change their own date of birth
    Given I am logged in as a Area Office 1
    When I try change my own date of birth to "20-10-1970"
    Then I should receive a Forbidden response

  @create-user("Apollo Creed")
  Scenario: A user without permission to change date of birth cannot change date of birth of another person
    Given I am logged in as a Tester
    When I try change "Apollo Creed" date of birth to "20-10-1970"
    Then I should receive a Forbidden response

  @create-user("Apollo Creed")
  Scenario Outline: Validation of date of birth is enforced
    Given I am logged in as an Scheme User
    When I try change "Apollo Creed" date of birth to <day> <month> <year>
    Then The person's date of birth should not be updated

    Examples:
      | day  | month   | year     |
      | asda | vsasdaf | yeaasdfr |
      | ""   | ""      | ""       |
      | 01   | 30      | 20a0     |
      | 10   | 01      | 1800     |

  @details-changed-notification
  @create-user("Apollo Creed")
  Scenario: A user gets a notification when their date of birth is updated by DVSA
    Given I am logged in as a Area Office 1
    When I change "Apollo Creed" date of birth to "01-01-1970"
    Then "Apollo Creed" should receive a notification about the change
