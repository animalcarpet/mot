Feature: Cancel a Direct Debit Mandate
  As an Authorised Examiner
  I wants to cancel an existing Direct Debit Mandate
  So that I can use alternative method to pay or run down the slot balance if it is too high

  @dd-aedm
  @slots
  Scenario Outline: Allowing an Authorised Examiner to Cancel a Direct Debit Mandate
    Given I am logged in as an Authorised Examiner
    And I have an active direct debit mandate set up for <slots> slots in <organisation> on <dayOfMonth>
    When I request to cancel the direct debit for <organisation>
    Then The direct debit should be inactive
  Examples:
    | organisation | slots | dayOfMonth |
    | halfords     | 25    | 20         |
    | asda        | 50    | 5          |

  @dd-no-aedm
  @slots
  Scenario Outline: No other user is authorised to Cancel Direct Debit
    Given I am logged in as <role>
    And I have an active direct debit mandate for <organisation>
    When I request to cancel the direct debit for <organisation>
    Then My direct debit should not be canceled
  Examples:
    | role            | organisation |
    | areaoffice1user | halfords     |
    | tester1         | halfords     |

  @slots
  @no-dd
  Scenario: Authorised Examiner attempts to cancel direct debit when no direct debit exists
    Given I am logged in as an Authorised Examiner
    When I request to cancel the direct debit for "kwikfit"
    Then My direct debit should not be canceled