<?php
namespace Cantiga\Metamodel;

/**
 * Keeps the information about the current locale, so that the services do not have
 * to depend on requests, or be preloaded without any need just to set the locale.
 *
 * @author Tomasz Jędrzejewski
 */
class LocaleProvider
{
	private $locale;
	
	public function __construct($fallbackLocale)
	{
		$this->locale = $fallbackLocale;
	}
	
	public function getLocale()
	{
		return $this->locale;
	}

	public function setLocale($locale)
	{
		$this->locale = $locale;
		return $this;
	}
}
