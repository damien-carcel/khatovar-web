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

namespace Khatovar\Bundle\ExactionBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Khatovar\Bundle\PhotoBundle\Entity\Photo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Exaction
 *
 * @author  Damien Carcel (https://github.com/damien-carcel)
 * @package Khatovar\Bundle\ExactionBundle\Entity
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Khatovar\Bundle\ExactionBundle\Entity\ExactionRepository")
 */
class Exaction
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * The name of the festival.
     *
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Length(max="255")
     */
    protected $name;

    /**
     * Where the festival take place.
     *
     * @var string
     *
     * @ORM\Column(name="place", type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Length(max="255")
     */
    protected $place;

    /**
     * When the festival starts.
     *
     * @var \DateTime
     *
     * @ORM\Column(name="starting_date", type="datetime")
     */
    protected $startingDate;

    /**
     * When the festival ends.
     *
     * @var \DateTime
     *
     * @ORM\Column(name="ending_date", type="datetime")
     */
    protected $endingDate;

    /**
     * Photos of the festival.
     *
     * @var ArrayCollection $photos
     *
     * @ORM\OneToMany(targetEntity="Khatovar\Bundle\PhotoBundle\Entity\Photo", mappedBy="exaction")
     */
    protected $photos;

    /**
     * A map in an iframe to locate the festival.
     *
     * @var string
     *
     * @ORM\Column(name="map", type="text")
     */
    protected $map;

    /**
     * The announcement of the festival.
     *
     * @var string
     *
     * @ORM\Column(name="introduction", type="text")
     */
    protected $introduction;

    /**
     * Useful links (festival website, town website…).
     *
     * @var array
     *
     * @ORM\Column(name="links", type="array")
     */
    protected $links;

    /**
     * An emblematic photo of the festival.
     *
     * @ORM\OneToOne(targetEntity="Khatovar\Bundle\PhotoBundle\Entity\Photo")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    protected $image;

    /**
     * An abstract of what happened on the festival.
     *
     * @var string
     *
     * @ORM\Column(name="abstract", type="text")
     */
    protected $abstract;

    /**
     * Description of the emblematic photo.
     *
     * @var string
     *
     * @ORM\Column(name="image_story", type="text")
     */
    protected $imageStory;

    /**
     * Is there an abstract or only photos?
     *
     * @var boolean
     *
     * @ORM\Column(name="only_photos", type="boolean")
     */
    protected $onlyPhotos;

    /**
     * Allow to save only the ID the entity in database as a string
     * when using entity type in forms.
     *
     * @return string
     */
    public function __toString()
    {
        return strval($this->id);
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->photos = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Exaction
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set place
     *
     * @param string $place
     *
     * @return Exaction
     */
    public function setPlace($place)
    {
        $this->place = $place;

        return $this;
    }

    /**
     * Get place
     *
     * @return string
     */
    public function getPlace()
    {
        return $this->place;
    }

    /**
     * Set map
     *
     * @param string $map
     *
     * @return Exaction
     */
    public function setMap($map)
    {
        $this->map = $map;

        return $this;
    }

    /**
     * Get map
     *
     * @return string
     */
    public function getMap()
    {
        return $this->map;
    }

    /**
     * Set introduction
     *
     * @param string $introduction
     *
     * @return Exaction
     */
    public function setIntroduction($introduction)
    {
        $this->introduction = $introduction;

        return $this;
    }

    /**
     * Get introduction
     *
     * @return string
     */
    public function getIntroduction()
    {
        return $this->introduction;
    }

    /**
     * Set links
     *
     * @param array $links
     *
     * @return Exaction
     */
    public function setLinks($links)
    {
        $this->links = $links;

        return $this;
    }

    /**
     * Get links
     *
     * @return array
     */
    public function getLinks()
    {
        return $this->links;
    }

    /**
     * Set abstract
     *
     * @param string $abstract
     *
     * @return Exaction
     */
    public function setAbstract($abstract)
    {
        $this->abstract = $abstract;

        return $this;
    }

    /**
     * Get abstract
     *
     * @return string
     */
    public function getAbstract()
    {
        return $this->abstract;
    }

    /**
     * Set imageStory
     *
     * @param string $imageStory
     *
     * @return Exaction
     */
    public function setImageStory($imageStory)
    {
        $this->imageStory = $imageStory;

        return $this;
    }

    /**
     * Get imageStory
     *
     * @return string
     */
    public function getImageStory()
    {
        return $this->imageStory;
    }

    /**
     * Set onlyPhotos
     *
     * @param boolean $onlyPhotos
     *
     * @return Exaction
     */
    public function setOnlyPhotos($onlyPhotos)
    {
        $this->onlyPhotos = $onlyPhotos;

        return $this;
    }

    /**
     * Get onlyPhotos
     *
     * @return boolean
     */
    public function isOnlyPhotos()
    {
        return $this->onlyPhotos;
    }

    /**
     * Add photo
     *
     * @param Photo $photo
     *
     * @return Exaction
     */
    public function addPhoto(Photo $photo)
    {
        $this->photos[] = $photo;

        return $this;
    }

    /**
     * Remove photo
     *
     * @param Photo $photo
     */
    public function removePhoto(Photo $photo)
    {
        $this->photos->removeElement($photo);
    }

    /**
     * Get photos
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPhotos()
    {
        return $this->photos;
    }

    /**
     * Set image
     *
     * @param Photo $image
     *
     * @return Exaction
     */
    public function setImage(Photo $image = null)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image
     *
     * @return Photo
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set startingDate
     *
     * @param \DateTime $startingDate
     *
     * @return Exaction
     */
    public function setStartingDate($startingDate)
    {
        $this->startingDate = $startingDate;

        return $this;
    }

    /**
     * Get startingDate
     *
     * @return \DateTime
     */
    public function getStartingDate()
    {
        return $this->startingDate;
    }

    /**
     * Set endingDate
     *
     * @param \DateTime $endingDate
     *
     * @return Exaction
     */
    public function setEndingDate($endingDate)
    {
        $this->endingDate = $endingDate;

        return $this;
    }

    /**
     * Get endingDate
     *
     * @return \DateTime
     */
    public function getEndingDate()
    {
        return $this->endingDate;
    }
}
