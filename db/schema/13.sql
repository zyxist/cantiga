ALTER TABLE  `cantiga_entities` ADD  `slug` VARCHAR( 12 ) NOT NULL AFTER  `name`, ADD UNIQUE (`slug`);

CREATE TABLE IF NOT EXISTS `cantiga_discussion_channels` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `projectId` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(250) NOT NULL DEFAULT '',
  `color` varchar(30) NOT NULL DEFAULT 'green',
  `icon` varchar(30) NOT NULL,
  `lastPostTime` int(11) NOT NULL,
  `projectVisible` tinyint(1) NOT NULL,
  `groupVisible` tinyint(1) NOT NULL,
  `areaVisible` tinyint(1) NOT NULL,
  `separate` tinyint(1) NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `projectId` (`projectId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cantiga_discussion_posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `channelId` int(11) NOT NULL,
  `authorId` int(11) NOT NULL,
  `entityId` int(11) NOT NULL,
  `createdAt` int(11) NOT NULL,
  `content` text,
  PRIMARY KEY (`id`),
  KEY `channelId` (`channelId`),
  KEY `authorId` (`authorId`),
  KEY `entityId` (`entityId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

ALTER TABLE `cantiga_discussion_posts`
  ADD CONSTRAINT `cantiga_discussion_posts_ibfk_1` FOREIGN KEY (`channelId`) REFERENCES `cantiga_discussion_channels` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cantiga_discussion_posts_ibfk_2` FOREIGN KEY (`authorId`) REFERENCES `cantiga_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cantiga_discussion_posts_ibfk_3` FOREIGN KEY (`entityId`) REFERENCES `cantiga_entities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `cantiga_discussion_channels`
  ADD CONSTRAINT `cantiga_discussion_channels_ibfk_1` FOREIGN KEY (`projectId`) REFERENCES `cantiga_projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;