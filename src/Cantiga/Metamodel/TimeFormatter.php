<?php
namespace Cantiga\Metamodel;

/**
 * Actual implementation of the time formatting service. Note that at some point of the request,
 * the service must be configured with the translator and the timezone, which depends on the logged
 * user settings. Until then, all the methods return empty strings.
 *
 * @author Tomasz Jędrzejewski
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

		$this->formatLong = new \IntlDateFormatter($locale, \IntlDateFormatter::LONG, \IntlDateFormatter::LONG);
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
		
		for($j = 0; $diff > self::$LENGTHS[$j] && $j < sizeof(self::$LENGTHS); $j++) {
			$diff /= self::$LENGTHS[$j];
		}
		$diff = round($diff);
		return $this->translator->transChoice(self::$PERIODS[$j], $diff, array('%count%' => $diff), 'general');
	}

	public function format($format, $utcTimestamp)
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
		return $this->formatDateLong->format(new \DateTime($date['year'].'-'.$date['month'].'-'.$date['day']));
	}
}
