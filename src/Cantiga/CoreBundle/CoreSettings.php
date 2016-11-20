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
namespace Cantiga\CoreBundle;

/**
 * Project settings required by this bundle.
 *
 * @author Tomasz Jędrzejewski
 */
class CoreSettings
{
	const AREA_NAME_HINT = 'core_area_name_hint';
	const AREA_REQUEST_INFO_TEXT = 'core_area_request_info_text';
	const AREA_REQUEST_FORM = 'core_area_request_form';
	const AREA_FORM = 'core_area_form';
	const DASHBOARD_SHOW_CHAT = 'core_dashboard_show_chat';
	const DASHOBARD_SHOW_REQUESTS = 'core_dashboard_show_requests';
}
