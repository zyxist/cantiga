ALTER TABLE  `cantiga_entities` ADD  `slug` VARCHAR( 12 ) NOT NULL AFTER  `name`, ADD UNIQUE (`slug`);

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE IF NOT EXISTS `cantiga_contacts` (
  `userId` int(11) NOT NULL,
  `projectId` int(11) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telephone` varchar(30) DEFAULT NULL,
  `notes` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`userId`,`projectId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cantiga_membership` (
  `entityId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `role` int(11) NOT NULL,
  `accessDownstreamContactData` tinyint(1) NOT NULL DEFAULT 0,
  `note` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`projectId`,`userId`),
  KEY `userId` (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `cantiga_user_profiles`
  DROP `telephone`,
  DROP `publicMail`,
  DROP `notes`,
  DROP `privShowTelephone`,
  DROP `privShowPublicMail`,
  DROP `privShowNotes`;

ALTER TABLE `cantiga_contacts` ADD FOREIGN KEY (`userId`) REFERENCES  `cantiga_fresh`.`cantiga_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ;
ALTER TABLE `cantiga_contacts` ADD FOREIGN KEY (`projectId`) REFERENCES  `cantiga_fresh`.`cantiga_projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ;

ALTER TABLE `cantiga_discussion_channels`
  ADD CONSTRAINT `cantiga_discussion_channels_ibfk_1` FOREIGN KEY (`projectId`) REFERENCES `cantiga_projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `cantiga_discussion_posts`
  ADD CONSTRAINT `cantiga_discussion_posts_ibfk_1` FOREIGN KEY (`subchannelId`) REFERENCES `cantiga_discussion_subchannels` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cantiga_discussion_posts_ibfk_2` FOREIGN KEY (`authorId`) REFERENCES `cantiga_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `cantiga_discussion_subchannels`
  ADD CONSTRAINT `cantiga_discussion_subchannels_ibfk_1` FOREIGN KEY (`channelId`) REFERENCES `cantiga_discussion_channels` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cantiga_discussion_subchannels_ibfk_2` FOREIGN KEY (`entityId`) REFERENCES `cantiga_entities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `cantiga_membership`
  ADD CONSTRAINT `cantiga_membership_ibfk_1` FOREIGN KEY (`entityId`) REFERENCES `cantiga_entities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cantiga_membership_ibfk_2` FOREIGN KEY (`userId`) REFERENCES `cantiga_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
