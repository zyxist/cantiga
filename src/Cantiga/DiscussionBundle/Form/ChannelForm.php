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
namespace Cantiga\DiscussionBundle\Form;

use Cantiga\Components\Ui\Form\BackgroundChoiceType;
use Cantiga\Components\Ui\Form\IconChoiceType;
use Cantiga\CoreBundle\Form\Type\BooleanType;
use Cantiga\DiscussionBundle\Entity\Channel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChannelForm extends AbstractType
{
	const TYPE_AREA = 'Area';
	const TYPE_GROUP = 'Group';
	const TYPE_PROJECT = 'Project';

	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefined(['isNew']);
		$resolver->setRequired(['isNew']);
		$resolver->setDefault('translation_domain', 'discussion');
	}
	
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('name', TextType::class, ['label' => 'Name'])
			->add('description', TextType::class, ['label' => 'Description'])
			->add('color', BackgroundChoiceType::class, ['label' => 'Background color'])
			->add('icon', IconChoiceType::class, ['label' => 'Icon'])
			->add('projectVisible', BooleanType::class, ['label' => 'Visible in project?'])
			->add('groupVisible', BooleanType::class, ['label' => 'Visible in group?'])
			->add('areaVisible', BooleanType::class, ['label' => 'Visible in area?'])
			->add('projectPosting', BooleanType::class, ['label' => 'Project members can post?'])
			->add('groupPosting', BooleanType::class, ['label' => 'Group members can post?'])
			->add('areaPosting', BooleanType::class, ['label' => 'Area members can post?']);
		if ($options['isNew']) {
			$builder->add('discussionGrouping', ChoiceType::class, ['label' => 'Discussion grouping', 'choices' => [
				'none' => Channel::BY_PROJECT,
				'by group' => Channel::BY_GROUP,
				'by area' => Channel::BY_AREA,
			], 'attr' => ['help_text' => 'Create separate discussions for each group or area within the channel.']]);
		}
		$builder
			->add('enabled', BooleanType::class, ['label' => 'Enabled?', 'attr' => ['help_text' => 'EnabledDiscussionText']])
			->add('save', SubmitType::class, ['label' => 'Save']);
	}

	public function getName()
	{
		return 'Channel';
	}
}