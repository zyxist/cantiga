<?php
/*
 * This file is part of Cantiga Project. Copyright 2015 Tomasz Jedrzejewski.
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
namespace Cantiga\ForumBundle;

/**
 * @author Tomasz Jędrzejewski
 */
class ForumTables
{
	const FORUM_ROOT_TBL = 'cantiga_forum_roots';
	const FORUM_CATEGORY_TBL = 'cantiga_forum_categories';
	const FORUM_TBL = 'cantiga_forums';
	const TOPIC_TBL = 'cantiga_forum_topics';
	const POST_TBL = 'cantiga_forum_posts';
	const POST_CONTENT_TPL = 'cantiga_forum_post_content';
}
