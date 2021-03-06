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

namespace Khatovar\Bundle\WebBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Checks that the exaction "iframe" field indeed contains an iframe.
 *
 * @author Damien Carcel <damien.carcel@gmail.com>
 */
class ContainsIframeValidator extends ConstraintValidator
{
    /** @staticvar string */
    public const IFRAME_OPENING_TAG = '<iframe src=';

    /** @staticvar string */
    public const IFRAME_CLOSING_TAG = '</iframe>';

    /**
     * {@inheritdoc}
     */
    public function validate($iframe, Constraint $constraint): void
    {
        if (null !== $iframe) {
            if (strlen($iframe) < strlen(static::IFRAME_OPENING_TAG.static::IFRAME_CLOSING_TAG)) {
                $this->context->buildViolation($constraint->messageLength)->addViolation();
            } else {
                $iframeOpening = substr(
                    $iframe,
                    0,
                    strlen(static::IFRAME_OPENING_TAG)
                );
                $iframeClosing = substr(
                    $iframe,
                    -strlen(static::IFRAME_CLOSING_TAG)
                );

                if ($iframeOpening !== static::IFRAME_OPENING_TAG || $iframeClosing !== static::IFRAME_CLOSING_TAG) {
                    $this->context->buildViolation($constraint->messageContent)->addViolation();
                }
            }
        }
    }
}
