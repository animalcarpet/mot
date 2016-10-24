Feature:
  As an unregistered user in the process of registering an account
  I want to be able to confirm my account details after completing the registration steps
  So that I can use the MOT system

  @VM-11722 @user-registration
  Scenario Outline: A user registers the account successfully
    Given I am an unregistered user
    And For the "email" step I input:
      | emailAddress   | confirmEmailAddress   |
      | <emailAddress> | <confirmEmailAddress> |
    And For the "details" step I input:
      | firstName   | middleName   | lastName   | phone   |
      | <firstName> | <middleName> | <lastName> | <phone> |
    And For the "address" step I input:
      | address1   | address2   | address3   | townOrCity | postcode |
      | <address1> | <address2> | <address3> | town       | PO57 0DE |
    And I supply valid answers to the security questions
    And I provide a valid password
    When I confirm my details
    Then an account is created
    Then I will be able to login

  Examples:
    | firstName | middleName | lastName         | phone         | emailAddress        | confirmEmailAddress | address1             | address2    | address3      |
    | John      | James      | Doe              | 123123123     | fake@dvsa.test      | fake@dvsa.test      | 123 address one      |             | address three |
    | Mary-Anne | Jane       | Smith            | 123456721     | fake123@dvsa.test   | fake123@dvsa.test   | address one          | address two | address three |
    | Jäné      | John       | Doe              | 0031123442134 | fake-mail@dvsa.test | fake-mail@dvsa.test | address one          | address two | address three |
    | Jane      | May        | Spencer-Campbell | 345623451     | fakemail1@dvsa.test | fakemail1@dvsa.test | address street       | address two | address three |
    | Jane      | May-Jane   | Smith            | 2345672345    | fakemail2@dvsa.test | fakemail2@dvsa.test | 12(a) address street | address two | address three |
    | Jane      | May        | Smith            | 123456234     | fakemail3@dvsa.test | fakemail3@dvsa.test | 12 address street    | address two |               |
    | Jane      | May        | Smith            | 23453456      | fakemail4@dvsa.test | fakemail4@dvsa.test  | 12 address street   |             |               |

  @VM-11722 @negative @user-registration
  Scenario Outline: A user attempts to register an account but supplies invalid or insufficient details
    Given I am an unregistered user
    And For the "email" step I input:
      | emailAddress   | confirmEmailAddress   |
      | <emailAddress> | <confirmEmailAddress> |
    And For the "details" step I input:
      | firstName   | middleName   | lastName   | phone   |
      | <firstName> | <middleName> | <lastName> | <phone> |
    And For the "address" step I input:
      | address1   | address2   | address3   | townOrCity | postcode |
      | <address1> | <address2> | <address3> | town       | PO57 0DE |
    And I supply valid answers to the security questions
    And I provide a valid password
    When I try to confirm my details
    Then an account is not created

  Examples:
    | firstName | middleName | lastName | phone      | emailAddress        | confirmEmailAddress  | address1          | address2    | address3      |
    |           |            | Smith    | 1234562345 | fake1@dvsa.test     | fake1@dvsa.test      | address street    | address two | address three |
    | John      |            |          | 1234562345 | fake2@dvsa.test     | fake2@dvsa.test      | address           | addresstwo  | address3      |
    |           |            |          | 123454542  | fakemail5@dvsa.test | fakemail5@dvsa.test  | 12 address street |             |               |
    | Jane      |            | Smith    | 34522224   | fakemail6@dvsa.test | incorrect@dvsa.test  | 12 address street |             |               |
    | Jane      |            | Smith    | 1345623456 | fakemail7@dvsa.test |                      | 12 address street |             |               |
    | Jane      |            | Smith    | 12345234   | fakemaildvsa.test   | fakemaildvsa.test    | 12 address street |             |               |
    | Jane      |            | Smith    | 2345345234 | fakemail8@dvsa.test | fakemail8@dvsa.test  |                   |             |               |

  @negative @user-registration
  Scenario Outline: A user attempts to register an email for an account already in use
    Given I am an unregistered user
    And For the "email" step I input:
      | emailAddress   | confirmEmailAddress   |
      | <emailAddress> | <confirmEmailAddress> |
    And For the "details" step I input:
      | firstName   | middleName   | lastName   | phone   |
      | <firstName> | <middleName> | <lastName> | <phone> |
    And For the "address" step I input:
      | address1   | address2   | address3   | townOrCity | postcode |
      | <address1> | <address2> | <address3> | town       | PO57 0DE |
    And I supply valid answers to the security questions
    And I provide a valid password
    When I try to confirm my details
    And I try to register an account with the same email
    Then an account is not created

  Examples:
  | firstName | middleName | lastName         | phone         | emailAddress                     | confirmEmailAddress               | address1          | address2    | address3      |
  | John      | James      | Doe              | 123123123     | duplicate-email-test@dvsa.test   | duplicate-email-test@dvsa.test    | 123 address one   |             | address three |

