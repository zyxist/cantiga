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
namespace Cantiga\Metamodel;

use \DateTimeZone;

/**
 * Utility for formatting the times and dates. We assume that all the time is kept as 64-bit UTC Unix timestamp.
 */
interface TimeFormatterInterface
{
	/**
	 * Prints out the full date with hours, minutes and seconds.
	 */
	const FORMAT_LONG = 0;
	/**
	 * Prints out the short date with hours and minutes.
	 */
	const FORMAT_SHORT = 1;
	/**
	 * Prints out the full date, but without time.
	 */
	const FORMAT_DATE_LONG = 2;
	/**
	 * Prints out the short date, but without time.
	 */
	const FORMAT_DATE_SHORT = 3;
	/**
	 * Prints out just the month and year.
	 */
	const FORMAT_MONTH_YEAR = 4;
	
	/**
	 * Formats the given UTC timestamp in the human-readable form.
	 * 
	 * @param int $format
	 * @param int $utcTimestamp UTC Unix timestamp
	 * @return string
	 */
	public function format(int $format, $utcTimestamp);
	/**
	 * Formats a date given as an array of cells 'year', 'month', 'day'.
	 * 
	 * @param array $date
	 * @return string
	 */
	public function formatDate(array $date);
	/**
	 * Prints out the time in the 'ago' convention (how long ago the given event has happened).
	 * 
	 * @param int $utcTimestamp UTC Unix timestamp
	 */
	public function ago($utcTimestamp);
	/**
	 * @return Current timezone.
	 */
	public function getTimezone(): DateTimeZone;
}
