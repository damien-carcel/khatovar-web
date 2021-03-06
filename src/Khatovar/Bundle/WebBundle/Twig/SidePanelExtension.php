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

namespace Khatovar\Bundle\WebBundle\Twig;

use Khatovar\Bundle\WebBundle\Entity\Photo;
use Khatovar\Bundle\WebBundle\Manager\PhotoManager;

/**
 * Twig extension for the side panel.
 *
 * @author Damien Carcel <damien.carcel@gmail.com>
 */
class SidePanelExtension extends \Twig_Extension
{
    /** @var PhotoManager */
    protected $photoManager;

    public function __construct(PhotoManager $photoManager)
    {
        $this->photoManager = $photoManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            'get_controller_photos' => new \Twig_SimpleFunction(
                'side_panel_extension',
                [$this, 'getControllerPhotos']
            ),
        ];
    }

    /**
     * Display a list of all photos uploaded for the current page in a
     * small sidebar. Editors and admin can access all photos, but
     * regular users can only access photos of their own member page.
     *
     * @param string     $controller the controller currently rendered
     * @param string     $action     the controller method used for rendering
     * @param string|int $slugOrId   the slug or the ID of the object currently rendered
     *
     * @return Photo[]
     */
    public function getControllerPhotos($controller, $action, $slugOrId)
    {
        return $this->photoManager->getControllerPhotos($controller, $action, $slugOrId);
    }
}
