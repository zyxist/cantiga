CREATE TABLE IF NOT EXISTS `cantiga_edk_area_notes` (
  `areaId` int(11) NOT NULL,
  `noteType` tinyint(4) NOT NULL,
  `content` text NOT NULL,
  `lastUpdatedAt` int(11) NULL,
  PRIMARY KEY (`areaId`,`noteType`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cantiga_data_export` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(40) NOT NULL,
  `projectId` INT(11) NOT NULL,
  `areaStatusId` INT(11) NULL,
  `url` VARCHAR(100) NOT NULL,
  `encryptionKey` VARCHAR(128) NOT NULL,
  `active` tinyint(1) NOT NULL,
  `notes` TEXT NULL,
  `lastExportedAt` INT(11) NULL,
  PRIMARY KEY (`id`),
  KEY `projectId` (`projectId`),
  KEY `areaStatusId` (`areaStatusId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cantiga_edk_messages` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `areaId` INT(11) NOT NULL,
  `subject` VARCHAR(100) NOT NULL,
  `content` TEXT NOT NULL,
  `authorName` VARCHAR(50) NOT NULL,
  `authorEmail` VARCHAR(100) NULL,
  `authorPhone` VARCHAR(30) NULL,
  `createdAt` INT(11) NOT NULL,
  `answeredAt` INT(11) NOT NULL,
  `completedAt` INT(11) NOT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 0,
  `responderId` INT(11) NULL DEFAULT NULL,
  `duplicate` TINYINT(1) NOT NULL DEFAULT 0,
  `ipAddress` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `areaId` (`areaId`),
  KEY `responderId` (`responderId`),
  KEY `lastUpdate` (`ipAddress`, `createdAt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cantiga_edk_registration_settings` (
  `routeId` INT(11) NOT NULL,
  `areaId` INT(11) NOT NULL,
  `registrationType` TINYINT(4) NOT NULL,
  `startTime` INT(11) NULL,
  `endTime` INT(11) NULL,
  `externalRegistrationUrl` VARCHAR(100) NULL,
  `participantLimit` INT(11) NULL,
  `maxPeoplePerRecord` INT(11) NULL,
  `allowLimitExceed` TINYINT(4) NULL,
  `participantNum` INT(11) NOT NULL DEFAULT 0,
  `customQuestion` VARCHAR(200) NULL,
  PRIMARY KEY (`routeId`),
  KEY `areaId` (`areaId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cantiga_edk_participants` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `accessKey` VARCHAR(40) NOT NULL,
  `routeId` INT(11) NOT NULL,
  `areaId` INT(11) NOT NULL,
  `firstName` VARCHAR(30) NOT NULL,
  `lastName` VARCHAR(40) NOT NULL,
  `sex` TINYINT(1) NOT NULL,
  `age` TINYINT(4) NOT NULL,
  `email` VARCHAR(100) NULL,
  `peopleNum` TINYINT(1) NOT NULL DEFAULT 1,
  `customAnswer` VARCHAR(250),
  `whichOnce` TINYINT(4) NOT NULL,
  `whyParticipate` VARCHAR(200) NOT NULL,
  `findOut` TINYINT(4) NOT NULL,
  `findOutOther` VARCHAR(40) NULL,
  `confirmationKey` VARCHAR(40) COLLATE utf8_polish_ci NOT NULL,
  `termsAccepted` TINYINT(4) NOT NULL,
  `isConfirmed` TINYINT(4) NOT NULL DEFAULT '0',
  `createdAt` INT(11) NOT NULL,
  PRIMARY KEY(`id`),
  KEY(`routeId`),
  KEY(`areaId`),
  KEY `confirmedByArea` (`areaId`,`isConfirmed`),
  KEY `byLastName` (`areaId`, `lastName`),
  UNIQUE `accessKey` (`accessKey`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cantiga_edk_removed_participants` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `areaId` int(11) NOT NULL,
  `participantId` int(11) NOT NULL,
  `email` varchar(100) COLLATE utf8_polish_ci NOT NULL,
  `reason` varchar(150) COLLATE utf8_polish_ci NOT NULL,
  `removedAt` int(11) NOT NULL,
  `removedById` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `participantId` (`participantId`),
  KEY `areaId` (`areaId`),
  KEY `removedById` (`removedById`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE  `cantiga_edk_route_notes` ADD  `lastUpdatedAt` INT NULL ;

ALTER TABLE `cantiga_edk_area_notes`
   ADD CONSTRAINT `cantiga_edk_area_notes_fk1` FOREIGN KEY (`areaId`) REFERENCES `cantiga_areas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `cantiga_data_export`
   ADD CONSTRAINT `cantiga_data_export_fk1` FOREIGN KEY (`projectId`) REFERENCES `cantiga_projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `cantiga_data_export`
   ADD CONSTRAINT `cantiga_data_export_fk2` FOREIGN KEY (`areaStatusId`) REFERENCES `cantiga_area_statuses` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `cantiga_edk_messages`
   ADD CONSTRAINT `cantiga_edk_messages_fk1` FOREIGN KEY (`areaId`) REFERENCES `cantiga_areas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `cantiga_edk_messages`
   ADD CONSTRAINT `cantiga_edk_messages_fk2` FOREIGN KEY (`responderId`) REFERENCES `cantiga_users` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `cantiga_edk_registration_settings`
   ADD CONSTRAINT `cantiga_edk_registration_settings_fk1` FOREIGN KEY (`routeId`) REFERENCES `cantiga_edk_routes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `cantiga_edk_registration_settings`
   ADD CONSTRAINT `cantiga_edk_registration_settings_fk2` FOREIGN KEY (`areaId`) REFERENCES `cantiga_areas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `cantiga_edk_participants`
   ADD CONSTRAINT `cantiga_edk_participants_fk1` FOREIGN KEY (`areaId`) REFERENCES `cantiga_areas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `cantiga_edk_participants`
   ADD CONSTRAINT `cantiga_edk_participants_fk2` FOREIGN KEY (`routeId`) REFERENCES `cantiga_edk_routes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `cantiga_edk_removed_participants`
   ADD CONSTRAINT `cantiga_edk_removed_participants_fk1` FOREIGN KEY (`areaId`) REFERENCES `cantiga_areas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `cantiga_edk_removed_participants`
   ADD CONSTRAINT `cantiga_edk_removed_participants_fk2` FOREIGN KEY (`removedById`) REFERENCES `cantiga_users` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;