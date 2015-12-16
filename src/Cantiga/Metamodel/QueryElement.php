<?php
namespace Cantiga\Metamodel;

/**
 * Used for building WHERE clauses in the query builder.
 * 
 * @author Tomasz Jędrzejewski
 */
interface QueryElement
{
	public function build();
	public function registerBindings(QueryBuilder $qb);
}
