<?php
namespace Cantiga\Metamodel;

/**
 * Utility for formatting the times and dates. We assume that all the time is kept as 64-bit UTC Unix timestamp.
 * 
 * @author Tomasz Jędrzejewski
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
	public function format($format, $utcTimestamp);
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
}
