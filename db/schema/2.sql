CREATE TABLE `cantiga_entities` (
   `id` INT(11) NOT NULL AUTO_INCREMENT,
   `name` VARCHAR(100) NOT NULL,
   `type` VARCHAR(30) NOT NULL,
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `cantiga_milestone_progress` (
   `entityId` INT(11) NOT NULL,
   `completedNum` INT(11) NOT NULL DEFAULT 0,
   PRIMARY KEY (`entityId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `cantiga_milestone_status` (
   `entityId` INT(11) NOT NULL,
   `milestoneId` INT(11) NOT NULL,
   `completedAt` INT(11) NOT NULL,
   `acknowledgedById` INT(11) NULL,
   PRIMARY KEY (`entityId`, `milestoneId`),
   KEY `ack` (`acknowledgedById`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `cantiga_milestones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `projectId` int(11) NOT NULL,
  `name` VARCHAR(60) NOT NULL,
  `description` TEXT,
  `displayOrder` INT(11) NOT NULL DEFAULT 1,
  `status` TINYINT(4) NOT NULL DEFAULT 0,
  `entityType` VARCHAR(30) NOT NULL,
  `deadline` DATE DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `projectId` (`projectId`),
  KEY `presentation` (`projectId`, `displayOrder`),
  KEY `entityType` (`entityType`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `cantiga_milestones` ADD CONSTRAINT `cantiga_milestones_fk1` FOREIGN KEY (`projectId`) REFERENCES `cantiga_projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `cantiga_milestone_status` ADD CONSTRAINT `cantiga_milestone_status_fk1` FOREIGN KEY (`entityId`) REFERENCES `cantiga_entities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `cantiga_milestone_status` ADD CONSTRAINT `cantiga_milestone_status_fk2` FOREIGN KEY (`milestoneId`) REFERENCES `cantiga_milestones` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `cantiga_milestone_status` ADD CONSTRAINT `cantiga_milestone_status_fk3` FOREIGN KEY (`acknowledgedById`) REFERENCES `cantiga_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `cantiga_milestone_progress` ADD CONSTRAINT `cantiga_milestone_progress_fk1` FOREIGN KEY (`entityId`) REFERENCES `cantiga_entities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;