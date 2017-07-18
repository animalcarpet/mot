Feature: Generate financial reports
  As a finance user
  I want to generate financial reports
  So that I can keep a track of historical financial information

  @quarantine
  @slot
  @report
  Scenario Outline: Request to generate slot usage report
    Given I am logged in as a Finance User
    When I request to generate a <reportType> report from <from> to <to>
    Then The report should be added to the queue
  Examples:
    | reportType       | from       | to        |
    | generalLedger    | yesterday  | now       |
    | allPayments      | lastWeek   | yesterday |
    | allPayments      | lastMonth  | today     |
    | bankingBreakDown | 3MonthsAgo | lastMonth |
    | allPayments      | 4MonthsAgo | lastMonth |
    | allPayments      | 3DaysAgo   | today     |

  @quarantine
  @slot
  @report
  Scenario Outline: Get the status report generation
    Given I am logged in as a Finance User
    And I request to generate a <reportType> report from <from> to <to>
    When I request to get the status of the report
    Then The status should be returned
  Examples:
    | reportType       | from       | to        |
    | generalLedger    | yesterday  | now       |
    | bankingBreakDown | 2MonthsAgo | lastMonth |