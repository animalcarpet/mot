Feature: Nomination
  As a valid user
  I want to nominate user to site or organisation role
  So that user can have new role

  Scenario Outline: A manager nominates user to site role
    Given I am logged in as <user_manager> to new site
    When I nominate user to <role_code> role
    Then the nominated user has a pending site role "<role_code>"
  Examples:
      | user_manager          | role_code    |
      | an Area Office User   | TESTER       |
      | an Area Office User   | SITE-MANAGER |
      | an Area Office User   | SITE-ADMIN   |
      | an Area Office User 2 | TESTER       |
      | an Area Office User 2 | SITE-MANAGER |
      | an Area Office User 2 | SITE-ADMIN   |
      | a Site Manager        | SITE-ADMIN   |
      | a Site Manager        | TESTER       |
      | a Site Manager        | SITE-MANAGER |

  Scenario Outline: A Manager nominates user to organisation role
    Given I am logged in as <user_manager> to new organisation
    When I nominate user to <role_name> role
    Then the nominated user has a pending organisation role "<role_code>"
  Examples:
      | user_manager | role_code | role_name                    |
      | an Aedm      | AED       | Authorised Examiner Delegate |
