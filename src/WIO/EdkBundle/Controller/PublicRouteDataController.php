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

use Cantiga\CoreBundle\Api\Controller\PublicPageController;
use Cantiga\Metamodel\Exception\ItemNotFoundException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WIO\EdkBundle\EdkSettings;

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
			$fileInfo = $repository->getFileDownloadInformation($slug, 'descriptionFile', EdkSettings::GUIDE_MIRROR_URL);
			$response = $this->redirectToMirror($fileInfo);
			if (null === $response) {
				$response = new Response();
				$fileRepository = $this->get('cantiga.files');
				$fileRepository->downloadFile($fileInfo['file'], 'edk-guide-route-' . $fileInfo['id'] . '.pdf', 'application/pdf', $response);
			}
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
			$fileInfo = $repository->getFileDownloadInformation($slug, 'mapFile', EdkSettings::MAP_MIRROR_URL);
			$response = $this->redirectToMirror($fileInfo);
			if (null === $response) {
				$response = new Response();
				$fileRepository = $this->get('cantiga.files');
				if(strpos($fileInfo['file'], '.jpg') !== false) {
					$fileRepository->downloadFile($fileInfo['file'], 'edk-map-route-' . $fileInfo['id'] . '.jpg', 'image/jpeg', $response);
				} else {
					$fileRepository->downloadFile($fileInfo['file'], 'edk-map-route-' . $fileInfo['id'] . '.pdf', 'application/pdf', $response);
				}
			}
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
			$fileInfo = $repository->getFileDownloadInformation($slug, 'gpsTrackFile', EdkSettings::GPS_MIRROR_URL);
			$response = $this->redirectToMirror($fileInfo);
			if (null === $response) {
				$response = new Response();
				$fileRepository = $this->get('cantiga.files');
				$fileRepository->downloadFile($fileInfo['file'], 'edk-gps-route-' . $fileInfo['id'] . '.kml', 'application/vnd.google-earth.kml+xml', $response);
			}
			return $response;
		} catch(ItemNotFoundException $exception) {
			throw $this->createNotFoundException('Plik nie istnieje.');
		}
	}
	
	/**
	 * Handles the redirects to mirrors, that can be enabled by configuring the project settings.
	 * If the method returns a NULL response, the file should be downloaded directly from the system.
	 * 
	 * @param array $fileInfo File information about the file and mirror settings
	 * @return Response
	 */
	private function redirectToMirror($fileInfo)
	{
		$setting = trim($fileInfo['setting']);
		if (empty($setting) || $setting == '---') {
			return null;
		}
		
		$ext = substr($fileInfo['file'], strrpos($fileInfo['file'], '.') + 1);
		$url = str_replace(['%HASH%', '%ID%', '%EXT%'], [$fileInfo['publicAccessSlug'], $fileInfo['id'], $ext], $fileInfo['setting']);
		return $this->redirect($url, 301);
	}
}
