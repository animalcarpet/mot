Feature: Authorised Examiner
  As a DVSA Administrator
  I want to create an Authorised Examiner
  So that a new Vehicle Testing Station could be assigned to it

  @defect @VM-8383
  @story @VM-2166
  @story @VM-10406
  Scenario: Area Office user creates an Authorised Examiner
    Given I am logged in as an Area Office User
    Then I should be able to create a new Authorised Examiner
    Then I should be able to approve this Authorised Examiner

  Scenario: Area Admin user retrieves details of an Authorised Examiner
    Given I am logged in as an Area Office User
    When I search for an existing Authorised Examiner
    Then I will see the Authorised Examiner's details

  @story @VM-2526
  Scenario: Linking an Authorised Examiner with a Site
    Given I am logged in as an Area Office User
    Then I should be able to create a new Authorised Examiner
    Then I should be able to create a site for linking
    Then I should be able to link the new AE and site together