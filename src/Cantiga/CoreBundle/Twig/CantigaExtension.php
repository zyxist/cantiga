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

use Cantiga\Branding;
use Cantiga\Components\Hierarchy\MembershipStorageInterface;
use Cantiga\CoreBundle\Api\Modules;
use Cantiga\CoreBundle\Api\Workspaces;
use Cantiga\CoreBundle\Api\WorkspaceSourceInterface;
use Cantiga\CoreBundle\Block\BlockLauncherInterface;
use Cantiga\Metamodel\DataTable;
use Cantiga\Metamodel\TimeFormatterInterface;
use ReflectionClass;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\Exception\LogicException;
use Twig_Environment;
use Twig_Extension;
use Twig_Extension_GlobalsInterface;
use Twig_SimpleFilter;
use Twig_SimpleFunction;
use function mb_strlen;
use function mb_strpos;
use function mb_substr;

/**
 * Exposes cantiga-specific helpers to Twig
 *
 * @author Tomasz Jędrzejewski
 */
class CantigaExtension extends Twig_Extension implements Twig_Extension_GlobalsInterface
{

	/**
	 * @var WorkspaceSourceInterface 
	 */
	private $workspaceSource;

	/**
	 * @var TimeFormatterInterface 
	 */
	private $timeFormatter;

	/**
	 * @var MembershipStorageInterface
	 */
	private $membershipStorage;

	/**
	 * @var RouterInterface
	 */
	private $router;

	/**
	 * @var BlockLauncherInterface
	 */
	private $blockLauncher;

	public function __construct(WorkspaceSourceInterface $workspaceSource, TimeFormatterInterface $timeFormatter, MembershipStorageInterface $membershipStorage, RouterInterface $router, BlockLauncherInterface $blockLauncher)
	{
		$this->workspaceSource = $workspaceSource;
		$this->timeFormatter = $timeFormatter;
		$this->membershipStorage = $membershipStorage;
		$this->router = $router;
		$this->blockLauncher = $blockLauncher;
	}

	public function getName()
	{
		return 'cantiga';
	}

	public function getGlobals()
	{
		$tf = new ReflectionClass(TimeFormatterInterface::class);
		$br = new ReflectionClass(Branding::class);
		$tfConstants = $tf->getConstants();
		$brConstants = $br->getConstants();

		return array('TimeFormatter' => $tfConstants, 'Branding' => $brConstants);
	}

	public function getFunctions()
	{
		return array(
			new Twig_SimpleFunction('dt_columns', [$this, 'dataTableColumns'], array('is_safe' => array('html'))),
			new Twig_SimpleFunction('dt_actions', [$this, 'dataTableActions'], array('is_safe' => array('html'))),
			new Twig_SimpleFunction('dt_col_link', [$this, 'dataTableLink'], array('is_safe' => array('html'))),
			new Twig_SimpleFunction('dt_col_label', [$this, 'dataTableLabel'], array('is_safe' => array('html'))),
			new Twig_SimpleFunction('dt_col_rewrite', [$this, 'dataTableRewrite'], array('is_safe' => array('html'))),
			new Twig_SimpleFunction('dt_col_boolean', [$this, 'dataTableBoolean'], array('is_safe' => array('html'))),
			new Twig_SimpleFunction('dt_col_progress', [$this, 'dataTableProgress'], array('is_safe' => array('html'))),
			new Twig_SimpleFunction('avatar', [$this, 'avatar']),
			new Twig_SimpleFunction('format_time', [$this, 'formatTime']),
			new Twig_SimpleFunction('format_date', [$this, 'formatDate']),
			new Twig_SimpleFunction('ago', [$this, 'ago']),
			new Twig_SimpleFunction('workspace_skin', [$this, 'workspaceSkin']),
			new Twig_SimpleFunction('use_icheck', [$this, 'useICheck'], array('is_safe' => array('html'))),
			new Twig_SimpleFunction('boolean_mark', [$this, 'booleanMark'], array('is_safe' => array('html'))),
			new Twig_SimpleFunction('empty_boolean_mark', [$this, 'emptyBooleanMark'], array('is_safe' => array('html'))),
			new Twig_SimpleFunction('callback_transform', [$this, 'callbackTransform'], array('is_safe' => array('html'))),
			new Twig_SimpleFunction('spath', [$this, 'spath']),
			new Twig_SimpleFunction('launch', [$this, 'launch'], array('is_safe' => array('html'))),
			new Twig_SimpleFunction('module_name', function($id) {
				$module = Modules::get($id);
				return $module['name'];
			}),
		);
	}
	
	public function getFilters()
	{
		return array(
			new Twig_SimpleFilter('truncate', [$this, 'truncate'], array('needs_environment' => true)),
		);
	}

	public function dataTableColumns(DataTable $dt, $useActionColumn = true)
	{
		$data = [];
		$id = null;
		foreach ($dt->getColumnDefinitions() as $column) {
			if ($column['type'] == DataTable::TYPE_ID) {
				$id = $column['name'];
			}
			$data[] = ['data' => $column['name']];
		}
		if ($useActionColumn && null !== $id) {
			$data[] = ['data' => $id];
		}
		return json_encode($data);
	}

	public function dataTableActions(DataTable $dt, array $actions)
	{
		$code = '{ targets: ' . $dt->columnCount() . ', render: function(data, type, row) { ' . "\n" . 'return ';

		$first = true;
		foreach ($actions as $action) {
			if (!isset($action['link']) || !isset($action['name']) || !isset($action['label'])) {
				throw new LogicException('The action is missing one of the properties: link, name, label');
			}
			if (!$first) {
				$code .= ' + ';
			}
			$first = false;
			if (isset($action['when'])) {
				$code .= '(row[\'' . $action['when'] . '\'] ? \'<a href="\' + row[\'' . $action['link'] . '\'] + \'" class="btn btn-xs ' . $action['label'] . '" role="button">' . $action['name'] . '</a> \' : \'\') ';
			} else {
				$code .= '\'<a href="\' + row[\'' . $action['link'] . '\'] + \'" class="btn btn-xs ' . $action['label'] . '" role="button">' . $action['name'] . '</a> \' ';
			}
		}
		$code .= ";\n } }";
		return $code;
	}

	public function dataTableLink(DataTable $dt, $columnName, $linkName)
	{
		$i = 0;
		foreach ($dt->getColumnDefinitions() as $column) {
			if ($column['name'] == $columnName) {
				return '{ targets: ' . $i . ', render: function(data, type, row) { if (row[\'' . $linkName . '\']) { return \'<a href="\'+row[\'' . $linkName . '\']+\'">\'+row[\'' . $columnName . '\']+\'</a>\'; } return \'--\'; } }, ';
			}
			$i++;
		}
		return '';
	}

	public function dataTableRewrite(DataTable $dt, $columnName, $takeFrom)
	{
		$i = 0;
		foreach ($dt->getColumnDefinitions() as $column) {
			if ($column['name'] == $columnName) {
				return '{ targets: ' . $i . ', render: function(data, type, row) { return row[\'' . $takeFrom . '\']; } }, ';
			}
			$i++;
		}
		return '';
	}

	public function dataTableLabel(DataTable $dt, $columnName, $takeText, $takeLabel)
	{
		$i = 0;
		foreach ($dt->getColumnDefinitions() as $column) {
			if ($column['name'] == $columnName) {
				return '{ targets: ' . $i . ', render: function(data, type, row) { return \'<span class="label label-\'+row[\'' . $takeLabel . '\']+\'">\'+row[\'' . $takeText . '\']+\'</span>\'; } }, ';
			}
			$i++;
		}
		return '';
	}

	public function dataTableBoolean(DataTable $dt, $columnName)
	{
		$i = 0;
		foreach ($dt->getColumnDefinitions() as $column) {
			if ($column['name'] == $columnName) {
				return '{ targets: ' . $i . ', render: function(data, type, row) { return (row[\'' . $columnName . '\'] == 1) ? \'<span class="glyphicon glyphicon-ok"></span>\' : \'\'; }, createdCell: function(td, cellData, rowData, row, col) { $(td).addClass(\'text-center\'); } }, ';
			}
			$i++;
		}
		return '';
	}

	public function dataTableProgress(DataTable $dt, $columnName, $color)
	{
		$i = 0;
		foreach ($dt->getColumnDefinitions() as $column) {
			if ($column['name'] == $columnName) {
				return '{ targets: ' . $i . ', render: function(data, type, row) { return \'<div class="progress progress-xs"><div class="progress-bar progress-bar-' . $color . '" style="width: \'+row[\'' . $columnName . '\']+\'%"></div></div>\'; } }, ';
			}
			$i++;
		}
		return '';
	}

	public function workspaceSkin()
	{
		$workspace = $this->workspaceSource->getWorkspace();

		if (null !== $workspace) {
			$workspaceInfo = Workspaces::get($workspace->getKey());
			return !empty($workspaceInfo['skin']) ? $workspaceInfo['skin'] : 'blue';
		}
		return 'blue';
	}

	public function spath($route, $args = [], $referenceType = null)
	{
		if ($this->membershipStorage->hasMembership()) {
			$args['slug'] = $this->membershipStorage->getMembership()->getPlace()->getSlug();
		}
		return $this->router->generate($route, $args, $referenceType);
	}

	public function launch($blockName, $args = [])
	{
		return $this->blockLauncher->launchBlock($blockName, $args);
	}

	public function formatTime($format, $utcTimestamp)
	{
		return $this->timeFormatter->format($format, $utcTimestamp);
	}

	public function formatDate(array $date)
	{
		return $this->timeFormatter->formatDate($date);
	}

	public function ago($utcTimestamp)
	{
		return $this->timeFormatter->ago($utcTimestamp);
	}

	public function useICheck()
	{
		return '	<script>
	  $(function () {
		$(\'input\').iCheck({
		  checkboxClass: \'icheckbox_square-blue\',
		  radioClass: \'iradio_square-blue\'
		});
	  });
	</script>';
	}

	public function booleanMark($value)
	{
		if ($value) {
			return '<span class="glyphicon glyphicon-ok"></span>';
		} else {
			return '<span class="glyphicon glyphicon-remove"></span>';
		}
	}

	public function emptyBooleanMark($value)
	{
		if ($value) {
			return '<p class="text-center"><span class="glyphicon glyphicon-ok"></span></p>';
		}
		return '';
	}

	public function callbackTransform($value, $callback)
	{
		return $callback($value);
	}

	public function avatar($user, $size = 128)
	{
		if (is_array($user)) {
			$avatar = $user['avatar'];
		} elseif (is_object($user)) {
			$avatar = $user->getAvatar();
		}
		if (!empty($avatar)) {
			$firstTwo = substr($avatar, 0, 2);
			$secondTwo = substr($avatar, 2, 2);
			return '/ph/' . $size . '/' . $firstTwo . '/' . $secondTwo . '/' . $avatar;
		} else {
			return '/ph/default.gif';
		}
	}
	
	/**
	 * Adapted from Twig Extensions project, author: Henrik Bjornskov, (c) 2009 Fabien Potencier
	 * 
	 * @see <a href="https://github.com/twigphp/Twig-extensions/blob/master/lib/Twig/Extensions/Extension/Text.php">https://github.com/twigphp/Twig-extensions/blob/master/lib/Twig/Extensions/Extension/Text.php</a>
	 * @param \Cantiga\CoreBundle\Twig\Twig_Environment $env
	 * @param string $value
	 * @param int $length
	 * @param boolean $preserve
	 * @param string $separator
	 * @return string
	 */
	public function truncate(Twig_Environment $env, $value, $length = 30, $preserve = false, $separator = '...')
	{
		if (mb_strlen($value, $env->getCharset()) > $length) {
			if ($preserve) {
				// If breakpoint is on the last word, return the value without separator.
				if (false === ($breakpoint = mb_strpos($value, ' ', $length, $env->getCharset()))) {
					return $value;
				}
				$length = $breakpoint;
			}
			return rtrim(mb_substr($value, 0, $length, $env->getCharset())) . $separator;
		}
		return $value;
	}
}
