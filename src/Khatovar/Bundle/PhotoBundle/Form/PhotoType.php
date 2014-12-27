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

namespace Khatovar\Bundle\PhotoBundle\Form;

use Carcel\UserBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class PhotoType
 *
 * @author Damien Carcel (https://github.com/damien-carcel)
 * @package Khatovar\Bundle\PhotoBundle\Form
 */
class PhotoType extends AbstractType
{
    private $currentUser;

    /**
     * @param User $currentUser
     */
    public function __construct(User $currentUser)
    {
        $this->currentUser = $currentUser;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (is_null($builder->getData()->getId())) {
            $builder->add('file', 'file', array('label' => false));
        }

        $builder
            ->add('alt', 'text', array('label' => 'Nom de substitution : '));

        $formModifier = function (FormInterface $form, $entity) {
            // First we check if entity is defined. If not, then it is
            // a photo upload, so we don't have other fields to add to
            // the form. If it is not null, then the photo is already
            // uploaded and we are editing it.
            if (!is_null($entity)) {
                if ($entity == 'homepage') {
                    $form->add('class', 'choice', array(
                            'label' => 'Taille de la photo : ',
                            'choices' => array(
                                'photo_small' => 'Petit format',
                                'photo' => 'Format normal',
                                'panorama' => 'Panorama'
                            ),
                            'preferred_choices' => array('photo'),
                            'required' => true
                        ));
                } else {
                    $form->add('class', 'hidden', array(
                            'data' => 'none'
                        ));
                }

                $form->add($entity, 'entity', array(
                        'class' => 'KhatovarWebBundle:' . ucfirst($entity),
                        'property' => 'name',
                        'label' => 'Page :'
                    ));
            }
        };

        if ($this->currentUser->hasRole('ROLE_SUPER_ADMIN', 'ROLE_ADMIN', 'ROLE_EDITOR')) {
            $builder->add('entity', 'choice', array(
                    'label' => 'Rattacher la photo à une : ',
                    'choices' => array(
                        'homepage' => 'Page d\'accueil',
                        'member' => 'Page de membre'
                    ),
                    'preferred_choices' => array('homepage')
                ));

            $builder->addEventListener(
                FormEvents::PRE_SET_DATA,
                function (FormEvent $event) use ($formModifier) {
                    $data = $event->getData();
                    $formModifier($event->getForm(), $data->getEntity());
                }
            );

            $builder->get('entity')->addEventListener(
                FormEvents::POST_SET_DATA,
                function (FormEvent $event) use ($formModifier) {
                    $entity = $event->getForm()->getData();
                    $formModifier($event->getForm()->getParent(), $entity);
                }
            );
        }

        $builder->add('submit', 'submit', array('label' => 'Envoyer'));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Khatovar\WebBundle\Entity\Photo'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'khatovar_webbundle_photo';
    }
}