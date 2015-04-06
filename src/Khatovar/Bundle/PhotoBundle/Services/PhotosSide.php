<?php
/**
 *
 * This file is part of KhatovarWeb.
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
 *
 * @copyright   Copyright (C) Damien Carcel (https://github.com/damien-carcel)
 * @link        https://github.com/damien-carcel/KhatovarWeb
 * @license     http://www.gnu.org/licenses/gpl.html
 */

namespace Khatovar\Bundle\PhotoBundle\Services;

use Carcel\UserBundle\Entity\User;
use Doctrine\ORM\EntityManager;

/**
 * Class PhotosSide
 *
 * @author Damien Carcel (https://github.com/damien-carcel)
 * @package Khatovar\Bundle\PhotoBundle\Services
 */
class PhotosSide
{
    /** @var EntityManager */
    protected $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Return the list of all photos of the current page that the user
     * can access.
     *
     * @param User   $currentUser
     * @param string $controller
     * @param string $action
     * @param string $slugOrId
     *
     * @return \Khatovar\Bundle\PhotoBundle\Entity\Photo[]
     */
    public function get(User $currentUser, $controller, $action, $slugOrId)
    {
        $photos = array();

        $currentlyRendered = $this->getCurrentlyRendered($controller, $action, $slugOrId);

        $owner = null;
        if ($controller == 'member' and !is_null($slugOrId)) {
            $owner = $currentlyRendered->getOwner();
        }

        if (!is_null($currentlyRendered)) {
            if ($currentUser->hasRole('ROLE_SUPER_ADMIN', 'ROLE_ADMIN', 'ROLE_EDITOR')
                or ($owner == $currentUser)) {
                $photos = $currentlyRendered->getPhotos();
            }
        }

        return $photos;
    }

    /**
     * Return the entity which content is currently rendered by the
     * application.
     *
     * @param $controller
     * @param $action
     * @param $slugOrId
     *
     * @return null|object
     */
    protected function getCurrentlyRendered($controller, $action, $slugOrId)
    {
        $currentlyRendered = null;
        $repo = $this->getRepository($controller);

        if (null === $repo) {
            return null;
        }

        if ($controller === 'homepage'
            and is_null($slugOrId)
            and $action !== 'create'
            and $action !== 'list') {
            $currentlyRendered = $repo->findOneBy(array('active' => true));
        } elseif (!is_null($slugOrId)) {
            if (is_string($slugOrId)) {
                $currentlyRendered = $repo->findOneBy(array('slug' => $slugOrId));
            } elseif (is_int($slugOrId)) {
                $currentlyRendered = $repo->find($slugOrId);
            }
        }

        return $currentlyRendered;
    }

    /**
     * Get entity repository for a corresponding controller.
     *
     * @param string $controller
     *
     * @return \Doctrine\ORM\EntityRepository|null
     */
    protected function getRepository($controller)
    {
        if ($controller !== 'default') {
            $entity = 'Khatovar' . ucfirst($controller) . 'Bundle:' . ucfirst($controller);

            return $this->entityManager->getRepository($entity);
        }

        return null;
    }
}