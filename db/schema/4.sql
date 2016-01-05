ALTER TABLE  `cantiga_areas` ADD  `entityId` INT NOT NULL;
ALTER TABLE  `cantiga_groups` ADD  `entityId` INT NOT NULL;
ALTER TABLE  `cantiga_projects` ADD  `entityId` INT NOT NULL;

ALTER TABLE  `cantiga_entities` ADD  `removedAt` INT NULL ,
  ADD  `removedById` INT NULL,
  ADD INDEX (  `removedById` ) ;

ALTER TABLE `cantiga_areas` ADD UNIQUE (`entityId`);
ALTER TABLE `cantiga_groups` ADD UNIQUE (`entityId`);
ALTER TABLE `cantiga_projects` ADD UNIQUE (`entityId`);

ALTER TABLE  `cantiga_areas`
  ADD CONSTRAINT  `cantiga_areas_fk5` FOREIGN KEY (  `entityId` ) REFERENCES `cantiga_entities` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE ;

ALTER TABLE  `cantiga_groups`
  ADD CONSTRAINT  `cantiga_groups_fk3` FOREIGN KEY (  `entityId` ) REFERENCES `cantiga_entities` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE ;

ALTER TABLE  `cantiga_projects`
  ADD CONSTRAINT  `cantiga_projects_fk1` FOREIGN KEY (  `entityId` ) REFERENCES `cantiga_entities` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE ;

ALTER TABLE  `cantiga_entities`
  ADD CONSTRAINT  `cantiga_entities_fk1` FOREIGN KEY (  `removedById` ) REFERENCES `cantiga_users` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE ;