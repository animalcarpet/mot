Feature: Account Claim
  As a Tester
  I want to claim my account
  So that I can start testing vehicles

  Scenario: Unclaimed account
    When I am logged in as a Tester with an unclaimed account
    Then I should not be able to test vehicles

  Scenario: Claimed account
    Given I am logged in as a Tester with an unclaimed account
    When I claim my Account
    Then my account has been claimed
    Then I should be able to test vehicles