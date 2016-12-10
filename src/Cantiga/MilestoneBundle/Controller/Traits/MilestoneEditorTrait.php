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
namespace Cantiga\MilestoneBundle\Controller\Traits;

use Cantiga\Components\Hierarchy\Entity\Membership;
use Cantiga\Components\Hierarchy\HierarchicalInterface;
use Cantiga\Metamodel\Exception\ModelException;
use Cantiga\MilestoneBundle\Repository\MilestoneStatusRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Common code for the management actions.
 */
trait MilestoneEditorTrait
{
	/**
	 * @var MilestoneStatusRepository
	 */
	protected $repository;
	
	protected function performReload(Request $request, Membership $membership)
	{
		try {
			$root = $membership->getPlace();
			$entity = $this->repository->findEntity($request->get('i'));
			if (!$this->repository->isAllowed($entity, $root)) {
				return new JsonResponse(['success' => 0]);
			}
			$editable = $this->repository->isEditable($entity, $root);
			
			$answer = ['success' => 1];
			$answer['selection'] = $this->repository->getAvailableEntities($root, $this->getTranslator());
			$answer['editable'] = $editable ? 1 : 0;
			$answer['progressBar'] = $this->repository->computeTotalProgress($entity, $this->extractProject($root));
			$answer['milestones'] = $this->repository->findAllMilestones($entity, $this->extractProject($root), $editable);
			return new JsonResponse($answer);
		} catch(ModelException $exception) {
			return new JsonResponse(['success' => 0, 'error' => $exception->getMessage()]); 
		}
	}
	
	protected function performComplete(Request $request, Membership $membership)
	{
		try {
			$root = $membership->getPlace();
			$entity = $this->repository->findEntity($request->get('i'));
			if (!$this->repository->isAllowed($entity, $root, true)) {
				return new JsonResponse(['success' => 0]);
			}
		
			$milestone = $this->repository->findMilestone($request->get('m'), $entity, $this->extractProject($root));
			
			$answer = ['success' => 1];
			$answer['milestone'] = $this->repository->completeMilestone($entity, $milestone, $root);
			$answer['progressBar'] = $this->repository->computeTotalProgress($entity, $this->extractProject($root));
			
			return new JsonResponse($answer);
		} catch(ModelException $exception) {
			return new JsonResponse(['success' => 0, 'error' => $exception->getMessage()]); 
		}
	}
	
	protected function performCancel(Request $request, Membership $membership)
	{
		try {
			$root = $membership->getPlace();
			$entity = $this->repository->findEntity($request->get('i'));
			if (!$this->repository->isAllowed($entity, $root, true)) {
				return new JsonResponse(['success' => 0]);
			}
		
			$milestone = $this->repository->findMilestone($request->get('m'), $entity, $this->extractProject($root));
			
			$answer = ['success' => 1];
			$answer['milestone'] = $this->repository->cancelMilestone($entity, $milestone, $root);
			$answer['progressBar'] = $this->repository->computeTotalProgress($entity, $this->extractProject($root));
			
			return new JsonResponse($answer);
		} catch(ModelException $exception) {
			return new JsonResponse(['success' => 0, 'error' => $exception->getMessage()]); 
		}
	}
	
	protected function performUpdate(Request $request, Membership $membership)
	{
		try {
			$root = $membership->getPlace();
			$entity = $this->repository->findEntity($request->get('i'));
			$progress = $request->get('p');
			if (!$this->repository->isAllowed($entity, $root, true)) {
				return new JsonResponse(['success' => 0]);
			}
		
			$milestone = $this->repository->findMilestone($request->get('m'), $entity, $this->extractProject($root));
			
			$answer = ['success' => 1];
			$answer['milestone'] = $this->repository->updateMilestone($entity, $milestone, $root, $progress);
			$answer['progressBar'] = $this->repository->computeTotalProgress($entity, $this->extractProject($root));
			
			return new JsonResponse($answer);
		} catch(ModelException $exception) {
			return new JsonResponse(['success' => 0, 'error' => $exception->getMessage()]); 
		}
	}
	
	private function extractProject(HierarchicalInterface $root)
	{
		return $root->getRootElement();
	}
}
