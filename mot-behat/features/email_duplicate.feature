Feature: I want existing MOT2 users to be prevented from changing their email address to
  one that is in use with another account
  So that email can become a more secure channel for password reset and account reclaim
  Users should not be able to change their email address to one that is already in use.
  Users can choose to keep the original email, even if that is a dupe, should they wish.

  Scenario: User cannot change email address if it is already in use
    Given I am logged in as a Tester
    When I update my email to one that is already in use.
    Then I should receive an a response with true as the email is in use.

  Scenario: User can change their email if it is unique on the user base
    Given I am logged in as a Tester
    When I update my email that is not already in use.
    Then I should receive an a response with false as the email is not in use.

  Scenario: Non logged in user can still check if they are using duplicated email.
    Given I am not logged in
    When I update my email that is not already in use while not logged in.
    Then I should receive an a response with false as the email is not in use.