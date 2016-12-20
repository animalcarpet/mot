Feature:
  As an unregistered user in the process of registering an account
  I want to be able to confirm my account details after completing the registration steps
  So that I can use the MOT system

  @VM-11722 @user-registration
  Scenario Outline: A user registers the account successfully
    Given I am an unregistered user
    And For the email step I input email address
    And For the "details" step I input:
      | firstName   | middleName   | lastName   |
      | <firstName> | <middleName> | <lastName> |
    And For the date of Birth I input:
      | day   | month   | year   |
      | <day> | <month> | <year> |
    And For the "contactDetails" step I input:
      | address1   | address2   | address3   | townOrCity | postcode | phone   |
      | <address1> | <address2> | <address3> | town       | PO57 0DE | <phone> |
    And I supply valid answers to the security questions
    And I provide a valid password
    When I confirm my details
    Then an account is created
    Then I will be able to login

    Examples:
      | firstName | middleName | lastName         | phone         | address1             | address2    | address3      | day | month | year |
      | John      | James      | Doe              | 123123123     | 123 address one      |             | address three | 01  | 02    | 1990 |
      | Mary-Anne | Jane       | Smith            | 123456721     | address one          | address two | address three | 1   |  2    | 1990 |
      | Jäné      | John       | Doe              | 0031123442134 | address one          | address two | address three | 01  | 02    | 1990 |
      | Jane      | May        | Spencer-Campbell | 345623451     | address street       | address two | address three | 01  | 02    | 1990 |
      | Jane      | May-Jane   | Smith            | 2345672345    | 12(a) address street | address two | address three | 01  | 02    | 1990 |
      | Jane      | May        | Smith            | 123456234     | 12 address street    | address two |               | 01  | 02    | 1990 |
      | Jane      | May        | Smith            | 23453456      | 12 address street   |             |               | 01  | 02    | 1990 |

  @VM-11722 @negative @user-registration
  Scenario Outline: A user attempts to register an account but supplies invalid or insufficient details
    Given I am an unregistered user
    And For the "email" step I input:
      | emailAddress   | confirmEmailAddress   |
      | <emailAddress> | <confirmEmailAddress> |
    And For the "details" step I input:
      | firstName   | middleName   | lastName   | phone   |
      | <firstName> | <middleName> | <lastName> | <phone> |
    And For the date of Birth I input:
      | day   | month   | year   |
      | <day> | <month> | <year> |
    And For the "contactDetails" step I input:
      | address1   | address2   | address3   | townOrCity | postcode | phone   |
      | <address1> | <address2> | <address3> | town       | PO57 0DE | <phone> |
    And I supply valid answers to the security questions
    And I provide a valid password
    When I try to confirm my details
    Then an account is not created

    Examples:
      | firstName | middleName | lastName | phone      | emailAddress                             | confirmEmailAddress                    | address1          | address2    | address3      | day | month | year |
      |           |            | Smith    | 1234562345 | success+fake1@simulator.amazonses.com    | success+fake1@simulator.amazonses.com  | address street    | address two | address three | 01  | 02    | 1990 |
      | John      |            |          | 1234562345 | success+fake2@simulator.amazonses.com    | success+fake2@simulator.amazonses.com  | address           | addresstwo  | address3      | 01  | 02    | 1990 |
      |           |            |          | 123454542  | success+fake3@simulator.amazonses.com    | success+fake3@simulator.amazonses.com  | 12 address street |             |               | 01  | 02    | 1990 |
      | Jane      |            | Smith    | 34522224   | success+fake4@simulator.amazonses.com    | incorrect@dvsa.test                    | 12 address street |             |               | 01  | 02    | 1990 |
      | Jane      |            | Smith    | 1345623456 | success+fake5@simulator.amazonses.com    |                                        | 12 address street |             |               | 01  | 02    | 1990 |
      | Jane      |            | Smith    | 2345345234 | success+fake7@simulator.amazonses.com    | success+fake7@simulator.amazonses.com  |                   |             |               | 01  | 02    | 1990 |
      | Jane      | May        | Smith    | 23453456   | success+fake8@simulator.amazonses.com    | success+fake8@simulator.amazonses.com  | 12 address street |             |               | 30  | 02    | 1900 |
      | Jane      | May        | Smith    | 23453456   | success+fake9@simulator.amazonses.com    | success+fake9@simulator.amazonses.com  | 12 address street |             |               | 01  | 02    | 1890 |
      | Jane      | May        | Smith    | 23453456   | success+fake10@simulator.amazonses.com   | success+fake10@simulator.amazonses.com | 12 address street |             |               | 01  | 02    | 2100 |
      | Jane      | May        | Smith    | 23453456   | success+fake11@simulator.amazonses.com   | success+fake11@simulator.amazonses.com | 12 address street |             |               | ss  | ss    | ssss |
      | Jane      | May        | Smith    | 23453456   | success+fake12@simulator.amazonses.com   | success+fake12@simulator.amazonses.com | 12 address street |             |               |     |       |      |

  @negative @user-registration
  Scenario Outline: A user attempts to register an email for an account already in use
    Given I am an unregistered user
    And For the "email" step I input:
      | emailAddress   | confirmEmailAddress   |
      | <emailAddress> | <confirmEmailAddress> |
    And For the "details" step I input:
      | firstName   | middleName   | lastName   |
      | <firstName> | <middleName> | <lastName> |
    And For the "contactDetails" step I input:
      | address1   | address2   | address3   | townOrCity | postcode | phone   |
      | <address1> | <address2> | <address3> | town       | PO57 0DE | <phone> |
    And I supply valid answers to the security questions
    And I provide a valid password
    When I try to confirm my details
    And I try to register an account with the same email
    Then an account is not created

    Examples:
      | firstName | middleName | lastName         | phone         | emailAddress                                | confirmEmailAddress                           | address1          | address2    | address3      |
      | John      | James      | Doe              | 123123123     | success+duplicate@simulator.amazonses.com   | success+duplicate0@simulator.amazonses.com    | 123 address one   |             | address three |

