Feature: Nomination
  As a valid user
  I want to nominate user to site or organisation role
  So that user can have new role

  @create-site("V-Tech UK")
  Scenario Outline: An Area Office User nominates user to site role
    Given I am logged in as <user_manager>
    When I nominate user to <role_code> role at "V-Tech UK" site
    Then the nominated user has a pending site role "<role_code>" at "V-Tech UK"
  Examples:
      | user_manager          | role_code    |
      | an Area Office User   | TESTER       |
      | an Area Office User   | SITE-MANAGER |
      | an Area Office User   | SITE-ADMIN   |
      | an Area Office User 2 | TESTER       |
      | an Area Office User 2 | SITE-MANAGER |
      | an Area Office User 2 | SITE-ADMIN   |

  @create-site("V-Tech UK")
  Scenario Outline: A Site Manager nominates user to site role
    Given I am logged in as <user_manager> at "V-Tech UK" site
    When I nominate user to <role_code> role at "V-Tech UK" site
    Then the nominated user has a pending site role "<role_code>" at "V-Tech UK"
    Examples:
      | user_manager          | role_code    |
      | a Site Manager        | SITE-ADMIN   |
      | a Site Manager        | TESTER       |
      | a Site Manager        | SITE-MANAGER |

  @create-default-site("Best Garage", "New Brave Organisation")
  @create-tester("Marty McFly")
  Scenario Outline: A Manager nominates user to organisation role
    And I am logged in as an AEDM
    When I nominate "Marty McFly" to <role_name> role
    Then the nominated user "Marty McFly" has a pending organisation role "<role_code>"
  Examples:
      | role_code | role_name                    |
      | AED       | Authorised Examiner Delegate |
