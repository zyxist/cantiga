<?php
namespace WIO\EdkBundle\CustomForm;

use Cantiga\Metamodel\CustomForm\CustomFormModelInterface;
use Cantiga\Metamodel\CustomForm\DefaultCustomFormRenderer;
use Cantiga\Metamodel\CustomForm\DefaultCustomFormSummary;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @author Tomasz Jędrzejewski
 */
class AreaModel implements CustomFormModelInterface
{
	private $translator;
	
	public function __construct(TranslatorInterface $translator)
	{
		$this->translator = $translator;
	}
	
	public function constructForm(FormBuilderInterface $builder)
	{
		$builder->add('positionLat', new NumberType, array('label' => 'Area location (lattitude)*', 'required' => false, 'attr' => ['help_text' => 'LattitudeHintText'], 'constraints' => [
			new Range(['min' => -90, 'max' => 90])
		]));
		$builder->add('positionLng', new NumberType, array('label' => 'Area location (longitude)*', 'required' => false, 'attr' => ['help_text' => 'LongitudeHintText'], 'constraints' => [
			new Range(['min' => -180, 'max' => 180])
		]));
		$builder->add('ewcDate', new DateType, array('label' => 'Date of Extreme Way of the Cross*', 'input' => 'array', 'required' => false, 'empty_value' => '-- none --', 'years' => $this->generateYears(), 'constraints' => [
		]));
		
		$builder->add('parishName', new TextType, array('label' => 'Parish name', 'required' => false, 'constraints' => [
			new Length(array('min' => 2, 'max' => 50))
		]));
		$builder->add('parishAddress', new TextType, array('label' => 'Parish address', 'required' => false, 'constraints' => [
			new Length(array('min' => 2, 'max' => 100))
		]));
		$builder->add('parishPostal', new TextType, array('label' => 'Postal code', 'required' => false, 'constraints' => [
			new Length(array('min' => 2, 'max' => 12))
		]));
		$builder->add('parishCity', new TextType, array('label' => 'Parish city', 'required' => false, 'constraints' => [
			new Length(array('min' => 2, 'max' => 50))
		]));
		$builder->add('parishWebsite', new UrlType, array('label' => 'Parish website', 'required' => false, 'constraints' => [
			new Url(array('message' => $this->translator->trans('ParishWebsiteUrlInvalidText')))
		]));
		$builder->add('responsiblePriest', new TextType, array('label' => 'Priest', 'required' => false, 'constraints' => [
			new Length(array('min' => 2, 'max' => 60))
		]));
		$builder->add('responsibleCoordinator', new TextType, array('label' => 'Area coordinator', 'required' => false, 'constraints' => [
			new Length(array('min' => 2, 'max' => 60))
		]));
		$builder->add('contactPhone', new TextType, array('label' => 'Contact telephone', 'required' => false, 'attr' => ['help_text' => 'ContactPhoneHintText'], 'constraints' => [
			new Regex(array('pattern' => '/^[0-9\-\+ ]{9,16}$/', 'htmlPattern' => '^[0-9\-\+ ]{9,16}$', 'message' => $this->translator->trans('ContactPhoneNumberInvalidText')))
		]));
		$builder->add('areaWebsite', new UrlType, array('label' => 'Area website', 'required' => false, 'constraints' => [
			new Url(array('message' => $this->translator->trans('AreaWebsiteUrlInvalidText')))
		]));
		$builder->add('facebookProfile', new UrlType, array('label' => 'Facebook profile', 'required' => false, 'attr' => ['help_text' => 'FacebookProfileHintText'], 'constraints' => [
			new Regex(array('pattern' => '/^[A-Za-z0-9\\.\\-]+$/', 'htmlPattern' => '^[A-Za-z0-9\\.\\-]+$', 'message' => $this->translator->trans('AreaFacebookProfileInvalidText')))
		]));
	}
	
	public function validateForm($data, ExecutionContextInterface $context)
	{
		if(empty($data['positionLng']) xor empty($data['positionLat'])) {
			$context->buildViolation('BothLattitudeAndLongitudeRequiredText')
				->atPath(empty($data['positionLng']) ? '[positionLng]' : '[positionLat]')
				->addViolation();
			return false;
		}
		return true;
	}
	
	public function createFormRenderer()
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
	
	public function createSummary()
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
