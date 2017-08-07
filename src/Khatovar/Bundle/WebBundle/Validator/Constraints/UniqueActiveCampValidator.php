<?php

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

namespace Khatovar\Bundle\WebBundle\Validator\Constraints;

use Doctrine\ORM\EntityManagerInterface;
use Khatovar\Bundle\WebBundle\Entity\Appearance;
use Khatovar\Bundle\WebBundle\Entity\AppearanceRepository;
use Khatovar\Bundle\WebBundle\Helper\AppearanceHelper;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Checks that only one camp description is active at a time.
 *
 * @author Damien Carcel <damien.carcel@gmail.com>
 */
class UniqueActiveCampValidator extends ConstraintValidator
{
    /** @var AppearanceRepository */
    protected $appearanceRepository;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->appearanceRepository = $entityManager->getRepository('KhatovarWebBundle:Appearance');
    }

    /**
     * {@inheritdoc}
     */
    public function validate($appearance, Constraint $constraint)
    {
        if (AppearanceHelper::CAMP_TYPE_CODE === $appearance->getPageType() && true === $appearance->isActive()) {
            $activeCamp = $this->appearanceRepository->findActiveCamp();

            if (null !== $activeCamp && $activeCamp->getId() !== $appearance->getId()) {
                $this->context->buildViolation($constraint->message)
                    ->setParameter('%name%', $activeCamp->getName())
                    ->addViolation();
            }
        }
    }
}