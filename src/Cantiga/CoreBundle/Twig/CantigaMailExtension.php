<?php
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
