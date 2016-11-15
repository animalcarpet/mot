Feature: Cancel a Direct Debit Mandate
  As an Authorised Examiner
  I wants to cancel an existing Direct Debit Mandate
  So that I can use alternative method to pay or run down the slot balance if it is too high

  @dd
  @slots
  Scenario Outline: Allowing an Authorised Examiner to Cancel a Direct Debit Mandate
    Given I am logged in as an AEDM of "<organisation>"
    And I have an active direct debit mandate set up for <slots> slots in "<organisation>" on <dayOfMonth>
    When I request to cancel the direct debit for "<organisation>"
    Then The direct debit should be inactive
    Examples:
      | organisation | slots | dayOfMonth |
      | Hot Wheels   | 25    | 20         |
      | Big Wheels   | 50    | 5          |

  @dd
  @slots
  @create-default-site("Popular Garage", "Hot Wheels")
  Scenario Outline: No other user is authorised to Cancel Direct Debit
    Given "Hot Wheels" has active direct debit mandate set up for "25" slots on "20"
    And I am logged in as user with <role>
    And I have an active direct debit mandate for "Vehicle Fixes Ltd"
    When I request to cancel the direct debit for "Vehicle Fixes Ltd"
    Then My direct debit should not be canceled
    Examples:
      | role       |
      | areaOffice |
      | tester     |

  @slots
  @dd
  @create-default-ae("kwikfit")
  Scenario: Authorised Examiner attempts to cancel direct debit when no direct debit exists
    Given I am logged in as an Authorised Examiner
    When I request to cancel the direct debit for "kwikfit"
    Then My direct debit should not be canceled

  @slots
  @dd
  @create-default-ae("Crazy Wheels")
  Scenario Outline: Authorised Examiner is not allowed to set up direct debit for more than 75000 slots
    Given I am logged in as an Authorised Examiner
    When  I setup direct debit of <directDebitSlots> slots for "Crazy Wheels" on <dayOfMonth> day of the month
    Then The direct debit should not be setup
  Examples:
    | dayOfMonth | directDebitSlots |
    | 20         | 76000            |
    | 5          | 89000            |