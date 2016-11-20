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

namespace Cantiga\CoreBundle\EventListener;

use Cantiga\CoreBundle\Exception\AreasNotSupportedException;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Http\HttpUtils;

/**
 * @author Tomasz Jędrzejewski
 */
class ErrorListener
{

	private $httpUtils;

	public function __construct(HttpUtils $httpUtils)
	{
		$this->httpUtils = $httpUtils;
	}

	public function onKernelException(GetResponseForExceptionEvent $event)
	{
		if ($event->getException() instanceof AreasNotSupportedException) {
			$subRequest = $this->httpUtils->createRequest($event->getRequest(), 'cantiga_error_areas_not_supported');
			$event->setResponse($event->getKernel()->handle($subRequest, HttpKernelInterface::SUB_REQUEST, true));
		}
	}

}
