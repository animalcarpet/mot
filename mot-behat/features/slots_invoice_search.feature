Feature: Search for payment by Invoice reference
  As a Finance User
  I want to search for a payment with an invoice reference
  So that I can see the slot transaction details associated with the payment

  @quarantine
  @slots
  Scenario: Finance User search for payment with a valid invoice reference
    Given I am logged in as a Finance User
    And I bought "100" slots for organisation "halfords" at "2.05" price
    When I search for the payment with a valid invoice
    Then I should receive invoice details

  @slots
  Scenario: Finance User search for payment with an invalid invoice reference
    Given I am logged in as a Finance User
    When I search for the payment with an invalid invoice
    Then I should not receive invoice details
