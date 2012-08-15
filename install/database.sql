CREATE TABLE `access_token` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `unique_id` varchar(15) NOT NULL DEFAULT '',
  `channel` varchar(10) NOT NULL DEFAULT '',
  `channel_uid` varchar(15) DEFAULT NULL,
  `access_token` varchar(50) DEFAULT NULL,
  `refresh_token` varchar(50) DEFAULT NULL,
  `expire_time` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_id` (`unique_id`,`channel`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;