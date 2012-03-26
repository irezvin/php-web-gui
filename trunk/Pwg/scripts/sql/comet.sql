SET NAMES UTF8;

CREATE TABLE `pax_cmt_counter` (
  `id` int(10) unsigned NOT NULL,
  `lastMsgId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

 CREATE TABLE `pax_cmt_messages` (
  `id` int(10) unsigned NOT NULL,
  `msgId` int(10) unsigned NOT NULL,
  `msgBody` longtext CHARACTER SET utf8 NOT NULL,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `counter` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`counter`),
  KEY `index_2` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `pax_cmt_sessmap` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sessId` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `index_2` (`sessId`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=latin1;

