Feature: Update replacement certificate
  As an Area Office User
  I want to update a certificate

  Scenario Outline: Updating the replacement certificate
    Given I am logged in as an Area Office User
    And a MOT test for vehicle with the following data exists:
      | make_id   | make_other   | model_other   |
      | <make_id> | <make_other> | <model_other> |
    When I update expiry date "+1 day" on replacement certificate for the vehicle
    Then expiry date on replacement certificate draft for the vehicle should be changed to "+1 day"
    And  a replacement certificate is created
    Examples:
      | make_id  | make_other | model_other |
      | 18811      |            | Car         |
      |            |  Super     | Car         |
