<?php
namespace Cantiga\Metamodel\Exception;

/**
 * Default exception class for all the problems related to the application data model. By capturing this
 * exception in controllers, we should be able to properly handle all the data model issues without crashing
 * the application itself.
 *
 * @author Tomasz Jędrzejewski
 */
class ModelException extends \Exception
{
}
