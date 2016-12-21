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
declare(strict_types=1);
namespace Cantiga\Components\Hierarchy\Entity;

/**
 * Member of the same entity, as us. We can see his/her contact information.
 */
class Member extends AbstractProfileView
{
	/**
	 * @var MemberInfo
	 */
	private $memberInfo;
	/**
	 * @var array
	 */
	private $places;
	
	public function __construct(array $data, MemberInfo $memberInfo, array $places = [])
	{
		parent::__construct($data);
		$this->memberInfo = $memberInfo;
		$this->places = $places;
	}
	
	public function getMemberInfo(): MemberInfo
	{
		return $this->memberInfo;
	}
	
	public function getPlaces(): array
	{
		return $this->places;
	}
	
	public function asArray(): array
	{
		$array = parent::asArray();
		$array['role'] = $this->memberInfo->getRole()->getId();
		$array['roleName'] = $this->memberInfo->getRole()->getName();
		$array['note'] = $this->memberInfo->getNote() ?? '';
		$array['showDownstreamContactData'] = $this->memberInfo->getShowDownstreamContactData() ?? 0;
		return $array;
	}
	
	public static function collectionAsArray(array $memberItems, $callback = null): array
	{
		$result = [];
		if (!empty($callback)) {
			foreach ($memberItems as $item) {
				$result[] = $callback($item->asArray());
			}
		} else {
			foreach ($memberItems as $item) {
				$result[] = $item->asArray();
			}
		}
		return $result;
	}
}
