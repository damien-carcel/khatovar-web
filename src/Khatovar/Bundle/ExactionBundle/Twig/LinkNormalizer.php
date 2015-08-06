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

namespace Khatovar\Bundle\ExactionBundle\Twig;

/**
 * Link normalizer
 *
 * @author Damien Carcel (https://github.com/damien-carcel)
 */
class LinkNormalizer extends \Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction(
                'khatovar_normalize_link',
                array($this, 'normalize'),
                array('is_sage' => array('html'))
            )
        );
    }

    /**
     * Return an html link.
     *
     * @param string $link
     *
     * @return string
     */
    public function normalize($link)
    {
        $explodedLink = explode('|', $link);

        if (count($explodedLink) === 3) {
            $formattedLink = $this->formatLink($explodedLink[1], $explodedLink[0], $explodedLink[2]);
        } elseif (count($explodedLink) === 2) {
            $formattedLink = $this->formatLink($explodedLink[1], $explodedLink[0]);
        } else {
            $formattedLink = $this->formatLink($explodedLink[0], $explodedLink[0]);
        }

        return $formattedLink;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'khatovar_link_normalizer';
    }

    /**
     * Format a web link.
     *
     * @param string $link
     * @param string $text
     * @param string $title
     *
     * @return string
     */
    protected function formatLink($link, $text, $title = '')
    {
        $format = '<a href="http://%s" title="%s">%s</a>';

        return sprintf($format, $link, $title, $text);
    }
}