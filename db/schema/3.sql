CREATE TABLE IF NOT EXISTS `cantiga_trainings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` varchar(255) NULL,
  `authorName` varchar(100) NOT NULL,
  `authorEmail` varchar(100) NOT NULL,
  `lastUpdated` int(11) NOT NULL,
  `presentationLink` varchar(255) NOT NULL,
  `deadline` date DEFAULT NULL,
  `isPublished` int(11) NOT NULL DEFAULT '0',
  `displayOrder` int(11) NOT NULL,
  `notes` varchar(255) COLLATE utf8_polish_ci DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `cantiga_training_results` (
  `areaId` int(11) NOT NULL,
  `trainingId` int(11) NOT NULL,
  `trialNumber` int(11) NOT NULL,
  `startedAt` int(11) NOT NULL,
  `completedAt` int(11) NULL,
  `result` tinyint(4) NOT NULL,
  `totalQuestions` int(11) NOT NULL,
  `passedQuestions` int(11) NOT NULL,
  PRIMARY KEY (`areaId`,`trainingId`),
  KEY `areaId` (`areaId`),
  KEY `trainingId` (`trainingId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cantiga_training_tests` (
  `trainingId` int(11) NOT NULL,
  `testStructure` text NOT NULL,
  PRIMARY KEY (`trainingId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `cantiga_training_results`
  ADD CONSTRAINT `cantiga_training_results_fk1` FOREIGN KEY (`areaId`) REFERENCES `cantiga_areas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cantiga_training_results_fk2` FOREIGN KEY (`trainingId`) REFERENCES `cantiga_trainings` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE  `cantiga_training_tests` ADD CONSTRAINT  `cantiga_training_tests_fk1` FOREIGN KEY (  `trainingId` ) REFERENCES `cantiga_trainings` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ;

CREATE TABLE IF NOT EXISTS `cantiga_training_progress` (
  `areaId` INT(11) NOT NULL,
  `mandatoryTrainingNum` INT(11) NOT NULL DEFAULT 0,
  `passedTrainingNum` INT(11) NOT NULL DEFAULT 0,
  `failedTrainingNum` INT(11) NOT NULL DEFAULT 0,
  PRIMARY KEY(`area_id`),
  KEY `passed_training_num` (`passed_training_num`)
) ENGINE=InnoDB;

ALTER TABLE `cantiga_training_progress`
  ADD CONSTRAINT `cantiga_training_progress_fk1` FOREIGN KEY (`areaId`) REFERENCES `cantiga_areas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;