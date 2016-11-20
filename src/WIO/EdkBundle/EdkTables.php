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
namespace WIO\EdkBundle;

/**
 * @author Tomasz Jędrzejewski
 */
class EdkTables
{
	const ROUTE_TBL = 'cantiga_edk_routes';
	const ROUTE_NOTE_TBL = 'cantiga_edk_route_notes';
	const ROUTE_COMMENT_TBL = 'cantiga_edk_route_comments';
	const AREA_NOTE_TBL = 'cantiga_edk_area_notes';
	
	const MESSAGE_TBL = 'cantiga_edk_messages';
	const REGISTRATION_SETTINGS_TBL = 'cantiga_edk_registration_settings';
	const PARTICIPANT_TBL = 'cantiga_edk_participants';
	const REMOVED_PARTICIPANT_TBL = 'cantiga_edk_removed_participants';
	
	const STAT_PARTICIPANT_TIME_TBL = 'cantiga_stat_edk_participants';
	const STAT_AREA_PARTICIPANT_TIME_TBL = 'cantiga_stat_edk_area_participants';
}
