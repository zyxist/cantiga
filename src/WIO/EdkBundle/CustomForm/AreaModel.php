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
namespace WIO\EdkBundle\CustomForm;

use Cantiga\Metamodel\Capabilities\CompletenessCalculatorInterface;
use Cantiga\Metamodel\CustomForm\CustomFormModelInterface;
use Cantiga\Metamodel\CustomForm\CustomFormRendererInterface;
use Cantiga\Metamodel\CustomForm\CustomFormSummaryInterface;
use Cantiga\Metamodel\CustomForm\DefaultCustomFormRenderer;
use Cantiga\Metamodel\CustomForm\DefaultCustomFormSummary;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class AreaModel implements CustomFormModelInterface, CompletenessCalculatorInterface
{
	private $translator;
	
	public function __construct(TranslatorInterface $translator)
	{
		$this->translator = $translator;
	}
	
	public function constructForm(FormBuilderInterface $builder)
	{
		$builder->add('positionLat', NumberType::class, array('label' => 'Area location (lattitude)*', 'required' => false, 'scale' => 6, 'attr' => ['help_text' => 'LattitudeHintText'], 'constraints' => [
			new Range(['min' => -90, 'max' => 90])
		]));
		$builder->add('positionLng', NumberType::class, array('label' => 'Area location (longitude)*', 'required' => false, 'scale' => 6, 'attr' => ['help_text' => 'LongitudeHintText'], 'constraints' => [
			new Range(['min' => -180, 'max' => 180])
		]));
		$builder->add('ewcDate', DateType::class, array('label' => 'Date of Extreme Way of the Cross*', 'input' => 'array', 'required' => false, 'years' => $this->generateYears(), 'constraints' => [
		]));
		
		$builder->add('parishName', TextType::class, array('label' => 'Parish name', 'required' => false, 'constraints' => [
			new Length(array('min' => 2, 'max' => 50))
		]));
		$builder->add('parishAddress', TextType::class, array('label' => 'Parish address', 'required' => false, 'constraints' => [
			new Length(array('min' => 2, 'max' => 100))
		]));
		$builder->add('parishPostal', TextType::class, array('label' => 'Postal code', 'required' => false, 'constraints' => [
			new Length(array('min' => 2, 'max' => 12))
		]));
		$builder->add('parishCity', TextType::class, array('label' => 'Parish city', 'required' => false, 'constraints' => [
			new Length(array('min' => 2, 'max' => 50))
		]));
		$builder->add('parishWebsite', UrlType::class, array('label' => 'Parish website', 'required' => false, 'constraints' => [
			new Url(array('message' => $this->translator->trans('ParishWebsiteUrlInvalidText')))
		]));
		$builder->add('responsiblePriest', TextType::class, array('label' => 'Priest', 'required' => false, 'constraints' => [
			new Length(array('min' => 2, 'max' => 60))
		]));
		$builder->add('responsibleCoordinator', TextType::class, array('label' => 'Area coordinator', 'required' => false, 'constraints' => [
			new Length(array('min' => 2, 'max' => 60))
		]));
		$builder->add('contactPhone', TextType::class, array('label' => 'Contact telephone', 'required' => false, 'attr' => ['help_text' => 'ContactPhoneHintText'], 'constraints' => [
			new Regex(array('pattern' => '/^[0-9\-\+ ]{9,16}$/', 'htmlPattern' => '^[0-9\-\+ ]{9,16}$', 'message' => $this->translator->trans('ContactPhoneNumberInvalidText')))
		]));
		$builder->add('areaWebsite', UrlType::class, array('label' => 'Area website', 'required' => false, 'constraints' => [
			new Url(array('message' => $this->translator->trans('AreaWebsiteUrlInvalidText')))
		]));
		$builder->add('facebookProfile', TextType::class, array('label' => 'Facebook profile', 'required' => false, 'attr' => ['help_text' => 'FacebookProfileHintText'], 'constraints' => [
			new Regex(array('pattern' => '/^[A-Za-z0-9\\.\\-]+$/', 'htmlPattern' => '^[A-Za-z0-9\\.\\-]+$', 'message' => $this->translator->trans('AreaFacebookProfileInvalidText')))
		]));
	}
	
	public function validateForm(array $data, ExecutionContextInterface $context)
	{
		if(empty($data['positionLng']) xor empty($data['positionLat'])) {
			$context->buildViolation('BothLattitudeAndLongitudeRequiredText')
				->atPath(empty($data['positionLng']) ? '[positionLng]' : '[positionLat]')
				->addViolation();
			return false;
		}
		return true;
	}
	
	public function createFormRenderer(): CustomFormRendererInterface
	{
		$r = new DefaultCustomFormRenderer();
		$r->group('EWC information', 'RequiredFieldsForProfilePublicationText');
		$r->fields('ewcDate');
		$r->group('Area location', 'AreaLocationInfoText');
		$r->fields('positionLat', 'positionLng');
		$r->group('Parish information');
		$r->fields('parishName', 'parishAddress', 'parishPostal', 'parishCity', 'parishWebsite');
		$r->group('Responsible persons');
		$r->fields('responsiblePriest', 'responsibleCoordinator');
		$r->group('Contact information');
		$r->fields('contactPhone', 'areaWebsite', 'facebookProfile');
		return $r;
	}
	
	public function createSummary(): CustomFormSummaryInterface
	{
		$s = new DefaultCustomFormSummary();
		$s->present('ewcDate', 'Date of Extreme Way of the Cross', 'date');
		$s->present('parishName', 'Parish name', 'string');
		$s->present('parishAddress', 'Parish address', 'string');
		$s->present('parishPostal', 'Postal code', 'string');
		$s->present('parishCity', 'Parish city', 'string');
		$s->present('parishWebsite', 'Parish website', 'callback-raw', function($url) {
			return '<a href="'.htmlspecialchars($url).'">'.htmlspecialchars($url).'</a>';
		});
		$s->present('responsiblePriest', 'Priest', 'string');
		$s->present('responsibleCoordinator', 'Area coordinator', 'string');
		$s->present('contactPhone', 'Contact telephone', 'string');
		$s->present('areaWebsite', 'Area website', 'callback-raw', function($url) {
			return '<a href="'.htmlspecialchars($url).'">'.htmlspecialchars($url).'</a>';
		});
		$s->present('facebookProfile', 'Facebook profile', 'string');
		return $s;
	}
	
	public function calculateCompleteness(array $data): int
	{
		$fieldCollection = ['positionLat', 'ewcDate', 'positionLng', 'parishName', 'parishAddress', 'parishPostal', 'parishCity', 'parishWebsite', 'responsiblePriest', 'responsibleCoordinator', 'contactPhone', 'areaWebsite', 'facebookProfile'];
	
		$total = sizeof($fieldCollection);
		$filled = 0;
		foreach ($fieldCollection as $name) {
			if (!empty($data[$name])) {
				if (is_array($data[$name])) {
					if (!empty($data[$name]['year'])) {
						$filled++;
					}
				} else {
					$filled++;
				}
			}
		}
		return (int) round(($filled / $total) * 100);
	}
	
	public function generateYears() {
		$current = (int) date('Y');
		
		$years = array(
			$current - 1,
			$current,
			$current + 1,
		);
		return $years;
	}
}
