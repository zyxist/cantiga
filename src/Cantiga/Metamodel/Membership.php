<?php
namespace Cantiga\Metamodel;

use Cantiga\Metamodel\Capabilities\IdentifiableInterface;

/**
 * Description of Membership
 *
 * @author Tomasz Jędrzejewski
 */
class Membership
{
	private $item;
	private $role;
	private $note;
	
	public function __construct(IdentifiableInterface $item = null, MembershipRole $role = null, $note = '')
	{
		$this->item = $item;
		$this->role = $role;
		$this->note = $note;
	}
	
	/**
	 * @return IdentifiableInterface
	 */
	public function getItem()
	{
		return $this->item;
	}

	/**
	 * @return MembershipRole
	 */
	public function getRole()
	{
		return $this->role;
	}

	/**
	 * @return string
	 */
	public function getNote()
	{
		return $this->note;
	}
}
