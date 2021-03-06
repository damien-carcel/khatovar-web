@end-to-end @fixtures-users
Feature: Delete a user account
  In order to administrate user accounts
  As an administrator
  I need to be able to delete a user account

  # TODO: Use a mail catcher to check that the removed user was notified by mail about the destruction of his/her account
  Scenario: I can delete a user
    Given I am logged as an administrator
    When I remove the user damien
    Then I should be notified that it was removed
