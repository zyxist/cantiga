<?php
namespace WIO\EdkBundle;
use Cantiga\CoreBundle\Api\CustomForms;
use Cantiga\CoreBundle\Api\Modules;
use Symfony\Component\HttpKernel\Bundle\Bundle;


class WioEdkBundle extends Bundle
{
	public function boot()
	{
		Modules::registerModule('edk', 'EDK module');
		CustomForms::registerService('edk:area-request-form', 'wio.edk.form.area_request');
		CustomForms::registerService('edk:area-form', 'wio.edk.form.area');
	}
}
