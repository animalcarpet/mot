Feature: Mystery shopper test certificates in "duplicate or replacement certificate" search results
  As a Vehicle Examiner
  I want to hide mystery shopper tests in "duplicate or replacement certificate" search results
    Unless the test was carried out at a VTS associated with the searching user
  So that mystery shopper tests do not look suspicious

  Scenario: Tester can find own mystery shopper test certificates
    Given I am logged in as a Tester
    When I pass an MOT Test on a Masked Vehicle
    Then I should be able to find the MOT Test certificate for reprinting

  @create-site("Some VTS")
  Scenario: Tester can find mystery shopper test certificates carried out at an associated VTS
    Given I am logged in as a Tester at site "Some VTS"
    When another Tester passes an MOT Test on a Masked Vehicle at "Some VTS"
    Then I should be able to find the MOT Test certificate for reprinting

  @create-ae("Some organisation")
  @create-site("Some VTS", "Some organisation")
  Scenario: AEDM can find mystery shopper test certificates carried out at an associated VTS
    Given I am logged in as an AEDM to "Some organisation"
    When another Tester passes an MOT Test on a Masked Vehicle at "Some VTS"
    Then I should be able to find the MOT Test certificate for reprinting

  @create-site("Some VTS")
  @create-site("Some other VTS")
  Scenario: Tester cannot find mystery shopper test certificates carried out at a different VTS
    Given I am logged in as a Tester at site "Some VTS"
    When another Tester passes an MOT Test on a Masked Vehicle at "Some other VTS"
    Then I should not be able to find the MOT Test certificate for reprinting

  Scenario: Tester can still find mystery shopper test certificates after the vehicle is unmasked
    Given I am logged in as a Tester
    When I pass an MOT Test on a Masked Vehicle
    And the vehicle from the previous MOT Test is unmasked
    Then I should still be able to find the MOT Test certificate for reprinting


