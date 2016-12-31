CREATE TABLE IF NOT EXISTS `cantiga_areas` (
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
  `customData` text NOT NULL,
  `placeId` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  UNIQUE KEY `placeId` (`placeId`),
  KEY `projectId` (`projectId`),
  KEY `groupId` (`groupId`),
  KEY `statusId` (`statusId`),
  KEY `territoryId` (`territoryId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cantiga_area_requests` (
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

CREATE TABLE IF NOT EXISTS `cantiga_area_request_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `requestId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `createdAt` int(11) NOT NULL,
  `message` varchar(500) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `requestId` (`requestId`),
  KEY `userId` (`userId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cantiga_area_statuses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL,
  `label` varchar(30) NOT NULL,
  `isDefault` tinyint(4) NOT NULL DEFAULT '0',
  `areaNum` int(11) NOT NULL DEFAULT '0',
  `projectId` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `projectId` (`projectId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cantiga_contacts` (
  `userId` int(11) NOT NULL,
  `placeId` int(11) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telephone` varchar(30) DEFAULT NULL,
  `notes` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`userId`,`placeId`),
  KEY `placeId` (`placeId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cantiga_courses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `projectId` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `authorName` varchar(100) NOT NULL,
  `authorEmail` varchar(100) NOT NULL,
  `lastUpdated` int(11) NOT NULL,
  `presentationLink` varchar(255) NOT NULL,
  `deadline` int(11) DEFAULT NULL,
  `isPublished` int(11) NOT NULL DEFAULT '0',
  `displayOrder` int(11) NOT NULL,
  `notes` varchar(255) DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `inProject` (`projectId`,`displayOrder`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cantiga_course_area_results` (
  `areaId` int(11) NOT NULL,
  `courseId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  PRIMARY KEY (`areaId`,`courseId`),
  KEY `userId` (`userId`),
  KEY `cantiga_course_area_results_fk2` (`userId`,`courseId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cantiga_course_progress` (
  `areaId` int(11) NOT NULL,
  `mandatoryCourseNum` int(11) NOT NULL DEFAULT '0',
  `passedCourseNum` int(11) NOT NULL DEFAULT '0',
  `failedCourseNum` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`areaId`),
  KEY `passedCourseNum` (`passedCourseNum`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cantiga_course_results` (
  `userId` int(11) NOT NULL,
  `courseId` int(11) NOT NULL,
  `trialNumber` int(11) NOT NULL,
  `startedAt` int(11) NOT NULL,
  `completedAt` int(11) DEFAULT NULL,
  `result` tinyint(4) NOT NULL,
  `totalQuestions` int(11) NOT NULL,
  `passedQuestions` int(11) NOT NULL,
  PRIMARY KEY (`userId`,`courseId`),
  KEY `userId` (`userId`),
  KEY `courseId` (`courseId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cantiga_course_tests` (
  `courseId` int(11) NOT NULL,
  `testStructure` text NOT NULL,
  PRIMARY KEY (`courseId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cantiga_credential_changes` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cantiga_data_export` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(40) NOT NULL,
  `projectId` int(11) NOT NULL,
  `areaStatusId` int(11) DEFAULT NULL,
  `url` varchar(100) NOT NULL,
  `encryptionKey` varchar(128) NOT NULL,
  `active` tinyint(1) NOT NULL,
  `notes` text,
  `lastExportedAt` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `projectId` (`projectId`),
  KEY `areaStatusId` (`areaStatusId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cantiga_discussion_channels` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `projectId` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(250) NOT NULL DEFAULT '',
  `color` varchar(30) NOT NULL DEFAULT 'green',
  `icon` varchar(30) NOT NULL,
  `projectVisible` tinyint(1) NOT NULL,
  `groupVisible` tinyint(1) NOT NULL,
  `areaVisible` tinyint(1) NOT NULL,
  `projectPosting` int(11) NOT NULL,
  `groupPosting` int(11) NOT NULL,
  `areaPosting` int(11) NOT NULL,
  `discussionGrouping` tinyint(2) NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `projectId` (`projectId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cantiga_discussion_posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subchannelId` int(11) NOT NULL,
  `authorId` int(11) NOT NULL,
  `createdAt` int(11) NOT NULL,
  `content` text,
  PRIMARY KEY (`id`),
  KEY `channelId` (`subchannelId`),
  KEY `authorId` (`authorId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cantiga_discussion_subchannels` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `channelId` int(11) NOT NULL,
  `entityId` int(11) NOT NULL,
  `lastPostTime` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `channelId` (`channelId`),
  KEY `entityId` (`entityId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cantiga_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `categoryId` int(11) DEFAULT NULL,
  `notes` varchar(500) DEFAULT '',
  `slug` varchar(12) NOT NULL,
  `projectId` int(11) NOT NULL,
  `areaNum` int(11) NOT NULL,
  `placeId` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `placeId` (`placeId`),
  KEY `projectId` (`projectId`),
  KEY `categoryId` (`categoryId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cantiga_group_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `projectId` int(11) NOT NULL,
  `name` varchar(40) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `projectId` (`projectId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cantiga_invitations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `userId` int(11) DEFAULT NULL,
  `role` int(11) NOT NULL,
  `note` varchar(30) NOT NULL,
  `showDownstreamContactData` tinyint(1) NOT NULL DEFAULT 0,
  `placeId` int(11) NOT NULL,
  `inviterId` int(11) NOT NULL,
  `createdAt` int(11) NOT NULL,
  `assignmentKey` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniqueCombination` (`email`,`placeId`),
  KEY `inviterId` (`inviterId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cantiga_languages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `locale` varchar(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cantiga_links` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `url` varchar(100) NOT NULL,
  `projectId` int(11) DEFAULT NULL,
  `presentedTo` tinyint(4) NOT NULL DEFAULT '0',
  `listOrder` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `projectId` (`projectId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cantiga_mail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `place` varchar(40) NOT NULL,
  `locale` varchar(10) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `lastUpdate` int(11) NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_locale_pair` (`place`,`locale`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cantiga_milestones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `projectId` int(11) NOT NULL,
  `name` varchar(60) NOT NULL,
  `description` text,
  `displayOrder` int(11) NOT NULL DEFAULT '1',
  `type` tinyint(4) NOT NULL DEFAULT '0',
  `entityType` varchar(30) NOT NULL,
  `deadline` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `projectId` (`projectId`),
  KEY `presentation` (`projectId`,`displayOrder`),
  KEY `entityType` (`entityType`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cantiga_milestone_progress` (
  `entityId` int(11) NOT NULL,
  `completedNum` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`entityId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cantiga_milestone_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `projectId` int(11) NOT NULL,
  `milestoneId` int(11) NOT NULL,
  `name` varchar(80) NOT NULL,
  `activator` varchar(80) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `milestoneId` (`milestoneId`),
  KEY `projectId` (`projectId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cantiga_milestone_status` (
  `entityId` int(11) NOT NULL,
  `milestoneId` int(11) NOT NULL,
  `progress` int(11) NOT NULL,
  `completedAt` int(11) DEFAULT NULL,
  PRIMARY KEY (`entityId`,`milestoneId`),
  KEY `cantiga_milestone_status_fk2` (`milestoneId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cantiga_milestone_status_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `projectId` int(11) NOT NULL,
  `name` varchar(80) NOT NULL,
  `newStatusId` int(11) NOT NULL,
  `prevStatusId` int(11) NOT NULL,
  `milestoneMap` text,
  `activationOrder` int(11) NOT NULL,
  `lastUpdatedAt` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `projectId` (`projectId`),
  KEY `projectActivation` (`projectId`,`activationOrder`),
  KEY `cantiga_milestone_status_rules_fk2` (`newStatusId`),
  KEY `cantiga_milestone_status_rules_fk3` (`prevStatusId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cantiga_password_recovery` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `provisionKey` varchar(40) NOT NULL,
  `requestIp` int(11) NOT NULL,
  `requestTime` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`,`provisionKey`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cantiga_places` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(12) NOT NULL,
  `type` varchar(30) NOT NULL,
  `removedAt` int(11) DEFAULT NULL,
  `memberNum` int(11) DEFAULT 0,
  `rootPlaceId` int(11) NULL,
  `archived` TINYINT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `rootPlaceId` (`rootPlaceId`),
  KEY `archived` (`archived`) USING HASH
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cantiga_place_members` (
  `placeId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `role` int(11) NOT NULL,
  `showDownstreamContactData` tinyint(1) NOT NULL DEFAULT 0,
  `note` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`placeId`,`userId`),
  KEY `userId` (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cantiga_projects` (
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
  `placeId` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  UNIQUE KEY `placeId` (`placeId`),
  KEY `parentProjectId` (`parentProjectId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cantiga_project_settings` (
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

CREATE TABLE IF NOT EXISTS `cantiga_stat_arq_time` (
  `projectId` int(11) NOT NULL,
  `datePoint` date NOT NULL,
  `requestsNew` int(11) NOT NULL,
  `requestsVerification` int(11) NOT NULL,
  `requestsApproved` int(11) NOT NULL,
  `requestsRejected` int(11) NOT NULL,
  PRIMARY KEY (`projectId`,`datePoint`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cantiga_stat_courses` (
  `projectId` int(11) NOT NULL,
  `datePoint` date NOT NULL,
  `areasWithCompletedCourses` int(11) NOT NULL,
  `avgCompletedCourses` double NOT NULL,
  PRIMARY KEY (`projectId`,`datePoint`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cantiga_territories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `projectId` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `locale` varchar(10) DEFAULT NULL,
  `areaNum` int(11) NOT NULL DEFAULT '0',
  `requestNum` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `projectId` (`projectId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cantiga_texts` (
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

CREATE TABLE IF NOT EXISTS `cantiga_users` (
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
  `placeNum` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `login` (`login`),
  KEY `active_users` (`active`,`login`),
  KEY `user_name` (`name`),
  KEY `removed` (`removed`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

CREATE TABLE IF NOT EXISTS `cantiga_user_profiles` (
  `userId` int(11) NOT NULL,
  `location` varchar(100) DEFAULT NULL,
  `settingsLanguageId` int(11) NOT NULL,
  `settingsTimezone` varchar(30) NOT NULL DEFAULT 'UTC',
  `afterLogin` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cantiga_user_registrations` (
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
  ADD CONSTRAINT `cantiga_areas_fk5` FOREIGN KEY (`placeId`) REFERENCES `cantiga_places` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `cantiga_areas_ibfk_1` FOREIGN KEY (`projectId`) REFERENCES `cantiga_projects` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `cantiga_areas_ibfk_2` FOREIGN KEY (`groupId`) REFERENCES `cantiga_groups` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `cantiga_areas_ibfk_3` FOREIGN KEY (`statusId`) REFERENCES `cantiga_area_statuses` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `cantiga_areas_ibfk_4` FOREIGN KEY (`territoryId`) REFERENCES `cantiga_territories` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

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

ALTER TABLE `cantiga_contacts`
  ADD CONSTRAINT `cantiga_contacts_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `cantiga_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cantiga_contacts_ibfk_2` FOREIGN KEY (`placeId`) REFERENCES `cantiga_places` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `cantiga_courses`
  ADD CONSTRAINT `cantiga_courses_fk1` FOREIGN KEY (`projectId`) REFERENCES `cantiga_projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `cantiga_course_area_results`
  ADD CONSTRAINT `cantiga_course_area_results_fk1` FOREIGN KEY (`areaId`) REFERENCES `cantiga_areas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cantiga_course_area_results_fk2` FOREIGN KEY (`userId`, `courseId`) REFERENCES `cantiga_course_results` (`userId`, `courseId`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `cantiga_course_progress`
  ADD CONSTRAINT `cantiga_course_progress_fk1` FOREIGN KEY (`areaId`) REFERENCES `cantiga_areas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `cantiga_course_results`
  ADD CONSTRAINT `cantiga_course_results_fk1` FOREIGN KEY (`userId`) REFERENCES `cantiga_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cantiga_course_results_fk2` FOREIGN KEY (`courseId`) REFERENCES `cantiga_courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `cantiga_course_tests`
  ADD CONSTRAINT `cantiga_course_tests_fk1` FOREIGN KEY (`courseId`) REFERENCES `cantiga_courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `cantiga_credential_changes`
  ADD CONSTRAINT `cantiga_credential_changes_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `cantiga_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `cantiga_data_export`
  ADD CONSTRAINT `cantiga_data_export_fk1` FOREIGN KEY (`projectId`) REFERENCES `cantiga_projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cantiga_data_export_fk2` FOREIGN KEY (`areaStatusId`) REFERENCES `cantiga_area_statuses` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `cantiga_discussion_channels`
  ADD CONSTRAINT `cantiga_discussion_channels_ibfk_1` FOREIGN KEY (`projectId`) REFERENCES `cantiga_projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `cantiga_discussion_posts`
  ADD CONSTRAINT `cantiga_discussion_posts_ibfk_1` FOREIGN KEY (`subchannelId`) REFERENCES `cantiga_discussion_subchannels` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cantiga_discussion_posts_ibfk_2` FOREIGN KEY (`authorId`) REFERENCES `cantiga_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `cantiga_discussion_subchannels`
  ADD CONSTRAINT `cantiga_discussion_subchannels_ibfk_1` FOREIGN KEY (`channelId`) REFERENCES `cantiga_discussion_channels` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cantiga_discussion_subchannels_ibfk_2` FOREIGN KEY (`entityId`) REFERENCES `cantiga_places` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `cantiga_groups`
  ADD CONSTRAINT `cantiga_groups_fk2` FOREIGN KEY (`categoryId`) REFERENCES `cantiga_group_categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `cantiga_groups_fk3` FOREIGN KEY (`placeId`) REFERENCES `cantiga_places` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `cantiga_groups_ibfk_1` FOREIGN KEY (`projectId`) REFERENCES `cantiga_projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `cantiga_group_categories`
  ADD CONSTRAINT `cantiga_group_categories_fk1` FOREIGN KEY (`projectId`) REFERENCES `cantiga_projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `cantiga_invitations` ADD FOREIGN KEY (`placeId`) REFERENCES `cantiga_places`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `cantiga_links`
  ADD CONSTRAINT `cantiga_links_ibfk_1` FOREIGN KEY (`projectId`) REFERENCES `cantiga_projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `cantiga_milestones`
  ADD CONSTRAINT `cantiga_milestones_fk1` FOREIGN KEY (`projectId`) REFERENCES `cantiga_projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `cantiga_milestone_progress`
  ADD CONSTRAINT `cantiga_milestone_progress_fk1` FOREIGN KEY (`entityId`) REFERENCES `cantiga_places` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `cantiga_milestone_rules`
  ADD CONSTRAINT `cantiga_milestone_rules_fk_1` FOREIGN KEY (`milestoneId`) REFERENCES `cantiga_milestones` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cantiga_milestone_rules_fk_2` FOREIGN KEY (`projectId`) REFERENCES `cantiga_projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `cantiga_milestone_status`
  ADD CONSTRAINT `cantiga_milestone_status_fk1` FOREIGN KEY (`entityId`) REFERENCES `cantiga_places` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cantiga_milestone_status_fk2` FOREIGN KEY (`milestoneId`) REFERENCES `cantiga_milestones` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `cantiga_milestone_status_rules`
  ADD CONSTRAINT `cantiga_milestone_status_rules_fk1` FOREIGN KEY (`projectId`) REFERENCES `cantiga_projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cantiga_milestone_status_rules_fk2` FOREIGN KEY (`newStatusId`) REFERENCES `cantiga_area_statuses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cantiga_milestone_status_rules_fk3` FOREIGN KEY (`prevStatusId`) REFERENCES `cantiga_area_statuses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `cantiga_password_recovery`
  ADD CONSTRAINT `cantiga_password_recovery_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `cantiga_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `cantiga_places`
  ADD CONSTRAINT `cantiga_places_ibfk_1` FOREIGN KEY (`rootPlaceId`) REFERENCES `cantiga_places` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `cantiga_place_members`
  ADD CONSTRAINT `cantiga_place_members_ibfk_1` FOREIGN KEY (`placeId`) REFERENCES `cantiga_places` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cantiga_place_members_ibfk_2` FOREIGN KEY (`userId`) REFERENCES `cantiga_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `cantiga_projects`
  ADD CONSTRAINT `cantiga_projects_fk1` FOREIGN KEY (`placeId`) REFERENCES `cantiga_places` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `cantiga_project_settings`
  ADD CONSTRAINT `cantiga_project_settings_ibfk_1` FOREIGN KEY (`projectId`) REFERENCES `cantiga_projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `cantiga_stat_arq_time`
  ADD CONSTRAINT `cantiga_stat_arq_time_ibfk_1` FOREIGN KEY (`projectId`) REFERENCES `cantiga_projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `cantiga_stat_courses`
  ADD CONSTRAINT `cantiga_stat_courses_fk_1` FOREIGN KEY (`projectId`) REFERENCES `cantiga_projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `cantiga_territories`
  ADD CONSTRAINT `cantiga_territories_ibfk_1` FOREIGN KEY (`projectId`) REFERENCES `cantiga_projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `cantiga_texts`
  ADD CONSTRAINT `cantiga_texts_ibfk_1` FOREIGN KEY (`projectId`) REFERENCES `cantiga_projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `cantiga_user_profiles`
  ADD CONSTRAINT `cantiga_user_profiles_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `cantiga_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
