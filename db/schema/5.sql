CREATE TABLE IF NOT EXISTS `cantiga_edk_routes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `areaId` int(11) NOT NULL,
  `routeType` tinyint(1) NOT NULL,
  `name` varchar(50) COLLATE utf8_polish_ci NOT NULL,
  `routeFrom` varchar(50) COLLATE utf8_polish_ci NOT NULL,
  `routeTo` varchar(50) COLLATE utf8_polish_ci NOT NULL,
  `routeCourse` varchar(500) COLLATE utf8_polish_ci NOT NULL,
  `routeLength` int(11) NOT NULL,
  `routeAscent` int(11) NOT NULL,
  `routeObstacles` VARCHAR( 100 ) NULL DEFAULT NULL,
  `createdAt` int(11) NOT NULL,
  `updatedAt` int(11) NOT NULL,
  `approved` tinyint(4) NOT NULL DEFAULT '0',
  `descriptionFile` VARCHAR( 60 ) NULL ,
  `mapFile` VARCHAR( 60 ) NULL ,
  `gpsTrackFile` VARCHAR( 60 ) NOT NULL ,
  `publicAccessSlug` VARCHAR( 40 ) NOT NULL ,
  `commentNum` INT(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `approved` (`approved`),
  KEY `areaId` (`areaId`),
  KEY `routeFrom` (`routeFrom`),
  KEY `routeTo` (`routeTo`),
  KEY `routeLength` (`routeLength`),
  UNIQUE `publicAccessSlug` (`publicAccessSlug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `cantiga_edk_route_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `routeId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `createdAt` int(11) NOT NULL,
  `message` varchar(500) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `routeId` (`routeId`),
  KEY `userId` (`userId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cantiga_edk_route_notes` (
  `routeId` int(11) NOT NULL,
  `noteType` tinyint(4) NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY (`routeId`,`noteType`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `cantiga_edk_routes`
  ADD CONSTRAINT `cantiga_edk_routes_fk_1` FOREIGN KEY (`areaId`) REFERENCES `cantiga_areas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `cantiga_edk_route_notes` ADD CONSTRAINT `cantiga_edk_route_notes_ibfk_1` FOREIGN KEY (`routeId`) REFERENCES `cantiga_edk_routes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `cantiga_edk_route_comments`
  ADD CONSTRAINT `cantiga_edk_route_comments_ibfk_1` FOREIGN KEY (`routeId`) REFERENCES `cantiga_edk_routes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cantiga_edk_route_comments_ibfk_2` FOREIGN KEY (`userId`) REFERENCES `cantiga_users` (`id`);