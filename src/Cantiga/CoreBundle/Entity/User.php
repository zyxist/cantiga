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
namespace Cantiga\CoreBundle\Entity;

use Cantiga\Components\Hierarchy\Entity\PlaceRef;
use Cantiga\Components\Hierarchy\MembershipRoleResolverInterface;
use Cantiga\Components\Hierarchy\User\CantigaUserRefInterface;
use Cantiga\CoreBundle\CoreTables;
use Cantiga\Metamodel\Capabilities\EditableEntityInterface;
use Cantiga\Metamodel\Capabilities\IdentifiableInterface;
use Cantiga\Metamodel\Capabilities\InsertableEntityInterface;
use Cantiga\Metamodel\Capabilities\RemovableEntityInterface;
use Cantiga\Metamodel\DataMappers;
use Cantiga\Metamodel\Join;
use Cantiga\Metamodel\Membership;
use Cantiga\Metamodel\QueryBuilder;
use Cantiga\Metamodel\QueryClause;
use Cantiga\Metamodel\QueryElement;
use Cantiga\Metamodel\QueryOperator;
use Cantiga\UserBundle\UserTables;
use Doctrine\DBAL\Connection;
use PDO;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * Represents an account of the user. Note that in the database, the account it split into two tables.
 * This entity masks this separation.
 */
class User implements UserInterface, IdentifiableInterface, InsertableEntityInterface, EditableEntityInterface, RemovableEntityInterface, CantigaUserRefInterface
{
	private $id;
	private $login;
	private $name;
	private $email;
	private $password;
	private $salt;
	private $active;
	private $admin;
	private $lastVisit;
	private $avatar;
	private $registeredAt;
	private $placeNum;

	private $location;
	private $settingsLanguage;
	private $settingsTimezone;
	
	private $afterLogin = null;
	/**
	 * The content of this array is auto-populated by the framework.
	 * @var array
	 */
	private $roles = ['ROLE_USER'];
	/**
	 * Information about the membership in the current context.
	 * @var Membership
	 */
	private $membership = null;
	
	/**
	 * Creates the user for the purpose of the automatic tests.
	 * 
	 * @param string $login
	 * @param string $name
	 */
	public static function newUser($login, $name, Language $lang)
	{
		$user = new User;
		$user->login = $login;
		$user->name = $name;
		$user->email = '';
		$user->password = '';
		$user->salt = '';
		$user->active = 1;
		$user->admin = 0;
		$user->registeredAt = time();
		$user->placeNum = 0;
		$user->settingsLanguage = $lang;
		$user->settingsTimezone = 'UTC';
		return $user;
	}

	public static function freshActive($password, $salt)
	{
		$user = new User;
		$user->password = $password;
		$user->salt = $salt;
		$user->active = 1;
		$user->admin = 0;
		return $user;
	}
	
	public static function fetchByCriteria(Connection $conn, QueryElement $queryElement, bool $allowInactive = false)
	{
		if ($allowInactive) {
			$clause = QueryClause::clause('u.`removed` = 0');
		} else {
			$clause = QueryClause::clause('u.`active` = 1 AND u.`removed` = 0');
		}
		
		$qb = QueryBuilder::select()
			->field('u.*')
			->field('p.*')
			->field('l.`id`', 'language_id')
			->field('l.`name`', 'language_name')
			->field('l.`locale`', 'language_locale')
			->from(CoreTables::USER_TBL, 'u')
			->join(CoreTables::USER_PROFILE_TBL, 'p', QueryClause::clause('p.`userId` = u.`id`'))
			->join(CoreTables::LANGUAGE_TBL, 'l', QueryClause::clause('l.`id` = p.`settingsLanguageId`'))
			->where(QueryOperator::op('AND')
				->expr($clause)
				->expr($queryElement));
		$data = $qb->fetchAssoc($conn);
		if (false === $data) {
			return false;
		}
		return User::fromArray($data);
	}

	public static function fetchLinkedProfile(Connection $conn, MembershipRoleResolverInterface $roleResolver, IdentifiableInterface $item, Join $join, QueryElement $element)
	{
		$qb = QueryBuilder::select()
			->field('u.*')
			->field('p.*')
			->field('m.role AS `membership_role`')
			->field('m.note AS `membership_note`')
			->field('l.`id`', 'language_id')
			->field('l.`name`', 'language_name')
			->field('l.`locale`', 'language_locale')
			->from(CoreTables::USER_TBL, 'u')
			->join(CoreTables::USER_PROFILE_TBL, 'p', QueryClause::clause('p.`userId` = u.`id`'))
			->join(CoreTables::LANGUAGE_TBL, 'l', QueryClause::clause('l.`id` = p.`settingsLanguageId`'))
			->join($join)
			->where(QueryOperator::op('AND')
				->expr(QueryClause::clause('u.`active` = 1 AND u.`removed` = 0'))
				->expr($element));
		$data = $qb->fetchAssoc($conn);
		if (false === $data) {
			return false;
		}
		$user = User::fromArray($data);
		$membership = new Membership($item, $roleResolver->getRole(get_class($item), $data['membership_role']), $data['membership_note']);
		User::installMembershipInformation($user, $membership);
		return $user;
	}

	public static function fromArray($array, $prefix = '')
	{
		$user = new User;
		DataMappers::fromArray($user, $array, $prefix);
		
		if (isset($array['language_id'])) {
			$user->settingsLanguage = Language::fromArray($array, 'language');
		}
		if ($user->getAdmin()) {
			$user->addRole('ROLE_ADMIN');
		}
		return $user;
	}
	
	public static function installMembershipInformation(User $user, Membership $membership)
	{
		$user->membership = $membership;
	}
	
	public static function getRelationships()
	{
		return ['settingsLanguage'];
	}
	
	public static function loadValidatorMetadata(ClassMetadata $metadata)
	{
		$metadata->addPropertyConstraint('location', new Length(array('max' => 100)));
	}

	public function getId()
	{
		return $this->id;
	}

	public function getLogin()
	{
		return $this->login;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function getEmail()
	{
		return $this->email;
	}

	public function getActive()
	{
		return $this->active;
	}

	public function getLastVisit()
	{
		return $this->lastVisit;
	}
	
	public function getAvatar()
	{
		return $this->avatar;
	}

	public function setId($id)
	{
		DataMappers::noOverwritingId($this->id);
		$this->id = $id;
		return $this;
	}

	public function setLogin($login)
	{
		$this->login = $login;
		return $this;
	}

	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}
	
	public function setPassword($password)
	{
		$this->password = $password;
		return $this;
	}
	
	public function setSalt($salt)
	{
		$this->salt = $salt;
		return $this;
	}

	public function setEmail($email)
	{
		$this->email = $email;
		return $this;
	}

	public function setActive($active)
	{
		$this->active = $active;
		return $this;
	}

	public function setLastVisit($lastVisit)
	{
		$this->lastVisit = $lastVisit;
		return $this;
	}
	
	public function setAvatar($avatar)
	{
		$this->avatar = $avatar;
		return $this;
	}

	public function eraseCredentials()
	{
		// do not erase anything, as we do not keep plaintext here.
	}

	public function getPassword()
	{
		return $this->password;
	}

	public function getRoles()
	{
		return $this->roles;
	}

	public function getSalt()
	{
		return $this->salt;
	}

	public function getUsername()
	{
		return $this->login;
	}
	
	public function getLocation()
	{
		return $this->location;
	}

	public function setLocation($location)
	{
		$this->location = $location;
		return $this;
	}
	
	public function getSettingsLanguage()
	{
		return $this->settingsLanguage;
	}

	public function getSettingsTimezone()
	{
		return $this->settingsTimezone;
	}

	public function setSettingsLanguage($settingsLanguage)
	{
		$this->settingsLanguage = $settingsLanguage;
		return $this;
	}

	public function setSettingsTimezone($settingsTimezone)
	{
		$this->settingsTimezone = $settingsTimezone;
		return $this;
	}
	
	public function getRegisteredAt()
	{
		return $this->registeredAt;
	}

	public function getPlaceNum()
	{
		return $this->placeNum;
	}

	public function setPlaceNum($placeNum): self
	{
		DataMappers::noOverwritingField($this->placeNum);
		$this->placeNum = $placeNum;
		return $this;
	}
	
	public function setRegisteredAt($registeredAt)
	{
		DataMappers::noOverwritingField($this->registeredAt);
		$this->registeredAt = $registeredAt;
		return $this;
	}

	public function getAfterLogin()
	{
		return $this->afterLogin;
	}

	public function setAfterLogin($afterLogin)
	{
		$this->afterLogin = $afterLogin;
		return $this;
	}
	
	public function getAdmin()
	{
		return $this->admin;
	}

	public function setAdmin($admin)
	{
		$this->admin = $admin;
		return $this;
	}
	
	/**
	 * @return Membership
	 */
	public function getMembership()
	{
		return $this->membership;
	}
	
	/**
	 * Adds a new role marker.
	 * 
	 * @param string $role Role name. False values are silently ignored.
	 */
	public function addRole($role)
	{
		if ($role !== false) {
			$this->roles[] = $role;
		}
	}

	public function serialize()
	{
		// Better not to make a mistake here, or Symfony will take us to the Kingdom of Chaos
		return serialize(array(
			'id' => $this->id,
			'login' => $this->login,
			'name' => $this->name,
			'email' => $this->email,
		));
	}

	public function unserialize($serialized)
	{
		// Better not to make a mistake here, or Symfony will take us to the Kingdom of Chaos
		$out = unserialize($serialized);
		$this->id = $out['id'];
		$this->login = $out['login'];
		$this->name = $out['name'];
		$this->email = $out['email'];
	}
	
	public function checkPassword($encoder, $password)
	{
		if ($encoder instanceof EncoderFactoryInterface) {
			$encoder = $encoder->getEncoder($this);
		}
		return $encoder->isPasswordValid($this->password, $password, $this->salt);
	}

	public function insert(Connection $conn)
	{
		$this->registeredAt = time();
		$conn->insert(
			CoreTables::USER_TBL,
			DataMappers::pick($this, ['login', 'name', 'email', 'password', 'salt', 'active', 'admin', 'avatar', 'registeredAt'])
		);
		$id = $conn->lastInsertId();
		$data = DataMappers::pick($this, ['location', 'settingsLanguage', 'settingsTimezone']);
		$data['userId'] = $id;
		$conn->insert(CoreTables::USER_PROFILE_TBL, $data);
		return $this->id = $id;
	}

	public function update(Connection $conn)
	{
		$conn->update(
			CoreTables::USER_TBL,
			DataMappers::pick($this, ['name', 'email', 'active', 'admin', 'avatar']),
			DataMappers::id($this)
		);
	}
	
	public function updateCredentials(Connection $conn)
	{
		$conn->update(
			CoreTables::USER_TBL,
			DataMappers::pick($this, ['password', 'salt', 'email']),
			DataMappers::id($this)
		);
	}

	public function updateProfile(Connection $conn)
	{
		$conn->update(
			CoreTables::USER_PROFILE_TBL,
			DataMappers::pick($this, ['location']),
			['userId' => $this->getId()]
		);
	}
	
	public function updateSettings(Connection $conn)
	{
		$conn->update(
			CoreTables::USER_PROFILE_TBL,
			DataMappers::pick($this, ['settingsLanguage', 'settingsTimezone']),
			['userId' => $this->getId()]
		);
	}
	
	public function canRemove()
	{
		return true;
	}

	public function remove(Connection $conn)
	{
		$conn->update(CoreTables::USER_TBL, ['removed' => 1, 'active' => 0, 'name' => '???'], DataMappers::id($this));
		$conn->executeQuery('DELETE FROM `'.CoreTables::USER_PROFILE_TBL.'` WHERE `userId` = :id', [':id' => $this->getId()]);
	}
	
	public function findPlaces(Connection $conn, MembershipRoleResolverInterface $roleResolver): array
	{
		$stmt = $conn->prepare('SELECT p.*, m.* '
			. 'FROM `'.CoreTables::PLACE_TBL.'` p '
			. 'INNER JOIN `'.UserTables::PLACE_MEMBERS_TBL.'` m ON m.`placeId` = p.`id` '
			. 'WHERE m.`userId` = :userId '
			. 'ORDER BY p.`type` DESC, p.`name`');
		$stmt->bindValue(':userId', $this->getId());
		$stmt->execute();
		$results = [];
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$results[] = new PlaceRef(
				(int) $row['id'],
				$row['name'],
				$row['type'],
				$row['slug'],
				(bool) $row['archived'],
				$roleResolver->getRole($row['type'], (int) $row['role']),
				$row['note'],
				(bool) $row['showDownstreamContactData']
			);
		}
		$stmt->closeCursor();
		return $results;
	}
}
