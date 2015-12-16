<?php
namespace Cantiga\CoreBundle\Api\ExtensionPoints;

/**
 * @author Tomasz Jędrzejewski
 */
interface ExtensionPointsInterface
{
	/**
	 * Returns an associative array of implementations available for the given extension point,
	 * together with their human-readable names. This method can be used in the forms which allow
	 * choosing the implementation (i.e. editable settings).
	 * 
	 * @param string $extPointName Extension point
	 * @param ExtensionPointFilter $filter Filter to limit the implementations
	 */
	public function describeImplementations($extPointName, ExtensionPointFilter $filter);
	/**
	 * Checks if there is any implementation registered for the given extension point.
	 * 
	 * @param string $extPointName Extension point
	 * @param ExtensionPointFilter $filter Filter to limit the implementations
	 * @return boolean
	 */
	public function hasImplementation($extPointName, ExtensionPointFilter $filter);
	/**
	 * Returns a single implementation of the given extension point, pointed
	 * by the specific filter. If no extension is found, the method throws an
	 * exception. The method shall access the service locator to obtain the
	 * actual instance and return it.
	 * 
	 * @param string $extPointName Extension point
	 * @param ExtensionPointFilter $key Implementation name
	 * @return object
	 */
	public function getImplementation($extPointName, ExtensionPointFilter $filter);
	/**
	 * Returns all implementations of the given extension point, pointed
	 * by the specific filter. If no implementation is found, the method
	 * returns an empty array. The method shall access the service locator to obtain the
	 * actual instances and return them.
	 * 
	 * @param string $extPointName Extension point
	 * @param ExtensionPointFilter $key Implementation name
	 * @return array
	 */
	public function findImplementations($extPointName, ExtensionPointFilter $filter);
}
