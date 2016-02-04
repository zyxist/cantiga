CREATE TABLE IF NOT EXISTS `cantiga_milestone_status_rules` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `projectId` INT(11) NOT NULL,
  `name` VARCHAR(80) NOT NULL,
  `newStatusId` INT(11) NOT NULL,
  `prevStatusId` INT(11) NOT NULL,
  `milestoneMap` TEXT,
  `activationOrder` INT(11) NOT NULL,
  `lastUpdatedAt` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `projectId` (`projectId`),
  KEY `projectActivation` (`projectId`, `activationOrder`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `cantiga_milestone_status_rules`
   ADD CONSTRAINT `cantiga_milestone_status_rules_fk1` FOREIGN KEY (`projectId`) REFERENCES `cantiga_projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
   ADD CONSTRAINT `cantiga_milestone_status_rules_fk2` FOREIGN KEY (`newStatusId`) REFERENCES `cantiga_area_statuses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
   ADD CONSTRAINT `cantiga_milestone_status_rules_fk3` FOREIGN KEY (`prevStatusId`) REFERENCES `cantiga_area_statuses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;