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
namespace Cantiga\Metamodel;

use Cantiga\Metamodel\Exception\DiskAssetException;
use LogicException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

/**
 * The file repository manages everything that has been uploaded to the server as a file.
 * We can order saving some uploaded file for us and return the key that allows to find it
 * later. 
 * 
 * <p>The repository is not a gateway that controls the access to the file. This part must
 * be implemented separately, as well as paying attention to remove the uploaded files, if
 * the connected resource is being removed.
 * 
 * <p>The filenames are hashed. The original names must be stored together with the resource.
 * You shall neither show the hashed slug to the user, nor allow downloading the file via this
 * slug!
 *
 * @author Tomasz Jędrzejewski
 */
class FileRepository implements FileRepositoryInterface
{
	/**
	 * Directory, where we store all the files.
	 * @var string 
	 */
	private $targetDirectory;
	
	public function __construct($targetDirectory)
	{
		$this->targetDirectory = $targetDirectory;
	}
	
	public function storeFile(UploadedFile $file)
	{
		$hashedName = sha1($file->getBasename().filemtime($file->getPath()));
		$extension = strtolower($file->getClientOriginalExtension());
		
		$finalName = $hashedName.'.'.$extension;
		while(file_exists($this->targetDirectory.DIRECTORY_SEPARATOR.$this->hashToLocation($finalName))) {
			$hashedName .= '1';
			$finalName = $hashedName.'.'.$extension;
		}
		$hashed = $this->hashToLocation($finalName);
		$directory = dirname($this->targetDirectory.DIRECTORY_SEPARATOR.$hashed);
		if(!is_dir($directory)) {
			$ret = mkdir($directory, 0777, true);
			if(!$ret) {
				throw new DiskAssetException('Cannot create a directory for uploading the files!');
			}
		}
		$file->move($directory, $hashed);
		return $finalName;
	}
	
	public function replaceFile($name, UploadedFile $file)
	{
		$fullPath = $this->targetDirectory.DIRECTORY_SEPARATOR.$this->hashToLocation($name);
		if(!file_exists($fullPath)) {
			return $this->storeFile($file);
		}
		$directory = dirname($fullPath);
		
		$extension = strtolower($file->getClientOriginalExtension());
		// extension changed, remove the old file and update the name accordingly
		if (strpos($name, '.'.$extension) === false) {
			unlink($fullPath);
			$extStart = strrpos($name, '.');
			$name = substr($name, 0, $extStart).'.'.$extension;
		}
		
		$file->move($directory, $name);
		return $name;
	}
	
	public function duplicateFile(string $name): string
	{
		$path = $this->targetDirectory.DIRECTORY_SEPARATOR.$this->hashToLocation($name);
		if(!file_exists($path)) {
			return '';
		}
		if (-1 !== ($extStart = strrpos($name, '.'))) {
			$extension = substr($name, $extStart + 1);
		} else {
			$extension = '';
		}
		$hashedName = sha1($path.time());
		$finalName = $hashedName.'.'.$extension;
		while(file_exists($this->targetDirectory.DIRECTORY_SEPARATOR.$this->hashToLocation($finalName))) {
			$hashedName .= '1';
			$finalName = $hashedName.'.'.$extension;
		}
		
		$hashed = $this->hashToLocation($finalName);
		$directory = dirname($this->targetDirectory.DIRECTORY_SEPARATOR.$hashed);
		if(!is_dir($directory)) {
			$ret = mkdir($directory, 0777, true);
			if(!$ret) {
				throw new DiskAssetException('Cannot create a directory for uploading the files!');
			}
		}
		copy($path, $this->targetDirectory.DIRECTORY_SEPARATOR.$hashed);
		return $finalName;
	}
	
	public function fileExists($name)
	{
		return file_exists($this->targetDirectory.DIRECTORY_SEPARATOR.$this->hashToLocation($name));
	}
	
	public function getFileSize($name)
	{
		return filesize($this->targetDirectory.DIRECTORY_SEPARATOR.$this->hashToLocation($name));
	}

	public function getFileHandle($name)
	{
		$path = $this->targetDirectory.DIRECTORY_SEPARATOR.$this->hashToLocation($name);
		if(!file_exists($path)) {
			throw new DiskAssetException('The specified file \''.$name.'\' does not exist in the file repository.');
		}
		return fopen($path, 'r');
	}

	public function downloadFile($name, $exposedName, $mimeType, Response $response)
	{
		$path = $this->targetDirectory.DIRECTORY_SEPARATOR.$this->hashToLocation($name);
		if(!file_exists($path)) {
			throw new DiskAssetException('The file \''.$name.'\' is not accessible.');
		}
		$response->headers->set('Content-type', $mimeType);
		$response->headers->set('Content-Disposition', 'attachment; filename="'.$exposedName.'"');
		$response->headers->set('Content-Length', filesize($path));
		$response->setContent(file_get_contents($path));
	}
	
	/**
	 * Usuwa plik z repozytorium.
	 * 
	 * @param string $name
	 */
	public function removeFile($name)
	{
		$path = $this->targetDirectory.DIRECTORY_SEPARATOR.$this->hashToLocation($name);
		if(!file_exists($path)) {
			throw new DiskAssetException('The file \''.$this->filename.'\' is not accessible.');
		}
		unlink($path);
	}
	
	private function hashToLocation($name)
	{
		if(strlen($name) < 40) {
			throw new LogicException('This is not a hash: '.$name);
		} 
		$firstLevel = $name[0];
		$secondLevel = $firstLevel.$name[1];
		
		return $firstLevel.DIRECTORY_SEPARATOR.$secondLevel.DIRECTORY_SEPARATOR.$name;
	}
	
}
