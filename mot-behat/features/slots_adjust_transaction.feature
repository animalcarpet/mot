Feature: Manual Adjustment of transaction
  As a Finance User
  I want to make adjustments to existing slot transactions
  When a payment previously input by Finance is discovered to contain an error
  So that the right customer gets the right payment, product or amount

  @slot
  @create-default-ae("Kwikfit")
  Scenario: Manual Adjustment when a transaction is allocated to an Incorrect Authorised Examiner
    Given I am logged in as a Finance User
    And A slot transaction exist
    When I adjust the transaction to the correct Authorised Examiner "Kwikfit"
    Then The transaction should be adjusted

  @slot
  Scenario Outline: Manual Adjustment when a transaction is created with Incorrect Data
    Given I am logged in as a Finance User
    And A slot transaction exist
    When I adjust the transaction attribute <field> to <value> because of <reason>
    Then The transaction should be adjusted
  Examples:
    | field        | value    | reason    |
    | chequeNumber | 89076834 | wrongData |
    | slipNumber   | 1019183  | wrongData |
    | accountName  | James    | wrongData |
    | amount       | 410.00   | wrongData |

  @slot
  @sip
  Scenario Outline: List of amendment reasons by type
    Given I am logged in as a Finance User
    When I request a list of amendment reasons by type "<type>"
    Then I should have "<reason>" available in the result
  Examples:
    | type                             | reason                                     |
    | Failures                         | Cheque is greater than 6 months old        |
    | Failures                         | Cheque - 2 sig. required, only 1 displayed |
    | Failures                         | Cheque - Fraud                             |
    | Failures                         | Cheque - Payment stopped                   |
    | Failures                         | Cheque - Returned to Drawer                |
    | Failures                         | Cheque - Words and Figures do not match    |
    | Failures                         | Card - Chargeback request made             |
    | Failures                         | Direct debit - DDI Claim made              |
    | Failures                         | Direct debit - Mandate payment failure     |
    | Slot Refund                      | Manual error                               |
    | Slot Refund                      | Account closure                            |
    | Slot Refund                      | User requested                             |
    | Manual Adjustment of slots       | Top-up DVSA Garage                         |
    | Manual Adjustment of slots       | Reconciliation                             |
    | Manual adjustment of transaction | Incorrect Customer allocated               |
    | Manual adjustment of transaction | Incorrect Amount input                     |
    | Manual adjustment of transaction | Incorrect Product allocated                |