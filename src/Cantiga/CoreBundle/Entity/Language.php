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
 * Description of Language
 *
 * @author Tomasz Jędrzejewski
 */
class Language implements IdentifiableInterface, InsertableEntityInterface, EditableEntityInterface, RemovableEntityInterface
{
	private $id;
	private $name;
	private $locale;

	public static function fromArray($array, $prefix = '')
	{
		$item = new Language;
		DataMappers::fromArray($item, $array, $prefix);
		return $item;
	}
	
	public function getId()
	{
		return $this->id;
	}
	
	public function setId($id)
	{
		DataMappers::noOverwritingId($this->id);
		$this->id = $id;
		return $this;
	}
	
	public function getName()
	{
		return $this->name;
	}

	public function getLocale()
	{
		return $this->locale;
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
	
	public function insert(Connection $conn)
	{
		$conn->insert(
			CoreTables::LANGUAGE_TBL,
			DataMappers::pick($this, ['name', 'locale'])
		);
		return $conn->lastInsertId();
	}

	public function update(Connection $conn)
	{
		return $conn->update(
			CoreTables::LANGUAGE_TBL,
			DataMappers::pick($this, ['name', 'locale']),
			DataMappers::pick($this, ['id'])
		);
	}
	
	public function canRemove()
	{
		return true;
	}
	
	public function remove(Connection $conn)
	{
		$conn->delete(CoreTables::LANGUAGE_TBL, DataMappers::pick($this, ['id']));
	}
}
