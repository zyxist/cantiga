CREATE TABLE IF NOT EXISTS `cantiga_courses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `projectId` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(255) NULL,
  `authorName` varchar(100) NOT NULL,
  `authorEmail` varchar(100) NOT NULL,
  `lastUpdated` int(11) NOT NULL,
  `presentationLink` varchar(255) NOT NULL,
  `deadline` int(11) DEFAULT NULL,
  `isPublished` int(11) NOT NULL DEFAULT '0',
  `displayOrder` int(11) NOT NULL,
  `notes` varchar(255) DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `inProject` (`projectId`, `displayOrder`) 
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cantiga_course_results` (
  `areaId` int(11) NOT NULL,
  `courseId` int(11) NOT NULL,
  `trialNumber` int(11) NOT NULL,
  `startedAt` int(11) NOT NULL,
  `completedAt` int(11) NULL,
  `result` tinyint(4) NOT NULL,
  `totalQuestions` int(11) NOT NULL,
  `passedQuestions` int(11) NOT NULL,
  PRIMARY KEY (`areaId`,`courseId`),
  KEY `areaId` (`areaId`),
  KEY `courseId` (`courseId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cantiga_course_tests` (
  `courseId` int(11) NOT NULL,
  `testStructure` text NOT NULL,
  PRIMARY KEY (`courseId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cantiga_course_progress` (
  `areaId` INT(11) NOT NULL,
  `mandatoryCourseNum` INT(11) NOT NULL DEFAULT 0,
  `passedCourseNum` INT(11) NOT NULL DEFAULT 0,
  `failedCourseNum` INT(11) NOT NULL DEFAULT 0,
  PRIMARY KEY(`areaId`),
  KEY `passedCourseNum` (`passedCourseNum`)
) ENGINE=InnoDB;

ALTER TABLE `cantiga_course_results`
  ADD CONSTRAINT `cantiga_course_results_fk1` FOREIGN KEY (`areaId`) REFERENCES `cantiga_areas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cantiga_course_results_fk2` FOREIGN KEY (`courseId`) REFERENCES `cantiga_courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE  `cantiga_course_tests`
  ADD CONSTRAINT  `cantiga_course_tests_fk1` FOREIGN KEY (  `courseId` ) REFERENCES `cantiga_courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ;

ALTER TABLE `cantiga_course_progress`
  ADD CONSTRAINT `cantiga_course_progress_fk1` FOREIGN KEY (`areaId`) REFERENCES `cantiga_areas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `cantiga_courses`
  ADD CONSTRAINT `cantiga_courses_fk1` FOREIGN KEY (`projectId`) REFERENCES `cantiga_projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;