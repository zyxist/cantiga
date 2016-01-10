CREATE TABLE `cantiga_milestone_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `projectId` int(11) NOT NULL,
  `milestoneId` int(11) NOT NULL,
  `name` VARCHAR (80) NOT NULL,
  `activator` VARCHAR (80) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `milestoneId` (`milestoneId`),
  KEY `projectId` (`projectId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `cantiga_milestone_rules`
  ADD CONSTRAINT `cantiga_milestone_rules_fk_1` FOREIGN KEY (`milestoneId`) REFERENCES `cantiga_milestones` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cantiga_milestone_rules_fk_2` FOREIGN KEY (`projectId`) REFERENCES `cantiga_projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;