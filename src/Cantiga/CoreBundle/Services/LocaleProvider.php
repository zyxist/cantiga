<?php
/*
 * This file is part of Cantiga Project. Copyright 2016-2017 Cantiga contributors.
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
 * along with Cantiga; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
namespace Cantiga\CoreBundle\Services;
use Cantiga\Components\Application\LocaleProviderInterface;

/**
 * Keeps the information about the current locale, so that the services do not have
 * to depend on requests, or be preloaded without any need just to set the locale.
 */
class LocaleProvider implements LocaleProviderInterface
{
	private $locale;
	private $known = false;

	public function __construct($fallbackLocale)
	{
		$this->locale = $fallbackLocale;
	}

	public function setLocale(string $locale): self
	{
		$this->locale = $locale;
		$this->known = true;
		return $this;
	}

	public function isLocaleKnown(): bool
	{
		return $this->known;
	}

	public function findLocale(): string
	{
		return $this->locale;
	}

	public function findKnownLocale(): string
	{
		if (!$this->known) {
			throw new \LogicException('The locale is not known yet. The method is probably called too early.');
		}
		return $this->locale;
	}
}
