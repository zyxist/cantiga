<?php
namespace Cantiga\CoreBundle\Entity;

use Doctrine\DBAL\Connection;
use Cantiga\CoreBundle\CoreTables;
use Cantiga\Metamodel\Capabilities\EditableEntityInterface;
use Cantiga\Metamodel\Capabilities\IdentifiableInterface;
use Cantiga\Metamodel\Capabilities\InsertableEntityInterface;
use Cantiga\Metamodel\Capabilities\RemovableEntityInterface;
use Cantiga\Metamodel\DataMappers;

/**
 * Description of ProjectForm
 *
 * @author Tomasz Jędrzejewski
 */
class ProjectForm implements IdentifiableInterface, InsertableEntityInterface, EditableEntityInterface, RemovableEntityInterface
{
	private $id;
	private $project;
	private $place;
	private $name;
	private $locale;
	private $version;
	private $content;
	private $user;
	private $createdAt;
	private $usage;
	
	public static function fromArray($array, $prefix = '')
	{
		$item = new ProjectForm;
		DataMappers::fromArray($item, $array, $prefix);
		return $item;
	}
	
	public function getId()
	{
		return $this->id;
	}

	public function getProject()
	{
		return $this->project;
	}

	public function getPlace()
	{
		return $this->place;
	}

	public function getName()
	{
		return $this->name;
	}

	public function getLocale()
	{
		return $this->locale;
	}

	public function getVersion()
	{
		return $this->version;
	}

	public function getContent()
	{
		return $this->content;
	}

	public function getUserId()
	{
		return $this->userId;
	}

	public function getCreatedAt()
	{
		return $this->createdAt;
	}

	public function getUsage()
	{
		return $this->usage;
	}

	public function setId($id)
	{
		DataMappers::noOverwritingId($this->id);
		$this->id = $id;
		return $this;
	}

	public function setProject($project)
	{
		$this->project = $project;
		return $this;
	}

	public function setPlace($place)
	{
		$this->place = $place;
		return $this;
	}

	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}

	public function setLocale($locale)
	{
		$this->locale = $locale;
		return $this;
	}

	public function setVersion($version)
	{
		DataMappers::noOverwritingField($this->version);
		$this->version = $version;
		return $this;
	}

	public function setContent($content)
	{
		$this->content = $content;
		return $this;
	}

	public function setUserId($userId)
	{
		$this->userId = $userId;
		return $this;
	}

	public function setCreatedAt($createdAt)
	{
		DataMappers::noOverwritingField($this->createdAt);
		$this->createdAt = $createdAt;
		return $this;
	}

	public function setUsage($usage)
	{
		$this->usage = $usage;
		return $this;
	}

	public function insert(Connection $conn)
	{
		$conn->insert(
			CoreTables::FORM_TBL,
			DataMappers::pick($this, ['projectId', 'place', 'name', 'locale'], ['lastVersion' => 1])
		);
		$id = $conn->lastInsertId();
		$this->createdAt = time();
		
		$conn->insert(CoreTables::FORM_VERSION_TBL,
			['formId' => $id, 'formVersion' => 1, 'content' => $this->content, 'userId' => $this->user->getId(), 'createdAt' => $this->createdAt, 'usage' => 0]
		);
		return $id;
	}

	public function update(Connection $conn)
	{
		$result = $conn->fetchAssoc('SELECT f.`lastVersion`, v.`usage`, v.`userId` FROM `'.CoreTables::FORM_TBL.'` f '
			. 'INNER JOIN `'.CoreTables::FORM_VERSION_TBL.'` v ON v.`formId` = f.`id` AND v.`formVersion` = f.`lastVersion` '
			. 'WHERE f.`id` = :id', [':id' => $this->id]);
		
		if (false === $result) {
			return false;
		}
		list($lastVersion, $usage, $userId) = $result;
		$this->createdAt = time();
		
		if ($usage == 0 && $userId == $this->user->getId()) {
			$modified = $conn->update(
				CoreTables::FORM_TBL,
				DataMappers::pick($this, ['place', 'name', 'locale']),
				DataMappers::pick($this, ['id'])
			);
			$conn->update(
				CoreTables::FORM_VERSION_TBL,
				DataMappers::pick($this, ['content', 'createdAt']),
				['formId' => $this->getId(), 'formVersion' => $lastVersion]
			);
			return $modified;
		} else {
			$conn->insert(CoreTables::FORM_VERSION_TBL,
				['formId' => $this->id, 'formVersion' => $lastVersion + 1, 'content' => $this->content, 'userId' => $this->user->getId(), 'createdAt' => $this->createdAt, 'usage' => 0]
			);
			
			return $conn->update(
				CoreTables::FORM_TBL,
				DataMappers::pick($this, ['place', 'name', 'locale'], ['lastVersion' => $lastVersion + 1]),
				DataMappers::pick($this, ['id'])
			);
		}
	}
	
	public function canRemove()
	{
		return true;
	}

	public function remove(Connection $conn)
	{
		
	}
}
