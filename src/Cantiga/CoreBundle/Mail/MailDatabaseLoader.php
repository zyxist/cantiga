<?php
namespace Cantiga\CoreBundle\Mail;

use Doctrine\DBAL\Connection;
use Cantiga\CoreBundle\CoreTables;
use Cantiga\CoreBundle\Exception\MailException;

/**
 * Mail content is stored in the database, so that it can be edited.
 *
 * @author Tomasz Jędrzejewski
 */
class MailDatabaseLoader implements MailLoaderInterface
{
	/**
	 * @var Connection
	 */
	private $conn;
	private $cachedMetadata = [];
	private $locale;
	
	public function __construct(Connection $conn, $fallbackLocale)
	{
		$this->conn = $conn;
		$this->locale = $fallbackLocale;
	}
	
	public function setLocale($locale)
	{
		$this->locale = $locale;
	}
	
	public function getCacheKey($place)
	{
		return sha1($place.':'.$this->locale);
	}
	
	public function getSubject($mailTemplate)
	{
		$metadata = $this->getMetadata($mailTemplate);
		return $metadata['subject'];
	}

	public function getSource($place)
	{
		return $this->conn->fetchColumn('SELECT `content` FROM `'.CoreTables::MAIL_TBL.'` WHERE `place` = :place AND `locale` = :locale', [':place' => $place, ':locale' => $this->locale]);
	}

	public function isFresh($place, $time)
	{
		$meta = $this->getMetadata($place);
		return $meta['lastUpdate'] < $time;
	}
	
	private function getMetadata($place)
	{
		if (!empty($this->cachedMetadata[$place])) {
			return $this->cachedMetadata[$place];
		}
		$metadata = $this->conn->fetchAssoc('SELECT `id`, `subject`, `lastUpdate` FROM `'.CoreTables::MAIL_TBL.'` WHERE `place` = :place AND `locale` = :locale', [':place' => $place, ':locale' => $this->locale]);
		if (empty($metadata)) {
			throw new MailException('No such mail template: '.$place.' with locale '.$this->locale);
		}
		return $this->cachedMetadata[$place] = $metadata;
	}
}
