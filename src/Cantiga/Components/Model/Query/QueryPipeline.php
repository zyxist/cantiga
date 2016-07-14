<?php
/*
 * This file is part of Cantiga Project. Copyright 2016 Cantiga Contributors.
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
namespace Cantiga\Components\Model\Query;

/**
 * This is an experimental API for the new Command&Query pipeline that may eventually
 * replace entities. Query pipeline tries to fetch the data from different sources,
 * probing them one after another. The use case is to start with a regular database
 * query, add caching layer later, replace the query with a different backend implementation,
 * and produce the result in one of the available output formats.
 */
class QueryPipeline
{
	private $sources = [];
	private $transformers = [];
	
	public function source($source)
	{
		if ($source instanceof QueryInterface || is_callable($source)) {
			$this->sources[] = $source;
			return $this;
		}
		throw new \InvalidArgumentException('QueryPipeline::source() accepts only QueryInterface instances of callbacks that produce them.');
	}
	
	public function transform(RecordTransformerInterface $transformer)
	{
		$this->transformer[] = $transformer;
		return $this;
	}
	
	public function launch()
	{
		return $this->applyTransformers($this->querySources());		
	}
	
	private function querySources()
	{
		foreach ($this->sources as $source) {
			$source = $this->toSource($source);
			$result = $source->executeQuery();
			if (is_object($result)) {
				return $result;
			}
		}
		throw new ModelException('Cannot create the query result: none of the data sources managed to execute.');
	}

	private function toSource($source)
	{
		if (is_callable($source)) {
			$source = $source();
			if (! ($source instanceof QueryInterface)) {
				throw new InvalidArgumentException('The callback registered as a query source in QueryPipeline did not return QueryInterface instance.');
			}
		}
		return $source;		
	}
	
	private function applyTransformers($result)
	{
		if ($result instanceof RecordList) {
			$result->forEach(function(Record $record) {
				$this->applyTransformersToRecord($record);
			});
		} else if ($result instanceof Record) {
			$this->applyTransformersToRecord($result);
		}
		return $result;
	}
	
	private function applyTransformersToRecord(Record $record)
	{
		foreach ($this->transformers as $transformer) {
			$transformer->transform($record);
		}
	}
}
