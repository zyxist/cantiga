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
namespace WIO\EdkBundle\Form;

use Cantiga\CoreBundle\Form\Type\BooleanType;
use Symfony\Component\Form\FormBuilderInterface;
use WIO\EdkBundle\Entity\EdkRegistrationSettings;

/**
 * Public version of the registration form. Requires specifying an additional text about
 * accepting terms, which is customizable.
 *
 * @author Tomasz Jędrzejewski
 */
class PublicParticipantForm extends AbstractParticipantForm
{
	private $terms1AcceptedText;
	private $terms2AcceptedText;
	private $terms3AcceptedText;
	
	public function __construct(EdkRegistrationSettings $rs = null, $terms1Text, $terms2Text, $terms3Text)
	{
		parent::__construct($rs);
		$this->terms1AcceptedText = $terms1Text;
		$this->terms2AcceptedText = $terms2Text;
		$this->terms3AcceptedText = $terms3Text;
	}
	
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		parent::buildForm($builder, $options);
		$builder->add('terms1Accepted', BooleanType::class, ['label' => $this->terms1AcceptedText, 'required' => true]);
		$builder->add('terms2Accepted', BooleanType::class, ['label' => $this->terms2AcceptedText, 'required' => true]);
		$builder->add('terms3Accepted', BooleanType::class, ['label' => $this->terms3AcceptedText, 'required' => true]);
	}
	
	protected function isMailRequired()
	{
		return true;
	}

	public function getName()
	{
		return 'Participant';
	}
}
