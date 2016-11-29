@parallel_suite_2
Feature: Driving Licence

  @driving-licence
  Scenario: An Area Office User can add a licence to a tester's profile
    Given I am logged in as an Area Office User
    And I have selected a user with name "Colin MCrae" who needs to have a licence added to their profile
    When I add a licence 'smith711215jb9az' to "Colin MCrae" profile
    Then "Colin MCrae" licence should match 'SMITH711215JB9AZ'

  @driving-licence
  Scenario: A Scheme manager can add a licence to a tester's profile
    Given I am logged in as a Scheme Manager
    And I have selected a user with name "Colin MCrae" who needs to have a licence added to their profile
    When I add a licence '11223344' with the region 'NI' to "Colin MCrae" profile
    Then "Colin MCrae" licence should match '11223344'

  @driving-licence
  Scenario: An Area Office User can update a licence on a tester's profile
    Given I am logged in as an Area Office User
    And I have selected a user with name "Colin MCrae" who needs to have their licence edited
    When I update "Colin MCrae" licence to 'SMITH711215JB9AZ'
    Then "Colin MCrae" licence should match 'SMITH711215JB9AZ'

  @driving-licence
  Scenario: A Scheme user can update a tester's licence and region
    Given I am logged in as an Scheme User
    And I have selected a user with name "Colin MCrae" who needs to have their licence edited
    When I update "Colin MCrae" licence to '11223344' and the region 'NI'
    Then "Colin MCrae" licence should match '11223344'

  @driving-licence
  Scenario: An Area Office User cannot add an invalid licence to a tester's profile
    Given I am logged in as an Area Office User
    And I have selected a user with name "Colin MCrae" who needs to have a licence added to their profile
    When I add a licence 'IAMINVALID' to "Colin MCrae" profile
    Then "Colin MCrae" should not have a licence associated with their account

  @driving-licence
  Scenario: An Area Office User cannot add an invalid licence and region to a tester's profile
    Given I am logged in as an Area Office User
    And I have selected a user with name "Colin MCrae" who needs to have a licence added to their profile
    When I add a licence 'IAMINVALID' to "Colin MCrae" profile
    Then "Colin MCrae" should not have a licence associated with their account

  @driving-licence
  Scenario: An Area Office User cannot update a tester's licence to invalid data
    Given I am logged in as an Area Office User
    And I have selected a user with name "Colin MCrae" who needs to have their licence edited
    When I update "Colin MCrae" licence to 'IAMINVALID'
    Then "Colin MCrae" licence should not match 'IAMINVALID'

  @driving-licence
  Scenario: An Area Office User can delete a licence on a tester's profile
    Given I am logged in as an Area Office User
    And I have selected a user with name "Colin MCrae" who needs to have their licence deleted
    When I delete "Colin MCrae" licence
    Then "Colin MCrae" should not have a licence associated with their account

  @driving-licence
  @details-changed-notification
  Scenario: An Area Office User can delete a licence on a tester's profile
    Given I am logged in as an Area Office User
    And I have selected a user with name "Colin MCrae" who needs to have their licence deleted
    When I delete "Colin MCrae" licence
    Then "Colin MCrae" should not have a licence associated with their account
