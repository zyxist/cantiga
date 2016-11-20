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
namespace Cantiga\CourseBundle;

use Cantiga\CoreBundle\Api\AppTexts;
use Cantiga\CoreBundle\Api\Modules;
use Cantiga\MilestoneBundle\Api\Activators;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class CantigaCourseBundle extends Bundle
{
	public function boot()
	{
		Modules::registerModule('course', 'Course module');
		
		AppTexts::registerName(CourseTexts::AREA_COURSE_LIST_TEXT);
		AppTexts::registerName(CourseTexts::GROUP_COURSE_LIST_TEXT);
		
		Activators::registerActivator('course.completed');
	}
}
