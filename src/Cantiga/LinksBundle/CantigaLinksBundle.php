<?php
namespace Cantiga\LinksBundle;

use Cantiga\CoreBundle\Api\Modules;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class CantigaLinksBundle extends Bundle
{
	public function boot()
	{
		Modules::registerModule('links', 'Links module');
	}
}
