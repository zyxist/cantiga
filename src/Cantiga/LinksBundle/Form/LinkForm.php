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
namespace Cantiga\LinksBundle\Form;

use Cantiga\LinksBundle\Entity\Link;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LinkForm extends AbstractType
{
	const PROJECT_SPECIFIC = 0;
	const GENERAL = 1;
	
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefined(['type']);
		$resolver->setRequired(['type']);
	}
	
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('name', TextType::class, array('label' => 'Name'))
			->add('url', UrlType::class, array('label' => 'Url'))
			->add('presentedTo', ChoiceType::class, array('label' => 'Presentation place', 'choices' => array_flip($this->createChoices($options['type']))))
			->add('listOrder', NumberType::class, array('label' => 'Order'))
			->add('save', SubmitType::class, array('label' => 'Save'));
	}

	public function getName()
	{
		return 'Link';
	}
	
	private function createChoices($type)
	{
		if ($type == self::PROJECT_SPECIFIC) {
			return [
				Link::PRESENT_PROJECT => Link::presentedToText(Link::PRESENT_PROJECT),
				Link::PRESENT_GROUP => Link::presentedToText(Link::PRESENT_GROUP),
				Link::PRESENT_AREA => Link::presentedToText(Link::PRESENT_AREA),
			];
		} else {
			return [
				Link::PRESENT_ADMIN => Link::presentedToText(Link::PRESENT_ADMIN),
				Link::PRESENT_USER => Link::presentedToText(Link::PRESENT_USER)
			];
		}
	}
}