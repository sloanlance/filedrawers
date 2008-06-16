--
-- Table structure for table `filedrawers_progress`
--

CREATE TABLE `filedrawers_progress` (
  `session_id` varchar(32) NOT NULL default '',
  `filename` tinytext,
  `size` bigint(20) NOT NULL default '0',
  `received` bigint(20) NOT NULL default '0',
  `complete` tinyint(1) NOT NULL default '0',
  `last_update` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `session_id` varchar(100) NOT NULL default '',
  `session_data` text NOT NULL,
  `expires` int(11) NOT NULL default '0',
  PRIMARY KEY  (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
