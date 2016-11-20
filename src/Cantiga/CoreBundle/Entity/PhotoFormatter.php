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
namespace Cantiga\CoreBundle\Entity;

use Cantiga\Metamodel\Exception\ModelException;

/**
 * Entity that represents a photo and allows formatting it.
 *
 * @author Tomasz Jędrzejewski
 */
class PhotoFormatter
{
	private $path;
	private $type;
	private $width;
	private $height;
	
	private $output;
	private $newName;
	
	public function __construct($path, $minSize, $maxSize, $output)
	{
		if (!file_exists($path)) {
			throw new ModelException("The image file to format is not accessible.");
		}
		
		$result = getimagesize($path);
		if ($result === false) {
			throw new ModelException("The specified file is not an image.");
		}
		
		$this->type = $result[2];
		$this->width = $result[0];
		$this->height = $result[1];
		
		if (!$this->isTypeSupported()) {
			throw new ModelException("The specified image format is not supported.");
		}
		
		if ($this->width < $minSize || $this->height < $minSize) {
			throw new ModelException("The specified image is not big enough. I cannot scale it up.");
		}
		if ($this->width > $maxSize || $this->height > $maxSize) {
			throw new ModelException("The specified image is too big.");
		}
		$this->path = $path;
		$this->output = $output;
	}
	
	public function setNewName($newName)
	{
		$this->newName = $newName;
	}
	
	public function loadAndScale($startScale)
	{
		$baseImage = $this->loadImageByType();
		
		if ($this->width == $startScale && $this->height == $startScale) {
			$this->outputResampled($baseImage, $startScale);
		} else {
			$this->outputResampled($this->resample($baseImage, $this->width, $this->height, $startScale), $startScale);
		}
		
		while ($startScale > 16) {
			$startScale = (int) ($startScale / 2);
			$this->outputResampled($this->resample($baseImage, $this->width, $this->height, $startScale), $startScale);
		}
	}
	
	public function removeOld($name, $startScale)
	{
		$firstTwo = substr($name, 0, 2);
		$secondTwo = substr($name, 2, 2);
		$startScale *= 2;
		while ($startScale > 16) {
			$startScale = (int) ($startScale / 2);
			$path = $this->output.DIRECTORY_SEPARATOR.$startScale.DIRECTORY_SEPARATOR.$firstTwo.DIRECTORY_SEPARATOR.$secondTwo.DIRECTORY_SEPARATOR.$name;
			if (file_exists($path)) {
				unlink($path);
			}
		}
	}
	
	private function isTypeSupported()
	{
		return ($this->type == IMAGETYPE_JPEG || $this->type == IMAGETYPE_JPEG2000 || $this->type == IMAGETYPE_PNG || $this->type == IMAGETYPE_GIF);
	}
	
	private function loadImageByType()
	{
		switch ($this->type) {
			case IMAGETYPE_JPEG:
			case IMAGETYPE_JPEG2000:
				return imagecreatefromjpeg($this->path);
			case IMAGETYPE_PNG:
				return imagecreatefrompng($this->path);
			case IMAGETYPE_GIF:
				return imagecreatefromgif($this->path);
		}
	}
	
	private function resample($base, $oldSizeX, $oldSizeY, $newSize)
	{
		$newImage = imagecreatetruecolor($newSize, $newSize);
		if ($oldSizeX == $oldSizeY) {
			imagecopyresampled($newImage, $base, 0, 0, 0, 0, $newSize, $newSize, $oldSizeX, $oldSizeY);
		} else {
			if ($oldSizeX < $oldSizeY) {
				$startY = ($oldSizeY - $oldSizeX) / 2;
				$oldSizeY = $oldSizeX;
				$startX = 0;
			} else {
				$startX = ($oldSizeX - $oldSizeY) / 2;
				$oldSizeX = $oldSizeY;
				$startY = 0;
			}
			imagecopyresampled($newImage, $base, 0, 0, $startX, $startY, $newSize, $newSize, $oldSizeX, $oldSizeY);
		}
		return $newImage;
	}
	
	private function outputResampled($image, $size)
	{
		$firstTwo = substr($this->newName, 0, 2);
		$secondTwo = substr($this->newName, 2, 2);
		$dir = $this->output.DIRECTORY_SEPARATOR.$size.DIRECTORY_SEPARATOR.$firstTwo.DIRECTORY_SEPARATOR.$secondTwo;
		if (!is_dir($dir)) {
			mkdir($dir, 0777, true);
		}
		
		$pp = $dir.DIRECTORY_SEPARATOR.$this->newName;
		switch ($this->type) {
			case IMAGETYPE_JPEG:
			case IMAGETYPE_JPEG2000:
				imagejpeg($image, $pp);
				break;
			case IMAGETYPE_PNG:
				imagepng($image, $pp);
				break;
			case IMAGETYPE_GIF:
				imagegif($image, $pp);
		}
	}
}
