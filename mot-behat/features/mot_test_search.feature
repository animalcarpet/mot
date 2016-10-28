Feature: MOT Test Search

  Scenario: Mot info retrieval with valid VRM and test number
    Given I am logged in as a Tester
    And I pass Mot Test with a Class 3 Vehicle
    When I search for an MOT test
    Then the MOT test data is returned

  Scenario: MOT info retrieval with VRM missing
    Given I am logged in as a Tester
    And I pass Mot Test with a Class 3 Vehicle
    When I search for an MOT test with missing VRM
    Then the search is failed

  Scenario: MOT info retrieval with Mot test number missing
    Given I am logged in as a Tester
    And I pass Mot Test with a Class 3 Vehicle
    When I search for an MOT test with invalid Mot test number
    Then the search is failed

  Scenario: Mot info retrieval with non-existing VRM
    Given I am logged in as a Tester
    And I pass Mot Test with a Class 3 Vehicle
    When I search for an MOT test with non-existing VRM
    Then the search is failed

  Scenario: Mot info retrieval with non-existing mot test number
    Given I am logged in as a Tester
    And I pass Mot Test with a Class 3 Vehicle
    When I search for an MOT test with non-existing mot test number
    Then the search is failed