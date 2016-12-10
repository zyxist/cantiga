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
 * Actual implementation of the time formatting service. Note that at some point of the request,
 * the service must be configured with the translator and the timezone, which depends on the logged
 * user settings. Until then, all the methods return empty strings.
 */
class TimeFormatter implements TimeFormatterInterface
{
	const CALENDAR_FMT = 'd MMMM';
	
	private static $LENGTHS = array(60, 60, 24, 7, 4.35, 12, 10);
	private static $PERIODS = array(
		'one second ago|%count% seconds ago',
		'a minute ago|%count% minutes ago',
		'a hour ago|%count% hours ago',
		'a day ago|%count% days ago',
		'a week ago|%count% weeks ago',
		'a month ago|%count% months ago',
		'a year ago|%count% years ago',
		'a decade ago|%count% decades ago'
	);
	
	private $formatShort;
	private $formatLong;
	private $formatDateLong;
	private $formatDateShort;
	private $formatCalendar;
	
	private $translator;
	private $locale;
	private $timezone;
	
	public function __construct($fallbackTimezone)
	{
		$this->timezone = new \DateTimeZone($fallbackTimezone);
	}
	
	public function configure($translator, $locale, $timezoneInfo)
	{
		$this->translator = $translator;
		$this->locale = $locale;
		if (!empty($timezoneInfo)) {
			$this->timezone = new \DateTimeZone($timezoneInfo);
		}

		$this->formatLong = new \IntlDateFormatter($locale, \IntlDateFormatter::LONG, \IntlDateFormatter::SHORT);
		$this->formatShort = new \IntlDateFormatter($locale, \IntlDateFormatter::SHORT, \IntlDateFormatter::SHORT);
		$this->formatCalendar = new \IntlDateFormatter($locale, \IntlDateFormatter::LONG, \IntlDateFormatter::LONG);
		$this->formatDateLong = new \IntlDateFormatter($locale, \IntlDateFormatter::LONG, \IntlDateFormatter::NONE);
		$this->formatDateShort = new \IntlDateFormatter($locale, \IntlDateFormatter::SHORT, \IntlDateFormatter::NONE);
		$this->formatCalendar->setPattern(self::CALENDAR_FMT);
	}
	
	public function ago($utcTimestamp)
	{
		if (null === $this->translator) {
			return '';
		}
		if (empty($utcTimestamp)) {
			return $this->translator->trans('Never', [], 'general');
		}		
		$diff = time() - $utcTimestamp;
		
		if($diff < 60) {
			return $this->translator->trans('Just now', [], 'general');
		}
		
		for($j = 0; $j < sizeof(self::$LENGTHS) && $diff > self::$LENGTHS[$j]; $j++) {
			$diff /= self::$LENGTHS[$j];
		}
		$diff = round($diff);
		return $this->translator->transChoice(self::$PERIODS[$j], $diff, array('%count%' => $diff), 'general');
	}

	public function format(int $format, $utcTimestamp)
	{
		if (null === $this->translator) {
			return '';
		}
		switch((int) $format) {
			case self::FORMAT_LONG:
				return $this->formatLong->format($utcTimestamp);
			case self::FORMAT_SHORT:
				return $this->formatShort->format($utcTimestamp);
			case self::FORMAT_DATE_LONG:
				return $this->formatDateLong->format($utcTimestamp);
			case self::FORMAT_DATE_SHORT:
				return $this->formatDateShort->format($utcTimestamp);
			case self::FORMAT_MONTH_YEAR:
				return $this->formatCalendar->format($utcTimestamp);
			default:
				throw new \InvalidArgumentException('Unknown time format: '+$format);
		}
	}
	
	public function formatDate(array $date)
	{
		if (!empty($date['year']) && !empty($date['month']) && !empty($date['day'])) {
			return $this->formatDateLong->format(new \DateTime($date['year'].'-'.$date['month'].'-'.$date['day']));
		}
		return '---';
	}

	public function getTimezone(): DateTimeZone
	{
		return $this->timezone;
	}
}
