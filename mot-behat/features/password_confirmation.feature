Feature: Confirm Password
  As a security officer
  I want users to confirm their password before changing sensitive data
  So that the risk of unauthorised access is reduced

  Background:
    Given there is a site "Fast cars garage" associated with Authorised Examiner "Hot Wheels"
    And There is a tester "John Doe" associated with "Fast cars garage"

  Scenario: Successfully confirm password
    Given I am logged in as a Tester "John Doe"
    And I need to confirm my password before changing sensitive data
    When I supply the correct password
    Then password verification should be successful

  Scenario: Fail to confirm password
    Given I am logged in as a Tester "John Doe"
    And I need to confirm my password before changing sensitive data
    When I supply the incorrect password
    Then password verification should not be successful

