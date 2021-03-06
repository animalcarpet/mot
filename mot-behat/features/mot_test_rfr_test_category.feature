Feature: MOT test category and RFR view
  As a MOT test user
  I am presented with new EU Roadworthiness categories and Reasons for Rejection
  So that I can verify a vehicle is road worthy

  Scenario Outline:  MOT test defect category view for new categories
    Given I am logged in as a Tester
    And I start an Mot Test with a Class <class> Vehicle
    Then the new start-dated defect <category> is available to use
    Examples:
      |class | category   |
      | 3    | Steering   |
      | 4    | Steering   |
      | 5    | Steering   |
      | 7    | Steering   |

  Scenario Outline:  MOT test defect category view for end-dated categories
    Given I am logged in as a Tester
    And I start an Mot Test with a Class <class> Vehicle
    Then the end-dated defect <category> is not available to use
    Examples:
      |class | category                              |
      | 3    | "EU - Identification of the vehicle"  |
      | 4    | "EU - Identification of the vehicle"  |
      | 5    | "EU - Identification of the vehicle"  |
      | 7    | "EU - Identification of the vehicle"  |


  Scenario:  MOT test defect RFR view start date past available to use
    Given I am logged in as a Tester
    And I start an MOT Test
    Then the new start-dated past rfr is available to use

  @disabled
  Scenario:  MOT test defect RFR start date in future not available to use
    Given I am logged in as a Tester
    And I start an MOT Test
    Then the new start-dated future rfr is not available to use

  Scenario:  MOT test defect RFR end date in future not available to use
    Given I am logged in as a Tester
    And I start an MOT Test
    Then the new end-dated past rfr is not available to use

