@parallel_suite_2
@create-site
Feature: VTS
  As DVSA I should be able to search for VTS by name/number/town/postcode
  In function to find the details of a Vehicle Testing Station

  Scenario Outline: As a DVSA User I need to be able to search for VTS
    Given I am logged in as <user>
    When I search for a existing Vehicle Testing Station by it's <attribute>
    Then I should see the Vehicle Testing Station result
  Examples:
    | user                  | attribute |
    | an Area Office User   | number    |
    | an Area Office User 2 | name      |
    | a Vehicle Examiner    | town      |
    | a Vehicle Examiner    | postcode  |

  Scenario: As a DVSA User I cannot search for VTS only base on classes
    Given I am logged in as a Vehicle Examiner
    When I search for a Vehicle Testing Station only by a class
    Then the search will return no results

  Scenario: As a DVSA User if the VTS search is not successful
    Given I am logged in as an Area Office User
    When I search for a town with no Vehicle Testing Station
    Then the search will return no results

  Scenario Outline: As a User I want retrieve the information about a VTS
    Given I am logged in as <user>
    When I request information about a VTS
    Then the VTS details are returned
    Examples:
      | user                  |
      | an Area Office User 2 |
      | a DVLA Operative      |
    
  Scenario Outline: As a DVSA User I can add rag status to site
    Given I am logged in as a Scheme Manager
    When I attempt to add risk assessment to site with data:
    | siteAssessmentScore   | aeRepresentativesFullName   | aeRepresentativesRole   | aeRepresentativesUserId   | testerUserId   | dvsaExaminersUserId   | dateOfAssessment   |
    | <siteAssessmentScore> | <aeRepresentativesFullName> | <aeRepresentativesRole> | <aeRepresentativesUserId> | <testerUserId> | <dvsaExaminersUserId> | <dateOfAssessment> |
    Then risk assessment is added to site
    Examples:
      | siteAssessmentScore | aeRepresentativesFullName | aeRepresentativesRole | aeRepresentativesUserId | testerUserId | dvsaExaminersUserId | dateOfAssessment |
      | 0.01                | John Kowalsky             | Boss                  |                         | tester       | dvsaExaminer        | 2015-09-01       |
      | 50                  | John Kowalsky             | Boss                  |                         | tester       |                     | 2014-09-01       |
      | 999.99              |                           | Boss                  |  ae                     | tester       | dvsaExaminer        | 2013-09-01       |
      | 549.00              | John Kowalsky             | Boss                  |  ae                     | tester       | dvsaExaminer        | 2012-09-01       |

  Scenario Outline: I cannot add rag status to site with invalid data
    Given I am logged in as a Scheme Manager
    When I attempt to add risk assessment to site with invalid data:
      | siteAssessmentScore   | aeRepresentativesFullName   | aeRepresentativesRole   | aeRepresentativesUserId   | testerUserId   | dvsaExaminersUserId   | dateOfAssessment   |
      | <siteAssessmentScore> | <aeRepresentativesFullName> | <aeRepresentativesRole> | <aeRepresentativesUserId> | <testerUserId> | <dvsaExaminersUserId> | <dateOfAssessment> |
    Then risk assessment is not added to site
    Examples:
      | siteAssessmentScore | aeRepresentativesFullName | aeRepresentativesRole | aeRepresentativesUserId | testerUserId | dvsaExaminersUserId | dateOfAssessment |
      | -0.01               | John Kowalsky             | Boss                  |                         | tester       | dvsaExaminer        | 2015-09-01       |
      | 50                  | John Kowalsky             | Boss                  |                         | tester       |                     | 3014-09-01       |
      | 1000                |                           | Boss                  |  ae                     | tester       | dvsaExaminer        | 2013-09-01       |
      | 02.03               |                           |                       |  ae                     |              | dvsaExaminer        |                  |
      |                     |                           |                       |                         | tester       | dvsaExaminer        | 2011-09-01       |
