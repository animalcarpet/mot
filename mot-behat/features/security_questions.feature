Feature: user changes their security questions
  As a registered user who already has security questions set up
  I want to be able to change my security questions and answers
  So that I can reset my answers if I forgot them without calling BSD

  Scenario Outline: A user successfully changes their security questions
    Given I am logged in as a Tester
    And I update my security question answers to be <firstAnswer> and <secondAnswer>
    When I confirm my changes to my security questions
    Then my questions have been updated
    Examples:
      | firstAnswer     | secondAnswer           |
      | spot            | something else         |
      | my mum          | a fluffy squirrel      |

  Scenario Outline: A user is unsuccessful when changing their security questions
    Given I am logged in as a Tester
    And I update my security question answers to be <firstAnswer> and <secondAnswer>
    When I confirm my changes to my security questions
    Then my questions have not been updated
    Examples:
      | firstAnswer     | secondAnswer           |
      | spot            | 1234567890 1234567890 1234567890 1234567890 1234567890 1234567890 1234567890 1234567890  |
