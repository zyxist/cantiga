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
		$tpl = $this->getActualPart($place);
		$locale = $this->getLocalePart($place);
		
		return $this->conn->fetchColumn('SELECT `content` FROM `'.CoreTables::MAIL_TBL.'` WHERE `place` = :place AND `locale` = :locale', [':place' => $tpl, ':locale' => $locale]);
	}

	public function isFresh($place, $time)
	{
		$meta = $this->getMetadata($place);
		return $meta['lastUpdate'] < $time;
	}
	
	private function getMetadata($place)
	{
		$tpl = $this->getActualPart($place);
		$locale = $this->getLocalePart($place);
		
		if (!empty($this->cachedMetadata[$place])) {
			return $this->cachedMetadata[$place];
		}
		$metadata = $this->conn->fetchAssoc('SELECT `id`, `subject`, `lastUpdate` FROM `'.CoreTables::MAIL_TBL.'` WHERE `place` = :place AND `locale` = :locale', [':place' => $tpl, ':locale' => $locale]);
		if (empty($metadata)) {
			throw new MailException('No such mail template: '.$place.' with locale '.$this->locale);
		}
		return $this->cachedMetadata[$place] = $metadata;
	}
	
	private function getActualPart($place)
	{
		if (($id = strpos($place, '@@')) !== false) {
			return substr($place, 0, $id);
		}
		return $place;
	}
	
	private function getLocalePart($place)
	{
		if (($id = strpos($place, '@@')) !== false) {
			return substr($place, $id + 2);
		}
		return $this->locale;
	}
}
