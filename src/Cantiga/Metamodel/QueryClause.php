<?php
namespace Cantiga\Metamodel;

/**
 * Description of QueryClause
 *
 * @author Tomasz Jędrzejewski
 */
class QueryClause implements QueryElement
{
	private $clause;
	private $bindingName;
	private $bindingValue;
	
	public function __construct($clause)
	{
		$this->clause = $clause;
	}
	
	public static function clause($clause, $binding = null, $value = null)
	{
		$cc = new QueryClause($clause);
		if (null !== $binding) {
			$cc->bindingName = $binding;
			$cc->bindingValue = $value;
		}
		return $cc;
	}
	
	public function registerBindings(QueryBuilder $qb)
	{
		if (null !== $this->bindingName) {
			$qb->bind($this->bindingName, $this->bindingValue);
		}
	}
	
	public function getClause()
	{
		return $this->clause;
	}

	public function build()
	{
		return '('.$this->clause.')';
	}
}
