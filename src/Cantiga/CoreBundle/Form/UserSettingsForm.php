<?php
/*
 * This file is part of Cantiga Project. Copyright 2015 Tomasz Jedrzejewski.
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
namespace Cantiga\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Cantiga\CoreBundle\Api\Workspaces;
use Cantiga\CoreBundle\Repository\LanguageRepository;
use Cantiga\Metamodel\Form\EntityTransformer;

class UserSettingsForm extends AbstractType
{
	private $langRepo;
	
	public function __construct(LanguageRepository $langRepo)
	{
		$this->langRepo = $langRepo;
	}
	
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('settingsLanguage', 'choice', array('label' => 'Site language', 'choices' => $this->langRepo->getFormChoices()))
			->add('settingsTimezone', 'timezone', array('label' => 'Timezone'))
			->add('save', 'submit', array('label' => 'Save'));
		$builder->get('settingsLanguage')->addModelTransformer(new EntityTransformer($this->langRepo));
	}

	public function getName()
	{
		return 'UserSettings';
	}
}