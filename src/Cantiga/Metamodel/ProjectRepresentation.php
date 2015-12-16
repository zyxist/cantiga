<?php
namespace Cantiga\Metamodel;

/**
 * Information about the items (projects, groups, areas) the user is member of.
 * It is used for generating the menu.
 *
 * @author Tomasz Jędrzejewski
 */
class ProjectRepresentation
{
	private $translationKey;
	private $route;
	private $slug;
	private $name;
	private $label;
	private $role;
	private $note;
	
	public function __construct($slug, $name, $route, $translationKey, $label, MembershipRole $role, $note)
	{
		$this->slug = $slug;
		$this->name = $name;
		$this->route = $route;
		$this->translationKey = $translationKey;
		$this->label = $label;
		$this->role = $role;
		$this->note = $note;
	}
	
	public function getTranslationKey()
	{
		return $this->translationKey;
	}

	public function getRoute()
	{
		return $this->route;
	}

	public function getSlug()
	{
		return $this->slug;
	}

	public function getName()
	{
		return $this->name;
	}
	
	public function getLabel()
	{
		return $this->label;
	}

	/**
	 * @return MembershipRole
	 */
	public function getRole()
	{
		return $this->role;
	}

	public function getNote()
	{
		return $this->note;
	}

	public function setLabel($label)
	{
		$this->label = $label;
		return $this;
	}

	public function setRole($role)
	{
		$this->role = $role;
		return $this;
	}

	public function setNote($note)
	{
		$this->note = $note;
		return $this;
	}


}
