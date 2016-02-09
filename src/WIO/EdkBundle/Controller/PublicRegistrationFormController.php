<?php
/*
 * This file is part of Cantiga Project. Copyright 2015 Tomasz Jedrzejewski.
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

use Cantiga\Metamodel\Exception\ItemNotFoundException;
use Cantiga\Metamodel\Exception\ModelException;
use DateInterval;
use DateTime;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use WIO\EdkBundle\EdkSettings;
use WIO\EdkBundle\EdkTexts;
use WIO\EdkBundle\Entity\EdkParticipant;
use WIO\EdkBundle\Form\PublicParticipantForm;

/**
 * @Route("/pub/edk/{slug}/zapisy")
 */
class PublicRegistrationFormController extends PublicEdkController
{
	const REPOSITORY_NAME = 'wio.edk.repo.participant';
	
	/**
	 * @Route("/formularz", name="public_edk_register")
	 */
	public function indexAction($slug, Request $request)
	{
		try {
			$activeStatus = $this->getProjectSettings()->get(EdkSettings::PUBLISHED_AREA_STATUS)->getValue();		
			$routeId = $request->get('r', null);
			if(null !== $routeId) {
				$repository = $this->get(self::REPOSITORY_NAME);
				$registrationSettings = $repository->getPublicRegistration($routeId, $activeStatus);
				if($registrationSettings->isRegistrationOpen()) {
					return $this->actualForm($registrationSettings, $slug, $request);
				} else {
					return $this->showErrorMessage('RegistrationOverErrorMsg');
				}
			} else {
				return $this->actualForm(null, $slug, $request);
			}
		} catch (ModelException $exception) {
			return $this->showErrorMessage($exception->getMessage());
		}
	}
	
	/**
	 * @Route("/sprawdz", name="public_edk_check")
	 */
	public function checkAction(Request $request)
	{
		try {
			$k = $request->get('k', null);
			$t = $request->get('t', null);
			
			if (!empty($k) && null !== $t) {
				switch ((int) $t) {
					case 0:
						return $this->checkRequest($k);
					case 1:
						return $this->removeRequest($k);
				}
			}
			return $this->render('WioEdkBundle:Public:check-registration.html.twig', ['slug' => $this->project->getSlug()]);
		} catch (ModelException $exception) {
			return $this->showErrorMessage($exception->getMessage());
		}
	}
	
	/**
	 * @Route("/potwierdzenie/{accessKey}", name="public_edk_registration_completed")
	 */
	public function completedAction($accessKey, Request $request)
	{
		return $this->render('WioEdkBundle:Public:registration-completed.html.twig', [
			'accessKey' => $accessKey,
			'slug' => $this->project->getSlug()
		]);
	}
	
	/**
	 * @Route("/api/data", name="public_edk_registration_data")
	 */
	public function registrationsAction(Request $request)
	{
		try {
			$repository = $this->get('wio.edk.repo.published_data');
			$registrations = $repository->getOpenRegistrations($this->project, $this->getProjectSettings()->get(EdkSettings::PUBLISHED_AREA_STATUS)->getValue());
			$response = new JsonResponse($registrations);
			$response->setDate(new DateTime());
			$exp = new DateTime();
			$exp->add(new DateInterval('PT0H5M0S'));
			$response->setExpires($exp);
			return $response;
		} catch(ItemNotFoundException $exception) {
			return new JsonResponse(['success' => 0]);
		}
	}
	
	private function actualForm($registrationSettings, $slug, Request $request)
	{
		$repository = $this->get(self::REPOSITORY_NAME);
		$recaptcha = $this->get('cantiga.security.recaptcha');
		$participant = new EdkParticipant();
		$participant->setPeopleNum(1);
		
		if (null !== $registrationSettings) {
			$participant->setRegistrationSettings($registrationSettings);
		}
		
		$terms1Text = $this->getTextRepository()->getText(EdkTexts::REGISTRATION_TERMS1_TEXT, $request, $this->project);
		$terms2Text = $this->getTextRepository()->getText(EdkTexts::REGISTRATION_TERMS2_TEXT, $request, $this->project);
		$terms3Text = $this->getTextRepository()->getText(EdkTexts::REGISTRATION_TERMS3_TEXT, $request, $this->project);
		
		$form = $this->createForm(new PublicParticipantForm($registrationSettings, strip_tags($terms1Text->getContent()), strip_tags($terms2Text->getContent()), strip_tags($terms3Text->getContent())), $participant, array(
			'action' => $this->generateUrl('public_edk_register', ['slug' => $slug]).(null !== $registrationSettings ? '?r='.$registrationSettings->getRoute()->getId() : '')
		));
		if ($this->getRequest()->getMethod() == 'POST') {
			$form->handleRequest($request);
			if ($form->isValid()) {
				if ($recaptcha->verifyRecaptcha($request)) {
					$participant->setIpAddress(ip2long($_SERVER['REMOTE_ADDR']));
					$repository->register($participant, $_SERVER['REMOTE_ADDR'], $slug);
					return $this->redirect($this->generateUrl('public_edk_registration_completed', ['slug' => $slug, 'accessKey' => $participant->getAccessKey()]));
				} else {
					return $this->showErrorMessage('You did not solve the CAPTCHA correctly, sorry.');
				}
			}
		}
		
		$text = $this->getTextRepository()->getText(EdkTexts::REGISTRATION_FORM_TEXT, $request, $this->project);		
		$response = $this->render('WioEdkBundle:Public:registration-form.html.twig', array(
			'form' => $form->createView(),
			'recaptcha' => $recaptcha,
			'route' => (null != $registrationSettings ? $registrationSettings->getRoute() : null),
			'registrationSettings' => $registrationSettings,
			'text' => $text,
			'slug' => $this->project->getSlug()
		));
		if (null === $registrationSettings && $request->getMethod() == 'GET') {
			$response->setPublic();
			$response->setMaxAge(600);
			$response->setSharedMaxAge(600);
		}
		return $response;
	}
	
	private function checkRequest($key)
	{
		try {
			$repository = $this->get(self::REPOSITORY_NAME);
			$item = $repository->getItemByKey($key, $this->getProjectSettings()->get(EdkSettings::PUBLISHED_AREA_STATUS)->getValue());
			return $this->render('WioEdkBundle:Public:check-result.html.twig', [
				'item' => $item,
				'slug' => $this->project->getSlug(),
			]);
		} catch(ItemNotFoundException $exception) {
			return $this->showErrorMessage('ParticipantNotFoundErrMsg');
		}
	}
	
	private function removeRequest($key)
	{
		try {
			$repository = $this->get(self::REPOSITORY_NAME);
			$item = $repository->removeItemByKey($key, $this->getProjectSettings()->get(EdkSettings::PUBLISHED_AREA_STATUS)->getValue());
			return $this->render('WioEdkBundle:Public:public-message.html.twig', [
				'message' => $this->trans('RequestRemovedMsg', [], 'public'),
				'slug' => $this->project->getSlug(),
			]);
		} catch(ItemNotFoundException $exception) {
			return $this->showErrorMessage('ParticipantNotFoundErrMsg');
		}
	}
	
	private function showErrorMessage($message)
	{
		return $this->render('WioEdkBundle:Public:public-error.html.twig', [
			'message' => $this->trans($message, [], 'public'),
			'slug' => $this->project->getSlug(),
		]);
	}
}
