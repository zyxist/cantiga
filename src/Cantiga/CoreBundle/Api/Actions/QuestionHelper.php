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
namespace Cantiga\CoreBundle\Api\Actions;

use Cantiga\CoreBundle\Api\Controller\CantigaController;
use Symfony\Component\HttpFoundation\Request;

/**
 * Encapsulates the logic for making 'question-like' actions, where we ask the user
 * some question and expect to either confirm it or reject. If the user confirms, the
 * actual action is performed.
 *
 * @author Tomasz Jędrzejewski
 */
class QuestionHelper
{
	private $questionTitle = 'Question';
	private $question;
	private $routeName;
	private $routeArgs;
	private $respondName;
	private $respondArgs;
	private $successCallback = null;
	
	private $title = '';
	private $subtitle = '';
	
	public function __construct($question)
	{
		$this->question = $question;
	}
	
	public function onSuccess($callback)
	{
		$this->successCallback = $callback;
		return $this;
	}
	
	public function path($routeName, $routeArgs = [])
	{
		$this->routeName = $routeName;
		$this->routeArgs = $routeArgs;
		return $this;
	}
	
	public function respond($respondName, $respondArgs = [])
	{
		$this->respondName = $respondName;
		$this->respondArgs = $respondArgs;
	}
	
	public function title($title, $subtitle)
	{
		$this->title = $title;
		$this->subtitle = $subtitle;
		return $this;
	}
	
	public function handleRequest(CantigaController $ctrl, Request $request)
	{
		$answer = $request->query->get('answer', null);
		if ($answer == 'yes') {
			$callback = $this->successCallback;
			$callback();
			return $ctrl->redirect($ctrl->generateUrl($this->routeName, $this->routeArgs));
		} elseif ($answer == 'no') {
			return $ctrl->redirect($ctrl->generateUrl($this->routeName, $this->routeArgs));
		} else {
			$successArgs = $this->respondArgs;
			$cancelArgs = $this->respondArgs;
			$successArgs['answer'] = 'yes';
			$cancelArgs['answer'] = 'no';
			
			return $ctrl->render('CantigaCoreBundle:layout:question.html.twig', array(
				'pageTitle' => $this->title,
				'pageSubtitle' => $this->subtitle,
				'questionTitle' => $ctrl->trans('Question', [], 'general'),
				'question' => $this->question,
				'successPath' => $ctrl->generateUrl($this->respondName, $successArgs),
				'failurePath' => $ctrl->generateUrl($this->respondName, $cancelArgs),
				'successBtn' => $ctrl->trans('Indeed', [], 'general'),
				'failureBtn' => $ctrl->trans('Cancel', [], 'general'),
			));
		}
	}
	
}
