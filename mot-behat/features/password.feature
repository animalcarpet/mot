Feature: Password
  As a User
  I want to change password when logging in
  So that I can log in with new password

  Scenario Outline: Password Change Successfully
    Given I am logged in as <user>
    When user fills in "<oldPassword>", "<newPassword>", "<confirmNewPassword>"
    Then my password is updated
    And I can log in with new password "<newPassword>"
  Examples:
    | user               | oldPassword | newPassword  | confirmNewPassword |
    | a Tester           | Password1   | NewPassword1 | NewPassword1       |
    | a Vehicle Examiner | Password1   | NewPassword1 | NewPassword1       |

  Scenario Outline: Password Change Unsuccessfully
    Given I am logged in as <user>
    When user fills in "<oldPassword>", "<newPassword>", "<confirmNewPassword>"
    Then my password is not updated
  Examples:
    | user               | oldPassword     | newPassword  | confirmNewPassword |
    | a Tester           | Password1       | NewPassword1 | NewPassword2       |
    | a Vehicle Examiner | Password1       | NewPassword1 | NewPassword2       |
    | a Tester           | InvalidPassword | NewPassword1 | NewPassword1       |
    | a Vehicle Examiner | InvalidPassword | NewPassword1 | NewPassword1       |
    | a Tester           | Password1       | NewPassword1 |                    |
    | a Vehicle Examiner | Password1       | NewPassword1 |                    |

  @password-reset
  Scenario Outline: Validate password reset token
    Given that I have a password reset token of type <tokenType>
    When I validate the token
    Then the token should be <result>
    Examples:
      |tokenType    |result            |
      |invalid      |invalid           |
      |valid        |valid             |

  @password-reset
  Scenario Outline: Change password through the token link
    Given that I have a password reset token of type <tokenType>
    And I attempt to change my password to <newPassword>
    Then the result should be <result> with <errorMessage>
    Examples:
      |newPassword           |tokenType   |errorMessage                             |result  |
      |                      |valid       |newPassword not found                    |false   |
      |newPassword12         |empty       |token not found                          |false   |
      |newPassword12         |invalid     |Message by Token INVALIDTOKEN12 not found|false   |
      |newPassword12         |valid       |                                         |true    |
      |newPassword12         |expired     |token not found                          |false   |

  @password-reset
  Scenario Outline: Sending emails to users who have forgotten their password
    When I attempt to reset my password with <userId>
    Then the message should be <message>
    Examples:
      | userId          | message                 |
      | invalidUserId   | Person not found        |
      | validUserId     | Password reset by email |

  @password-expiry-notification
  Scenario: Send password expiry remainder emails
    Given I am logged in as a Tester
    And I set remainder emails to be sent regarding password expiry
