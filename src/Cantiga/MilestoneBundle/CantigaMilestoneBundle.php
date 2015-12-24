<?php
namespace Cantiga\MilestoneBundle;

use Cantiga\CoreBundle\Api\Modules;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class CantigaMilestoneBundle extends Bundle
{
	public function boot()
	{
		Modules::registerModule('milestone', 'Milestone module');
	}
}
