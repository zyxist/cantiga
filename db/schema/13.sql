ALTER TABLE  `cantiga_entities` ADD  `slug` VARCHAR( 12 ) NOT NULL AFTER  `name`, ADD UNIQUE (`slug`);

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

CREATE TABLE IF NOT EXISTS `cantiga_forum_topics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rootId` int(11) NOT NULL,
  `forumId` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `createdAt` int(11) NOT NULL,
  `lastPostId` int(11) NOT NULL,
  `lastPostCreatedAt` int(11) NOT NULL,
  `lastAuthorId` int(11) NOT NULL,
  `lastAuthorName` varchar(255) NOT NULL,
  `authorId` int(11) NOT NULL,
  `authorName` varchar(255) NOT NULL,
  `type` tinyint(4) NOT NULL DEFAULT '0',
  `firstPostId` int(11) NOT NULL,
  `closed` tinyint(4) NOT NULL DEFAULT '0',
  `replyNum` int(11) NOT NULL DEFAULT '0',
  `viewNum` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `forumId` (`forumId`),
  KEY `topicType` (`forumId`,`type`),
  KEY `lastPostCreatedAt` (`lastPostCreatedAt`),
  KEY `firstPostId` (`firstPostId`),
  KEY `lastAuthorId` (`lastAuthorId`),
  KEY `lastPostId` (`lastPostId`),
  KEY `rootId` (`rootId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cantiga_forum_posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `topicId` int(11) NOT NULL,
  `authorId` int(11) NOT NULL,
  `authorIp` int(11) NOT NULL,
  `createdAt` int(11) NOT NULL,
  `postOrder` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `topicorder` (`topicId`,`postOrder`),
  KEY `topicId` (`topicId`),
  KEY `authorId` (`authorId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cantiga_forum_post_content` (
  `postId` int(11) NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY (`postId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `cantiga_forum_posts`
  ADD CONSTRAINT `cantiga_forum_posts_ibfk_1` FOREIGN KEY (`topicId`) REFERENCES `cantiga_forum_topics` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cantiga_forum_posts_ibfk_2` FOREIGN KEY (`authorId`) REFERENCES `cantiga_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `cantiga_forum_post_content`
  ADD CONSTRAINT `cantiga_forum_post_content_ibfk_1` FOREIGN KEY (`postId`) REFERENCES `cantiga_forum_posts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `cantiga_forums`
  ADD CONSTRAINT `cantiga_forums_ibfk_1` FOREIGN KEY (`rootId`) REFERENCES `cantiga_forum_roots` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cantiga_forums_ibfk_2` FOREIGN KEY (`categoryId`) REFERENCES `cantiga_forum_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `cantiga_forum_categories`
  ADD CONSTRAINT `cantiga_forum_categories_ibfk_1` FOREIGN KEY (`rootId`) REFERENCES `cantiga_forum_roots` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `cantiga_forum_topics`
  ADD CONSTRAINT `cantiga_forum_topics_ibfk_1` FOREIGN KEY (`forumId`) REFERENCES `cantiga_forums` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cantiga_forum_topics_ibfk_2` FOREIGN KEY (`rootId`) REFERENCES `cantiga_forum_roots` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;