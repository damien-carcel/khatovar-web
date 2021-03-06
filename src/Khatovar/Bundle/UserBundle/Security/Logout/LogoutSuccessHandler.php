<?php

declare(strict_types=1);

/*
 * This file is part of KhatovarWeb.
 *
 * Copyright (c) 2016 Damien Carcel <damien.carcel@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Khatovar\Bundle\UserBundle\Security\Logout;

use Khatovar\Bundle\UserBundle\Factory\RedirectResponseFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;

/**
 * Redirect user after logout to the last page he was.
 *
 * @author Damien Carcel <damien.carcel@gmail.com>
 */
class LogoutSuccessHandler implements LogoutSuccessHandlerInterface
{
    /** @var RedirectResponseFactory */
    private $factory;

    public function __construct(RedirectResponseFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function onLogoutSuccess(Request $request)
    {
        $url = $request->headers->get('referer');

        if (null !== $url) {
            return $this->factory->create($url);
        }

        return $this->factory->create('/');
    }
}
