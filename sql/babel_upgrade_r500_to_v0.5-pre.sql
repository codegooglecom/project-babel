ALTER TABLE `babel_online` DROP INDEX `INDEX_NICK`;
ALTER TABLE `babel_online` DROP INDEX `INDEX_CREATED`;
ALTER TABLE `babel_online` DROP INDEX `INDEX_LASTMOVED`;
ALTER TABLE `babel_online` DROP INDEX `INDEX_IP`;
CREATE TABLE `babel_geo_usage_simple` (
  `gus_geo` varchar(100) NOT NULL default 'earth',
  `gus_name_cn` varchar(100) NOT NULL default '??',
  `gus_name_en` varchar(100) NOT NULL default 'Earth',
  `gus_hits` int(10) unsigned NOT NULL default '0',
  `gus_lastupdated` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`gus_geo`),
  UNIQUE KEY `INDEX_NAME_CN` (`gus_name_cn`),
  UNIQUE KEY `INDEX_NAME_EN` (`gus_name_en`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Macau Geo Usage';
CREATE TABLE `babel_job` (
  `job_id` int(10) unsigned NOT NULL auto_increment,
  `job_cid` int(10) unsigned NOT NULL default '0',
  `job_company` varchar(100) NOT NULL,
  `job_url` varchar(100) NOT NULL,
  `job_title` varchar(60) NOT NULL,
  `job_description` text NOT NULL,
  `job_apply` varchar(100) NOT NULL,
  `job_location` varchar(30) NOT NULL,
  `job_email` varchar(100) default NULL,
  `job_status` int(10) unsigned NOT NULL default '0',
  `job_created` int(10) unsigned NOT NULL default '0',
  `job_activated` int(10) unsigned NOT NULL default '0',
  `job_deactivated` int(10) unsigned NOT NULL default '0',
  `job_hits` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`job_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Babel Job';
CREATE TABLE `babel_log_email_sent` (
  `log_id` int(10) unsigned NOT NULL auto_increment,
  `log_uid` int(10) unsigned NOT NULL default '0',
  `log_email` varchar(100) default NULL,
  `log_email_type` int(10) unsigned NOT NULL default '0',
  `log_email_sent` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`log_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Babel Log E-mail Sent';
CREATE TABLE `babel_related` (
  `rlt_id` int(10) unsigned NOT NULL auto_increment,
  `rlt_pid` int(10) unsigned NOT NULL default '0',
  `rlt_title` varchar(200) NOT NULL default '',
  `rlt_url` varchar(200) NOT NULL default '',
  `rlt_created` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`rlt_id`),
  KEY `INDEX_PID` (`rlt_pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Babel Related Table';
CREATE TABLE `babel_user_portrait` (
  `urp_id` int(10) unsigned NOT NULL auto_increment,
  `urp_filename` varchar(40) default NULL,
  `urp_content` blob NOT NULL,
  PRIMARY KEY  (`urp_id`),
  UNIQUE KEY `INDEX_FILENAME` (`urp_filename`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
ALTER TABLE `babel_expense` MODIFY COLUMN `exp_amount` DOUBLE NOT NULL DEFAULT '0';
ALTER TABLE `babel_expense` MODIFY COLUMN `exp_created` INTEGER(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `babel_topic` ADD COLUMN `tpc_favs` INTEGER(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `babel_topic` ADD COLUMN `tpc_followers` TEXT COLLATE utf8_general_ci;
ALTER TABLE `babel_user` ADD COLUMN `usr_email_notify` VARCHAR(100) COLLATE utf8_general_ci DEFAULT NULL;
ALTER TABLE `babel_user` ADD COLUMN `usr_geo` VARCHAR(100) COLLATE utf8_general_ci NOT NULL DEFAULT 'earth';
ALTER TABLE `babel_user` ADD COLUMN `usr_logins` INTEGER(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `babel_user` ADD COLUMN `usr_lastlogin` INTEGER(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `babel_user` ADD COLUMN `usr_lastlogin_ua` VARCHAR(400) COLLATE utf8_general_ci DEFAULT NULL;
ALTER TABLE `babel_user` ADD COLUMN `usr_sw_shell` SMALLINT(6) NOT NULL DEFAULT '0';
ALTER TABLE `babel_user` ADD COLUMN `usr_sw_notify_reply` SMALLINT(6) NOT NULL DEFAULT '0';
ALTER TABLE `babel_user` ADD COLUMN `usr_sw_notify_reply_all` SMALLINT(6) NOT NULL DEFAULT '0';
ALTER TABLE `babel_zen_project` ADD COLUMN `zpr_type` INTEGER(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `babel_zen_project` ADD COLUMN `zpr_tasks` INTEGER(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `babel_zen_project` ADD COLUMN `zpr_notes` INTEGER(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `babel_zen_project` ADD COLUMN `zpr_dbs` INTEGER(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `babel_zen_task` ADD COLUMN `zta_lasttouched` INTEGER(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `babel_channel` ADD KEY `INDEX_TITLE` (`chl_title`);
ALTER TABLE `babel_channel` ADD KEY `INDEX_URL` (`chl_url`);
ALTER TABLE `babel_topic` ADD KEY `INDEX_LASTTOUCHED` (`tpc_lasttouched`);
ALTER TABLE `babel_user` ADD KEY `INDEX_PORTRAIT` (`usr_portrait`);
ALTER TABLE `babel_user` ADD KEY `INDEX_HITS` (`usr_hits`);
ALTER TABLE `babel_user` ADD KEY `INDEX_LASTLOGIN` (`usr_lastlogin`);
ALTER TABLE `babel_user` ADD KEY `INDEX_GEO` (`usr_geo`);
