ALTER TABLE  `cantiga_areas` ADD  `entityId` INT NOT NULL;
ALTER TABLE  `cantiga_groups` ADD  `entityId` INT NOT NULL;
ALTER TABLE  `cantiga_projects` ADD  `entityId` INT NOT NULL;

ALTER TABLE  `cantiga_entities` ADD  `removedAt` INT NULL ,
  ADD  `removedById` INT NULL,
  ADD INDEX (  `removedById` ) ;

ALTER TABLE `cantiga_areas` ADD UNIQUE (`entityId`);
ALTER TABLE `cantiga_groups` ADD UNIQUE (`entityId`);
ALTER TABLE `cantiga_projects` ADD UNIQUE (`entityId`);

ALTER TABLE `cantiga_milestones` DROP `status`;
ALTER TABLE  `cantiga_milestones` ADD  `type` TINYINT NOT NULL DEFAULT  '0' AFTER  `displayOrder` ;
ALTER TABLE  `cantiga_milestone_status` DROP FOREIGN KEY  `cantiga_milestone_status_fk3` ;
ALTER TABLE  `cantiga_milestone_status` DROP  `acknowledgedById` ;
ALTER TABLE  `cantiga_milestone_status` ADD  `progress` INT NOT NULL AFTER  `milestoneId` ;
ALTER TABLE  `cantiga_milestone_status` CHANGE  `completedAt`  `completedAt` INT( 11 ) NULL ;
ALTER TABLE  `cantiga_milestones` CHANGE  `deadline`  `deadline` INT( 11 ) NULL DEFAULT NULL ;

ALTER TABLE  `cantiga_areas`
  ADD CONSTRAINT  `cantiga_areas_fk5` FOREIGN KEY (  `entityId` ) REFERENCES `cantiga_entities` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE ;

ALTER TABLE  `cantiga_groups`
  ADD CONSTRAINT  `cantiga_groups_fk3` FOREIGN KEY (  `entityId` ) REFERENCES `cantiga_entities` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE ;

ALTER TABLE  `cantiga_projects`
  ADD CONSTRAINT  `cantiga_projects_fk1` FOREIGN KEY (  `entityId` ) REFERENCES `cantiga_entities` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE ;

ALTER TABLE  `cantiga_entities`
  ADD CONSTRAINT  `cantiga_entities_fk1` FOREIGN KEY (  `removedById` ) REFERENCES `cantiga_users` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE ;