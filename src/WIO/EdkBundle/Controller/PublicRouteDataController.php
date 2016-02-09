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

use Cantiga\CoreBundle\Api\Controller\PublicPageController;
use Cantiga\Metamodel\Exception\ItemNotFoundException;
use DateInterval;
use DateTime;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/api/edk/route-files")
 */
class PublicRouteDataController extends PublicPageController
{
	const REPOSITORY_NAME = 'wio.edk.repo.route';
	
	/**
	 * @Route("/{slug}/download/description", name="public_edk_download_description")
	 */
	public function downloadDescriptionAction($slug, Request $request)
	{
		try {
			$repository = $this->get(self::REPOSITORY_NAME);
			$response = new Response();
			$route = $repository->getItemBySlug($slug);
			$route->downloadDescription($this->get('cantiga.files'), $response);
			return $response;
		} catch(ItemNotFoundException $exception) {
			throw $this->createNotFoundException('Plik nie istnieje.');
		}
	}
	
	/**
	 * @Route("/{slug}/download/map", name="public_edk_download_map")
	 */
	public function downloadMapAction($slug, Request $request)
	{
		try {
			$repository = $this->get(self::REPOSITORY_NAME);
			$response = new Response();
			$route = $repository->getItemBySlug($slug);
			$route->downloadMap($this->get('cantiga.files'), $response);
			return $response;
		} catch(ItemNotFoundException $exception) {
			throw $this->createNotFoundException('Plik nie istnieje.');
		}
	}
	
	/**
	 * @Route("/{slug}/download/gps", name="public_edk_download_gps")
	 */
	public function downloadGPSAction($slug, Request $request)
	{
		try {
			$repository = $this->get(self::REPOSITORY_NAME);
			$response = new Response();
			$route = $repository->getItemBySlug($slug, false);
			$route->downloadGpsTrack($this->get('cantiga.files'), $response);
			return $response;
		} catch(ItemNotFoundException $exception) {
			throw $this->createNotFoundException('Plik nie istnieje.');
		}
	}
}
