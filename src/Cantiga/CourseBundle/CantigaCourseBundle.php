<?php
namespace Cantiga\CourseBundle;

use Cantiga\CoreBundle\Api\Modules;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class CantigaCourseBundle extends Bundle
{
	public function boot()
	{
		Modules::registerModule('course', 'Course module');
	}
}
