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
namespace Cantiga\CoreBundle\Twig;

use Twig_Extension;
use Twig_SimpleFunction;

/**
 * Taken from excellent Braincrafted Bootstrap bundle by Florian Eckerstorfer.
 *
 * @package    BraincraftedBootstrapBundle
 * @subpackage Twig
 * @author     Florian Eckerstorfer <florian@eckerstorfer.co>
 * @copyright  2012-2013 Florian Eckerstorfer
 * @license    http://opensource.org/licenses/MIT The MIT License
 * @link       http://bootstrap.braincrafted.com Bootstrap for Symfony2
 */
class BootstrapFormExtension extends Twig_Extension
{

	/** @var string */
	private $style;

	/** @var string */
	private $colSize = 'lg';

	/** @var integer */
	private $widgetCol = 10;

	/** @var integer */
	private $labelCol = 2;

	/** @var integer */
	private $simpleCol = false;

	/** @var array */
	private $settingsStack = array();

	/**
	 * @var string
	 */
	private $iconPrefix;

	/**
	 * @var string
	 */
	private $iconTag;

	/**
	 * @param string $iconPrefix
	 * @param string $iconTag
	 */
	public function __construct($iconPrefix, $iconTag = 'span')
	{
		$this->iconPrefix = $iconPrefix;
		$this->iconTag = $iconTag;
	}

	public function getFilters()
	{
		return array(
			new \Twig_SimpleFilter('parse_icons', [$this, 'parseIconsFilter'], array('pre_escape' => 'html', 'is_safe' => array('html')))
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFunctions()
	{
		return array(
			new Twig_SimpleFunction('bootstrap_set_style', array($this, 'setStyle')),
			new Twig_SimpleFunction('bootstrap_get_style', array($this, 'getStyle')),
			new Twig_SimpleFunction('bootstrap_set_col_size', array($this, 'setColSize')),
			new Twig_SimpleFunction('bootstrap_get_col_size', array($this, 'getColSize')),
			new Twig_SimpleFunction('bootstrap_set_widget_col', array($this, 'setWidgetCol')),
			new Twig_SimpleFunction('bootstrap_get_widget_col', array($this, 'getWidgetCol')),
			new Twig_SimpleFunction('bootstrap_set_label_col', array($this, 'setLabelCol')),
			new Twig_SimpleFunction('bootstrap_get_label_col', array($this, 'getLabelCol')),
			new Twig_SimpleFunction('bootstrap_set_simple_col', array($this, 'setSimpleCol')),
			new Twig_SimpleFunction('bootstrap_get_simple_col', array($this, 'getSimpleCol')),
			new Twig_SimpleFunction('bootstrap_backup_form_settings', array($this, 'backupFormSettings')),
			new Twig_SimpleFunction('bootstrap_restore_form_settings', array($this, 'restoreFormSettings')),
			new Twig_SimpleFunction(
				'checkbox_row', null, array('node_class' => 'Symfony\Bridge\Twig\Node\SearchAndRenderBlockNode', 'is_safe' => array('html'))
			),
			new Twig_SimpleFunction(
				'radio_row', null, array('node_class' => 'Symfony\Bridge\Twig\Node\SearchAndRenderBlockNode', 'is_safe' => array('html'))
			),
			new Twig_SimpleFunction(
				'global_form_errors', null, array('node_class' => 'Symfony\Bridge\Twig\Node\SearchAndRenderBlockNode', 'is_safe' => array('html'))
			),
			new Twig_SimpleFunction(
				'form_control_static', [$this, 'formControlStaticFunction'], array('is_safe' => array('html'))
			),
			new Twig_SimpleFunction(
				'icon', [$this,
				'iconFunction'], array('pre_escape' => 'html', 'is_safe' => array('html'))
			)
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getName()
	{
		return 'braincrafted_bootstrap_form';
	}

	/**
	 * Sets the style.
	 *
	 * @param string $style Name of the style
	 */
	public function setStyle($style)
	{
		$this->style = $style;
	}

	/**
	 * Returns the style.
	 *
	 * @return string Name of the style
	 */
	public function getStyle()
	{
		return $this->style;
	}

	/**
	 * Sets the column size.
	 *
	 * @param string $colSize Column size (xs, sm, md or lg)
	 */
	public function setColSize($colSize)
	{
		$this->colSize = $colSize;
	}

	/**
	 * Returns the column size.
	 *
	 * @return string Column size (xs, sm, md or lg)
	 */
	public function getColSize()
	{
		return $this->colSize;
	}

	/**
	 * Sets the number of columns of widgets.
	 *
	 * @param integer $widgetCol Number of columns.
	 */
	public function setWidgetCol($widgetCol)
	{
		$this->widgetCol = $widgetCol;
	}

	/**
	 * Returns the number of columns of widgets.
	 *
	 * @return integer Number of columns.Class
	 */
	public function getWidgetCol()
	{
		return $this->widgetCol;
	}

	/**
	 * Sets the number of columns of labels.
	 *
	 * @param integer $labelCol Number of columns.
	 */
	public function setLabelCol($labelCol)
	{
		$this->labelCol = $labelCol;
	}

	/**
	 * Returns the number of columns of labels.
	 *
	 * @return integer Number of columns.
	 */
	public function getLabelCol()
	{
		return $this->labelCol;
	}

	/**
	 * Sets the number of columns of simple widgets.
	 *
	 * @param integer $simpleCol Number of columns.
	 */
	public function setSimpleCol($simpleCol)
	{
		$this->simpleCol = $simpleCol;
	}

	/**
	 * Returns the number of columns of simple widgets.
	 *
	 * @return integer Number of columns.
	 */
	public function getSimpleCol()
	{
		return $this->simpleCol;
	}

	/**
	 * Backup the form settings to the stack.
	 *
	 * @internal Should only be used at the beginning of form_start. This allows
	 *           a nested subform to change its settings without affecting its
	 *           parent form.
	 */
	public function backupFormSettings()
	{
		$settings = array(
			'style' => $this->style,
			'colSize' => $this->colSize,
			'widgetCol' => $this->widgetCol,
			'labelCol' => $this->labelCol,
			'simpleCol' => $this->simpleCol,
		);

		array_push($this->settingsStack, $settings);
	}

	/**
	 * Restore the form settings from the stack.
	 *
	 * @internal Should only be used at the end of form_end.
	 * @see backupFormSettings
	 */
	public function restoreFormSettings()
	{
		if (count($this->settingsStack) < 1) {
			return;
		}

		$settings = array_pop($this->settingsStack);

		$this->style = $settings['style'];
		$this->colSize = $settings['colSize'];
		$this->widgetCol = $settings['widgetCol'];
		$this->labelCol = $settings['labelCol'];
		$this->simpleCol = $settings['simpleCol'];
	}

	/**
	 * @param string $label
	 * @param string $value
	 *
	 * @return string
	 */
	public function formControlStaticFunction($label, $value)
	{
		return sprintf(
			'<div class="form-group"><label class="col-sm-%s control-label">%s</label><div class="col-sm-%s"><p class="form-control-static">%s</p></div></div>', $this->getLabelCol(), $label, $this->getWidgetCol(), $value
		);
	}

	/**
	 * Parses the given string and replaces all occurrences of .icon-[name] with the corresponding icon.
	 *
	 * @param string $text The text to parse
	 *
	 * @return string The HTML code with the icons
	 */
	public function parseIconsFilter($text)
	{
		$that = $this;
		return preg_replace_callback(
			'/\.([a-z]+)-([a-z0-9+-]+)/', function ($matches) use ($that) {
			return $that->iconFunction($matches[2], $matches[1]);
		}, $text
		);
	}

	/**
	 * Returns the HTML code for the given icon.
	 *
	 * @param string $icon The name of the icon
	 * @param string $iconSet The icon-set name
	 *
	 * @return string The HTML code for the icon
	 */
	public function iconFunction($icon, $iconSet = 'icon')
	{
		if ($iconSet == 'icon')
			$iconSet = $this->iconPrefix;
		$icon = str_replace('+', ' ' . $iconSet . '-', $icon);
		return sprintf('<%1$s class="%2$s %2$s-%3$s"></%1$s>', $this->iconTag, $iconSet, $icon);
	}

}
