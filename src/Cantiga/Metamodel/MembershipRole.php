<?php
namespace Cantiga\Metamodel;

/**
 * Represents a single role that can be assigned to a member of something.
 *
 * @author Tomasz Jędrzejewski
 */
class MembershipRole
{
	private $id;
	private $name;
	private $authRole;
	
	public function __construct($id, $name, $authRole)
	{
		$this->id = $id;
		$this->name = $name;
		$this->authRole = $authRole;
	}
	
	public function getId()
	{
		return $this->id;
	}

	public function getName()
	{
		return $this->name;
	}

	public function getAuthRole()
	{
		return $this->authRole;
	}
	
	/**
	 * True, if this role represents an unknown role. It may appear, if the given entity doesn't support
	 * the role of the specified ID.
	 * 
	 * @return boolean
	 */
	public function isUnknown()
	{
		return $this->id < 0;
	}
}
