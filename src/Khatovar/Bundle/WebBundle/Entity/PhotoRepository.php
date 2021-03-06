<?php

declare(strict_types=1);

/**
 * This file is part of KhatovarWeb.
 *
 * Copyright (c) 2015 Damien Carcel (https://github.com/damien-carcel)
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

namespace Khatovar\Bundle\WebBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @author Damien Carcel <damien.carcel@gmail.com>
 */
class PhotoRepository extends EntityRepository
{
    /**
     * Finds a photo by its ID or throws a 404.
     *
     * @param int $id
     *
     * @throws NotFoundHttpException
     *
     * @return Photo
     */
    public function findByIdOr404($id)
    {
        $photo = $this->find($id);

        if (!$photo) {
            throw new NotFoundHttpException('Impossible de trouver la photo.');
        }

        return $photo;
    }

    /**
     * Return photos ordered by entity and entry to ease the
     * there display when an editor list all photos.
     *
     * @return Photo[]
     */
    public function getOrphans()
    {
        $query = $this->createQueryBuilder('p')
            ->where('p.homepage IS NULL')
            ->andWhere('p.member IS NULL')
            ->andWhere('p.exaction IS NULL')
            ->andWhere('p.contact IS NULL')
            ->andWhere('p.appearance IS NULL')
            ->getQuery();

        return $query->getResult();
    }

    /**
     * Return a list of all the photos of a member, except its portrait.
     *
     * @return Photo[]
     */
    public function getAllButPortrait(Member $member)
    {
        $query = $this->createQueryBuilder('p')
            ->where('p.member = :member')
            ->andWhere('p.id !== :portrait')
            ->setParameters(
                [
                    'member' => $member,
                    'portrait' => $member->getPortrait(),
                ]
            )
            ->getQuery();

        return $query->getResult();
    }
}
