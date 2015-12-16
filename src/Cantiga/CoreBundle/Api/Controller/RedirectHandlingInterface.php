<?php
namespace Cantiga\CoreBundle\Api\Controller;

/**
 * If this interface is implemented in a controller, the CRUD actions will use
 * the following methods to perform redirects, instead of default ones. The
 * methods must return <tt>Response</tt> object.
 * 
 * @author Tomasz Jędrzejewski
 */
interface RedirectHandlingInterface {
	public function onError($message);
	public function onSuccess($message);
	public function toIndexPage();
}
