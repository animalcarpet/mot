Feature: MOT Logs
  As a User
  I want to download MOT Test Logs
  So that I can view MOT Test Log data

  Scenario: Area Office Downloads Today's Test Logs
    Given I am logged in as a Tester
    And I create an mot test
    And I am logged in as an Area Office User
    When I download my test logs for today
    Then I will see the correct MOT Test Log Data

  Scenario:
    Given I am logged in as a Tester
    And I create an mot test
    And I am logged in as an Area Office User
    When I download that site's test logs for today
    Then I will see the correct MOT Test Log Data

  @quarantine
  Scenario:
    Given there is a test performed at the VTS when it's linked to some AE first time
    And there is a test performed at the VTS when it's linked to other AE first time
    And there is a test performed at the VTS when it's linked to some AE second time
    When I am logged in as a Area Office 1
    And I fetch test logs for those AE and VTS's
    Then test logs show correct test count
    And slot usage shows correct value