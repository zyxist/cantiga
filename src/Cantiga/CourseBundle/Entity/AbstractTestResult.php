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

use Cantiga\Metamodel\DataMappers;

/**
 * The course results can be viewed from the point of the individual user
 * or the entire area (the first user that passes also passes the course for
 * the entire area). Here you can find the common code to represent both
 * views on the results.
 *
 * @author Tomasz Jędrzejewski
 */
abstract class AbstractTestResult
{
	protected $trialNumber;
	protected $startedAt;
	protected $completedAt;
	protected $result;
	protected $totalQuestions;
	protected $passedQuestions;
	
	public function getTrialNumber()
	{
		return $this->trialNumber;
	}
	
	public function getStartedAt()
	{
		return $this->startedAt;
	}

	public function getCompletedAt()
	{
		return $this->completedAt;
	}

	public function getResult()
	{
		return $this->result;
	}

	public function getTotalQuestions()
	{
		return $this->totalQuestions;
	}

	public function getPassedQuestions()
	{
		return $this->passedQuestions;
	}
	
	public function setTrialNumber($trialNumber)
	{
		$this->trialNumber = $trialNumber;
		return $this;
	}

	public function setStartedAt($startedAt)
	{
		DataMappers::noOverwritingField($this->startedAt);
		$this->startedAt = $startedAt;
		return $this;
	}

	public function setCompletedAt($completedAt)
	{
		DataMappers::noOverwritingField($this->completedAt);
		$this->completedAt = $completedAt;
		return $this;
	}

	public function setResult($result)
	{
		$this->result = $result;
		return $this;
	}

	public function setTotalQuestions($totalQuestions)
	{
		$this->totalQuestions = $totalQuestions;
		return $this;
	}

	public function setPassedQuestions($passedQuestions)
	{
		$this->passedQuestions = $passedQuestions;
		return $this;
	}

	public function getPercentageResult()
	{
		return round($this->passedQuestions / $this->totalQuestions * 100.0);
	}
	
	public function isSolved()
	{
		return $this->result != Question::RESULT_UNKNOWN;
	}
}
