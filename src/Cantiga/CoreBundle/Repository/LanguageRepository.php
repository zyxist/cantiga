<?php
namespace Cantiga\CoreBundle\Repository;

use Doctrine\DBAL\Connection;
use PDO;
use Cantiga\CoreBundle\CoreTables;
use Cantiga\CoreBundle\Entity\Language;
use Cantiga\Metamodel\DataTable;
use Cantiga\Metamodel\Exception\ItemNotFoundException;
use Cantiga\Metamodel\Form\EntityTransformerInterface;
use Cantiga\Metamodel\QueryBuilder;
use Cantiga\Metamodel\Transaction;

/**
 * Description of LanguageRepository
 *
 * @author Tomasz Jędrzejewski
 */
class LanguageRepository implements EntityTransformerInterface
{
	/**
	 * @var Connection 
	 */
	private $conn;
	/**
	 * @var Transaction
	 */
	private $transaction;
	
	public function __construct(Connection $conn, Transaction $transaction)
	{
		$this->conn = $conn;
		$this->transaction = $transaction;
	}
	
	public function createDataTable()
	{
		$dt = new DataTable();
		$dt->id('id', 'l.id')
			->searchableColumn('name', 'l.name')
			->searchableColumn('locale', 'l.locale');
		return $dt;
	}
	
	public function listData(DataTable $dataTable)
	{
		$qb = QueryBuilder::select()
			->field('l.id', 'id')
			->field('l.name', 'name')
			->field('l.locale', 'locale')
			->from(CoreTables::LANGUAGE_TBL, 'l');	
		
		$recordsTotal = QueryBuilder::copyWithoutFields($qb)
			->field('COUNT(id)', 'cnt')
			->where($dataTable->buildCountingCondition($qb->getWhere()))
			->fetchCell($this->conn);
		$recordsFiltered = QueryBuilder::copyWithoutFields($qb)
			->field('COUNT(id)', 'cnt')
			->where($dataTable->buildFetchingCondition($qb->getWhere()))
			->fetchCell($this->conn);

		$dataTable->processQuery($qb);
		return $dataTable->createAnswer(
			$recordsTotal,
			$recordsFiltered,
			$qb->where($dataTable->buildFetchingCondition($qb->getWhere()))->fetchAll($this->conn)
		);
	}
	
	public function getItem($id)
	{
		$this->transaction->requestTransaction();
		$data = $this->conn->fetchAssoc('SELECT * FROM `'.CoreTables::LANGUAGE_TBL.'` WHERE `id` = :id', [':id' => $id]);
		
		if(null === $data) {
			$this->transaction->requestRollback();
			throw new ItemNotFoundException('The specified language has not been found.', $id);
		}

		return Language::fromArray($data);
	}
	
	public function getLanguageCodes()
	{
		$stmt = $this->conn->query('SELECT `locale` FROM `'.CoreTables::LANGUAGE_TBL.'` ORDER BY `locale`');
		$languages = [];
		while($row = $stmt->fetch(PDO::FETCH_NUM)) {
			$languages[] = $row[0];
		}
		$stmt->closeCursor();
		return $languages;
	}
	
	public function insert(Language $language)
	{
		$this->transaction->requestTransaction();
		return $language->insert($this->conn);
	}
	
	public function update(Language $language)
	{
		$this->transaction->requestTransaction();
		$language->update($this->conn);
	}
	
	public function remove(Language $language)
	{
		$this->transaction->requestTransaction();
		return $language->remove($this->conn);
	}
	
	public function getFormChoices()
	{
		$this->transaction->requestTransaction();
		$stmt = $this->conn->query('SELECT `id`, `name` FROM `'.CoreTables::LANGUAGE_TBL.'` ORDER BY `name`');
		$result = array();
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$result[$row['id']] = $row['name'];
		}
		$stmt->closeCursor();
		return $result;
	}

	public function transformToEntity($key)
	{
		return $this->getItem($key);
	}

	public function transformToKey($entity)
	{
		return $entity->getId();
	}
}
