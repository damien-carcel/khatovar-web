<?php

declare(strict_types=1);

/*
 * This file is part of Khatovar.
 *
 * Copyright (c) 2019 Damien Carcel <damien.carcel@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Khatovar\Tests\EndToEnd\Context\User\Anonymous;

use Khatovar\Component\User\Domain\Repository\UserRepositoryInterface;
use Khatovar\Tests\EndToEnd\Assertion\AssertAuthenticatedAsUser;
use Khatovar\Tests\EndToEnd\Assertion\AssertUserIsAnonymous;
use Khatovar\Tests\EndToEnd\Context\User\UserRawContext;

/**
 * @author Damien Carcel <damien.carcel@gmail.com>
 */
final class RegisterNewUserContext extends UserRawContext
{
    private $assertAuthenticatedAsUser;
    private $assertUserIsAnonymous;
    private $userRepository;

    public function __construct(
        AssertAuthenticatedAsUser $assertAuthenticatedAsUser,
        AssertUserIsAnonymous $assertUserIsAnonymous,
        UserRepositoryInterface $userRepository
    ) {
        $this->assertAuthenticatedAsUser = $assertAuthenticatedAsUser;
        $this->assertUserIsAnonymous = $assertUserIsAnonymous;
        $this->userRepository = $userRepository;
    }

    /**
     * @Given I want to register a new account
     */
    public function registerNewAccount(): void
    {
        $this->visitPath('login');
        $this->page()->clickLink('Nouvel utilisateur');
        $this->assertPath('register/');
    }

    /**
     * @When I create a new account as :username
     */
    public function createNewAccountAs(string $username): void
    {
        $this->fillFormFieldsAndValidateWithAction([
            'Nom d\'utilisateur' => $username,
            'Adresse e-mail' => $username.'@khatovar.fr',
            'Mot de passe' => $username,
            'Répéter le mot de passe' => $username,
        ], 'Créer un compte');
    }

    /**
     * @When I try to create a new account as :username with an already existing username
     */
    public function createNewAccountWithExistingUsername(string $username): void
    {
        $this->fillFormFieldsAndValidateWithAction([
            'Nom d\'utilisateur' => 'damien',
            'Adresse e-mail' => $username.'@khatovar.fr',
            'Mot de passe' => $username,
            'Répéter le mot de passe' => $username,
        ], 'Créer un compte');
    }

    /**
     * @When I try to create a new account as :username with an already existing email
     */
    public function createNewAccountWithExistingEmail(string $username): void
    {
        $this->fillFormFieldsAndValidateWithAction([
            'Nom d\'utilisateur' => $username,
            'Adresse e-mail' => 'damien@khatovar.fr',
            'Mot de passe' => $username,
            'Répéter le mot de passe' => $username,
        ], 'Créer un compte');
    }

    /**
     * @When I try to create a new account as :username without confirming my password
     */
    public function createNewAccountWithoutConfirmingPassword(string $username): void
    {
        $this->fillFormFieldsAndValidateWithAction([
            'Nom d\'utilisateur' => $username,
            'Adresse e-mail' => $username.'@khatovar.fr',
            'Mot de passe' => $username,
            'Répéter le mot de passe' => 'not_the_same_password',
        ], 'Créer un compte');
    }

    /**
     * @Then a new user ":username" is created
     */
    public function newUserIsCreated(string $username): void
    {
        $this->assertPageContainsText('L\'utilisateur a été créé avec succès');
        $this->assertPageContainsText(
            'Un e-mail a été envoyé à l\'adresse '.$username.'@khatovar.fr. Il contient un lien d\'activation sur lequel il vous faudra cliquer afin d\'activer votre compte.'
        );

        ($this->assertUserIsAnonymous)();
    }

    /**
     * @Then :username account can be activated
     */
    public function userAccountCanBeActivated(string $username): void
    {
        $user = $this->userRepository->get($username);
        $activationToken = $user->getConfirmationToken();

        $this->visitPath('register/confirm/'.$activationToken);

        $this->assertPageContainsText('Félicitations '.$username.', votre compte est maintenant activé.');
        ($this->assertAuthenticatedAsUser)($username);
    }

    /**
     * @Then I should be notified that the username is already used
     */
    public function usernameIsAlreadyUsed(): void
    {
        $this->assertPageContainsText('Le nom d\'utilisateur est déjà utilisé');
        ($this->assertUserIsAnonymous)();
    }

    /**
     * @Then I should be notified that the email is already used
     */
    public function emailIsAlreadyUsed(): void
    {
        $this->assertPageContainsText('L\'adresse e-mail est déjà utilisée');
        ($this->assertUserIsAnonymous)();
    }

    /**
     * @Then I should be notified that the confirmation password is different from the original one
     */
    public function confirmationPasswordIsDifferent(): void
    {
        $this->assertPageContainsText('Les deux mots de passe ne sont pas identiques');
        ($this->assertUserIsAnonymous)();
    }
}
