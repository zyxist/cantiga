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
namespace Cantiga\CoreBundle\Generator;

/**
 * Generates the code for a basic CRUD panel structure.
 *
 * @author Tomasz Jędrzejewski
 */
class CrudGenerator extends Generator
{
	private $name;
	private $baseController;
	private $routePrefix;
	private $entityName;
	private $repoService;
	
	public function __construct(ReportInterface $reportIfc, $name, $baseController, $routePrefix, $entityName, $repoService)
	{
		parent::__construct($reportIfc);
		$this->name = $name;
		$this->baseController = $baseController;
		$this->routePrefix = $routePrefix;
		$this->entityName = $entityName;
		$this->repoService = $repoService;
	}
	
	public function generate()
	{
		$this->createControllerFile();
		$this->createFormFile();
		$this->createViewDirectory();
		$this->createIndexTemplate();
		$this->createInfoTemplate();
		$this->createInsertTemplate();
		$this->createEditTemplate();
		
		$this->printRoutingSnippet();
		$this->printMenuInformation();
	}
	
	private function createControllerFile()
	{
		$routeBeginning = str_replace('_', '/', $this->routePrefix);
$code = <<<EOF
<?php
namespace {$this->genNamespace('Controller')};

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Cantiga\CoreBundle\Api\Actions\CRUDInfo;
use Cantiga\CoreBundle\Api\Actions\RemoveAction;
use Cantiga\CoreBundle\Api\Actions\InsertAction;
use Cantiga\CoreBundle\Api\Actions\EditAction;
use Cantiga\CoreBundle\Api\Actions\InfoAction;
use Cantiga\CoreBundle\Api\Controller\\{$this->baseController};
use {$this->genNamespace('Form')}\\{$this->name}Form;
use {$this->genNamespace('Entity')}\\{$this->entityName};

/**
 * @Route("/{$routeBeginning}")
 * @Security("has_role('ROLE_USER')")
 */
class {$this->name}Controller extends {$this->baseController}
{
	const REPOSITORY_NAME = '{$this->repoService}';
	/**
	 * @var CRUDInfo
	 */
	private \$crudInfo;
	
	public function initialize(Request \$request, AuthorizationCheckerInterface \$authChecker)
	{
		\$this->crudInfo = \$this->newCrudInfo(self::REPOSITORY_NAME)
			->setTemplateLocation('{$this->genBundleName()}:{$this->name}:')
			->setItemNameProperty('name')
			->setPageTitle('{$this->entityName}s')
			->setPageSubtitle('Put some description here')
			->setIndexPage('{$this->routePrefix}_index')
			->setInfoPage('{$this->routePrefix}_info')
			->setInsertPage('{$this->routePrefix}_insert')
			->setEditPage('{$this->routePrefix}_edit')
			->setRemovePage('{$this->routePrefix}_remove')
			->setRemoveQuestion('Do you really want to remove \'0\' item?');
		
		\$this->breadcrumbs()
			->workgroup('workgroup_id')
			->entryLink(\$this->trans('{$this->entityName}s', [], 'pages'), \$this->crudInfo->getIndexPage());
	}
		
	/**
	 * @Route("/index", name="{$this->routePrefix}_index")
	 */
	public function indexAction(Request \$request)
	{
		\$repository = \$this->get(self::REPOSITORY_NAME);
		\$dataTable = \$repository->createDataTable();
		return \$this->render('{$this->genBundleName()}:{$this->name}:index.html.twig', array(
			'pageTitle' => \$this->crudInfo->getPageTitle(),
			'pageSubtitle' => \$this->crudInfo->getPageSubtitle(),
			'dataTable' => \$dataTable,
			'locale' => \$request->getLocale()
		));
	}
	
	/**
	 * @Route("/ajax-list", name="{$this->routePrefix}_ajax_list")
	 */
	public function ajaxListAction(Request \$request)
	{
		\$routes = \$this->dataRoutes()
			->link('info_link', '{$this->routePrefix}_info', ['id' => '::id'])
			->link('edit_link', '{$this->routePrefix}_edit', ['id' => '::id'])
			->link('remove_link', '{$this->routePrefix}_remove', ['id' => '::id']);

		\$repository = \$this->get(self::REPOSITORY_NAME);
		\$dataTable = \$repository->createDataTable();
		\$dataTable->process(\$request);
		return new JsonResponse(\$routes->process(\$repository->listData(\$dataTable)));
	}
	
	/**
	 * @Route("/{id}/info", name="{$this->routePrefix}_info")
	 */
	public function infoAction(\$id)
	{
		\$action = new InfoAction(\$this->crudInfo);
		return \$action->run(\$this, \$id);
	}
	 
	/**
	 * @Route("/insert", name="{$this->routePrefix}_insert")
	 */
	public function insertAction(Request \$request)
	{
		\$action = new InsertAction(\$this->crudInfo, new {$this->entityName}(), new {$this->name}Form());
		return \$action->run(\$this, \$request);
	}
	
	/**
	 * @Route("/{id}/edit", name="{$this->routePrefix}_edit")
	 */
	public function editAction(\$id, Request \$request)
	{
		\$action = new EditAction(\$this->crudInfo, new {$this->name}Form());
		return \$action->run(\$this, \$id, \$request);
	}
	
	/**
	 * @Route("/{id}/remove", name="{$this->routePrefix}_remove")
	 */
	public function removeAction(\$id, Request \$request)
	{
		\$action = new RemoveAction(\$this->crudInfo);
		return \$action->run(\$this, \$id, \$request);
	}
}
EOF;
		$this->save('Controller/'.$this->name.'Controller.php', $code);
	}
	
	private function createFormFile()
	{
$code = <<<EOF
<?php
namespace {$this->genNamespace('Form')};

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class {$this->name}Form extends AbstractType
{
	public function buildForm(FormBuilderInterface \$builder, array \$options)
	{
		\$builder
			->add('name', 'text', array('label' => 'Name'))
			->add('save', 'submit', array('label' => 'Save'));
	}

	public function getName()
	{
		return '{$this->entityName}';
	}
}
EOF;
		$this->save('Form/'.$this->name.'Form.php', $code);
	}
	
	private function createViewDirectory()
	{
		$this->createDirectory('Resources/views/'.$this->name);
	}
	
	private function createIndexTemplate()
	{
$code = <<<EOF
{% extends 'CantigaCoreBundle:layout:list-layout.html.twig' %}

{% block column_list %}
<tr>
	<th width="30">#</th>
	<th>{{ 'Name' | trans }}</th>
	<th width="120">{{ 'Actions' | trans({}, 'general') }}</th>
</tr>
{% endblock %}

{% block box_footer %}
<p>
	<a href="{{ path('{$this->routePrefix}_insert') }}" class="btn btn-success btn-sm" role="button">{{ 'Insert' | trans({}, 'general') }}</a>
</p>
{% endblock %}

{% block custom_datatable_config %}
	ajax: "{{ path('{$this->routePrefix}_ajax_list') }}",
	columnDefs: [
		{{ dt_actions(dataTable, [
			{ 'link': 'info_link', 'name': 'Info' | trans({}, 'general'), 'label': 'btn-primary' },
			{ 'link': 'edit_link', 'name': 'Edit' | trans({}, 'general'), 'label': 'btn-warning' },
			{ 'link': 'remove_link', 'name': 'Remove' | trans({}, 'general'), 'label': 'btn-danger' },
		]) }}
	]
{% endblock %}
EOF;
		$this->save('Resources/views/'.$this->name.'/index.html.twig', $code);
	}
	
	private function createInfoTemplate()
	{
$code = <<<EOF
{% extends 'CantigaCoreBundle:layout:common-layout.html.twig' %}

{% block box_header %}
	<h4>{{ 'Details: 0' | trans([item.name], 'general') }}</h4>
{% endblock %}

{% block box_body %}
<table class="table table-hover">
	<tbody>
		<tr>
			<td width="30%">{{ 'Name' | trans }}</td>
			<td>{{ item.name }}</td>
		</tr>
	</tbody>
</table>
{% endblock %}

{% block box_footer %}
<p>
	<a href="{{ path('{$this->routePrefix}_index') }}" class="btn btn-default btn-sm" role="button">{{ 'Back' | trans({}, 'general') }}</a>
	<a href="{{ path('{$this->routePrefix}_edit', {'id': item.id }) }}" class="btn btn-warning btn-sm" role="button">{{ 'Edit' | trans({}, 'general') }}</a>
	<a href="{{ path('{$this->routePrefix}_remove', {'id': item.id }) }}" class="btn btn-danger btn-sm" role="button">{{ 'Remove' | trans({}, 'general') }}</a>
</p>
{% endblock %}
EOF;
		$this->save('Resources/views/'.$this->name.'/info.html.twig', $code);
	}
	
	private function createInsertTemplate()
	{
$code = <<<EOF
{% extends 'CantigaCoreBundle:layout:common-layout.html.twig' %}

{% block box_body %}
	{{ form_start(form) }}
	{{ form_row(form.name) }}
	{{ form_end(form) }}
{% endblock %}
EOF;
		$this->save('Resources/views/'.$this->name.'/insert.html.twig', $code);
	}
	
	private function createEditTemplate()
	{
$code = <<<EOF
{% extends 'CantigaCoreBundle:layout:common-layout.html.twig' %}

{% block box_body %}
	{{ form_start(form) }}
	{{ form_row(form.name) }}
	{{ form_end(form) }}
{% endblock %}
EOF;
		$this->save('Resources/views/'.$this->name.'/edit.html.twig', $code);
	}
	
	private function printRoutingSnippet()
	{
		$this->reportIfc->reportStatus('<info>Add the following code to routing.yml:</info>');
		$this->reportIfc->reportStatus(<<<EOF
_{$this->routePrefix}_controller:
    resource: "@CantigaCoreBundle/Controller/{$this->name}Controller.php"
    type:     annotation
EOF
);
	}
	
	private function printMenuInformation()
	{
		$this->reportIfc->reportStatus('<info>Add the following code to your Workspace Listener:</info>');
		$this->reportIfc->reportStatus(<<<EOF
\$workspace->addWorkItem('workgroup_id', new WorkItem('{$this->routePrefix}_index', '{$this->entityName}s'));
EOF
);
	}
}
