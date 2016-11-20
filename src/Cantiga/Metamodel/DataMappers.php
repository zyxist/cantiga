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
namespace Cantiga\Metamodel;

use Cantiga\Metamodel\Capabilities\IdentifiableInterface;
use Cantiga\Metamodel\Exception\SlugException;
use Doctrine\DBAL\Connection;
use LogicException;

/**
 * Static utilities for writing entities.
 *
 * @author Tomasz Jędrzejewski
 */
final class DataMappers
{
	/**
	 * Automatic array-to-object mapper, through setter. We assume that the database
	 * fields follow the camel case naming convention to simplify this. The method can
	 * also import data from the prefixed fields. In this case the prefixed database
	 * column should look like this: <tt>someprefix_camelCasedProperty</tt>.
	 * 
	 * @param object $destination Destination object.
	 * @param array $source Source array
	 * @param type $prefix Optional property prefix in the source array.
	 */
	public static function fromArray($destination, array $source, $prefix = '')
	{
		$methods = get_class_methods($destination);
		if ('' != $prefix) {
			$prefix .= '_';
		}
		foreach ($methods as $method) {
			if (strpos($method, 'set') === 0) {
				$property = lcfirst(substr($method, 3));
				if (isset($source[$prefix . $property])) {
					$destination->$method($source[$prefix . $property]);
				}
			}
		}
	}

	/**
	 * Use this method in all your setters for ID property to avoid overwriting it.
	 * 
	 * @param int $id Current ID of the entity.
	 * @throws LogicException
	 */
	public static function noOverwritingId($id)
	{
		if (null !== $id) {
			throw new LogicException('No overwriting the ID!');
		}
	}
	
	/**
	 * Use this method in all your setters that do not allow overwriting entity value.
	 * 
	 * @param int $value Current value of the entity.
	 * @throws LogicException
	 */
	public static function noOverwritingField($value)
	{
		if (null !== $value) {
			throw new LogicException('This property cannot be overwritten.');
		}
	}

	public static function pick($entity, array $properties, array $result = [])
	{
		$relationships = [];
		if(method_exists($entity, 'getRelationships')) {
			$relationships = $entity::getRelationships();
		}
		foreach ($properties as $property) {
			$getter = 'get' . ucfirst($property);
			if (!method_exists($entity, $getter)) {
				throw new \LogicException('No such getter: \''.$getter.'()\' for property '.$property);
			}
			$value = $entity->$getter();
			
			if (is_object($value) && $value instanceof IdentifiableInterface) {
				$result[$property.'Id'] = $value->getId();
			} elseif(in_array($property, $relationships)) {
				$result[$property.'Id'] = null;
			} else {
				$result[$property] = $value;
			}
		}
		return $result;
	}

	/**
	 * Produces an array with a single key: <tt>id</tt>. The array can be used as an input
	 * for the last argument of <tt>$conn->update()</tt>
	 * @param IdentifiableInterface $entity
	 * @return array
	 */
	public static function id(IdentifiableInterface $entity)
	{
		return array('id' => $entity->getId());
	}
	
	/**
	 * Checks whether the two identifiable objects are the same in terms of their unique ID-s. The method
	 * correctly handles NULL instances, and is recommended to use.
	 * 
	 * @param IdentifiableInterface $a
	 * @param IdentifiableInterface $b
	 * @return boolean
	 */
	public static function same(IdentifiableInterface $a = null, IdentifiableInterface $b = null)
	{
		if ($a === null && $b === null) {
			return true;
		} elseif ($a === null && $b !== null) {
			return false;
		} elseif ($a !== null && $b === null) {
			return false;
		}
		return $a->getId() == $b->getId();
	}
	
	/**
	 * Helps manipulating the static, denormalized counters, when some counted entity appears or disappears. If the old
	 * entity is set, the counter is decremented. If the new entity is set, the counter is incremented. 
	 * 
	 * @param Connection $conn
	 * @param string $table
	 * @param IdentifiableEntity $old
	 * @param IdentifiableEntity $new
	 * @param string $countField
	 * @param string $idField
	 */
	public static function recount(Connection $conn, $table, $old, $new, $countField, $idField)
	{
		$idGetter = 'get'.ucfirst($idField);
		if (null !== $old) {
			$id = $old->$idGetter();
			$conn->executeQuery('UPDATE `'.$table.'` SET `'.$countField.'` = (`'.$countField.'` - 1) WHERE `id` = :id', [':id' => $id]);
		}
		if (null !== $new) {
			$id = $new->$idGetter();
			$conn->executeQuery('UPDATE `'.$table.'` SET `'.$countField.'` = (`'.$countField.'` + 1) WHERE `id` = :id', [':id' => $id]);
		}
	}
	
	/**
	 * Generates a random, 12-key slug and verifies that it is unique.
	 * 
	 * @param Connection $conn
	 * @param string $table
	 */
	public static function generateSlug(Connection $conn, $table)
	{
		$allowedCharacters = 'qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM1234567890';
		$text = '';
		$size = strlen($allowedCharacters);
		for ($i = 0; $i < 12; $i ++) {
			$random = abs(mt_rand());
			$text .= $allowedCharacters[$random % $size];
		}
		$exists = $conn->fetchColumn('SELECT `slug` FROM `'.$table.'` WHERE `slug` = :slug', [':slug' => $text]);
		if ($exists) {
			throw new SlugException('This entity uses 12-character unique slugs to identify itself, instead of plain old numbers. These slugs are automatically generated and you have run into a rare situation, where the generated slug is not unique. Try to add the object once again and take a chance in the lottery.');
		}
		return $text;
	}
}
