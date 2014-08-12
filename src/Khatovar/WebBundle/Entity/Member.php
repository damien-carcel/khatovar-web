<?php

namespace Khatovar\WebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Member
 *
 * @ORM\Table(name="khatovar_web_members")
 * @ORM\Entity(repositoryClass="Khatovar\WebBundle\Entity\MemberRepository")
 */
class Member
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Length(max="255")
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="rank", type="string", length=255)
     * @Assert\Length(max="255")
     */
    private $rank;

    /**
     * @var string
     *
     * @ORM\Column(name="quote", type="string", length=255)
     * @Assert\Length(max="255")
     */
    private $quote;

    /**
     * @var string
     *
     * @ORM\Column(name="skill", type="string", length=255)
     * @Assert\Length(max="255")
     */
    private $skill;

    /**
     * @var string
     *
     * @ORM\Column(name="age", type="string", length=255)
     * @Assert\Length(max="255")
     */
    private $age;

    /**
     * @var string
     *
     * @ORM\Column(name="size", type="string", length=255)
     * @Assert\Length(max="255")
     */
    private $size;

    /**
     * @var string
     *
     * @ORM\Column(name="weight", type="string", length=255)
     * @Assert\Length(max="255")
     */
    private $weight;

    /**
     * @var string
     *
     * @ORM\Column(name="strength", type="string", length=255)
     * @Assert\Length(max="255")
     */
    private $strength;

    /**
     * @var string
     *
     * @ORM\Column(name="weakness", type="string", length=255)
     * @Assert\Length(max="255")
     */
    private $weakness;

    /**
     * @var string
     *
     * @ORM\Column(name="story", type="text")
     * @Assert\NotBlank()
     */
    private $story;

    /**
     * @ORM\OneToMany(targetEntity="Khatovar\WebBundle\Entity\Photo", mappedBy="member")
     */
    private $photos;

    /**
     * @ORM\OneToOne(targetEntity="Carcel\UserBundle\Entity\User")
     */
    private $owner;


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
     * @return Member
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
     * Set rank
     *
     * @param string $rank
     * @return Member
     */
    public function setRank($rank)
    {
        $this->rank = $rank;

        return $this;
    }

    /**
     * Get rank
     *
     * @return string
     */
    public function getRank()
    {
        return $this->rank;
    }

    /**
     * Set quote
     *
     * @param string $quote
     * @return Member
     */
    public function setQuote($quote)
    {
        $this->quote = $quote;

        return $this;
    }

    /**
     * Get quote
     *
     * @return string
     */
    public function getQuote()
    {
        return $this->quote;
    }

    /**
     * Set skill
     *
     * @param string $skill
     * @return Member
     */
    public function setSkill($skill)
    {
        $this->skill = $skill;

        return $this;
    }

    /**
     * Get skill
     *
     * @return string
     */
    public function getSkill()
    {
        return $this->skill;
    }

    /**
     * Set age
     *
     * @param string $age
     * @return Member
     */
    public function setAge($age)
    {
        $this->age = $age;

        return $this;
    }

    /**
     * Get age
     *
     * @return string
     */
    public function getAge()
    {
        return $this->age;
    }

    /**
     * Set size
     *
     * @param string $size
     * @return Member
     */
    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * Get size
     *
     * @return string
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Set weight
     *
     * @param string $weight
     * @return Member
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;

        return $this;
    }

    /**
     * Get weight
     *
     * @return string
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * Set strength
     *
     * @param string $strength
     * @return Member
     */
    public function setStrength($strength)
    {
        $this->strength = $strength;

        return $this;
    }

    /**
     * Get strength
     *
     * @return string
     */
    public function getStrength()
    {
        return $this->strength;
    }

    /**
     * Set weakness
     *
     * @param string $weakness
     * @return Member
     */
    public function setWeakness($weakness)
    {
        $this->weakness = $weakness;

        return $this;
    }

    /**
     * Get weakness
     *
     * @return string
     */
    public function getWeakness()
    {
        return $this->weakness;
    }

    /**
     * Set story
     *
     * @param string $story
     * @return Member
     */
    public function setStory($story)
    {
        $this->story = $story;

        return $this;
    }

    /**
     * Get story
     *
     * @return string
     */
    public function getStory()
    {
        return $this->story;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->photos = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add photos
     *
     * @param \Khatovar\WebBundle\Entity\Photo $photos
     * @return Member
     */
    public function addPhoto(\Khatovar\WebBundle\Entity\Photo $photos)
    {
        $this->photos[] = $photos;

        return $this;
    }

    /**
     * Remove photos
     *
     * @param \Khatovar\WebBundle\Entity\Photo $photos
     */
    public function removePhoto(\Khatovar\WebBundle\Entity\Photo $photos)
    {
        $this->photos->removeElement($photos);
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
     * Set owner
     *
     * @param \Carcel\UserBundle\Entity\User $owner
     * @return Member
     */
    public function setOwner(\Carcel\UserBundle\Entity\User $owner = null)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get owner
     *
     * @return \Carcel\UserBundle\Entity\User
     */
    public function getOwner()
    {
        return $this->owner;
    }
}