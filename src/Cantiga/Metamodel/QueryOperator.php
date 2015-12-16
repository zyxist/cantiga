<?php
namespace Cantiga\Metamodel;

/**
 * Description of QueryOperator
 *
 * @author Tomasz Jędrzejewski
 */
class QueryOperator implements QueryElement
{
	private $operator;
	private $expressions = array();
	
	private function __construct($operator)
	{
		$this->operator = $operator;
	}
	
	public static function op($operator)
	{
		return new QueryOperator($operator);
	}
	
	public function expr(QueryElement $element = null)
	{
		if (null !== $element) {
			$this->expressions[] = $element;
		}
		return $this;
	}
	
	public function registerBindings(QueryBuilder $qb)
	{
		foreach ($this->expressions as $expr) {
			$expr->registerBindings($qb);
		}
	}

	public function build()
	{
		if (sizeof($this->expressions) == 0) {
			return null;
		}
		if (sizeof($this->expressions) == 1) {
			return '('.$this->expressions[0]->build().')';
		}
		$subItems = array();
		foreach($this->expressions as $expr) {
			$subItems[] = $expr->build();
		}
		return '('.implode(' '.$this->operator.' ', $subItems).')';
	}
}
