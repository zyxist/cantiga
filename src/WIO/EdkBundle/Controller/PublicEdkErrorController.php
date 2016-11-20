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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;


/**
 * @Route("/pub/edk")
 */
class PublicEdkErrorController extends PublicPageController
{
	/**
	 * @Route("/blad", name="public_edk_error", defaults={"_localeFromQuery" = true})
	 */
	public function completedAction(Request $request)
	{
		return $this->render('WioEdkBundle:Public:public-error.html.twig', [
			'message' => $this->trans('We are sorry, but we cannot find the specified edition of Extreme Way of the Cross.', [], 'public'),
			'currentPage' => 'public_edk_error',
		]);
	}
}
