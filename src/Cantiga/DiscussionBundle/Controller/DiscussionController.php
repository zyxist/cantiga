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
namespace Cantiga\DiscussionBundle\Controller;

use Cantiga\Components\Hierarchy\Entity\Membership;
use Cantiga\CoreBundle\Api\Controller\WorkspaceController;
use Cantiga\DiscussionBundle\Entity\Subchannel;
use Cantiga\Metamodel\Exception\ItemNotFoundException;
use Cantiga\Metamodel\Exception\ModelException;
use Cantiga\Metamodel\TimeFormatter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/s/{slug}/discussion")
 * @Security("is_granted('MEMBEROF_ANY')")
 */
class DiscussionController extends WorkspaceController
{
	const TEMPLATE_LOCATION = 'CantigaDiscussionBundle:Discussion:';
	const REPOSITORY = 'cantiga.discussion.repo.channel';
	const SERVED_PORTION = 20;
	
	const CODE_FAILURE = 0;
	const CODE_SUCCESS = 1;
	const CODE_END = 2;
	const CODE_NOT_PUBLISHED = 3;
	
	/**
	 * @Route("/index", name="discussion_index")
	 */
	public function indexAction(Membership $membership)
	{
		$this->breadcrumbs()
			->workgroup('community')
			->entryLink($this->trans('Discussion', [], 'pages'), 'discussion_index', ['slug' => $this->getSlug()]);
		
		$repository = $this->get(self::REPOSITORY);
		$repository->setProject($this->getActiveProject());
		$channels = $repository->findWorkspaceChannels($membership->getPlace());
		
		return $this->render(self::TEMPLATE_LOCATION . 'channels.html.twig', ['channels' => $channels]);
	}

	/**
	 * @Route("/channel/{id}", name="discussion_channel")
	 */
	public function channelAction($id, Request $request, Membership $membership)
	{
		try {		
			$repository = $this->get(self::REPOSITORY);
			$repository->setProject($this->getActiveProject());
			$hierarchical = $membership->getPlace();
			
			$subchannel = $repository->getSubchannel((int) $id, $hierarchical);
			
			if (!$subchannel->getChannel()->isVisible($hierarchical)) {
				return $this->error('We are sorry, but you do not have an access to this channel.');
			}
			
			$recentPosts = $subchannel->getRecentPostsSince($this->get('cantiga.discussion.adapter'), self::SERVED_PORTION, time());

			$this->breadcrumbs()
				->workgroup('community')
				->entryLink($this->trans('Discussion', [], 'pages'), 'discussion_index', ['slug' => $this->getSlug()])
				->link($subchannel->getChannel()->getName(), 'discussion_channel', ['slug' => $this->getSlug(), 'id' => $subchannel->getChannel()->getId()]);
			return $this->render(self::TEMPLATE_LOCATION . 'discussion.html.twig', [
				'channel' => $subchannel->getChannel(),
				'discussion' => $recentPosts,
				'canPost' => $subchannel->getChannel()->canPost($hierarchical) ? 1 : 0,
				'user' => $this->getUser(),
				'lastPostTime' => $this->findLastPostTime($recentPosts)
			]);
		} catch(ItemNotFoundException $exception) {
			return $this->error($exception->getMessage());
		} catch(ModelException $exception) {
			return $this->error($exception->getMessage());
		}
	}
	
	/**
	 * @Route("/channel/{id}/feed", name="discussion_api_feed")
	 */
	public function apiFeedAction($id, Request $request, Membership $membership)
	{
		try {
			$lastPostTime = $request->get('lastPostTime');
			$repository = $this->get(self::REPOSITORY);
			$repository->setProject($this->getActiveProject());
			$hierarchical = $membership->getPlace();
			
			$subchannel = $repository->getSubchannel((int) $id, $hierarchical);
			if (!$subchannel->getChannel()->isVisible($hierarchical)) {
				return new JsonResponse(['success' => self::CODE_FAILURE]);
			}
			return $this->prepareRecentPosts($subchannel, $lastPostTime);
		} catch(ItemNotFoundException $exception) {
			return new JsonResponse(['success' => 0, 'error' => $exception->getMessage()]);
		} catch(ModelException $exception) {
			return new JsonResponse(['success' => 0, 'error' => $exception->getMessage()]);
		}
	}
	
	/**
	 * @Route("/channel/{id}/post", name="discussion_api_post")
	 */
	public function apiPostAction($id, Request $request, Membership $membership)
	{
		try {
			$repository = $this->get(self::REPOSITORY);
			$repository->setProject($this->getActiveProject());
			$hierarchical = $membership->getPlace();
			
			$subchannel = $repository->getSubchannel((int) $id, $hierarchical);
			if (!$subchannel->getChannel()->isVisible($hierarchical)) {
				return new JsonResponse(['success' => self::CODE_FAILURE]);
			}
			if (!$repository->publish($subchannel, $request->get('content'), $this->getUser(), $hierarchical)) {
				return new JsonResponse(['success' => self::CODE_FAILURE]);
			}
			return $this->prepareRecentPosts($subchannel, time() + 86400);
		} catch(ItemNotFoundException $exception) {
			return new JsonResponse(['success' => 0, 'error' => $exception->getMessage()]);
		} catch(ModelException $exception) {
			return new JsonResponse(['success' => 0, 'error' => $exception->getMessage()]);
		}
	}
	
	private function prepareRecentPosts(Subchannel $subchannel, int $lastPostTime): JsonResponse
	{
		$recentPosts = $subchannel->getRecentPostsSince(
			$this->get('cantiga.discussion.adapter'),
			self::SERVED_PORTION,
			$lastPostTime);

		if (sizeof($recentPosts) == 0) {
			return new JsonResponse(['success' => self::CODE_END]);
		}
		return new JsonResponse([
			'success' => self::CODE_SUCCESS,
			'days' => $this->formatResults($recentPosts)
		]);
	}
	
	private function formatResults(array $recentPosts): array
	{
		$timeFormatter = $this->get('cantiga.time');
		$lastPostTime = 0;
		foreach ($recentPosts as &$day) {
			$day['timeFormatted'] = $timeFormatter->format(TimeFormatter::FORMAT_DATE_LONG, $day['time']);
			foreach ($day['posts'] as &$post) {
				$post['createdAtFormatted'] = $timeFormatter->format(TimeFormatter::FORMAT_LONG, $post['createdAt']);
				$post['profileUrl'] = $this->generateUrl('memberlist_profile', ['slug' => $this->getSlug(), 'id' => $post['userId']]);
			}
		}
		return $recentPosts;
	}
	
	private function findLastPostTime(array $recentPosts): int
	{
		$lastPostTime = 0;
		foreach ($recentPosts as $day) {
			foreach ($day['posts'] as $post) {
				$lastPostTime = $post['createdAt'];
			}
		}
		return $lastPostTime;
	}
	
	private function error(string $message)
	{
		return $this->showPageWithError($this->trans($message), 'discussion_index', ['slug' => $this->getSlug()]);
	}
}
