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
use Khatovar\Bundle\WebBundle\Helper\AppearanceHelper;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @author Damien Carcel <damien.carcel@gmail.com>
 */
class AppearanceRepository extends EntityRepository
{
    /**
     * Finds one appearance by its id or throws a 404.
     *
     * @param int $id
     *
     * @throws NotFoundHttpException
     *
     * @return Appearance
     */
    public function findByIdOr404($id)
    {
        $appearance = $this->find($id);

        if (null === $appearance) {
            throw new NotFoundHttpException('Impossible de trouver la prestation.');
        }

        return $appearance;
    }

    /**
     * @return Appearance[]
     */
    public function findAllWorkshopsSortedBySlug()
    {
        $query = $this->createQueryBuilder('a')
            ->orderBy('a.slug', 'ASC')
            ->where('a.pageType = :pageType')
            ->setParameter('pageType', AppearanceHelper::WORKSHOP_TYPE_CODE)
            ->getQuery();

        return $query->getResult();
    }

    /**
     * @return Appearance[]
     */
    public function findActiveWorkshopsSortedBySlug()
    {
        $query = $this->createQueryBuilder('a')
            ->orderBy('a.slug', 'ASC')
            ->where('a.active = true')
            ->andWhere('a.pageType = :pageType')
            ->setParameter('pageType', AppearanceHelper::WORKSHOP_TYPE_CODE)
            ->getQuery();

        return $query->getResult();
    }

    /**
     * @return Appearance[]
     */
    public function findAllProgrammesSortedBySlug()
    {
        $query = $this->createQueryBuilder('a')
            ->orderBy('a.slug', 'ASC')
            ->where('a.pageType = :pageType')
            ->setParameter('pageType', AppearanceHelper::PROGRAMME_TYPE_CODE)
            ->getQuery();

        return $query->getResult();
    }

    /**
     * @return Appearance[]
     */
    public function findActiveProgrammesSortedBySlug()
    {
        $query = $this->createQueryBuilder('a')
            ->orderBy('a.slug', 'ASC')
            ->where('a.active = true')
            ->andWhere('a.pageType = :pageType')
            ->setParameter('pageType', AppearanceHelper::PROGRAMME_TYPE_CODE)
            ->getQuery();

        return $query->getResult();
    }

    /**
     * @throws NotFoundHttpException
     *
     * @return Appearance
     */
    public function findActiveCamp()
    {
        $query = $this->createQueryBuilder('a')
            ->where('a.active = true')
            ->andWhere('a.pageType = :pageType')
            ->setParameter('pageType', AppearanceHelper::CAMP_TYPE_CODE)
            ->getQuery();

        $camp = $query->getOneOrNullResult();

        if (null === $camp) {
            throw new NotFoundHttpException('Impossible de trouver une page de camp active.');
        }

        return $camp;
    }

    /**
     * @throws NotFoundHttpException
     *
     * @return Appearance
     */
    public function findActiveIntroduction()
    {
        $query = $this->createQueryBuilder('a')
            ->where('a.active = true')
            ->andWhere('a.pageType = :pageType')
            ->setParameter('pageType', AppearanceHelper::INTRO_TYPE_CODE)
            ->getQuery();

        $introduction = $query->getOneOrNullResult();

        if (null === $introduction) {
            throw new NotFoundHttpException('Impossible de trouver une page d\'introduction active.');
        }

        return $introduction;
    }

    /**
     * @return Appearance[]
     */
    public function findActiveCamps()
    {
        $query = $this->createQueryBuilder('a')
            ->where('a.active = true')
            ->andWhere('a.pageType = :pageType')
            ->setParameter('pageType', AppearanceHelper::CAMP_TYPE_CODE)
            ->getQuery();

        return $query->getResult();
    }
}
