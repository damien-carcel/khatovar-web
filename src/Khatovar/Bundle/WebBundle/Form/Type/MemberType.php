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

namespace Khatovar\Bundle\WebBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Khatovar\Bundle\UserBundle\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Damien Carcel <damien.carcel@gmail.com>
 */
class MemberType extends AbstractType
{
    /** @var EventSubscriberInterface */
    protected $addPortraitSubscriber;

    /** @var EventSubscriberInterface */
    protected $removeOwnerSubscriber;

    public function __construct(
        EventSubscriberInterface $addPortraitSubscriber,
        EventSubscriberInterface $removeOwnerSubscriber
    ) {
        $this->addPortraitSubscriber = $addPortraitSubscriber;
        $this->removeOwnerSubscriber = $removeOwnerSubscriber;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->addBaseFields($builder);
        $this->addDescriptionFields($builder);
        $this->addEventSubscribers($builder);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => 'Khatovar\Bundle\WebBundle\Entity\Member']);
    }

    protected function addBaseFields(FormBuilderInterface $builder): void
    {
        $builder
            ->add('name', TextType::class, ['label' => 'Nom'])
            ->add(
                'active',
                CheckboxType::class,
                [
                    'label' => 'Membre actif',
                    'required' => false,
                ]
            )
            ->add(
                'owner',
                EntityType::class,
                [
                    'label' => 'Utilisateur lié',
                    'class' => User::class,
                    'choice_label' => 'username',
                    'required' => false,
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('o')->orderBy('o.username');
                    },
                ]
            );
    }

    protected function addDescriptionFields(FormBuilderInterface $builder): void
    {
        $builder
            ->add(
                'rank',
                TextType::class,
                [
                    'label' => 'Rang',
                    'required' => false,
                ]
            )
            ->add(
                'quote',
                TextType::class,
                [
                    'label' => 'Citation',
                    'required' => false,
                ]
            )
            ->add(
                'skill',
                TextType::class,
                [
                    'label' => 'Compétences',
                    'required' => false,
                ]
            )
            ->add(
                'age',
                TextType::class,
                [
                    'label' => 'Âge',
                    'required' => false,
                ]
            )
            ->add(
                'size',
                TextType::class,
                [
                    'label' => 'Taille',
                    'required' => false,
                ]
            )
            ->add(
                'weight',
                TextType::class,
                [
                    'label' => 'Poids',
                    'required' => false,
                ]
            )
            ->add(
                'strength',
                TextType::class,
                [
                    'label' => 'Point fort',
                    'required' => false,
                ]
            )
            ->add(
                'weakness',
                TextType::class,
                [
                    'label' => 'Point faible',
                    'required' => false,
                ]
            )
            ->add('story', TextareaType::class, ['label' => 'Histoire personnelle']);
    }

    protected function addEventSubscribers(FormBuilderInterface $builder): void
    {
        $builder
            ->addEventSubscriber($this->addPortraitSubscriber)
            ->addEventSubscriber($this->removeOwnerSubscriber);
    }
}
