<?php
/*
 * This file is part of Cantiga Project. Copyright 2015-2016 Tomasz Jedrzejewski.
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
namespace Cantiga\CoreBundle\CustomForm;

use Cantiga\Metamodel\Capabilities\CompletenessCalculatorInterface;
use Cantiga\Metamodel\CustomForm\CustomFormModelInterface;
use Cantiga\Metamodel\CustomForm\CustomFormRendererInterface;
use Cantiga\Metamodel\CustomForm\CustomFormSummaryInterface;
use Cantiga\Metamodel\CustomForm\DefaultCustomFormRenderer;
use Cantiga\Metamodel\CustomForm\DefaultCustomFormSummary;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Specifies the default area profile with some common properties.
 */
class DefaultAreaModel implements CustomFormModelInterface, CompletenessCalculatorInterface
{
	public function constructForm(FormBuilderInterface $builder)
	{
		$builder->add('areaDescription', TextareaType::class, array('label' => 'AreaDescriptionLabel', 'required' => false, 'constraints' => [
			new Length(['min' => 2, 'max' => 250])
		]));
		$builder->add('areaWebsite', UrlType::class, ['label' => 'AreaWebsiteLabel', 'required' => false, 'constraints' => [
			new Url()
		]]);
		$builder->add('facebookProfile', UrlType::class, ['label' => 'AreaFacebookProfileLabel', 'required' => false,  'constraints' => [
			new Url()
		]]);
		$builder->add('orgName', TextType::class, ['label' => 'OrgNameLabel', 'required' => false, 'constraints' => [
			new Length(['min' => 2, 'max' => 40])
		]]);
		$builder->add('orgWebsite', TextType::class, ['label' => 'OrgWebsiteLabel', 'required' => false, 'constraints' => [
			new Url()
		]]);
	}
	
	public function validateForm(array $data, ExecutionContextInterface $context)
	{
	}
	
	public function createFormRenderer(): CustomFormRendererInterface
	{
		$r = new DefaultCustomFormRenderer();
		$r->group('Area information');
		$r->fields('areaWebsite', 'facebookProfile');
		$r->fields('areaDescription');
		$r->group('Your organization', 'YourOrganizationInfoText');
		$r->fields('orgName', 'orgWebsite');
		return $r;
	}
	
	public function createSummary(): CustomFormSummaryInterface
	{
		$s = new DefaultCustomFormSummary();
		$s->present('areaDescription', 'AreaDescriptionLabel', DefaultCustomFormSummary::TYPE_STRING);
		$s->present('areaWebsite', 'AreaWebsiteLabel', DefaultCustomFormSummary::TYPE_URL);
		$s->present('facebookProfile', 'AreaFacebookProfileLabel', DefaultCustomFormSummary::TYPE_URL);
		$s->present('orgName', 'OrgNameLabel', DefaultCustomFormSummary::TYPE_STRING);
		$s->present('orgWebsite', 'OrgWebsiteLabel', DefaultCustomFormSummary::TYPE_URL);
		return $s;
	}
	
	public function calculateCompleteness(array $data): int
	{
		$fieldCollection = ['areaDescription', 'areaWebsite', 'facebookProfile'];
	
		$total = sizeof($fieldCollection);
		$filled = 0;
		foreach ($fieldCollection as $name) {
			if (!empty($data[$name])) {
				$filled++;
			}
		}
		return (int) round(($filled / $total) * 100);
	}
}
