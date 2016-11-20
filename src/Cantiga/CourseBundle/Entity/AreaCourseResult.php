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
namespace Cantiga\CourseBundle\Entity;

use Cantiga\CoreBundle\Entity\Area;
use Cantiga\CourseBundle\CourseTables;
use Cantiga\Metamodel\DataMappers;
use Doctrine\DBAL\Connection;

/**
 * Represents a view on the course results from the perspective of the entire area.
 * The assumption is that the first user who passes the test, also passes the test
 * for the entire area.
 *
 * @author Tomasz Jędrzejewski
 */
class AreaCourseResult extends AbstractTestResult
{
	/**
	 * @var Area
	 */
	private $area;
	/**
	 * @var Course
	 */
	private $course;
	
	public static function fetchResult(Connection $conn, Area $area, Course $course)
	{
		$data = $conn->fetchAssoc('SELECT r.* '
			. 'FROM `'.CourseTables::COURSE_RESULT_TBL.'` r '
			. 'INNER JOIN `'.CourseTables::COURSE_AREA_RESULT_TBL.'` a ON a.`courseId` = r.`courseId` AND a.`userId` = r.`userId` '
			. 'WHERE a.`areaId` = :areaId AND a.`courseId` = :courseId', 
			array(':areaId' => $area->getId(), ':courseId' => $course->getId())
		);
		$result = new AreaCourseResult();
		$result->area = $area;
		$result->course = $course;
		$result->result = Question::RESULT_UNKNOWN;
		if (false === $data) {
			return $result;
		}
		DataMappers::fromArray($result, $data);
		return $result;
	}
}
