SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

CREATE TABLE `cantiga_areas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(12) NOT NULL,
  `projectId` int(11) NOT NULL,
  `groupId` int(11) DEFAULT NULL,
  `groupName` varchar(50) DEFAULT NULL,
  `statusId` int(11) NOT NULL,
  `territoryId` int(11) NOT NULL,
  `reporterId` int(11) DEFAULT NULL,
  `createdAt` int(11) NOT NULL,
  `lastUpdatedAt` int(11) NOT NULL,
  `percentCompleteness` int(11) NOT NULL,
  `visiblePublicly` int(11) NOT NULL,
  `memberNum` int(11) NOT NULL,
  `customData` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `projectId` (`projectId`),
  KEY `groupId` (`groupId`),
  KEY `statusId` (`statusId`),
  KEY `territoryId` (`territoryId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `cantiga_area_members` (
  `areaId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `role` int(11) NOT NULL,
  `note` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`areaId`,`userId`),
  KEY `userId` (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `cantiga_area_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `projectId` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `requestorId` int(11) NOT NULL,
  `verifierId` int(11) DEFAULT NULL,
  `territoryId` int(11) NOT NULL,
  `createdAt` int(11) NOT NULL,
  `lastUpdatedAt` int(11) NOT NULL,
  `customData` text NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `commentNum` int(11) NOT NULL DEFAULT '0',
  `areaId` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reporterId` (`requestorId`),
  KEY `verifierId` (`verifierId`),
  KEY `areaId` (`areaId`),
  KEY `territoryId` (`territoryId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `cantiga_area_request_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `requestId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `createdAt` int(11) NOT NULL,
  `message` varchar(500) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `requestId` (`requestId`),
  KEY `userId` (`userId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `cantiga_area_statuses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL,
  `label` varchar(30) NOT NULL,
  `isDefault` tinyint(4) NOT NULL DEFAULT '0',
  `areaNum` int(11) NOT NULL DEFAULT '0',
  `projectId` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `projectId` (`projectId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `cantiga_credential_changes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `provisionKey` varchar(40) NOT NULL,
  `password` varchar(100) DEFAULT NULL,
  `salt` varchar(40) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `requestIp` int(11) NOT NULL,
  `requestTime` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `cantiga_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `slug` varchar(12) NOT NULL,
  `projectId` int(11) NOT NULL,
  `memberNum` int(11) NOT NULL,
  `areaNum` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `projectId` (`projectId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `cantiga_group_members` (
  `groupId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `role` int(11) NOT NULL,
  `note` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`groupId`,`userId`),
  KEY `userId` (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `cantiga_invitations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `userId` int(11) DEFAULT NULL,
  `role` int(11) NOT NULL,
  `note` varchar(30) NOT NULL,
  `resourceType` varchar(30) NOT NULL,
  `resourceId` int(11) NOT NULL,
  `resourceName` varchar(100) NOT NULL,
  `inviterId` int(11) NOT NULL,
  `createdAt` int(11) NOT NULL,
  `assignmentKey` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniqueCombination` (`email`,`resourceType`,`resourceId`),
  KEY `inviterId` (`inviterId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `cantiga_languages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `locale` varchar(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

INSERT INTO `cantiga_languages` VALUES(1, 'English', 'en');
INSERT INTO `cantiga_languages` VALUES(2, 'Polski', 'pl');

CREATE TABLE `cantiga_links` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `url` varchar(100) NOT NULL,
  `projectId` int(11) DEFAULT NULL,
  `presentedTo` tinyint(4) NOT NULL DEFAULT '0',
  `listOrder` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `projectId` (`projectId`),
  KEY `projectId_2` (`projectId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `cantiga_mail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `place` varchar(40) NOT NULL,
  `locale` varchar(10) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `lastUpdate` int(11) NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_locale_pair` (`place`,`locale`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `cantiga_password_recovery` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `provisionKey` varchar(40) NOT NULL,
  `requestIp` int(11) NOT NULL,
  `requestTime` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`,`provisionKey`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `cantiga_projects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL,
  `slug` varchar(12) NOT NULL,
  `description` varchar(1000) NOT NULL,
  `parentProjectId` int(11) DEFAULT NULL,
  `modules` varchar(1000) NOT NULL,
  `areasAllowed` tinyint(4) NOT NULL,
  `areaRegistrationAllowed` tinyint(4) NOT NULL,
  `archived` tinyint(4) NOT NULL,
  `createdAt` int(11) NOT NULL,
  `archivedAt` int(11) DEFAULT NULL,
  `memberNum` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `parentProjectId` (`parentProjectId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `cantiga_project_members` (
  `projectId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `role` int(11) NOT NULL,
  `note` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`projectId`,`userId`),
  KEY `userId` (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `cantiga_project_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `projectId` int(11) NOT NULL,
  `module` varchar(30) NOT NULL,
  `name` varchar(70) NOT NULL,
  `key` varchar(250) NOT NULL,
  `value` varchar(250) NOT NULL,
  `type` tinyint(4) NOT NULL,
  `extensionPoint` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_project_pair` (`projectId`,`key`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `cantiga_stat_arq_time` (
  `projectId` int(11) NOT NULL,
  `datePoint` date NOT NULL,
  `requestsNew` int(11) NOT NULL,
  `requestsVerification` int(11) NOT NULL,
  `requestsApproved` int(11) NOT NULL,
  `requestsRejected` int(11) NOT NULL,
  PRIMARY KEY (`projectId`,`datePoint`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `cantiga_territories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `projectId` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `areaNum` int(11) NOT NULL DEFAULT '0',
  `requestNum` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `projectId` (`projectId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `cantiga_texts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `place` varchar(30) NOT NULL,
  `projectId` int(11) DEFAULT NULL,
  `title` varchar(100) NOT NULL,
  `content` text NOT NULL,
  `locale` varchar(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `key` (`place`),
  KEY `projectId` (`projectId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `cantiga_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `login` varchar(40) COLLATE utf8_polish_ci NOT NULL,
  `name` varchar(60) COLLATE utf8_polish_ci NOT NULL,
  `password` varchar(100) COLLATE utf8_polish_ci NOT NULL,
  `salt` varchar(40) COLLATE utf8_polish_ci NOT NULL,
  `email` varchar(100) COLLATE utf8_polish_ci NOT NULL,
  `active` int(11) NOT NULL DEFAULT '1',
  `removed` int(11) NOT NULL DEFAULT '0',
  `admin` tinyint(4) NOT NULL DEFAULT '0',
  `lastVisit` int(11) DEFAULT NULL,
  `avatar` varchar(40) COLLATE utf8_polish_ci DEFAULT NULL,
  `registeredAt` int(11) NOT NULL,
  `projectNum` int(11) NOT NULL DEFAULT '0',
  `groupNum` int(11) NOT NULL DEFAULT '0',
  `areaNum` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `login` (`login`),
  KEY `active_users` (`active`,`login`),
  KEY `user_name` (`name`),
  KEY `removed` (`removed`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

CREATE TABLE `cantiga_user_profiles` (
  `userId` int(11) NOT NULL,
  `location` varchar(100) DEFAULT NULL,
  `telephone` varchar(30) DEFAULT NULL,
  `publicMail` varchar(100) DEFAULT NULL,
  `notes` varchar(250) DEFAULT NULL,
  `settingsLanguageId` int(11) NOT NULL,
  `settingsTimezone` varchar(30) NOT NULL DEFAULT 'UTC',
  `privShowTelephone` int(11) NOT NULL DEFAULT '0',
  `privShowPublicMail` int(11) NOT NULL DEFAULT '0',
  `privShowNotes` int(11) NOT NULL DEFAULT '0',
  `afterLogin` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `cantiga_user_registrations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `login` varchar(40) NOT NULL,
  `name` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `salt` varchar(40) NOT NULL,
  `email` varchar(100) NOT NULL,
  `languageId` int(11) NOT NULL,
  `provisionKey` varchar(40) NOT NULL,
  `requestIp` int(11) NOT NULL,
  `requestTime` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `magicKey` (`provisionKey`),
  KEY `language` (`languageId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `cantiga_areas`
  ADD CONSTRAINT `cantiga_areas_ibfk_1` FOREIGN KEY (`projectId`) REFERENCES `cantiga_projects` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `cantiga_areas_ibfk_2` FOREIGN KEY (`groupId`) REFERENCES `cantiga_groups` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `cantiga_areas_ibfk_3` FOREIGN KEY (`statusId`) REFERENCES `cantiga_area_statuses` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `cantiga_areas_ibfk_4` FOREIGN KEY (`territoryId`) REFERENCES `cantiga_territories` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE `cantiga_area_members`
  ADD CONSTRAINT `cantiga_area_members_ibfk_1` FOREIGN KEY (`areaId`) REFERENCES `cantiga_areas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cantiga_area_members_ibfk_2` FOREIGN KEY (`userId`) REFERENCES `cantiga_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `cantiga_area_requests`
  ADD CONSTRAINT `cantiga_area_requests_ibfk_2` FOREIGN KEY (`requestorId`) REFERENCES `cantiga_users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `cantiga_area_requests_ibfk_3` FOREIGN KEY (`verifierId`) REFERENCES `cantiga_users` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `cantiga_area_requests_ibfk_4` FOREIGN KEY (`areaId`) REFERENCES `cantiga_areas` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `cantiga_area_requests_ibfk_5` FOREIGN KEY (`territoryId`) REFERENCES `cantiga_territories` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE `cantiga_area_request_comments`
  ADD CONSTRAINT `cantiga_area_request_comments_ibfk_1` FOREIGN KEY (`requestId`) REFERENCES `cantiga_area_requests` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cantiga_area_request_comments_ibfk_2` FOREIGN KEY (`userId`) REFERENCES `cantiga_users` (`id`);

ALTER TABLE `cantiga_area_statuses`
  ADD CONSTRAINT `cantiga_area_statuses_ibfk_1` FOREIGN KEY (`projectId`) REFERENCES `cantiga_projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `cantiga_credential_changes`
  ADD CONSTRAINT `cantiga_credential_changes_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `cantiga_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `cantiga_groups`
  ADD CONSTRAINT `cantiga_groups_ibfk_1` FOREIGN KEY (`projectId`) REFERENCES `cantiga_projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `cantiga_group_members`
  ADD CONSTRAINT `cantiga_group_members_ibfk_1` FOREIGN KEY (`groupId`) REFERENCES `cantiga_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cantiga_group_members_ibfk_2` FOREIGN KEY (`userId`) REFERENCES `cantiga_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `cantiga_links`
  ADD CONSTRAINT `cantiga_links_ibfk_1` FOREIGN KEY (`projectId`) REFERENCES `cantiga_projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `cantiga_password_recovery`
  ADD CONSTRAINT `cantiga_password_recovery_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `cantiga_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `cantiga_project_members`
  ADD CONSTRAINT `cantiga_project_members_ibfk_1` FOREIGN KEY (`projectId`) REFERENCES `cantiga_projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cantiga_project_members_ibfk_2` FOREIGN KEY (`userId`) REFERENCES `cantiga_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `cantiga_project_settings`
  ADD CONSTRAINT `cantiga_project_settings_ibfk_1` FOREIGN KEY (`projectId`) REFERENCES `cantiga_projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `cantiga_stat_arq_time`
  ADD CONSTRAINT `cantiga_stat_arq_time_ibfk_1` FOREIGN KEY (`projectId`) REFERENCES `cantiga_projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `cantiga_territories`
  ADD CONSTRAINT `cantiga_territories_ibfk_1` FOREIGN KEY (`projectId`) REFERENCES `cantiga_projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `cantiga_texts`
  ADD CONSTRAINT `cantiga_texts_ibfk_1` FOREIGN KEY (`projectId`) REFERENCES `cantiga_projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `cantiga_user_profiles`
  ADD CONSTRAINT `cantiga_user_profiles_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `cantiga_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
