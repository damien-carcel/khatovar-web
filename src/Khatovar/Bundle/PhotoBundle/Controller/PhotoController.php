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

namespace Khatovar\Bundle\PhotoBundle\Controller;

use Khatovar\Bundle\PhotoBundle\Entity\Photo;
use Khatovar\Bundle\PhotoBundle\Helper\PhotoHelper;
use Khatovar\Bundle\WebBundle\Helper\EntityHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Main controller for Photo bundle.
 *
 * Only a user with "ROLE_EDITOR as a minimum
 * security clearance can see and manipulate photos for all the web
 * site sections. Regular users can only see their own members photos.
 *
 * @author Damien Carcel (https://github.com/damien-carcel)
 * @todo   There should be a PhotoHandler with two methods: handleCreation and handleUpdate
 */
class PhotoController extends Controller
{
    /** @staticvar string */
    const MAX_PHOTO_HEIGHT = 720;

    /**
     * Returns the list of all photos uploaded for the website and
     * display admin utilities to manage them.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Security("has_role('ROLE_VIEWER')")
     */
    public function indexAction()
    {
        if ($this->isGranted('ROLE_EDITOR')) {
            $photos = $this->get('khatovar_photo.manager.photo')->getPhotosSortedByEntities();
        } else {
            $photos = $this->get('khatovar_photo.manager.photo')->getMemberPhotos($this->getUser());
        }

        return $this->render(
            'KhatovarPhotoBundle:Photo:index.html.twig',
            [
                'sorted_photos' => $photos,
                'delete_forms'  => $this->createDeleteForms($photos),
            ]
        );
    }

    /**
     * Displays a form to upload a new photo.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Security("has_role('ROLE_VIEWER')")
     */
    public function newAction()
    {
        $this->userHasEditRights();

        $photo = new Photo();

        if (!$this->isGranted('ROLE_EDITOR')) {
            $member = $this->getLoggedMember();

            $photo->setClass('none')->setEntity(EntityHelper::MEMBER_CODE)->setMember($member);

            $form = $this->createCreateForm($photo);

            // TODO should be done in a subscriber
            $form->remove('class')->remove('entity')->remove(EntityHelper::MEMBER_CODE);
        } else {
            $form = $this->createCreateForm($photo);
        }

        return $this->render(
            'KhatovarPhotoBundle:Photo:new.html.twig',
            ['form' => $form->createView()]
        );
    }

    /**
     * Uploads a new photo.
     *
     * Editors can add photos to every part of the web site, but
     * regular users can only add photos to their own member page (if
     * they have one).
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     *
     * @Security("has_role('ROLE_VIEWER')")
     */
    public function createAction(Request $request)
    {
        $this->userHasEditRights();

        $photo = new Photo();

        if (!$this->isGranted('ROLE_EDITOR')) {
            $isEditor = false;
            $member   = $this->getLoggedMember();

            $photo->setClass('none')->setEntity(EntityHelper::MEMBER_CODE)->setMember($member);

            $form = $this->createCreateForm($photo);

            // TODO should be done in a subscriber
            $form->remove('class')->remove('entity')->remove(EntityHelper::MEMBER_CODE);
        } else {
            $isEditor = true;
            $form     = $this->createCreateForm($photo);
        }

        $form->handleRequest($request);

        if ($form->isValid()) {
            $entityManager = $this->get('doctrine.orm.entity_manager');
            $entityManager->persist($photo);
            $entityManager->flush();

            $this->get('khatovar_photo.manager.photo')->imageResize(
                $photo->getAbsolutePath(),
                static::MAX_PHOTO_HEIGHT
            );

            $this->addFlash(
                'notice',
                'Photo ajoutée'
            );

            if ($isEditor) {
                return $this->redirect(
                    $this->generateUrl(
                        'khatovar_web_photo_edit',
                        ['id'=> $photo->getId()]
                    )
                );
            }

            return $this->redirect($this->generateUrl('khatovar_web_photo'));
        }

        return $this->render(
            'KhatovarPhotoBundle:Photo:new.html.twig',
            ['form' => $form->createView()]
        );
    }

    /**
     * Displays a form to edit an existing photo.
     *
     * @param int $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Security("has_role('ROLE_VIEWER')")
     */
    public function editAction($id)
    {
        $photo = $this->get('doctrine.orm.entity_manager')
            ->getRepository('KhatovarPhotoBundle:Photo')
            ->findByIdOr404($id);

        $this->userHasEditRights($photo);

        $editForm = $this->createEditForm($photo);

        return $this->render(
            'KhatovarPhotoBundle:Photo:edit.html.twig',
            [
                'edit_form' => $editForm->createView(),
                'photo'     => $photo,
            ]
        );
    }

    /**
     * Updates a photo.
     *
     * @param Request $request
     * @param int     $id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     *
     * @Security("has_role('ROLE_VIEWER')")
     */
    public function updateAction(Request $request, $id)
    {
        $photo = $this->get('doctrine.orm.entity_manager')
            ->getRepository('KhatovarPhotoBundle:Photo')
            ->findByIdOr404($id);

        $this->userHasEditRights($photo);

        $editForm = $this->createEditForm($photo);
        $entity   = $photo->getEntity();

        if (!$this->isGranted('ROLE_EDITOR')) {
            // TODO should be done in a subscriber
            $editForm->remove('class')->remove('entity')->remove(EntityHelper::MEMBER_CODE);
        }

        $editForm->handleRequest($request);
        if ($editForm->isValid()) {
            $entityManager = $this->get('doctrine.orm.entity_manager');
            $entityManager->persist($photo);

            if ($photo->getEntity() !== $entity) {
                foreach (PhotoHelper::getPhotoEntities() as $code) {
                    $setter = 'set' . ucfirst($code);
                    $photo->$setter(null);
                }

                $entityManager->flush();

                return $this->redirect(
                    $this->generateUrl(
                        'khatovar_web_photo',
                        ['id' => $photo->getId()]
                    )
                );
            } else {
                $entityManager->flush();

                $this->addFlash('notice', 'Photo modifiée');
            }

            return $this->redirect($this->generateUrl('khatovar_web_photo'));
        }

        return $this->render(
            'KhatovarPhotoBundle:Photo:edit.html.twig',
            [
                'edit_form' => $editForm->createView(),
                'photo'     => $photo,
            ]
        );
    }

    /**
     * Deletes a photo.
     *
     * @param Request $request
     * @param int     $id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     *
     * @Security("has_role('ROLE_VIEWER')")
     */
    public function deleteAction(Request $request, $id)
    {
        $photo = $this->get('doctrine.orm.entity_manager')
            ->getRepository('KhatovarPhotoBundle:Photo')
            ->findByIdOr404($id);

        $this->userHasEditRights($photo);

        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $entityManager = $this->get('doctrine.orm.entity_manager');
            $entityManager->remove($photo);
            $entityManager->flush();

            $this->addFlash('notice', 'Photo supprimée');
        }

        return $this->redirect($this->generateUrl('khatovar_web_photo'));
    }

    /**
     * Creates a form to create a Photo entity.
     *
     * @param Photo $photo
     *
     * @return \Symfony\Component\Form\Form
     */
    protected function createCreateForm(Photo $photo)
    {
        $form = $this->createForm(
            'Khatovar\Bundle\PhotoBundle\Form\Type\PhotoType',
            $photo,
            [
                'action' => $this->generateUrl('khatovar_web_photo_create'),
                'method' => 'POST',
            ]
        );

        $form->add('submit', SubmitType::class, ['label' => 'Créer']);

        return $form;
    }

    /**
     * Creates a form to edit a Photo entity.
     *
     * @param Photo $photo
     *
     * @return \Symfony\Component\Form\Form
     */
    protected function createEditForm(Photo $photo)
    {
        $form = $this->createForm(
            'Khatovar\Bundle\PhotoBundle\Form\Type\PhotoType',
            $photo,
            [
                'action' => $this->generateUrl('khatovar_web_photo_update', ['id' => $photo->getId()]),
                'method' => 'PUT',
            ]
        );

        $form->add('submit', SubmitType::class, ['label' => 'Mettre à jour']);

        return $form;
    }

    /**
     * Creates a form to delete a Photo entity.
     *
     * @param int $id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    protected function createDeleteForm($id)
    {
        return $this
            ->createFormBuilder()
            ->setAction($this->generateUrl('khatovar_web_photo_delete', ['id' => $id]))
            ->setMethod('DELETE')
            ->add(
                'submit',
                SubmitType::class,
                [
                    'label' => 'Effacer',
                    'attr'  => ['onclick' => 'return confirm("Êtes-vous sûr ?")'],
                ]
            )
            ->getForm();
    }

    /**
     * Return a list of delete forms for a set of sorted Photo entities.
     *
     * @param Photo[] $sortedPhotos
     *
     * @return \Symfony\Component\Form\Form[]
     */
    protected function createDeleteForms(array $sortedPhotos)
    {
        $deleteForms = [];

        foreach ($sortedPhotos as $photoLists) {
            foreach ($photoLists as $photos) {
                foreach ($photos as $photo) {
                    if ($photo instanceof Photo) {
                        $deleteForms[$photo->getId()] = $this->createDeleteForm($photo->getId())->createView();
                    }
                }
            }
        }

        return $deleteForms;
    }

    /**
     * Get the member page corresponding to the current user.
     *
     * @return \Khatovar\Bundle\MemberBundle\Entity\Member
     */
    protected function getLoggedMember()
    {
        return $this->get('doctrine.orm.entity_manager')
            ->getRepository('KhatovarMemberBundle:Member')
            ->findOneBy(['owner' => $this->getUser()->getId()]);
    }

    /**
     * Checks if the logged user has the editor role. If he has not,
     * checks if he has a member page (he can then upload new photos),
     * and if editing a photo, that it belongs to its member page.
     *
     * @param Photo $photo
     *
     * @return bool
     *
     * @throws AccessDeniedHttpException
     */
    protected function userHasEditRights(Photo $photo = null)
    {
        $member = $this->getLoggedMember();

        if ($this->isGranted('ROLE_EDITOR')) {
            return true;
        }

        if (null === $member) {
            throw new AccessDeniedHttpException(
                'Désolé, vous n\'avez pas de page de membre, et ne pouvez donc pas manipuler les photos.'
            );
        } elseif (null !== $photo) {
            if (null === $photo->getMember() ||
                $member->getId() !== $photo->getMember()->getId()
            ) {
                throw new AccessDeniedHttpException(
                    'Désolé, vous n\'avez pas les droits requis pour modifier cette photo.'
                );
            }
        }

        return true;
    }
}
