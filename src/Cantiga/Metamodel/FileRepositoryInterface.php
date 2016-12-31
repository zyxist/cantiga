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
 */
interface FileRepositoryInterface
{
	/**
	 * Stores the uploaded file in the repository. Returns the hashed name, which is the key that
	 * can be used to find it later. You must store this key somewhere, otherwise you won't find
	 * this file.
	 * 
	 * @param \WIO\CommonUtilsBundle\Data\UploadedFile $file
	 * @return string
	 * @throws DiskAssetException
	 */
	public function storeFile(UploadedFile $file);
	/**
	 * Replaces an already uploaded file with a new content.
	 * 
	 * @param string $key Key used for storing this file in the repository
	 * @param UploadedFile $file New uploaded file
	 * @return Hashed name - will be the same as the first argument.
	 * @throws DiskAssetException
	 */
	public function replaceFile($key, UploadedFile $file);
	/**
	 * Generates a new key for the given file and copies it. An empty string is returned,
	 * if the generator was not able to create the file.
	 * 
	 * @param string $key
	 */
	public function duplicateFile(string $key): string;
	
	public function fileExists($key);
	
	public function getFileSize($key);
	/**
	 * Returns a handle to the file which allows reading it.
	 * 
	 * @param string $key Key used for storing this file in the repository
	 * @return resource
	 * @throws DiskAssetException
	 */
	public function getFileHandle($key);
	/**
	 * Starts the operation of downloading the file. You must specify an empty Response object 
	 * and set the download parameters.
	 * 
	 * @param string $key Key used for storing this file in the repository
	 * @param string $exposedName Name to show to the user
	 * @param string $mimeType MIME type to be introduced to the browser
	 * @param Response $response
	 * @throws DiskAssetException
	 */
	public function downloadFile($key, $exposedName, $mimeType, Response $response);
	/**
	 * Removes a file from the repository.
	 * 
	 * @param string $key Key used for storing this file in the repository
	 */
	public function removeFile($name);
}
