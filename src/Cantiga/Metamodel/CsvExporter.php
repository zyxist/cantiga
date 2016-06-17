<?php
/*
 * This file is part of Cantiga Project. Copyright 2015-2016 Tomasz Jedrzejewski.
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
namespace Cantiga\Metamodel;

/**
 * Simple wrapper for exporting the data as a CSV file to download.
 * It enforces consistent CSV settings across the entire project.
 *
 * @author Tomasz Jędrzejewski
 */
class CsvExporter
{
	const CELL_DELIMITER = ';';
	const FLUSH_EVERY = 5;
	
	private $feed;
	
	public function __construct (CsvFeedInterface $feed)
	{
		$this->feed = $feed;
	}
	
	public function export()
	{
		$out = fopen('php://output', 'w');
		$i = 0;
		fputcsv($out, $this->feed->createHeader(), self::CELL_DELIMITER);
		while($row = $this->feed->createRow()) {
			fputcsv($out, $row, self::CELL_DELIMITER);
			if(($i++) % self::FLUSH_EVERY == 0) {
				fflush($out);
			}
		}
		fclose($out);
	}
}
