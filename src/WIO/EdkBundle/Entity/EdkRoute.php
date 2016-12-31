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
namespace WIO\EdkBundle\Entity;

use Cantiga\Components\Hierarchy\HierarchicalInterface;
use Cantiga\CoreBundle\CoreTables;
use Cantiga\CoreBundle\Entity\Area;
use Cantiga\CoreBundle\Entity\Group;
use Cantiga\CoreBundle\Entity\Message;
use Cantiga\CoreBundle\Entity\Project;
use Cantiga\Metamodel\Capabilities\EditableEntityInterface;
use Cantiga\Metamodel\Capabilities\IdentifiableInterface;
use Cantiga\Metamodel\Capabilities\InsertableEntityInterface;
use Cantiga\Metamodel\Capabilities\RemovableEntityInterface;
use Cantiga\Metamodel\DataMappers;
use Cantiga\Metamodel\Exception\DiskAssetException;
use Cantiga\Metamodel\Exception\ItemNotFoundException;
use Cantiga\Metamodel\Exception\ModelException;
use Cantiga\Metamodel\FileRepositoryInterface;
use Cantiga\Metamodel\TimeFormatterInterface;
use Doctrine\DBAL\Connection;
use LogicException;
use PDO;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use WIO\EdkBundle\EdkTables;

/**
 * Represents a single route of the Extreme Way of the Cross.
 */
class EdkRoute implements IdentifiableInterface, InsertableEntityInterface, EditableEntityInterface, RemovableEntityInterface
{
	const TYPE_FULL = 0;
	const TYPE_INSPIRED = 1;

	private $id;
	private $area;
	private $name;
	private $routeType;
	private $routeFrom;
	private $routeTo;
	private $routeCourse;
	private $routeLength;
	private $routeAscent;
	private $routeObstacles;
	private $createdAt;
	private $updatedAt;
	private $approved;
	private $descriptionFile;
	private $mapFile;
	private $gpsTrackFile;

	/**
	 * @var UploadedFile
	 */
	private $descriptionFileUpload;

	/**
	 * @var UploadedFile
	 */
	private $mapFileUpload;

	/**
	 * @var UploadedFile
	 */
	private $gpsTrackFileUpload;
	private $publicAccessSlug;
	private $commentNum;
	private $importedFrom;

	/**
	 * Additional notes related to the route.
	 * @var array
	 */
	private $notes = [];
	
	private $postedMessage = null;
	
	public static function fetchByRoot(Connection $conn, $id, HierarchicalInterface $root)
	{
		if ($root instanceof Area) {
			$data = $conn->fetchAssoc('SELECT * FROM `'.EdkTables::ROUTE_TBL.'` WHERE `areaId` = :areaId AND `id` = :id', [':id' => $id, ':areaId' => $root->getId()]);
		} elseif ($root instanceof Group) {
			$data = $conn->fetchAssoc('SELECT r.* '
				. 'FROM `'.EdkTables::ROUTE_TBL.'` r '
				. 'INNER JOIN `'.CoreTables::AREA_TBL.'` a ON a.`id` = r.`areaId` '
				. 'WHERE a.`groupId` = :groupId AND r.`id` = :id', [':id' => $id, ':groupId' => $root->getId()]);
		} elseif ($root instanceof Project) {
			$data = $conn->fetchAssoc('SELECT r.* '
				. 'FROM `'.EdkTables::ROUTE_TBL.'` r '
				. 'INNER JOIN `'.CoreTables::AREA_TBL.'` a ON a.`id` = r.`areaId` '
				. 'WHERE a.`projectId` = :projectId AND r.`id` = :id', [':id' => $id, ':projectId' => $root->getId()]);
		}
		if (false === $data) {
			return false;
		}
		$item = self::fromArray($data);
		if ($root instanceof Area) {
			$item->area = $root;
		} else {
			$item->area = Area::fetchByPlace($conn, $data['areaId'], $root);
		}

		$notes = $conn->fetchAll('SELECT * FROM `'.EdkTables::ROUTE_NOTE_TBL.'` WHERE `routeId` = :routeId', [':routeId' => $item->getId()]);
		foreach ($notes as $note) {
			$item->notes[$note['noteType']] = $note['content'];
		}
		
		return $item;
	}
	
	public static function fetchApproved(Connection $conn, $id)
	{
		$data = $conn->fetchAssoc('SELECT * FROM `'.EdkTables::ROUTE_TBL.'` WHERE `id` = :id AND `approved` = 1', [':id' => $id]);
		if (false === $data) {
			return false;
		}
		$item = self::fromArray($data);
		$item->area = Area::fetchActive($conn, $data['areaId']);
		return $item;
	}
	
	public static function fetchBySlug(Connection $conn, $slug)
	{
		$data = $conn->fetchAssoc('SELECT * FROM `'.EdkTables::ROUTE_TBL.'` WHERE `publicAccessSlug` = :slug', [':slug' => $slug]);
		if (false === $data) {
			return false;
		}
		return self::fromArray($data);
	}
	
	public static function fromArray($array, $prefix = '')
	{
		$item = new EdkRoute;
		DataMappers::fromArray($item, $array, $prefix);
		return $item;
	}
	
	public static function getRelationships()
	{
		return ['area'];
	}
	
	public static function loadValidatorMetadata(ClassMetadata $metadata)
	{
		$metadata->addConstraint(new Callback('validate'));

		$metadata->addPropertyConstraint('name', new NotBlank());
		$metadata->addPropertyConstraint('name', new Length(array('min' => 2, 'max' => 50)));
		$metadata->addPropertyConstraint('routeFrom', new NotBlank());
		$metadata->addPropertyConstraint('routeFrom', new Length(array('min' => 2, 'max' => 50)));
		$metadata->addPropertyConstraint('routeTo', new NotBlank());
		$metadata->addPropertyConstraint('routeTo', new Length(array('min' => 2, 'max' => 50)));
		$metadata->addPropertyConstraint('routeCourse', new NotBlank());
		$metadata->addPropertyConstraint('routeCourse', new Length(array('min' => 2, 'max' => 500)));
		$metadata->addPropertyConstraint('routeObstacles', new Length(array('min' => 0, 'max' => 100)));
	}
	
	public function validate(ExecutionContextInterface $context)
	{
		if ($this->routeType == self::TYPE_FULL)
		{
			if ($this->routeLength < 30) {
				$context->buildViolation('RouteLengthGreaterThan30Km')
					->atPath('routeLength')
					->addViolation();
				return false;
			}
			if ($this->routeLength >= 30 && $this->routeLength < 40) {
				if ($this->routeAscent < 500) {
					$context->buildViolation('Routes30To40KmMustHaveEnoughAscent')
						->atPath('routeAscent')
						->addViolation();
					return false;
				}
			}
		} else {
			if ($this->routeLength < 20) {
				$context->buildViolation('RouteLengthGreaterThan20Km')
					->atPath('routeLength')
					->addViolation();
				return false;
			}
			if ($this->routeAscent < 0) {
				$context->buildViolation('NegativeAscentInvalid')
					->atPath('routeAscent')
					->addViolation();
				return false;
			}
		}
		return true;
	}
	
	public function getId()
	{
		return $this->id;
	}

	public function getArea()
	{
		return $this->area;
	}

	public function getName()
	{
		return $this->name;
	}
	
	public function getRouteType()
	{
		return $this->routeType;
	}

	public function getRouteFrom()
	{
		return $this->routeFrom;
	}

	public function getRouteTo()
	{
		return $this->routeTo;
	}

	public function getRouteCourse()
	{
		return $this->routeCourse;
	}

	public function getRouteLength()
	{
		return $this->routeLength;
	}

	public function getRouteAscent()
	{
		return $this->routeAscent;
	}

	public function getRouteObstacles()
	{
		return $this->routeObstacles;
	}

	public function getCreatedAt()
	{
		return $this->createdAt;
	}

	public function getUpdatedAt()
	{
		return $this->updatedAt;
	}

	public function getApproved()
	{
		return $this->approved;
	}

	public function getDescriptionFile()
	{
		return $this->descriptionFile;
	}

	public function getMapFile()
	{
		return $this->mapFile;
	}

	public function getGpsTrackFile()
	{
		return $this->gpsTrackFile;
	}

	public function getDescriptionFileUpload()
	{
		return $this->descriptionFileUpload;
	}

	public function getMapFileUpload()
	{
		return $this->mapFileUpload;
	}

	public function getGpsTrackFileUpload()
	{
		return $this->gpsTrackFileUpload;
	}

	public function getPublicAccessSlug()
	{
		return $this->publicAccessSlug;
	}
	
	public function getCommentNum()
	{
		return $this->commentNum;
	}

	public function setId($id)
	{
		DataMappers::noOverwritingId($this->id);
		$this->id = $id;
		return $this;
	}

	public function setArea(Area $area)
	{
		$this->area = $area;
		return $this;
	}

	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}
	
	public function setRouteType($routeType)
	{
		$this->routeType = (int) $routeType;
		return $this;
	}

	public function setRouteFrom($routeFrom)
	{
		$this->routeFrom = $routeFrom;
		return $this;
	}

	public function setRouteTo($routeTo)
	{
		$this->routeTo = $routeTo;
		return $this;
	}

	public function setRouteCourse($routeCourse)
	{
		$this->routeCourse = $routeCourse;
		return $this;
	}

	public function setRouteLength($routeLength)
	{
		$this->routeLength = $routeLength;
		return $this;
	}

	public function setRouteAscent($routeAscent)
	{
		$this->routeAscent = $routeAscent;
		return $this;
	}

	public function setRouteObstacles($routeObstacles)
	{
		if (empty($routeObstacles)) {
			$this->routeObstacles = null;
		} else {
			$this->routeObstacles = $routeObstacles;
		}
		return $this;
	}

	public function setCreatedAt($createdAt)
	{
		$this->createdAt = $createdAt;
		return $this;
	}

	public function setUpdatedAt($updatedAt)
	{
		$this->updatedAt = $updatedAt;
		return $this;
	}

	public function setApproved($approved)
	{
		$this->approved = $approved;
		return $this;
	}

	public function setDescriptionFile($descriptionFile)
	{
		$this->descriptionFile = $descriptionFile;
		return $this;
	}

	public function setMapFile($mapFile)
	{
		$this->mapFile = $mapFile;
		return $this;
	}

	public function setGpsTrackFile($gpsTrackFile)
	{
		$this->gpsTrackFile = $gpsTrackFile;
		return $this;
	}

	public function setDescriptionFileUpload(UploadedFile $descriptionFileUpload)
	{
		$this->descriptionFileUpload = $descriptionFileUpload;
		return $this;
	}

	public function setMapFileUpload(UploadedFile $mapFileUpload)
	{
		$this->mapFileUpload = $mapFileUpload;
		return $this;
	}

	public function setGpsTrackFileUpload(UploadedFile $gpsTrackFileUpload)
	{
		$this->gpsTrackFileUpload = $gpsTrackFileUpload;
		return $this;
	}

	public function setPublicAccessSlug($publicAccessSlug)
	{
		$this->publicAccessSlug = $publicAccessSlug;
		return $this;
	}
	
	public function setCommentNum($commentNum)
	{
		DataMappers::noOverwritingField($this->commentNum);
		$this->commentNum = $commentNum;
	}
	
	function getImportedFrom()
	{
		return $this->importedFrom;
	}

	function setImportedFrom($importedFrom)
	{
		$this->importedFrom = $importedFrom;
	}
		
	public function getEditableNote($type)
	{
		if (!isset($this->notes[$type])) {
			return '';
		}
		return $this->notes[$type];
	}
	
	public function post(Message $message)
	{
		$this->postedMessage = $message;
	}
	
	public function getFullEditableNote(TranslatorInterface $translator, $type)
	{
		foreach (self::getNoteTypes() as $id => $name) {
			if ($id == $type) {
				$content = $this->getEditableNote($id);
				return ['id' => $id, 'name' => $translator->trans($name, [], 'edk'), 'content' => $content, 'editable' => $content];
			}
		}
		return ['id' => 0, 'name' => '', 'content' => ''];
	}
	
	public function getFullNoteInformation(TranslatorInterface $translator)
	{
		$result = [];
		foreach (self::getNoteTypes() as $id => $name) {
			$content = $this->getEditableNote($id);
			$result[] = ['id' => $id, 'name' => $translator->trans($name, [], 'edk'), 'content' => $content, 'editable' => $content];
		}
		return $result;
	}
	
	public function isDescriptionFilePublished()
	{
		return !empty($this->descriptionFile);
	}
	
	public function isMapFilePublished()
	{
		return !empty($this->mapFile);
	}
	
	/**
	 * @param Connection $conn Database connection
	 * @param int $type Note type (numbers from 1 to 4)
	 * @param string $content New content
	 * @throws ModelException
	 */
	public function saveEditableNote(Connection $conn, $type, $content)
	{
		if ($type < 1 || $type > 4) {
			throw new ModelException('Invalid note type.');
		}

		if (empty($content)) {
			$html = '';
			$conn->delete(EdkTables::ROUTE_NOTE_TBL, array('routeId' => $this->getId(), 'noteType' => $type));
			unset($this->notes[$type]);
		} else {
			$stmt = $conn->prepare('INSERT INTO `' . EdkTables::ROUTE_NOTE_TBL . '` (`routeId`, `noteType`, `content`, `lastUpdatedAt`) VALUES(:routeId, :noteType, :content, :lastUpdatedAt)'
				. ' ON DUPLICATE KEY UPDATE `content` = VALUES(`content`), `lastUpdatedAt` = VALUES(`lastUpdatedAt`)');
			$stmt->bindValue(':routeId', $this->getId());
			$stmt->bindValue(':noteType', $type);
			$stmt->bindValue(':content', $content);
			$stmt->bindValue(':lastUpdatedAt', time());
			$stmt->execute();
			$this->notes[$type] = $content;
		}
	}
	
	public function downloadDescription(FileRepositoryInterface $repository, Response $response)
	{
		if(null === $this->descriptionFile) {
			throw new ItemNotFoundException('There is no file with the route description available.', $this->id);
		}
		$repository->downloadFile($this->descriptionFile, 'edk-desc-route-' . $this->id . '.pdf', 'application/pdf', $response);
	}

	public function downloadMap(FileRepositoryInterface $repository, Response $response)
	{
		if(null === $this->mapFile) {
			throw new ItemNotFoundException('There is no file with the route map available.', $this->id);
		}
		
		if(strpos($this->mapFile, '.jpg') !== false) {
			$repository->downloadFile($this->mapFile, 'edk-map-route-' . $this->id . '.jpg', 'image/jpeg', $response);
		} else {
			$repository->downloadFile($this->mapFile, 'edk-map-route-' . $this->id . '.pdf', 'application/pdf', $response);
		}
	}

	public function downloadGpsTrack(FileRepositoryInterface $repository, Response $response)
	{
		$repository->downloadFile($this->gpsTrackFile, 'edk-gps-route-' . $this->id . '.kml', 'application/vnd.google-earth.kml+xml', $response);
	}
	
	public function storeFiles(FileRepositoryInterface $fileRepository)
	{
		if (!$this->getGpsTrackFileUpload() instanceof UploadedFile) {
			throw new ModelException('Files not uploaded!');
		}
		if($this->getDescriptionFileUpload() instanceof UploadedFile) {
			$this->verifyMIMEOfDescription();
		}
		if($this->getMapFileUpload() instanceof UploadedFile) {
			$this->verifyMIMEOfMap();
		}
		$this->verifyMIMEOfKML();

		if($this->getDescriptionFileUpload() instanceof UploadedFile) {
			$this->descriptionFile = $fileRepository->storeFile($this->getDescriptionFileUpload());
		}
		if($this->getMapFileUpload() instanceof UploadedFile) {
			$this->mapFile = $fileRepository->storeFile($this->getMapFileUpload());
		}
		$this->gpsTrackFile = $fileRepository->storeFile($this->getGpsTrackFileUpload());
	}
	
	public function updateFiles(FileRepositoryInterface $fileRepository)
	{
		if($this->getDescriptionFileUpload() instanceof UploadedFile) {
			$this->verifyMIMEOfDescription();
		}
		if($this->getMapFileUpload() instanceof UploadedFile) {
			$this->verifyMIMEOfMap();
		}
		$this->verifyMIMEOfKML();

		if (null !== $this->getDescriptionFileUpload()) {
			if(null === $this->descriptionFile) {
				$this->descriptionFile = $fileRepository->storeFile($this->getDescriptionFileUpload());
			} else {
				$this->descriptionFile = $fileRepository->replaceFile($this->descriptionFile, $this->getDescriptionFileUpload());
			}
		}
		if (null !== $this->getMapFileUpload()) {
			if(null === $this->mapFile) {
				$this->mapFile = $fileRepository->storeFile($this->getMapFileUpload());
			} else {
				$this->mapFile = $fileRepository->replaceFile($this->mapFile, $this->getMapFileUpload());
			}
		}
		if (null !== $this->getGpsTrackFileUpload()) {
			$this->gpsTrackFile = $fileRepository->replaceFile($this->gpsTrackFile, $this->getGpsTrackFileUpload());
		}
	}
	
	public function insert(Connection $conn)
	{
		if (null === $this->area) {
			throw new LogicException('EdkRoute requires ::area to be specified.');
		}

		$this->approved = false;
		
		$this->publicAccessSlug = sha1(time() . $this->routeAscent . $this->routeFrom . rand(-20000, 20000));
		$this->createdAt = time();
		$this->updatedAt = time();
		$conn->insert(
			EdkTables::ROUTE_TBL,
			DataMappers::pick($this, ['name', 'area', 'routeType', 'routeFrom', 'routeTo', 'routeCourse', 'routeLength', 'routeAscent', 'routeObstacles', 'createdAt', 'updatedAt', 'approved', 'descriptionFile', 'mapFile', 'gpsTrackFile', 'publicAccessSlug', 'importedFrom'])
		);
		$this->id = $conn->lastInsertId();
		return $this->id;
	}
	
	public function update(Connection $conn)
	{
		$this->updatedAt = time();
		
		if (null !== $this->postedMessage) {
			$this->commentNum = $conn->fetchColumn('SELECT `commentNum` FROM `'.EdkTables::ROUTE_TBL.'` WHERE `id` = :id', [':id' => $this->id]);
			$conn->insert(EdkTables::ROUTE_COMMENT_TBL, [
				'routeId' => $this->id,
				'userId' => $this->postedMessage->getUser()->getId(),
				'createdAt' => $this->postedMessage->getCreatedAt(),
				'message' => $this->postedMessage->getMessage()
			]);
			$this->commentNum++;
		}
		
		$conn->update(
			EdkTables::ROUTE_TBL,
			DataMappers::pick($this, ['name', 'routeType', 'routeFrom', 'routeTo', 'routeCourse', 'routeLength', 'routeAscent', 'routeObstacles', 'updatedAt', 'approved', 'descriptionFile', 'mapFile', 'gpsTrackFile', 'publicAccessSlug', 'commentNum']),
			DataMappers::pick($this, ['id'])
		);
	}
	
	public function canRemove()
	{
		return !$this->approved;
	}
	
	public function cleanupFiles(FileRepositoryInterface $fileRepository)
	{
		if (null !== $this->descriptionFile) {
			$fileRepository->removeFile($this->descriptionFile);
		}
		if (null !== $this->mapFile) {
			$fileRepository->removeFile($this->mapFile);
		}
		if (null !== $this->gpsTrackFile) {
			$fileRepository->removeFile($this->gpsTrackFile);
		}
	}

	public function remove(Connection $conn)
	{
		$conn->delete(EdkTables::ROUTE_TBL, DataMappers::pick($this, ['id']));
	}
	
	public function approve(Connection $conn)
	{
		$this->approved = (boolean) $conn->fetchColumn('SELECT `approved` FROM `'.EdkTables::ROUTE_TBL.'` WHERE `id` = :id', [':id' => $this->id]);
		if (!$this->approved) {
			$this->updatedAt = time();
			$this->approved = true;

			$conn->update(EdkTables::ROUTE_TBL,
				['updatedAt' => $this->updatedAt, 'approved' => $this->approved],
				['id' => $this->getId()]
			);
			return true;
		}
		return false;
	}
	
	public function revoke(Connection $conn)
	{
		$this->approved = (boolean) $conn->fetchColumn('SELECT `approved` FROM `'.EdkTables::ROUTE_TBL.'` WHERE `id` = :id', [':id' => $this->id]);
		if ($this->approved) {
			$this->updatedAt = time();
			$this->approved = false;

			$conn->update(EdkTables::ROUTE_TBL,
				['updatedAt' => $this->updatedAt, 'approved' => $this->approved],
				['id' => $this->getId()]
			);
			return true;
		}
		return false;
	}
	
	public function getComments(Connection $conn, TimeFormatterInterface $timeFormatter)
	{
		$stmt = $conn->prepare('SELECT m.`createdAt`, m.`message`, u.`id` AS `user_id`, u.`name`, u.`avatar` FROM `'.EdkTables::ROUTE_COMMENT_TBL.'` m '
			. 'INNER JOIN `'.CoreTables::USER_TBL.'` u ON u.`id` = m.`userId` '
			. 'WHERE m.`routeId` = :id ORDER BY m.`id`');
		$stmt->bindValue(':id', $this->id);
		$stmt->execute();
		$result = [];
		$direction = 1;
		$previousUser = null;
		while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			if ($previousUser != $row['user_id']) {
				$direction = ($direction == 1 ? 0 : 1);
			}
			$result[] = [
				'message' => $row['message'],
				'time' => $timeFormatter->ago($row['createdAt']),
				'author' => $row['name'],
				'avatar' => $row['avatar'],
				'dir' => $direction
			];
			$previousUser = $row['user_id'];
		}
		$stmt->closeCursor();
		return $result;
	}
	
	public static function getNoteTypes()
	{
		return [
			1 => 'RouteDescriptionNote',
			2 => 'RouteRecommendationNote',
			3 => 'RouteArrivalNote',
			4 => 'RouteSecurityNote',
		];
	}

	private function verifyMIMEOfDescription()
	{
		if (null !== $this->getDescriptionFileUpload()) {
			if ($this->getDescriptionFileUpload()->getMimeType() != 'application/pdf') {
				throw new DiskAssetException('The MIME type of the uploaded description is invalid.');
			}
			if ($this->getDescriptionFileUpload()->getClientOriginalExtension() != 'pdf') {
				throw new DiskAssetException('The extension of the uploaded description is invalid.');
			}
		}
	}

	private function verifyMIMEOfMap()
	{
		if (null !== $this->getMapFileUpload()) {
			if ($this->getMapFileUpload()->getMimeType() != 'application/pdf' && $this->getMapFileUpload()->getMimeType() != 'image/jpeg') {
				throw new DiskAssetException('The MIME type of the uploaded map is invalid.');
			}
			$ext = $this->getMapFileUpload()->getClientOriginalExtension();
			if ($ext != 'jpg' && $ext != 'pdf') {
				throw new DiskAssetException('The extension of the uploaded map is invalid.');
			}
		}
	}

	private function verifyMIMEOfKML()
	{
		if (null !== $this->getGpsTrackFileUpload()) {
			if ($this->getGpsTrackFileUpload()->getMimeType() != 'application/vnd.google-earth.kml+xml' && $this->getGpsTrackFileUpload()->getMimeType() != 'application/xml') {
				throw new DiskAssetException('The MIME type of the uploaded GPS track is invalid.');
			}
			if ($this->getGpsTrackFileUpload()->getClientOriginalExtension() != 'kml') {
				throw new DiskAssetException('The extension of the uploaded GPS track is invalid.');
			}
		}
	}
}
