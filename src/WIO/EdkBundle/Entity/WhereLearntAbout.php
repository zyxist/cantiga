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
namespace WIO\EdkBundle\Entity;

use \LogicException;

/**
 * Static dictionary of the available choices for the question, where has
 * the participant learnt about EWC.
 *
 * @author Piotr Zak
 */
class WhereLearntAbout
{
	private static $CHOICES;
	private $name;
	private $id;
	private $custom;

	public function __construct($id, $name, $custom = false)
	{
		$this->id = $id;
		$this->name = $name;
		$this->custom = $custom;
	}

	public function getId()
	{
		return $this->id;
	}

	public function getName()
	{
		return $this->name;
	}
	
	public function isCustom()
	{
		return $this->custom;
	}

	public function __toString()
	{
		return $this->name;
	}

	private static function generateItems()
	{
		self::$CHOICES = array(
			1 => new WhereLearntAbout(1, 'FromFriendsWLChoice'),
			2 => new WhereLearntAbout(2, 'FromFacebookWLChoice'),
			3 => new WhereLearntAbout(3, 'FromPosterWLChoice'),
			4 => new WhereLearntAbout(4, 'FromChurchWLChoice'),
			5 => new WhereLearntAbout(5, 'FromCommunityWLChoice'),
			6 => new WhereLearntAbout(6, 'FromNewsPortalWLChoice'),
			7 => new WhereLearntAbout(7, 'FromWebsiteWLChoice'),
			8 => new WhereLearntAbout(8, 'FromTelevisionWLChoice'),
			9 => new WhereLearntAbout(9, 'FromYouTubeWLChoice'),
			10 => new WhereLearntAbout(10, 'FromNewsletterWLChoice'),
			11 => new WhereLearntAbout(11, 'FromNobleParcelProjectWLChoice'),
			12 => new WhereLearntAbout(12, 'FromPressArticleWLChoice'),
			13 => new WhereLearntAbout(13, 'FromRadioWLChoice'),
			100 => new WhereLearntAbout(100, 'OtherWLChoice', true)
		);
	}


	public static function getItem($id)
	{
		if (null === self::$CHOICES) {
			self::generateItems();
		}
		if (!isset(self::$CHOICES[$id])) {
			throw new \LogicException('Invalid ID: ' . $id);
		}
		return self::$CHOICES[$id];
	}

	public static function getItems()
	{
		if (null === self::$CHOICES) {
			self::generateItems();
		}
		return self::$CHOICES;
	}
	
	public static function getChoiceIds()
	{
		if (null === self::$CHOICES) {
			self::generateItems();
		}
		$result = [];
		foreach (self::$CHOICES as $ch) {
			$result[] = $ch->getId();
		}
		return $result;
	}
	
	public static function getFormChoices()
	{
		if (null === self::$CHOICES) {
			self::generateItems();
		}
		$result = ['-- choose --' => null];
		foreach (self::$CHOICES as $ch) {
			$result[$ch->getName()] = $ch->getId();
		}
		return $result;
	}
}
