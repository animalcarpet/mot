Feature: Role Management
  As a valid user
  I want to be able to assign roles to other users
  So that I can delegate tasks to other users

  @VM-10619 @VM-10737 @VM-10722 @VM-10723 @VM-11697
  @create-user("Mr Smith")
  Scenario Outline: A scheme manager adds a role to another user
    When I am logged in as a <manager>
    And I add the role of "<role_name>" to "Mr Smith"
    Then "Mr Smith" RBAC will have the role "<role_name>"
    And a status change event is generated for "Mr Smith" of "Role Association Change"
    And an event description contains my name
    And an event description contains phrase "<role_full_name>"
    And "Mr Smith" will receive a "DVSA Assign Role" notification
    And a notification subject contains phrase "<role_full_name>"
  Examples:
    | manager                  | role_name                         | role_full_name             |
    | Scheme Manager           | DVSA-SCHEME-MANAGEMENT            | Scheme manager             |
    | Customer Service Manager | CUSTOMER-SERVICE-CENTRE-OPERATIVE | Customer service operative |
    | DVLA Manager             | DVLA-OPERATIVE                    | DVLA operative             |

  @VM-10619 @VM-10737 @VM-10722 @VM-10723 @VM-11697
  Scenario Outline: A permitted user can add an internal role to a user that does not have a trade role
    Given I am logged in as a <permitted user>
    And The user "<non trade user>" exists
    When I add the role of "<role>" to "<non trade user>"
    Then "<non trade user>" RBAC will have the role "<role>"
  Examples:
    | permitted user           | role                              | non trade user             |
    | Scheme Manager           | CUSTOMER-SERVICE-MANAGER          | Customer Service Operative |
    | Scheme Manager           | DVLA-OPERATIVE                    | Area Office User 2         |
    | Scheme Manager           | FINANCE                           | Vehicle Examiner           |
    | Scheme Manager           | CUSTOMER-SERVICE-CENTRE-OPERATIVE | Vehicle Examiner           |
    | Scheme Manager           | DVSA-SCHEME-USER                  | Customer Service Operative |
    | Scheme Manager           | DVSA-AREA-OFFICE-1                | Finance User               |
    | Scheme Manager           | VEHICLE-EXAMINER                  | Customer Service Manager   |
    | Scheme User              | FINANCE                           | Vehicle Examiner           |
    | Customer Service Manager | CUSTOMER-SERVICE-CENTRE-OPERATIVE | DVLA Operative             |
    | DVLA Manager             | DVLA-OPERATIVE                    | DVLA Manager               |
    | Area Office User         | VEHICLE-EXAMINER                  | Customer Service Manager   |
    | Area Office User         | DVSA-AREA-OFFICE-2                | Customer Service Manager   |

  # We have deliberately only tested a selection of the possibilities here.. not the entire grid matrix.
  # We were told not to slow down behat too much and that the main coverage is done through selenium.
  @negative @VM-10619 @VM-10737 @VM-10722 @VM-10723 @VM-11697
  Scenario Outline: An unpermitted user can not add an internal role to a user
    Given I am logged in as an <unpermitted user>
    And The user "<non trade user>" exists
    When I try add the role of "<role>" to "<non trade user>"
    Then "<non trade user>" RBAC will not have the role "<role>"
  Examples:
    | unpermitted user          | role                              | non trade user   |
    | Scheme User               | DVLA-OPERATIVE                    | DVLA Manager     |
    | Scheme User               | CUSTOMER-SERVICE-CENTRE-OPERATIVE | DVLA Manager     |
    | Customer Service Operator | DVLA-OPERATIVE                    | Scheme User      |
    | DVLA Manager              | CUSTOMER-SERVICE-CENTRE-OPERATIVE | Vehicle Examiner |
    | Area Office User          | DVLA-OPERATIVE                    | Scheme User      |
    | Area Office User 2        | CUSTOMER-SERVICE-CENTRE-OPERATIVE | Vehicle Examiner |
    | Vehicle Examiner          | CUSTOMER-SERVICE-CENTRE-OPERATIVE | Area Office User |

  @VM-5041 @VM-11697
  Scenario Outline: A permitted user can remove a role from a user
    Given I am logged in as a <permitted user>
    And The user "<user>" exists
    And "<user>" has the role "<role>"
    When I remove the role of "<role>" from "<user>"
    Then "<user>" RBAC will not have the role "<role>"
    And a status change event is generated for "<user>" of "Role Association Change"
    And an event description contains my name
    And an event description contains phrase "<role_full_name>"
    And "<user>" will receive a "DVSA Remove Role" notification
    And a notification subject contains phrase "<role_full_name>"
  Examples:
    | permitted user | role                              | role_full_name             | user         |
    | Scheme Manager | FINANCE                           | Finance                    | DVLA Manager |
    | Scheme Manager | DVLA-OPERATIVE                    | DVLA operative             | DVLA Manager |
    | Scheme Manager | CUSTOMER-SERVICE-CENTRE-OPERATIVE | Customer service operative | DVLA Manager |
    | DVLA Manager   | DVLA-OPERATIVE                    | DVLA operative             | Scheme User  |

  # Self management
  @VM-11244 @VM-11697
  Scenario Outline: Check permitted DVSA users can not allocate a role to themselves
    Given I am logged in as a <permitted user>
    When I try to add the role of "<role>" to myself
    Then I should receive a Forbidden response
    And my RBAC will not contain the role "<role>"
  Examples:
    | permitted user           | role                              |
    | Scheme Manager           | DVSA-SCHEME-USER                  |
    | Scheme Manager           | FINANCE                           |
    | Scheme Manager           | CUSTOMER-SERVICE-MANAGER          |
    | Scheme Manager           | CUSTOMER-SERVICE-CENTRE-OPERATIVE |
    | Scheme Manager           | DVSA-AREA-OFFICE-1                |
    | Scheme Manager           | DVSA-AREA-OFFICE-2                |
    | Scheme Manager           | VEHICLE-EXAMINER                  |
    | Scheme User              | FINANCE                           |
    | Customer Service Manager | CUSTOMER-SERVICE-CENTRE-OPERATIVE |
    | DVLA Manager             | DVLA-OPERATIVE                    |
    | Area Office User         | DVSA-AREA-OFFICE-2                |
    | Area Office User         | VEHICLE-EXAMINER                  |

  @VM-11244 @VM-11697
  Scenario Outline: Check permitted DVSA users can not remove a role from themselves
    Given I am logged in as a <permitted user>
    And my RBAC has the role "<role>"
    When I try to remove the role of "<role>" from myself
    Then I should receive a Forbidden response
    And my RBAC will still have the role "<role>"
  Examples:
    | permitted user            | role                              |
    | Scheme Manager            | DVSA-SCHEME-MANAGEMENT            |
    | Scheme User               | DVSA-SCHEME-USER                  |
    | Customer Service Manager  | CUSTOMER-SERVICE-MANAGER          |
    | Customer Service Operator | CUSTOMER-SERVICE-CENTRE-OPERATIVE |
    | Area Office User          | DVSA-AREA-OFFICE-1                |
    | Finance User              | FINANCE                           |
