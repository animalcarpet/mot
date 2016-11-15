Feature: Manual Adjustment
  As a finance user
  I want to manually adjust an organisations slot balance
  So that they have the correct number of slots

  Background:
    Given I am logged in as a Finance User

  @slots
  @manual-adjustments
  Scenario Outline: Submit a valid manual slot adjustment
    Given An AE has a slot balance of <initialBalance>
    When I submit a valid <typeOf> manual adjustment of <numberOfSlots> slots
    Then the AE slot balance should be updated to <updatedBalance>
    Examples:
      | initialBalance| typeOf   | numberOfSlots | updatedBalance |
      | -10           | positive | 20            | 10             |
      | 10            | positive | 50            | 60             |
      | -10           | negative | 50            | -60            |
      | 30            | negative | 20            | 10             |

  @slots
  @manual-adjustments
  Scenario Outline: Submit a manual adjustment with an invalid number of slots
    Given An AE requires a manual slot balance adjustment
    When I submit a <type> manual adjustment with <number_of_slots> slots
    Then I should see the validation error "must be a valid number of slots. For example, 1200"
    Examples:
      | type     | number_of_slots |
      | negative | 0               |
      | positive | 0               |
      | negative | -1              |
      | positive | -1              |

  @slots
  @manual-adjustments
  Scenario: Submit a top-up adjustment with negative type
    Given An AE requires a manual slot balance adjustment
    When I submit a top-up manual adjustment with negative type
    Then I should see the validation error "you can't choose Top-up when removing slots"

  @slots
  @manual-adjustments
  Scenario: Submit a manual adjustment with no reason
    Given An AE requires a manual slot balance adjustment
    When I submit a manual adjustment with no reason
    Then I should see the validation error "you must choose a reason"
