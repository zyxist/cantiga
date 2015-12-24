<?php

namespace Cantiga\Metamodel;

use Cantiga\Metamodel\Capabilities\IdentifiableInterface;
use Doctrine\DBAL\Connection;
use LogicException;

/**
 * Description of DataMappers
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

	public static function id(IdentifiableInterface $entity)
	{
		return array('id' => $entity->getId());
	}
	
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
