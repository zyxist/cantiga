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
namespace Cantiga\CoreBundle\Extension;

use Cantiga\CoreBundle\Entity\Project;

/**
 * Allows writing "magic buttons" to the "Magic buttons" page. The page displays the buttons
 * that do something, when they are clicked (i.e. export some data). Usually they are used for
 * handling the most commonly requested actions.
 * 
 * @author Tomasz Jędrzejewski
 */
interface MagicButtonExtension
{
	public function execute(Project $project);
}
