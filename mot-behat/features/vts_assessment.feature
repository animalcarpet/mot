@parallel_suite_2
Feature: Creating Site Assessment

  Background:
    Given there is a site associated with Authorised Examiner with following data:
      | siteName         | aeName     | startDate                  |
      | Fast cars garage | Hot Wheels | first day of 15 months ago |
    And There is a tester "John Doe" associated with "Fast cars garage"

  Scenario Outline: As a DVSA User I can add rag status to site
    Given I am logged in as a Scheme Manager
    When I attempt to add risk assessment to "Fast cars garage" site with data:
    | siteAssessmentScore   | aeRepresentativesFullName   | aeRepresentativesRole   | aeRepresentativesUserId   | testerUserId   | dvsaExaminersUserId   | dateOfAssessment   |
    | <siteAssessmentScore> | <aeRepresentativesFullName> | <aeRepresentativesRole> | <aeRepresentativesUserId> | <testerUserId> | <dvsaExaminersUserId> | <dateOfAssessment> |
    Then risk assessment is added to "Fast cars garage" site
    Examples:
    | siteAssessmentScore | aeRepresentativesFullName | aeRepresentativesRole | aeRepresentativesUserId | testerUserId | dvsaExaminersUserId | dateOfAssessment          |
    | 0.01                | John Kowalsky             | Boss                  |                         | tester       | dvsaExaminer        | first day of 6 months ago |
    | 50                  | John Kowalsky             | Boss                  |                         | tester       |                     | first day of 6 months ago |
    | 999.99              |                           | Boss                  |  ae                     | tester       | dvsaExaminer        | first day of 6 months ago |
    | 549.00              | John Kowalsky             | Boss                  |  ae                     | tester       | dvsaExaminer        | first day of 6 months ago |

  Scenario Outline: I cannot add rag status to site with invalid data
    Given I am logged in as a Scheme Manager
    When I attempt to add risk assessment to "Fast cars garage" site with invalid data:
    | siteAssessmentScore   | aeRepresentativesFullName   | aeRepresentativesRole   | aeRepresentativesUserId   | testerUserId   | dvsaExaminersUserId   | dateOfAssessment   |
    | <siteAssessmentScore> | <aeRepresentativesFullName> | <aeRepresentativesRole> | <aeRepresentativesUserId> | <testerUserId> | <dvsaExaminersUserId> | <dateOfAssessment> |
    Then risk assessment is not added to "Fast cars garage" site
    Examples:
    | siteAssessmentScore | aeRepresentativesFullName | aeRepresentativesRole | aeRepresentativesUserId | testerUserId | dvsaExaminersUserId | dateOfAssessment           |
    | -0.01               | John Kowalsky             | Boss                  |                         | tester       | dvsaExaminer        | first day of 16 months ago |
    | 50                  | John Kowalsky             | Boss                  |                         | tester       |                     | 3014-09-01                 |
    | 1000                |                           | Boss                  |  ae                     | tester       | dvsaExaminer        | first day of 16 months ago |
    | 02.03               |                           |                       |  ae                     |              | dvsaExaminer        |                            |
    |                     |                           |                       |                         | tester       | dvsaExaminer        | first day of 16 months ago |
