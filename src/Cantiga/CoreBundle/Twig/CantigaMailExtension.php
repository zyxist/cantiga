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

namespace Cantiga\CoreBundle\Twig;

use Cantiga\Metamodel\TimeFormatterInterface;
use ReflectionClass;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Twig_Extension;
use Twig_SimpleFilter;
use Twig_SimpleFunction;

/**
 * Extension for parsing mail templates which use a different Twig
 * environment, with different set of plugins. Here we provide basic
 * functionality, such as link generation optimized for e-mails.
 *
 * @author Tomasz Jędrzejewski
 */
class CantigaMailExtension extends Twig_Extension
{

	/**
	 * @var RouterInterface
	 */
	private $router;

	/**
	 * @var TimeFormatterInterface
	 */
	private $timeFormatter;

	/**
	 * @var TranslatorInterface
	 */
	private $translator;

	public function __construct(RouterInterface $router, TimeFormatterInterface $timeFormatter, TranslatorInterface $translator)
	{
		$this->router = $router;
		$this->timeFormatter = $timeFormatter;
		$this->translator = $translator;
	}

	public function getName()
	{
		return 'mail';
	}

	public function getGlobals()
	{
		$class = new ReflectionClass('Cantiga\Metamodel\TimeFormatterInterface');
		$constants = $class->getConstants();

		return array('TimeFormatter' => $constants);
	}

	public function getFunctions()
	{
		return array(
			new Twig_SimpleFunction('path', [$this, 'mailPath'], array('is_safe' => array('html'))),
			new Twig_SimpleFunction('format_time', [$this, 'formatTime']),
			new Twig_SimpleFunction('ago', [$this, 'ago']),
		);
	}

	public function getFilters()
	{
		return array(
			new Twig_SimpleFilter('trans', [$this, 'trans']),
		);
	}

	/**
	 * Generates a URL with the Symfony router, but optimized for e-mails. The URL
	 * contains the full domain name.
	 * 
	 * @param string $routeName Name of the route
	 * @param array $args Route arguments
	 * @return string
	 */
	public function mailPath($routeName, array $args = [])
	{
		return $this->router->generate($routeName, $args, RouterInterface::ABSOLUTE_URL);
	}

	public function formatTime($format, $utcTimestamp)
	{
		return $this->timeFormatter->format($format, $utcTimestamp);
	}

	public function ago($utcTimestamp)
	{
		return $this->timeFormatter->ago($utcTimestamp);
	}

	public function trans($string, array $args = [], $domain = null)
	{
		return $this->translator->trans($string, $args, $domain);
	}

}
