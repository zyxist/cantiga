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
namespace WIO\EdkBundle\Entity;

use Cantiga\CoreBundle\Entity\Area;
use Cantiga\Metamodel\Exception\ModelException;
use Doctrine\DBAL\Connection;
use Symfony\Component\Translation\TranslatorInterface;
use WIO\EdkBundle\EdkTables;

class EdkAreaNotes
{
	private $area;
	/**
	 * Additional notes related to the area.
	 * @var array
	 */
	private $notes = [];
	
	public static function fetchNotes(Connection $conn, Area $area)
	{
		$item = new EdkAreaNotes($area);
		$notes = $conn->fetchAll('SELECT * FROM `'.EdkTables::AREA_NOTE_TBL.'` WHERE `areaId` = :areaId', [':areaId' => $area->getId()]);
		foreach ($notes as $note) {
			$item->notes[$note['noteType']] = $note['content'];
		}
		return $item;
	}
	
	private function __construct($area)
	{
		$this->area = $area;
	}
	
	public function getEditableNote($type)
	{
		if (!isset($this->notes[$type])) {
			return '';
		}
		return $this->notes[$type];
	}
	
	public function getFullEditableNote(TranslatorInterface $translator, $type)
	{
		foreach (self::getNoteTypes() as $id => $name) {
			if ($id == $type) {
				$content = $this->getEditableNote($id);
				return ['id' => $id, 'name' => $translator->trans($name, [], 'edk'), 'content' => $content, 'editable' => $content];
			}
		}
		return ['id' => 0, 'name' => '', 'content' => ''];
	}
	
	public function getFullNoteInformation(TranslatorInterface $translator)
	{
		$result = [];
		foreach (self::getNoteTypes() as $id => $name) {
			$content = $this->getEditableNote($id);
			$result[] = ['id' => $id, 'name' => $translator->trans($name, [], 'edk'), 'content' => $content, 'editable' => $content];
		}
		return $result;
	}
	
	/**
	 * @param Connection $conn Database connection
	 * @param int $type Note type (numbers from 1 to 4)
	 * @param string $content New content
	 * @throws ModelException
	 */
	public function saveEditableNote(Connection $conn, $type, $content)
	{
		if ($type < 1 || $type > 4) {
			throw new ModelException('Invalid note type.');
		}

		if (empty($content)) {
			$html = '';
			$conn->delete(EdkTables::AREA_NOTE_TBL, array('areaId' => $this->area->getId(), 'noteType' => $type));
			unset($this->notes[$type]);
		} else {
			$stmt = $conn->prepare('INSERT INTO `' . EdkTables::AREA_NOTE_TBL . '` (`areaId`, `noteType`, `content`, `lastUpdatedAt`) VALUES(:routeId, :noteType, :content, :lastUpdatedAt)'
				. ' ON DUPLICATE KEY UPDATE `content` = VALUES(`content`), `lastUpdatedAt` = VALUES(`lastUpdatedAt`)');
			$stmt->bindValue(':routeId', $this->area->getId());
			$stmt->bindValue(':noteType', $type);
			$stmt->bindValue(':content', $content);
			$stmt->bindValue(':lastUpdatedAt', time());
			$stmt->execute();
			$this->notes[$type] = $content;
		}
	}
	
	public static function getNoteTypes()
	{
		return [
			1 => 'EwcBeginningNote',
			2 => 'EwcRegistrationNote',
			3 => 'EwcAdditionalNote'
		];
	}
}
