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

namespace Cantiga\ExportBundle\Form;

use Cantiga\CoreBundle\Entity\Project;
use Cantiga\CoreBundle\Form\Type\BooleanType;
use Cantiga\CoreBundle\Repository\ProjectAreaStatusRepository;
use Cantiga\CoreBundle\Repository\ProjectRepository;
use Cantiga\Metamodel\Form\EntityTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DataExportForm extends AbstractType
{
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefined(['projectRepository', 'areaStatusRepository']);
		$resolver->setRequired(['projectRepository', 'areaStatusRepository']);
		$resolver->addAllowedTypes('projectRepository', ProjectRepository::class);
		$resolver->addAllowedTypes('areaStatusRepository', ProjectAreaStatusRepository::class);
	}

	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$projects = $options['projectRepository']->getFormChoices();
		$first = reset($projects);		
		
		$builder
			->add('name', TextType::class, array('label' => 'Name'))
			->add('project', ChoiceType::class, array('label' => 'Project', 'choices' => $projects))
			->add('url', UrlType::class, array('label' => 'Export URL'))
			->add('encryptionKey', TextType::class, ['label' => 'Encryption key'])
			->add('active', BooleanType::class, ['label' => 'Active'])
			->add('notes', TextareaType::class, ['label' => 'Notes'])
			->add('save', SubmitType::class, array('label' => 'Save'));

		$builder->get('project')->addModelTransformer(new EntityTransformer($options['projectRepository']));

		$formModifier = function (FormInterface $form, Project $project = null) use($options, $first) {
			if (null === $project && false !== $first) {
				$project = $options['projectRepository']->getItem($first);
			}
			$statuses = (null === $project ? [] : $options['areaStatusRepository']->getFormChoices($project));
			$form->add('areaStatus', ChoiceType::class, ['label' => 'Area status', 'choices' => $statuses]);
		};

		$builder->addEventListener(
			FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($formModifier) {
				$data = $event->getData();
				$formModifier($event->getForm(), $data->getProject());

				if (!empty($data->getAreaStatus())) {
					$data->setAreaStatus($data->getAreaStatus()->getId());
				}
			}
		);
		$builder->addEventListener(
			FormEvents::SUBMIT, function (FormEvent $event) use($options) {
				$data = $event->getData();
				if (!empty($data->getAreaStatus()) && null !== $data->getProject()) {
					$options['areaStatusRepository']->setProject($data->getProject());
					$data->setAreaStatus($options['areaStatusRepository']->getItem($data->getAreaStatus()));
				}
			}
		);

		$builder->get('project')->addEventListener(
			FormEvents::POST_SUBMIT, function (FormEvent $event) use ($formModifier) {
				$project = $event->getForm()->getData();
				$formModifier($event->getForm()->getParent(), $project);
			}
		);
	}

	public function getName()
	{
		return 'DataExport';
	}

}
