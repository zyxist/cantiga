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
namespace Cantiga\UserBundle\Extension;

use Cantiga\Components\Hierarchy\Entity\Member;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * This built-in profile extension shows other places in the project the user is
 * member of.
 */
class MembershipProfileExtension implements ProfileExtensionInterface
{
	private $templating;
	private $translator;

	public function __construct(EngineInterface $templating, TranslatorInterface $translator)
	{
		$this->templating = $templating;
		$this->translator = $translator;
	}
	
	public function getTabTitle(): string
	{
		return $this->translator->trans('Membership', [], 'users');
	}
	
	public function getTabHashtag(): string
	{
		return 'membership';
	}

	public function getTabContent(Member $member)
	{
		return $this->templating->render('CantigaUserBundle:Memberlist:membership-profile-extension.html.twig', ['member' => $member]);
	}
}
