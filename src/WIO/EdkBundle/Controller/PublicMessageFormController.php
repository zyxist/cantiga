<?php
/*
 * This file is part of Cantiga Project. Copyright 2016 Cantiga contributors.
 *
 * Cantiga Project is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * Cantiga Project is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Foobar; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
namespace WIO\EdkBundle\Controller;

use Cantiga\Metamodel\Exception\ModelException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use WIO\EdkBundle\EdkSettings;
use WIO\EdkBundle\Entity\Intent\PostMessageIntent;
use WIO\EdkBundle\Form\EdkMessageForm;

/**
 * @Route("/pub/edk/{slug}/napisz-wiadomosc")
 */
class PublicMessageFormController extends PublicEdkController
{
	const REPOSITORY_NAME = 'wio.edk.repo.message';
	const PUBLISHED_REPO_NAME = 'wio.edk.repo.published_data';
	
	/**
	 * @Route("/formularz/{id}", name="public_edk_write_msg", defaults={"_localeFromQuery" = true, "id" = null})
	 */
	public function indexAction($id, Request $request)
	{
		try {
			$repository = $this->get(self::REPOSITORY_NAME);
			$recaptcha = $this->get('cantiga.security.recaptcha');
			$publishedRepository = $this->get(self::PUBLISHED_REPO_NAME);
			$publishedRepository->setProject($this->project);
			$publishedRepository->setPublishedStatusId($this->getProjectSettings()->get(EdkSettings::PUBLISHED_AREA_STATUS)->getValue());
			
			$intent = new PostMessageIntent($repository);
			if (null !== $id) {
				$intent->area = $publishedRepository->getArea($id);
			}
			
			$form = $this->createForm(EdkMessageForm::class, $intent, [
				'repository' => $publishedRepository,
				'action' => $this->generateUrl('public_edk_write_msg', ['slug' => $this->getSlug()])
			]);
			$form->handleRequest($request);
			
			if ($form->isValid()) {
				if ($recaptcha->verifyRecaptcha($request)) {
					$intent->execute();
					return $this->redirect($this->generateUrl('public_edk_msg_sent', ['slug' => $this->getSlug()]));
				} else {
					return $this->render('WioEdkBundle:Public:public-error.html.twig', [
						'message' => $this->trans('You did not solve the CAPTCHA correctly, sorry.', [], 'public'),
						'slug' => $this->project->getSlug(),
						'currentPage' => 'public_edk_write_msg',
					]);
				}
			}
			
			return $this->render('WioEdkBundle:Public:message-form.html.twig', [
				'form' => $form->createView(),
				'recaptcha' => $recaptcha,
				'slug' => $this->project->getSlug(),
				'currentPage' => 'public_edk_write_msg'
			]);
		} catch (ModelException $exception) {
			return $this->render('WioEdkBundle:Public:public-error.html.twig', [
				'message' => $this->trans($exception->getMessage(), [], 'public'),
				'slug' => $this->project->getSlug(),
				'currentPage' => 'public_edk_write_msg'
			]);
		}
	}
	
	/**
	 * @Route("/potwierdzenie", name="public_edk_msg_sent", defaults={"_localeFromQuery" = true})
	 */
	public function completedAction(Request $request)
	{
		return $this->render('WioEdkBundle:Public:public-message.html.twig', [
			'message' => $this->trans('Thank you, the message has been passed to the area. Please wait for the answer.', [], 'public'),
			'slug' => $this->project->getSlug(),
				'currentPage' => 'public_edk_write_msg'
		]);
	}
}
