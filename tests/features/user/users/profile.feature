@end-to-end @fixtures-users
Feature: Manage a user account
  In order to manage my user account
  As a user
  I need to interact with the account page

  Scenario: I can access my profile
    Given I am logged as damien
    When I go to my user profile
    Then I can see my user information

  Scenario: I can edit my profile:
    Given I am logged as damien
    Given I am on "profile/"
    When I follow "Éditer le profil"
    Then I should be on "profile/edit"
    When I fill in the following:
      | Nom d'utilisateur   | pandore             |
      | Adresse e-mail      | pandore@khatovar.fr |
      | Mot de passe actuel | damien              |
    And I press "Mettre à jour"
    Then I should see "Le profil a été mis à jour"
    And I should see "Nom d'utilisateur: pandore"
    And I should see "Adresse e-mail: pandore@khatovar.fr"

  Scenario: I cannot edit my profile without my password
    Given I am logged as damien
    Given I am on "profile/"
    And I follow "Éditer le profil"
    When I fill in the following:
      | Nom d'utilisateur   | pandore |
      | Mot de passe actuel | pandore |
    And I press "Mettre à jour"
    Then I should see "Le mot de passe est invalide."

  Scenario: I can change my password
    Given I am logged as damien
    Given I am on "profile/"
    When I follow "Changer le mot de passe"
    Then I should be on "profile/change-password"
    When I fill in the following:
      | Mot de passe actuel             | damien  |
      | Nouveau mot de passe            | pandore |
      | Répéter le nouveau mot de passe | pandore |
    And I press "Modifier le mot de passe"
    Then I should see "Le mot de passe a été modifié"
    When I follow "Déconnexion"
    And I fill in the following:
      | Nom d'utilisateur | damien  |
      | Mot de passe      | pandore |
    And I press "Connexion"
    Then I should be on "profile/"
    And I should see "Nom d'utilisateur: damien"

  Scenario: I cannot change my password without knowing it
    Given I am logged as damien
    Given I am on "profile/"
    And I follow "Changer le mot de passe"
    When I fill in the following:
      | Mot de passe actuel             | pandore |
      | Nouveau mot de passe            | pandore |
      | Répéter le nouveau mot de passe | pandore |
    And I press "Modifier le mot de passe"
    Then I should see "Le mot de passe est invalide."

  Scenario: I cannot change my password if I don't confirm the new one
    Given I am logged as damien
    Given I am on "profile/"
    And I follow "Changer le mot de passe"
    When I fill in the following:
      | Mot de passe actuel             | damien  |
      | Nouveau mot de passe            | pandore |
      | Répéter le nouveau mot de passe | pendora |
    And I press "Modifier le mot de passe"
    Then I should see "Les deux mots de passe ne sont pas identiques"
