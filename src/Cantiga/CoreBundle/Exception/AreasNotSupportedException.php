<?php
namespace Cantiga\CoreBundle\Exception;

/**
 * Thrown, when we are attempting to access a content which depends on areas, and
 * the areas are disabled in the project settings.
 *
 * @author Tomasz Jędrzejewski
 */
class AreasNotSupportedException extends \RuntimeException
{
}
