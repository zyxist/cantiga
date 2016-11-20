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
namespace Cantiga\CoreBundle\Generator;

/**
 * Generates the code for a simple entity.
 *
 * @author Tomasz Jędrzejewski
 */
class EntityGenerator extends Generator
{
	private $entityName;
	private $service;
	
	public function __construct(ReportInterface $reportIfc, $entityName, $service)
	{
		parent::__construct($reportIfc);
		$this->entityName = $entityName;
		$this->service = $service;
	}

	public function generate()
	{
		$this->createEntityFile();
		$this->createRepositoryFile();
		$this->createFormFile();
		$this->printServiceEntry();
	}
	
	private function createEntityFile()
	{
		$tables = $this->genTableRepository();
		$capitalized = strtoupper($this->entityName);
$code = <<<EOF
<?php
namespace {$this->genNamespace('Entity')};

use Doctrine\DBAL\Connection;
use {$this->genNamespace($tables)};
use Cantiga\Metamodel\Capabilities\EditableEntityInterface;
use Cantiga\Metamodel\Capabilities\IdentifiableInterface;
use Cantiga\Metamodel\Capabilities\InsertableEntityInterface;
use Cantiga\Metamodel\Capabilities\RemovableEntityInterface;
use Cantiga\Metamodel\DataMappers;

class {$this->entityName} implements IdentifiableInterface, InsertableEntityInterface, EditableEntityInterface, RemovableEntityInterface
{
	private \$id;
	private \$name;

	public static function fromArray(\$array, \$prefix = '')
	{
		\$item = new {$this->entityName};
		DataMappers::fromArray(\$item, \$array, \$prefix);
		return \$item;
	}
	
	public function getId()
	{
		return \$this->id;
	}
	
	public function setId(\$id)
	{
		DataMappers::noOverwritingId(\$this->id);
		\$this->id = \$id;
		return \$this;
	}
	
	public function getName()
	{
		return \$this->name;
	}

	public function setName(\$name)
	{
		\$this->name = \$name;
		return \$this;
	}

	public function insert(Connection \$conn)
	{
		\$conn->insert(
			{$tables}::{$capitalized}_TBL,
			DataMappers::pick(\$this, ['name'])
		);
		return \$conn->lastInsertId();
	}

	public function update(Connection \$conn)
	{
		return \$conn->update(
			{$tables}::{$capitalized}_TBL,
			DataMappers::pick(\$this, ['name']),
			DataMappers::pick(\$this, ['id'])
		);
	}
	
	public function remove(Connection \$conn)
	{
		\$conn->delete({$tables}::{$capitalized}_TBL, DataMappers::pick(\$this, ['id']));
	}
}
EOF;
		$this->save('Entity/'.$this->entityName.'.php', $code);
	}
	
	private function createRepositoryFile()
	{
		$tables = $this->genTableRepository();
		$capitalized = strtoupper($this->entityName);
$code = <<<EOF
<?php
namespace {$this->genNamespace('Repository')};

use Doctrine\DBAL\Connection;
use PDO;
use {$this->genNamespace($tables)};
use {$this->genNamespace('Entity\\'.$this->entityName)};
use Cantiga\Metamodel\DataTable;
use Cantiga\Metamodel\Exception\ItemNotFoundException;
use Cantiga\Metamodel\Form\EntityTransformerInterface;
use Cantiga\Metamodel\QueryBuilder;
use Cantiga\Metamodel\QueryClause;
use Cantiga\Metamodel\Transaction;

class {$this->entityName}Repository implements EntityTransformerInterface
{
	/**
	 * @var Connection 
	 */
	private \$conn;
	/**
	 * @var Transaction
	 */
	private \$transaction;
	
	public function __construct(Connection \$conn, Transaction \$transaction)
	{
		\$this->conn = \$conn;
		\$this->transaction = \$transaction;
	}
	
	/**
	 * @return DataTable
	 */
	public function createDataTable()
	{
		\$dt = new DataTable();
		\$dt->id('id', 'i.id')
			->searchableColumn('name', 'i.name');
		return \$dt;
	}
	
	public function listData(DataTable \$dataTable)
	{
		\$qb = QueryBuilder::select()
			->field('i.id', 'id')
			->field('i.name', 'name')
			->from({$tables}::{$capitalized}_TBL, 'i');	
		
		\$recordsTotal = QueryBuilder::copyWithoutFields(\$qb)
			->field('COUNT(id)', 'cnt')
			->where(\$dataTable->buildCountingCondition())
			->fetchCell(\$this->conn);
		\$recordsFiltered = QueryBuilder::copyWithoutFields(\$qb)
			->field('COUNT(id)', 'cnt')
			->where(\$dataTable->buildFetchingCondition())
			->fetchCell(\$this->conn);

		\$dataTable->processQuery(\$qb);
		return \$dataTable->createAnswer(
			\$recordsTotal,
			\$recordsFiltered,
			\$qb->where(\$dataTable->buildFetchingCondition())->fetchAll(\$this->conn)
		);
	}
	
	/**
	 * @return {$this->entityName}
	 */
	public function getItem(\$id)
	{
		\$this->transaction->requestTransaction();
		\$data = \$this->conn->fetchAssoc('SELECT * FROM `'.CoreTables::{$capitalized}_TBL.'` WHERE `id` = :id', [':id' => \$id]);
		
		if(null === \$data) {
			\$this->transaction->requestRollback();
			throw new ItemNotFoundException('The specified item has not been found.', \$id);
		}

		return {$this->entityName}::fromArray(\$data);
	}
	
	public function update({$this->entityName} \$item)
	{
		\$this->transaction->requestTransaction();
		\$item->update(\$this->conn);
	}
	
	public function getFormChoices()
	{
		\$this->transaction->requestTransaction();
		\$stmt = \$this->conn->query('SELECT `id`, `name` FROM `'.{$tables}::{$capitalized}_TBL.'` ORDER BY `name`');
		\$result = array();
		while (\$row = \$stmt->fetch(PDO::FETCH_ASSOC)) {
			\$result[\$row['id']] = \$row['name'];
		}
		\$stmt->closeCursor();
		return \$result;
	}

	public function transformToEntity(\$key)
	{
		return \$this->getItem(\$key);
	}

	public function transformToKey(\$entity)
	{
		return \$entity->getId();
	}
}
EOF;
		$this->save('Repository/'.$this->entityName.'Repository.php', $code);
	}

	private function createFormFile()
	{
$code = <<<EOF
<?php
namespace {$this->genNamespace('Form')};

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class {$this->entityName}Form extends AbstractType
{
	public function buildForm(FormBuilderInterface \$builder, array \$options)
	{
		\$builder
			->add('name', 'text', array('label' => 'Name'))
			->add('save', 'submit', array('label' => 'Save'));
	}

	public function getName()
	{
		return '{$this->entityName}';
	}
}
EOF;
		$this->save('Form/'.$this->entityName.'Form.php', $code);
	}
	
	private function printServiceEntry()
	{
		$fullName = $this->genNamespace('Repository').'\\'.$this->entityName.'Repository';
		$this->reportIfc->reportStatus('<info>Add the following code to services.yml:</info>');
		$this->reportIfc->reportStatus(<<<EOF
    {$this->service}:
        class:     "{$fullName}"
        arguments: ["@database_connection", "@cantiga.transaction"]			
EOF
);
		
	}
}
