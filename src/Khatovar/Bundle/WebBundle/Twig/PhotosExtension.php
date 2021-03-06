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

use Doctrine\Common\Collections\Collection;
use Khatovar\Bundle\WebBundle\Entity\Photo;

/**
 * Twig extension for photo disposition in text.
 *
 * @author Damien Carcel <damien.carcel@gmail.com>
 */
class PhotosExtension extends \Twig_Extension
{
    /**
     * The minimum length between floating photos in a page.
     */
    public const PARAGRAPH_LENGTH = 500;

    /**
     * Return the filters defined in this class.
     *
     * @return array
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter(
                'popup_picture',
                [$this, 'popupPicture'],
                ['is_safe' => ['html']]
            ),
            new \Twig_SimpleFilter(
                'link_picture',
                [$this, 'linkPicture'],
                ['is_safe' => ['html']]
            ),
            new \Twig_SimpleFilter(
                'link_album',
                [$this, 'linkAlbum'],
                ['is_safe' => ['html']]
            ),
            new \Twig_SimpleFilter(
                'thumbnail',
                [$this, 'thumbnail'],
                ['is_safe' => ['html']]
            ),
            new \Twig_SimpleFilter(
                'thumbnail_link',
                [$this, 'thumbnailLink'],
                ['is_safe' => ['html']]
            ),
            new \Twig_SimpleFilter(
                'add_paragraph_and_photos',
                [$this, 'addParagraphAndPhotos'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    /**
     * Return a picture as a link to display it using lightbox framework.
     *
     * @param string $path  the path to the picture
     * @param string $class the stylesheet class to use
     * @param string $alt   the alternative name of the picture
     * @param string $data  the data-lightbox attribute
     *
     * @return string
     */
    public function popupPicture($path, $class = '', $alt = '', $data = 'Photos Khatovar')
    {
        $link = '<a href="'.$path
            .'" data-lightbox="'.$data.'" title="Copyright &copy; '
            .date('Y').' association La Compagnie franche du Khatovar"><img class="'
            .$class.' photo_rest" onmouseover="this.className=\''
            .$class.' photo_over\'" onmouseout="this.className=\''.$class
            .' photo_rest\'" src="'.$path.'" alt="'.$alt.'" /></a>';

        return $link;
    }

    /**
     * Return a link to display a picture with lightbox framework.
     *
     * @param string $path the path to the picture
     * @param string $text the link text
     * @param string $data the data-lightbox attribute
     *
     * @return string
     */
    public function linkPicture($path, $text = '', $data = '')
    {
        $link = sprintf(
            <<<HTML
<a href="%s" data-lightbox="%s" title="Copyright &copy; %s association La Compagnie franche du Khatovar">
    %s
</a>
HTML
            ,
            $path,
            $data ? $data : $path,
            date('Y'),
            $text
        );

        return $link;
    }

    /**
     * Return a lightbox photo album.
     *
     * @param string           $icon       the photo album icon
     * @param string           $data       the data-lightbox attribute
     * @param Collection|array $photos     the rest of the photos to display as an album
     * @param Photo            $firstPhoto The first photo of the album..
     *
     * @return string
     */
    public function linkAlbum($icon, $data, $photos, Photo $firstPhoto = null)
    {
        if ($photos instanceof Collection) {
            $photos = $photos->toArray();
        }

        $link = '';
        $first = true;
        $text = sprintf(
            '<img src="%s" class="photolink" alt="Album photo" /><br />Album photo',
            $icon
        );

        if (null === $firstPhoto && empty($photos)) {
            $link = $this->linkPicture('/bundles/khatovarweb/images/logonoir.jpg', $text, $data);
        }

        if (null !== $firstPhoto) {
            $link = $this->linkPicture($firstPhoto->getWebPath(), $text, $data);
            $first = false;
        }

        foreach ($photos as $photo) {
            if ($first) {
                $link = $this->linkPicture($photo->getWebPath(), $text, $data);
                $first = false;
            } else {
                if (null === $firstPhoto || $photo->getWebPath() !== $firstPhoto->getWebPath()) {
                    $link .= $this->linkPicture($photo->getWebPath(), '', $data);
                }
            }
        }

        return $link;
    }

    /**
     * .
     *
     * @param string $path  The path to the picture for the thumbnail
     * @param string $class The class to apply to the thumbnail: "portrait" or "landscape"
     * @param string $alt   The alternative text for the thumbnail
     *
     * @return string
     */
    public function thumbnail($path, $class, $alt = '')
    {
        $link = '<img src="'.$path.'" class="thumbnail '.$class.' photo_rest" alt="'.$alt.'" />';

        return $link;
    }

    /**
     * Make an hyperlink from a thumbnail.
     *
     * @param string $destination The hyperlink
     * @param string $path        The path of the picture for the thumbnail
     * @param string $class       The class to apply to the thumbnail: "portrait" or "landscape"
     * @param string $alt         The alternative text for the thumbnail
     * @param string $text        An optional text to display under the thumbnail
     *
     * @return string
     */
    public function thumbnailLink($path, $destination, $class, $alt = '', $text = '')
    {
        $link = '<div class="thumbnail_link '.$class.' photo_rest"
            onmouseover="this.className=\'thumbnail_link '.$class.' photo_over\';"
            onmouseout="this.className=\'thumbnail_link '.$class.' photo_rest\';"
            onclick="location.href=\''.$destination.'\';">
            <img src="'.$path.'" class="trombinoscope" alt="'.$alt.'" /><br />'
            .$text.'</div>';

        return $link;
    }

    /**
     * Replace line breaks, and remove useless ones, by paragraphs and
     * insert floatings between paragraphs.
     *
     * @param string $text   the text to transform
     * @param mixed  $photos a list of photos to insert in the text
     *
     * @return string
     */
    public function addParagraphAndPhotos($text, $photos = null)
    {
        if ($photos instanceof Collection) {
            $photos = $photos->toArray();
        }

        $text = preg_replace('`[\r\n]+`', "\n", $text);
        $text = '<p>'.$text.'</p>';

        if (strlen($text) < self::PARAGRAPH_LENGTH || null === $photos || empty($photos)) {
            return str_replace("\n", "</p>\n<p>", $text);
        }

        $text = str_replace("\n", "</p>\n[break]<p>", $text);

        if (!empty($photos)) {
            $text = $this->addPhotos($text, $photos);
        }

        return $text;
    }

    /**
     * Return the name of the extension.
     *
     * @return string
     */
    public function getName()
    {
        return 'khatovar_photos_extension';
    }

    /**
     * Insert floatings between paragraphs.
     *
     * @param string  $text
     * @param Photo[] $photos
     *
     * @return string
     */
    protected function addPhotos($text, array $photos)
    {
        $exploded = explode('[break]', $text);

        shuffle($photos);
        $textWithPhotos = $this->addFloat($photos[0], 'right');

        $currentPhoto = 1;
        $photosCount = count($photos);
        $paragraphs = count($exploded);

        for ($p = 0; $p < $paragraphs; ++$p) {
            /**
             * @var Photo
             */
            $photo = $photos[$currentPhoto - 1];
            $photoSize = getimagesize($photo->getAbsolutePath());
            if ($photoSize[0] / $photoSize[1] < 1) {
                $photoLimit = self::PARAGRAPH_LENGTH + 100;
            } else {
                $photoLimit = self::PARAGRAPH_LENGTH;
            }

            if (strlen($exploded[$p]) > $photoLimit) {
                $textWithPhotos .= $exploded[$p];

                $remain = strlen(implode(' ', array_slice($exploded, $p + 1)));
                if ($currentPhoto < $photosCount && $remain > $photoLimit + 600) {
                    $textWithPhotos .= $this->addFloat(
                        $photos[$currentPhoto],
                        $currentPhoto % 2 ? 'left' : 'right'
                    );
                    ++$currentPhoto;
                }
            } elseif ($p + 1 < $paragraphs) {
                $exploded[$p + 1] = $exploded[$p].$exploded[$p + 1];
                $exploded[$p] = '';
            } else {
                $textWithPhotos .= $exploded[$p];
            }
        }

        return $textWithPhotos;
    }

    /**
     * Return the html code to display a photo as a floating element in
     * a lightbox container.
     *
     * @param Photo  $photo the photo to display
     * @param string $side  the side the photo float (left or right)
     *
     * @return string
     */
    protected function addFloat(Photo $photo, $side)
    {
        $text = '<a href="'
            .$photo->getWebPath()
            .'" data-lightbox="Photos Khatovar" title="Copyright &copy; '
            .date('Y').' association La Compagnie franche du Khatovar"><img class="float float'
            .$side.' photo_rest" onmouseover="this.className=\'float float'
            .$side.' photo_over\'" onmouseout="this.className=\'float float'
            .$side.' photo_rest\'" src="'
            .$photo->getWebPath().'" alt="'
            .$photo->getAlt().'" /></a>';

        return $text."\n";
    }
}
