CREATE TABLE IF NOT EXISTS `cantiga_stat_edk_participants` (
  `projectId` int(11) NOT NULL,
  `datePoint` date NOT NULL,
  `participantNum` int(11) NOT NULL,
  PRIMARY KEY (`projectId`,`datePoint`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `cantiga_stat_edk_participants`
  ADD CONSTRAINT `cantiga_stat_edk_participants_fk_1` FOREIGN KEY (`projectId`) REFERENCES `cantiga_projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;