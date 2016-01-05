INSERT INTO `cantiga_entities` (`id`, `name`, `type`) VALUES(1, 'Fixture', 'Project');

INSERT INTO `cantiga_projects` (`id`, `name`, `slug`, `description`, `parentProjectId`, `modules`, `areasAllowed`, `areaRegistrationAllowed`, `archived`, `createdAt`, `archivedAt`, `memberNum`, `entityId`) VALUES
   (1, 'Fixture', 'fixture12345', '', NULL, '', '1', '1', '0', '1234567890', NULL, '0', 1);

INSERT INTO `cantiga_territories` (`id`, `projectId`, `name`, `areaNum`, `requestNum`) VALUES (1, 1, 'Demo', '0', '0');

INSERT INTO `cantiga_area_statuses` (`id`, `name`, `label`, `isDefault`, `areaNum`, `projectId`) VALUES (1, 'Test', 'primary', '1', '0', '1');