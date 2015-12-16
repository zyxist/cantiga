<?php
namespace Cantiga\Metamodel;

/**
 * Injectable JOIN clause for Query builder.
 *
 * @author Tomasz Jędrzejewski
 */
class Join
{
	private $table;
	private $alias;
	private $link;
	
	public static function create($table, $alias, QueryElement $link)
	{
		$join = new Join();
		$join->table = $table;
		$join->alias = $alias;
		$join->link = $link;
		return $join;
	}
	
	public function getTable()
	{
		return $this->table;
	}

	public function getAlias()
	{
		return $this->alias;
	}

	/**
	 * @return QueryElement
	 */
	public function getLink()
	{
		return $this->link;
	}
}
