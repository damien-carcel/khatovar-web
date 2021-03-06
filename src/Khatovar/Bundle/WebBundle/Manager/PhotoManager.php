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

namespace Khatovar\Bundle\WebBundle\Manager;

use Khatovar\Bundle\UserBundle\Entity\User;
use Khatovar\Bundle\WebBundle\Entity\Photo;
use Khatovar\Bundle\WebBundle\Helper\EntityHelper;
use Khatovar\Bundle\WebBundle\Helper\PhotoHelper;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @author Damien Carcel <damien.carcel@gmail.com>
 */
class PhotoManager
{
    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /** @var RegistryInterface */
    protected $doctrine;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    public function __construct(
        RegistryInterface $doctrine,
        AuthorizationCheckerInterface $authorizationChecker,
        TokenStorageInterface $tokenStorage
    ) {
        $this->doctrine = $doctrine;
        $this->authorizationChecker = $authorizationChecker;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Look for special photo insertion tags and transform it in html syntax.
     *
     * @param string $text the text to transform
     *
     * @return string
     */
    public function imageTranslate($text)
    {
        $text = $this->insertTagsInText($text);
        $paths = $this->getPhotoPaths($text);

        $repository = $this->doctrine->getRepository('KhatovarWebBundle:Photo');

        $photos = [];

        foreach ($paths as $path) {
            $photo = $repository->findOneBy(['path' => $path]);
            if (null !== $photo) {
                $photos[] = '<a href="/uploaded/photos/'
                    .$photo->getPath()
                    .'" data-lightbox="Photos Khatovar" title="Copyright &copy; '
                    .date('Y')
                    .' association La Compagnie franche du Khatovar"><img class="'
                    .$photo->getClass()
                    .' photo_rest" onmouseover="this.className=\''
                    .$photo->getClass()
                    .' photo_over\'" onmouseout="this.className=\''
                    .$photo->getClass()
                    .' photo_rest\'" src="/uploaded/photos/'
                    .$photo->getPath()
                    .'" alt="'.$photo->getAlt()
                    .'" /></a>';
            } else {
                $photos[] = 'Cette photo n\'existe pas';
            }
        }

        return str_replace($paths, $photos, $text);
    }

    /**
     * Resize an jpeg image according to a given height, but only if
     * the original image is higher.
     *
     * @param string $image     The path to the original image
     * @param int    $newHeight
     */
    public function imageResize($image, $newHeight): void
    {
        // We first find the dimensions of the photo and its ratio
        $original = imagecreatefromjpeg($image);
        list($width, $height) = getimagesize($image);
        $ratio = $width / $height;

        if ($height > $newHeight) {
            $newWidth = round($newHeight * $ratio);

            $resized = imagecreatetruecolor($newWidth, $newHeight);
            imagecopyresampled($resized, $original, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

            copy($image, $image.'.old');
            unlink($image);

            if (imagejpeg($resized, $image)) {
                unlink($image.'.old');
            } else {
                copy($image.'.old', $image);
            }

            imagedestroy($resized);
        }

        imagedestroy($original);
    }

    /**
     * Get a sorted list of all photos currently uploaded.
     *
     * @return array
     */
    public function getPhotosSortedByEntities()
    {
        $sortedPhotos = [
            'Photos orphelines' => [
                'Liste des photos n\'appartenant à aucune page' => $this->doctrine
                    ->getRepository('KhatovarWebBundle:Photo')
                    ->getOrphans(),
            ],
        ];

        foreach (PhotoHelper::getPhotoEntities() as $label => $code) {
            $sortedPhotos[$label] = $this->getEntityPhotos($code);
        }

        return $sortedPhotos;
    }

    /**
     * Get a member's photos.
     *
     * @return Photo[]
     */
    public function getMemberPhotos(User $currentUser)
    {
        $sortedPhotos = [];

        $member = $this->doctrine
            ->getRepository('KhatovarWebBundle:Member')
            ->findOneBy(['owner' => $currentUser]);

        $memberPhotos = $member->getPhotos();

        foreach ($memberPhotos as $photo) {
            $sortedPhotos['Membre'][$member->getName()][] = $photo;
        }

        return $sortedPhotos;
    }

    /**
     * Return the list of all photos of the current page that the user
     * can access.
     *
     * @param string $controller
     * @param string $action
     * @param string $slugOrId
     *
     * @return \Khatovar\Bundle\WebBundle\Entity\Photo[]
     */
    public function getControllerPhotos($controller, $action, $slugOrId)
    {
        $photos = [];

        if ('web' === $controller) {
            return $photos;
        }

        $currentlyRendered = $this->getCurrentlyRendered($controller, $action, $slugOrId);

        $owner = null;
        if (EntityHelper::MEMBER_CODE === $controller && null !== $slugOrId) {
            $owner = $currentlyRendered->getOwner();
        }

        if (null !== $currentlyRendered) {
            if ($this->authorizationChecker->isGranted('ROLE_EDITOR') ||
                ($owner === $this->tokenStorage->getToken()->getUser())
            ) {
                $photos = $currentlyRendered->getPhotos();
            }
        }

        return $photos;
    }

    /**
     * Return the entity which content is currently rendered by the
     * application.
     *
     * @param string $controller
     * @param string $action
     * @param $slugOrId
     *
     * @return object|null
     */
    protected function getCurrentlyRendered($controller, $action, $slugOrId)
    {
        $repo = $this->getRepository($controller);

        if (null === $repo || 'photo' === $controller) {
            return null;
        }

        if (in_array($controller, EntityHelper::getActivables()) && null === $slugOrId && 'index' === $action) {
            return $repo->findOneBy(['active' => true]);
        }

        if (is_string($slugOrId)) {
            return $repo->findOneBy(['slug' => $slugOrId]);
        }

        if (is_int($slugOrId)) {
            return $repo->find($slugOrId);
        }

        return null;
    }

    /**
     * Get entity repository for a corresponding controller.
     *
     * @param string $controller
     *
     * @return \Doctrine\Common\Persistence\ObjectRepository|null
     */
    protected function getRepository($controller)
    {
        $entity = 'Khatovar'.ucfirst($controller).'Bundle:'.ucfirst($controller);

        return $this->doctrine->getRepository($entity);
    }

    /**
     * @param string $text
     *
     * @return array
     */
    protected function getPhotoPaths($text)
    {
        preg_match_all('#(\w+\-\d+\.jpeg)#', $text, $matches);

        return $matches[0];
    }

    /**
     * @param string $text
     *
     * @return string
     */
    protected function insertTagsInText($text)
    {
        $text = str_replace('<p>[', '<div>[', $text);
        $text = str_replace(']</p>', ']</div>', $text);

        $text = str_replace('<div>[', '<div class="container">', $text);
        $text = str_replace(']</div>', '</div>', $text);
        $text = str_replace('][', '', $text);

        return $text;
    }

    /**
     * Get all photos owned by a given entity type.
     *
     * @param string $entityCode
     *
     * @return Photo[]
     */
    protected function getEntityPhotos($entityCode)
    {
        $sortedPhotos = [];

        $entities = $this->doctrine
            ->getRepository('Khatovar'.ucfirst($entityCode).'Bundle:'.ucfirst($entityCode))
            ->findAll();

        foreach ($entities as $entity) {
            $photos = $entity->getPhotos();
            foreach ($photos as $photo) {
                if (EntityHelper::EXACTION_CODE === $entityCode) {
                    $name = $entity->getCompleteName();
                } else {
                    $name = $entity->getName();
                }

                $sortedPhotos[$name][] = $photo;
            }
        }

        return $sortedPhotos;
    }
}
