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

use Cantiga\Components\Hierarchy\Entity\Membership;
use Cantiga\CoreBundle\Api\Controller\AreaPageController;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use WIO\EdkBundle\Entity\EdkAreaNotes;

/**
 * @Route("/area/{slug}/notes")
 * @Security("is_granted('PLACE_MEMBER')")
 */
class AreaNoteController extends AreaPageController
{
	/**
	 * @Route("/index", name="area_note_index")
	 */
	public function indexAction(Request $request) {
		$this->breadcrumbs()
			->workgroup('area')
			->entryLink($this->trans('WWW: area information', [], 'pages'), 'area_note_index', ['slug' => $this->getSlug()]);
		return $this->render('WioEdkBundle:EdkNote:index.html.twig', array(
				'pageTitle' => 'WWW: area information',
				'pageSubtitle' => 'Edit the additional notes visible on the website next to your area',
				'ajaxReloadPage' => 'area_note_api_reload',
				'ajaxUpdatePage' => 'area_note_api_update',
				'user' => $this->getUser(),
		));
	}

	/**
	 * @Route("/api-reload", name="area_note_api_reload")
	 */
	public function apiReloadAction(Request $request, Membership $membership)
	{
		try {
			$areaNotes = EdkAreaNotes::fetchNotes($this->get('database_connection'), $membership->getPlace());
			return new JsonResponse(['success' => 1, 'notes' => $areaNotes->getFullNoteInformation($this->getTranslator())]);
		} catch (Exception $ex) {
			return new JsonResponse(['success' => 0, 'message' => $ex->getMessage()]);
		}
	}
	
	/**
	 * @Route("/api-update", name="area_note_api_update")
	 */
	public function ajaxUpdateAction(Request $request, Membership $membership)
	{
		try {
			$i = $request->get('i');
			$c = $request->get('c');
			if (empty($c)) {
				$c = null;
			}
			
			$areaNotes = EdkAreaNotes::fetchNotes($this->get('database_connection'), $membership->getPlace());
			$areaNotes->saveEditableNote($this->get('database_connection'), $i, $c);
			return new JsonResponse(['success' => 1, 'note' => $areaNotes->getFullEditableNote($this->getTranslator(), $i)]);
		} catch (Exception $ex) {
			return new JsonResponse(['success' => 0]);
		}
	}
}
