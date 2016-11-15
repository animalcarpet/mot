Feature: Obtain payment gateway redirection data
  As an Finance User
  I want to obtain the payment gateway redirection parameters
  So that I can buy slots by credit / debit card

  @quarantine
  @slots
  @card
  @create-default-ae("kwikfit")
  Scenario Outline: Finance User initiates a request to make a card payment
    Given  I am logged in as a Finance User
    When I initiate the request to make a card payment
    Then I should receive <parameter> parameter in the data returned
  Examples:
    | parameter         |
    | gateway_url       |
    | receipt_reference |
    | redirection_data  |