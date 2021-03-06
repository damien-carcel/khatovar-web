<?php

declare(strict_types=1);

namespace spec\Khatovar\Component\User\Domain\Exception;

use Khatovar\Component\User\Domain\Exception\UserDoesNotExist;
use PhpSpec\ObjectBehavior;

class UserDoesNotExistSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('username');
    }

    function it_is_a_user_does_not_exist_exception()
    {
        $this->shouldHaveType(UserDoesNotExist::class);
        $this->shouldHaveType(\InvalidArgumentException::class);
    }

    function it_creates_an_exception_message_during_instanciation()
    {
        $this->getMessage()->shouldReturn('The user with the name "username" does not exist');
    }
}
