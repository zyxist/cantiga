<?php
namespace Cantiga\TrainingBundle;

use Cantiga\CoreBundle\Api\Modules;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class CantigaTrainingBundle extends Bundle
{
	public function boot()
	{
		Modules::registerModule('training', 'Training module');
	}
}
