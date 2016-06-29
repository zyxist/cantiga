CREATE TABLE IF NOT EXISTS `cantiga_forum_roots` (
  `id` int(11) NOT NULL,
  `name` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cantiga_forum_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rootId` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `displayOrder` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `rootId` (`rootId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cantiga_forums` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rootId` int(11) NOT NULL,
  `categoryId` int(11) NOT NULL,
  `parentId` int(11) DEFAULT NULL,
  `leftPosition` int(11) NOT NULL,
  `rightPosition` int(11) NOT NULL,
  `name` varchar(250) NOT NULL,
  `description` mediumtext NOT NULL,
  `topicNum` int(11) NOT NULL DEFAULT '0',
  `postNum` int(11) NOT NULL DEFAULT '0',
  `lastTopicId` int(11) DEFAULT NULL,
  `lastPostId` int(11) DEFAULT NULL,
  `lastAuthorId` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rootId` (`rootId`),
  KEY `categoryId` (`categoryId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

ALTER TABLE `cantiga_forums`
  ADD CONSTRAINT `cantiga_forums_ibfk_1` FOREIGN KEY (`rootId`) REFERENCES `cantiga_forum_roots` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cantiga_forums_ibfk_2` FOREIGN KEY (`categoryId`) REFERENCES `cantiga_forum_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `cantiga_forum_categories`
  ADD CONSTRAINT `cantiga_forum_categories_ibfk_1` FOREIGN KEY (`rootId`) REFERENCES `cantiga_forum_roots` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;