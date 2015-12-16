<?php
namespace Cantiga\CoreBundle\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Utilities for the information pages.
 *
 * @author Tomasz Jędrzejewski
 */
trait InformationTrait
{
	protected function renderInformationExtensions($extensionPoint, Request $request, $item)
	{
		$extensions = $this->getExtensionPoints()->findImplementations($extensionPoint, $this->getExtensionPointFilter());
		if (false === $extensions) {
			$extensions = [];
		}
		usort($extensions, function($a, $b) {
			return $a->getPriority() - $b->getPriority();
		});
		$html = '';
		foreach($extensions as $extension) {
			$html .= $extension->render($this, $request, $item);
		}
		return $html;
	}
}
