<?php
namespace Cantiga\CoreBundle\Entity;

/**
 * Short user information about the verifier.
 *
 * @author Tomasz Jędrzejewski
 */
class Verifier
{
	private $id;
	private $name;
	
	public function __construct($id, $name)
	{
		$this->id = $id;
		$this->name = $name;
	}
	
	public function getId()
	{
		return $this->id;
	}

	public function getName()
	{
		return $this->name;
	}
}
