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

namespace Khatovar\Bundle\ContactBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Khatovar\Bundle\ContactBundle\Entity\Contact;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Main controller for Contact bundle.
 *
 * @author Damien Carcel (https://github.com/damien-carcel)
 */
class ContactController extends Controller
{
    /** @var EntityManagerInterface */
    protected $entityManager;

    /** @var Session */
    protected $session;

    /**
     * @param EntityManagerInterface $entityManager
     * @param Session                $session
     */
    public function __construct(EntityManagerInterface $entityManager, Session $session)
    {
        $this->entityManager = $entityManager;
        $this->session       = $session;

    }

    /**
     * Displays the active contact page.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $activeContact = $this->findActiveOr404();

        return $this->render(
            'KhatovarContactBundle:Contact:show.html.twig',
            ['contact' => $activeContact]
        );
    }

    /**
     * Finds and displays a contact page.
     *
     * @param int $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction($id)
    {
        $contact = $this->findByIdOr404($id);

        return $this->render(
            'KhatovarContactBundle:Contact:show.html.twig',
            ['contact' => $contact]
        );
    }

    /**
     * Lists all contact pages, and allow to activate one of them.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Secure(roles="ROLE_EDITOR")
     */
    public function listAction(Request $request)
    {
        $form = $this->createActivationForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->changeActiveContact($form);

            $this->session->getFlashBag()->add(
                'notice',
                'Page de contact activée'
            );

            return $this->redirect($this->generateUrl('khatovar_web_contact_list'));
        }

        $contacts    = $this->entityManager->getRepository('KhatovarContactBundle:Contact')->findAll();
        $deleteForms = $this->createDeleteForms($contacts);

        return $this->render(
            'KhatovarContactBundle:Contact:list.html.twig',
            [
                'contacts'        => $contacts,
                'activation_form' => $form->createView(),
                'delete_forms'    => $deleteForms,
            ]
        );
    }

    /**
     * Displays a form to create a new contact page.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Secure(roles="ROLE_EDITOR")
     */
    public function newAction()
    {
        $contact = new Contact();

        $form = $this->createCreateForm($contact);

        return $this->render(
            'KhatovarContactBundle:Contact:new.html.twig',
            ['form' => $form->createView()]
        );
    }

    /**
     * Creates a new contact page.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     *
     * @Secure(roles="ROLE_EDITOR")
     */
    public function createAction(Request $request)
    {
        $contact = new Contact();

        $form = $this->createCreateForm($contact);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->entityManager->persist($contact);
            $this->entityManager->flush();

            $this->session->getFlashBag()->add(
                'notice',
                'Page de contact créée'
            );

            return $this->redirect(
                $this->generateUrl(
                    'khatovar_web_contact_show',
                    ['id' => $contact->getId()]
                )
            );
        }

        return $this->render(
            'KhatovarContactBundle:Contact:new.html.twig',
            ['form' => $form->createView()]
        );
    }

    /**
     * Displays a form to edit an existing contact page.
     *
     * @param int $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Secure(roles="ROLE_EDITOR")
     */
    public function editAction($id)
    {
        $contact = $this->findByIdOr404($id);

        $editForm = $this->createEditForm($contact);

        return $this->render(
            'KhatovarContactBundle:Contact:edit.html.twig',
            ['edit_form' => $editForm->createView()]
        );
    }

    /**
     * Updates a contact page.
     *
     * @param Request $request
     * @param int     $id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     *
     * @Secure(roles="ROLE_EDITOR")
     */
    public function updateAction(Request $request, $id)
    {
        $contact = $this->findByIdOr404($id);

        $editForm = $this->createEditForm($contact);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $this->entityManager->flush();

            $this->session->getFlashBag()->add(
                'notice',
                'Page de contact modifiée'
            );

            return $this->redirect(
                $this->generateUrl(
                    'khatovar_web_contact_show',
                    ['id' => $id]
                )
            );
        }

        return $this->render(
            'KhatovarContactBundle:Contact:edit.html.twig',
            ['edit_form' => $editForm->createView()]
        );
    }

    /**
     * Deletes a contact page.
     *
     * @param Request $request
     * @param int     $id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @Secure(roles="ROLE_EDITOR")
     */
    public function deleteAction(Request $request, $id)
    {
        $contact = $this->findByIdOr404($id);

        if ($contact->isActive()) {
            $this->session->getFlashBag()->add(
                'notice',
                'Vous ne pouvez pas supprimer la page d\'accueil active'
            );
        } else {
            $form = $this->createDeleteForm($id);
            $form->handleRequest($request);

            if ($form->isValid()) {
                $this->entityManager->remove($contact);
                $this->entityManager->flush();

                $this->session->getFlashBag()->add(
                    'notice',
                    'Page de contact supprimée'
                );
            }
        }

        return $this->redirect($this->generateUrl('khatovar_web_contact_list'));
    }

    /**
     * Creates a form to create a Contact entity.
     *
     * @param Contact $contact
     *
     * @return \Symfony\Component\Form\Form
     */
    protected function createCreateForm(Contact $contact)
    {
        $form = $this->createForm(
            'khatovar_contact_type',
            $contact,
            [
                'action' => $this->generateUrl('khatovar_web_contact_create'),
                'method' => 'POST',
            ]
        );

        $form->add('submit', 'submit', ['label' => 'Créer']);

        return $form;
    }

    /**
     * Creates a form to edit a Contact entity.
     *
     * @param Contact $contact
     *
     * @return \Symfony\Component\Form\Form
     */
    protected function createEditForm(Contact $contact)
    {
        $form = $this->createForm(
            'khatovar_contact_type',
            $contact,
            [
                'action' => $this->generateUrl('khatovar_web_contact_update', ['id' => $contact->getId()]),
                'method' => 'PUT',
            ]
        );

        $form->add('submit', 'submit', ['label' => 'Mettre à jour']);

        return $form;
    }

    /**
     * Creates a form to delete a Contact entity.
     *
     * @param int $id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    protected function createDeleteForm($id)
    {
        return $this
            ->createFormBuilder()
            ->setAction($this->generateUrl('khatovar_web_contact_delete', ['id' => $id]))
            ->setMethod('DELETE')
            ->add(
                'submit',
                'submit',
                [
                    'label' => 'Effacer',
                    'attr'  => ['onclick' => 'return confirm("Êtes-vous sûr ?")'],
                ]
            )
            ->getForm();
    }

    /**
     * Return a list of delete forms for a set of Contact entities.
     *
     * @param Contact[] $contacts
     *
     * @return \Symfony\Component\Form\Form[]
     */
    protected function createDeleteForms(array $contacts)
    {
        $deleteForms = [];

        foreach ($contacts as $contact) {
            $deleteForms[$contact->getId()] = $this->createDeleteForm($contact->getId())->createView();
        }

        return $deleteForms;
    }

    /**
     * @param int $id
     *
     * @return Contact
     */
    protected function findByIdOr404($id)
    {
        $contact = $this->entityManager->getRepository('KhatovarContactBundle:Contact')->find($id);

        if (!$contact) {
            throw $this->createNotFoundException('Impossible de trouver le contact.');
        }

        return $contact;
    }

    /**
     * @return Contact
     */
    protected function findActiveOr404()
    {
        $contact = $this->entityManager
            ->getRepository('KhatovarContactBundle:Contact')
            ->findOneBy(['active' => true]);

        if (null === $contact) {
            throw new NotFoundHttpException('There is no active Contact entity. You must activate one.');
        }

        return $contact;
    }

    /**
     * Create a form to activate a Homepage.
     *
     * @return \Symfony\Component\Form\Form
     */
    protected function createActivationForm()
    {
        $previousContact = $this->entityManager
            ->getRepository('KhatovarContactBundle:Contact')
            ->findOneBy(['active' => true]);

        $form = $this->createFormBuilder()
            ->add(
                'active',
                'entity',
                [
                    'class'             => 'Khatovar\Bundle\ContactBundle\Entity\Contact',
                    'label'             => false,
                    'property'          => 'name',
                    'preferred_choices' => [$previousContact],
                ]
            )
            ->add('submit', 'submit', ['label' => 'Activer'])
            ->getForm();

        return $form;
    }

    /**
     * Change the active Homepage.
     *
     * @param FormInterface $form
     */
    protected function changeActiveContact(FormInterface $form)
    {
        $repository = $this->entityManager->getRepository('KhatovarContactBundle:Contact');
        $newContact = $repository->find($form->get('active')->getData());
        $oldContact = $repository->findOneBy(['active' => true]);

        if (null !== $oldContact) {
            $oldContact->setActive(false);
            $this->entityManager->persist($oldContact);
        }

        $newContact->setActive(true);
        $this->entityManager->persist($newContact);
        $this->entityManager->flush();

        $this->session->getFlashBag()->add(
            'notice',
            'Page de contact activée'
        );
    }
}
