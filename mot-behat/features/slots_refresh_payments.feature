Feature: Refresh payments as a CRON job
  As a Cron user
  I want to refresh payments in MOT that might be not updated
  So that all payments will be synchronized with CPMS
#
#  @slots
#  Scenario Outline: Cron user gets list of "in progress" payments older than x minutes
#  in order to process it later and update those that changed
#    Given I am logged in as a Finance User
#    And I have payment with status <status> and is <minutes> minutes old
#    And I am logged in as an Cron User
#    When I request the the list of payments to be refreshed
#    Then I should get results with receipt references
#
#  Examples:
#  | status | minutes |
#  | P      | 40      |
#  | P      | 20      |
#  | S      | 50      |
  @quarantine
  @slots
  @sip
  Scenario: Cron user updates the payment status on given transaction
    Given I am logged in as a Finance User
    And I have payment with status "P" and is 40 minutes old
    And I am logged in as an Cron User
    When I attempt to refresh the payment status
    Then I should get valid message from refresh endpoint


