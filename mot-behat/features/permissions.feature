@transform
Feature: Permissions
  As the Roles Based Access Control process
  I want to implement user permissions and roles
  So that I can control how users interact with the application

  Scenario Outline: Vehicle Examiner is not authorised to perform an MOT Test for any Class
    Given I am logged in as a Vehicle Examiner
    When I attempt to start an Mot Test for a class <class> vehicle
    Then I should receive a Forbidden response
  Examples:
    | class |
    | 1     |
    | 2     |
    | 3     |
    | 4     |
    | 5     |
    | 7     |

  @story @VM-4497
  Scenario Outline: Users with no permission to test cannot start an MOT test
    Given I am logged in as a Tester
    And I Create a new Vehicle Technical Record with Class of <class>
    And I'm authenticated with my username and password <username> <password>
    When I attempt to start an Mot Test with a Class <class> Vehicle
    Then an MOT test number should not be allocated
  Examples:
    | username          | password | class |
    | cron-job          | DEFAULT  | 3     |
    | novtstester       | DEFAULT  | 3     |
    | areaadmin         | DEFAULT  | 4     |
    | schememgt         | DEFAULT  | 5     |
    | csco              | DEFAULT  | 7     |
    | aed1              | DEFAULT  | 3     |
    | aedm              | DEFAULT  | 4     |
    | siteManagerAtVts1 | DEFAULT  | 7     |
    | schemeuser        | DEFAULT  | 3     |
    | do                | DEFAULT  | 4     |
    | ft-enf-tester     | DEFAULT  | 4     |

  Scenario Outline: Validate Tester permissions
    Given I am logged in as a Tester
    When I get my Profile details
    Then my profile will contain the role "<roles>"

  Examples:
    | roles         |
    | TESTER        |
    | TESTER-ACTIVE |
    | USER          |

  @story @VM-9865
  @transform
  Scenario Outline: Vehicle Examiner can view the summary of a MOT test that is completed
    Given there is a "<status>" "<test>" MOT test
    When I log in as a Vehicle Examiner
    Then I can view the "<test>" MOT summary

  Examples:
    | test   | status |
    | demo   | passed |
    | demo   | failed |
    | normal | passed |
    | normal | failed |

  @story @VM-10522
  Scenario Outline: As a user I want to be able to carry out a demo test
    Given I am logged in as a Tester
    When I have a Tester Qualification status of "<status>" for group "<group>"
    And I don't have a demo test already in progress
    Then I can complete a Demo test for vehicle class "<vehicleClassCode>"

  Examples:
    | status           | group   | vehicleClassCode |
    | Demo Test Needed | A       | 2                |
    | Demo Test Needed | B       | 4                |
    | Demo Test Needed | A and B | 1                |
    | Demo Test Needed | A and B | 7                |
    | Qualified        | A       | 2                |
    | Qualified        | B       | 5                |
    | Qualified        | A and B | 2                |
    | Qualified        | A and B | 4                |

  Scenario Outline: Validate the permission of who can create an AE
    Given I am logged in as user with <role>
    When I attempt to create a new AE
    Then the creation of AE will be <status>
    Examples:
      | role            | status        |
      | tester          | FORBIDDEN     |
      | siteManager     | FORBIDDEN     |
      | siteAdmin       | FORBIDDEN     |
      | aedm            | FORBIDDEN     |
      | vehicleExaminer | FORBIDDEN     |
      | csco            | FORBIDDEN     |
      | schememgt       | FORBIDDEN     |
      | schemeuser      | FORBIDDEN     |
      | dvlaOper        | FORBIDDEN     |

  Scenario Outline: User cannot ABORT MOT test
    Given there is a Mot test with "<test_type>" type in progress
    And I am logged in as user with <role>
    When I abort the Mot Test
    Then I should receive a Forbidden response
    Examples:
      | test_type                | role            |
      | Targeted Reinspection    | areaOffice      |
      #| MOT Compliance Survey    | tester          |
      | Inverted Appeal          | siteManager     |
      #| Targeted Reinspection    | siteAdmin       |
      | Statutory Appeal         | aedm            |
      | MOT Compliance Survey    | csco            |
      | Targeted Reinspection    | schememgt       |
      | Inverted Appeal          | schemeuser      |
      | Statutory Appeal         | dvlaOper        |

  Scenario Outline: User can ABORT MOT test
    Given there is a Mot test with "<test_type>" type in progress
    And I am logged in as user with <role>
    When I abort the Mot Test
    Then the MOT Test Status is "<status>"
    Examples:
      | test_type                | role            | status     |
      | Targeted Reinspection    | vehicleExaminer | ABORTED    |
