Feature: MOT Test history
  As an authenticated DVSA
  I want to be able to search mot tests
  So that I can see mot test history
  
  Scenario Outline: Search for MOT tests by username
    Given I am logged in as <user_role>
    And "2" MOT tests have been created by different testers with the same prefix
    When I search for an MOT tests by username
    Then the MOT test history is returned
    Examples:
      | user_role          |
      | a Vehicle Examiner |
      | an Area Office User|

  Scenario Outline: Search for MOT tests by partial username
    Given I am logged in as <user_role>
    And "2" MOT tests have been created by different testers with the same prefix
    When I search for an MOT tests by partial username
    Then the MOT test history is not returned
    Examples:
      | user_role          |
      | a Vehicle Examiner |
      | an Area Office User|

  @quarantine
  Scenario Outline: Search for MOT tests history by site
    Given I am logged in as a Tester
    And vehicle has a <test_type> test started
    When I am logged in as a Vehicle Examiner
    And I search for an MOT tests history by site
    Then MOT test history for vehicle and type <test_type> is <result>
    Examples:
    | test_type                             | result       |
    | Normal Test                           | returned     |
    | Partial Retest Left VTS               | returned     |
    | Partial Retest Repaired at VTS        | returned     |
#    | Targeted Reinspection                 | returned     |
    | MOT Compliance Survey                 | returned     |
    | Inverted Appeal                       | returned     |
    | Statutory Appeal                      | returned     |
    | Other                                 | returned     |
#    | Re-Test                               | returned     |
    | Demonstration Test following training | not returned |
    | Routine Demonstration Test            | not returned |
    | Non-Mot Test                          | not returned |

  @quarantine
  Scenario Outline: Search for MOT tests history by vin 
    Given I am logged in as a Tester
    And vehicle has a <test_type> test started
    When I am logged in as a Vehicle Examiner
    And I search for an MOT tests history by vin
    Then MOT test history for vehicle and type <test_type> is <result>
  Examples:
    | test_type                             | result       |
    | Normal Test                           | returned     |
    | Partial Retest Left VTS               | returned     |
    | Partial Retest Repaired at VTS        | returned     |
#    | Targeted Reinspection                 | returned     |
    | MOT Compliance Survey                 | returned     |
    | Inverted Appeal                       | returned     |
    | Statutory Appeal                      | returned     |
    | Other                                 | returned     |
#    | Re-Test                               | returned     |
    | Demonstration Test following training | not returned |
    | Routine Demonstration Test            | not returned |
    | Non-Mot Test                          | not returned |

  @quarantine
  Scenario Outline: Search for MOT tests history by registration 
    Given I am logged in as a Tester
    And vehicle has a <test_type> test started
    When I am logged in as a Vehicle Examiner
    And I search for an MOT tests history by registration
    Then MOT test history for vehicle and type <test_type> is <result>
  Examples:
    | test_type                             | result       |
    | Normal Test                           | returned     |
    | Partial Retest Left VTS               | returned     |
    | Partial Retest Repaired at VTS        | returned     |
#    | Targeted Reinspection                 | returned     |
    | MOT Compliance Survey                 | returned     |
    | Inverted Appeal                       | returned     |
    | Statutory Appeal                      | returned     |
    | Other                                 | returned     |
#   | Re-Test                               | returned     |
    | Demonstration Test following training | not returned |
    | Routine Demonstration Test            | not returned |
    | Non-Mot Test                          | not returned |

  @quarantine
  Scenario Outline: Search for MOT tests history by test number
    Given I am logged in as a Tester
    And vehicle has a <test_type> test started
    When I am logged in as a Vehicle Examiner
    When I search for an MOT tests history by testNumber
    Then MOT test history for vehicle and type <test_type> is <result>
  Examples:
    | test_type                             | result       |
    | Normal Test                           | returned     |
    | Partial Retest Left VTS               | returned     |
    | Partial Retest Repaired at VTS        | returned     |
#    | Targeted Reinspection                 | returned     |
    | MOT Compliance Survey                 | returned     |
    | Inverted Appeal                       | returned     |
    | Statutory Appeal                      | returned     |
    | Other                                 | returned     |
    | Re-Test                               | returned     |
    | Demonstration Test following training | not returned |
    | Routine Demonstration Test            | not returned |
    | Non-Mot Test                          | not returned |
