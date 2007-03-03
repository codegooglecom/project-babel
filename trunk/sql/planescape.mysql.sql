-- phpMyAdmin SQL Dump
-- version 2.9.0
-- http://www.phpmyadmin.net
-- 
-- 主机: localhost
-- 生成日期: 2006 年 10 月 11 日 15:22
-- 服务器版本: 5.0.24
-- PHP 版本: 5.1.6-1
-- 
-- 数据库: `planescape`
-- 

-- --------------------------------------------------------

-- 
-- 表的结构 `babel_channel`
-- 

CREATE TABLE `babel_channel` (
  `chl_id` int(10) unsigned NOT NULL auto_increment,
  `chl_pid` int(10) unsigned NOT NULL default '0',
  `chl_title` varchar(200) NOT NULL default '',
  `chl_url` varchar(200) NOT NULL default '',
  `chl_created` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`chl_id`),
  KEY `INDEX_PID` (`chl_pid`),
  KEY `INDEX_TITLE` (`chl_title`),
  KEY `INDEX_URL` (`chl_url`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 PACK_KEYS=0 COMMENT='Babel Channel Table';

-- --------------------------------------------------------

-- 
-- 表的结构 `babel_expense`
-- 

CREATE TABLE `babel_expense` (
  `exp_id` int(10) unsigned NOT NULL auto_increment,
  `exp_uid` int(10) unsigned NOT NULL default '0',
  `exp_amount` int(11) NOT NULL default '0',
  `exp_type` int(10) unsigned NOT NULL default '0',
  `exp_memo` text,
  `exp_created` double NOT NULL default '0',
  PRIMARY KEY  (`exp_id`),
  KEY `INDEX_UID` (`exp_uid`),
  KEY `INDEX_TYPE` (`exp_type`),
  KEY `INDEX_CREATED` (`exp_created`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Babel Expense Table';

-- --------------------------------------------------------

-- 
-- 表的结构 `babel_favorite`
-- 

CREATE TABLE `babel_favorite` (
  `fav_id` int(10) unsigned NOT NULL auto_increment,
  `fav_uid` int(10) unsigned NOT NULL default '0',
  `fav_title` varchar(200) NOT NULL default '',
  `fav_author` varchar(100) NOT NULL default '',
  `fav_res` varchar(200) NOT NULL default '',
  `fav_brief` text,
  `fav_type` int(10) unsigned NOT NULL default '0',
  `fav_created` int(10) unsigned NOT NULL default '0',
  `fav_lastupdated` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`fav_id`),
  KEY `INDEX_UID` (`fav_uid`),
  KEY `INDEX_RES` (`fav_res`),
  KEY `INDEX_TYPE` (`fav_type`),
  KEY `INDEX_CREATED` (`fav_created`),
  KEY `INDEX_LASTUPDATED` (`fav_lastupdated`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Babel Favorite Table';

-- --------------------------------------------------------

-- 
-- 表的结构 `babel_foundation`
-- 

CREATE TABLE `babel_foundation` (
  `fdt_id` int(10) unsigned NOT NULL auto_increment,
  `fdt_uid` int(10) unsigned NOT NULL default '0',
  `fdt_title` varchar(40) NOT NULL default 'Untitled foundation',
  `fdt_money` int(11) NOT NULL default '0',
  `fdt_type` int(10) unsigned NOT NULL default '0',
  `fdt_brief` text,
  `fdt_created` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`fdt_id`),
  KEY `INDEX_UID` (`fdt_uid`),
  KEY `INDEX_TYPE` (`fdt_type`),
  KEY `INDEX_CREATED` (`fdt_created`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Babel Foundation Table';

-- --------------------------------------------------------

-- 
-- 表的结构 `babel_friend`
-- 

CREATE TABLE `babel_friend` (
  `frd_id` int(10) unsigned NOT NULL auto_increment,
  `frd_uid` int(10) unsigned NOT NULL default '0',
  `frd_fid` int(10) unsigned NOT NULL default '0',
  `frd_description` varchar(200) NOT NULL default '',
  `frd_created` int(10) unsigned NOT NULL default '0',
  `frd_lastupdated` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`frd_id`),
  KEY `INDEX_UID` (`frd_uid`),
  KEY `INDEX_FID` (`frd_fid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 PACK_KEYS=0 COMMENT='Babel Friend Table';

-- --------------------------------------------------------

-- 
-- 表的结构 `babel_group`
-- 

CREATE TABLE `babel_group` (
  `grp_id` int(10) unsigned NOT NULL auto_increment,
  `grp_oid` int(10) unsigned NOT NULL default '0',
  `grp_nick` varchar(40) NOT NULL default '',
  `grp_brief` longtext,
  `grp_created` int(10) unsigned NOT NULL default '0',
  `grp_lastupdated` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`grp_id`),
  KEY `INDEX_OID` (`grp_oid`),
  KEY `INDEX_NICK` (`grp_nick`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Babel Group Table';

-- --------------------------------------------------------

-- 
-- 表的结构 `babel_message`
-- 

CREATE TABLE `babel_message` (
  `msg_id` int(10) unsigned NOT NULL auto_increment,
  `msg_sid` int(10) unsigned NOT NULL default '0',
  `msg_rid` int(10) unsigned NOT NULL default '0',
  `msg_body` text,
  `msg_draft` int(10) unsigned NOT NULL default '0',
  `msg_hits` int(10) unsigned NOT NULL default '0',
  `msg_created` int(10) unsigned NOT NULL default '0',
  `msg_sent` int(10) unsigned NOT NULL default '0',
  `msg_opened` int(10) unsigned NOT NULL default '0',
  `msg_sdeleted` int(10) unsigned NOT NULL default '0',
  `msg_rdeleted` int(10) unsigned NOT NULL default '0',
  `msg_lastaccessed` int(10) unsigned NOT NULL default '0',
  `msg_lastupdated` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`msg_id`),
  KEY `INDEX_SID` (`msg_sid`),
  KEY `INDEX_RID` (`msg_rid`),
  KEY `INDEX_DRAFT` (`msg_draft`),
  KEY `INDEX_CREATED` (`msg_created`),
  KEY `INDEX_SENT` (`msg_sent`),
  KEY `INDEX_SDELETED` (`msg_sdeleted`),
  KEY `INDEX_RDELETED` (`msg_rdeleted`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Babel Message Table';

-- --------------------------------------------------------

-- 
-- 表的结构 `babel_mobile_data`
-- 

CREATE TABLE `babel_mobile_data` (
  `mob_no` int(10) unsigned NOT NULL,
  `mob_area` varchar(20) NOT NULL,
  `mob_subarea` varchar(20) NOT NULL,
  PRIMARY KEY  (`mob_no`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Babel Mobile Data Table';

-- --------------------------------------------------------

-- 
-- 表的结构 `babel_node`
-- 

CREATE TABLE `babel_node` (
  `nod_id` int(10) unsigned NOT NULL auto_increment,
  `nod_pid` int(10) unsigned NOT NULL default '5',
  `nod_uid` int(10) unsigned NOT NULL default '1',
  `nod_sid` int(10) unsigned NOT NULL default '5',
  `nod_level` int(10) unsigned NOT NULL default '2',
  `nod_name` varchar(100) NOT NULL default 'node',
  `nod_title` varchar(100) NOT NULL default 'Untitled node',
  `nod_description` text,
  `nod_header` text,
  `nod_footer` text,
  `nod_portrait` varchar(40) default NULL,
  `nod_topics` int(10) unsigned NOT NULL default '0',
  `nod_favs` int(10) unsigned NOT NULL default '0',
  `nod_weight` int(11) NOT NULL default '0',
  `nod_created` int(10) unsigned NOT NULL default '0',
  `nod_lastupdated` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`nod_id`),
  KEY `INDEX_PID` (`nod_pid`),
  KEY `INDEX_UID` (`nod_uid`),
  KEY `INDEX_SID` (`nod_sid`),
  KEY `INDEX_TOPICS` (`nod_topics`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Babel Node Table';

-- --------------------------------------------------------

-- 
-- 表的结构 `babel_online`
-- 

CREATE TABLE `babel_online` (
  `onl_hash` char(32) NOT NULL default '',
  `onl_nick` varchar(40) default NULL,
  `onl_ua` varchar(200) default NULL,
  `onl_ip` varchar(15) default '0.0.0.0',
  `onl_uri` varchar(200) default '/',
  `onl_ref` varchar(200) default '/',
  `onl_created` int(10) unsigned NOT NULL default '0',
  `onl_lastmoved` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`onl_hash`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8 COMMENT='Babel Online Table';

-- --------------------------------------------------------

-- 
-- 表的结构 `babel_passwd`
-- 

CREATE TABLE `babel_passwd` (
  `pwd_id` int(10) unsigned NOT NULL auto_increment,
  `pwd_uid` int(10) unsigned NOT NULL default '0',
  `pwd_hash` char(100) default NULL,
  `pwd_ip` varchar(15) NOT NULL default '0.0.0.0',
  `pwd_created` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`pwd_id`),
  KEY `INDEX_UID` (`pwd_uid`),
  KEY `INDEX_HASH` (`pwd_hash`),
  KEY `INDEX_CREATED` (`pwd_created`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Babel Passwd Table';

-- --------------------------------------------------------

-- 
-- 表的结构 `babel_post`
-- 

CREATE TABLE `babel_post` (
  `pst_id` int(10) unsigned NOT NULL auto_increment,
  `pst_tid` int(10) unsigned NOT NULL default '5',
  `pst_uid` int(10) unsigned NOT NULL default '0',
  `pst_title` varchar(100) default 'Untitled reply',
  `pst_content` text,
  `pst_created` int(10) unsigned NOT NULL default '0',
  `pst_lastupdated` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`pst_id`),
  KEY `INDEX_TID` (`pst_tid`),
  KEY `INDEX_UID` (`pst_uid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Babel Post Table';

-- --------------------------------------------------------

-- 
-- 表的结构 `babel_related`
-- 

CREATE TABLE `babel_related` (
  `rlt_id` int(10) unsigned NOT NULL auto_increment,
  `rlt_pid` int(10) unsigned NOT NULL default '0',
  `rlt_title` varchar(200) NOT NULL default '',
  `rlt_url` varchar(200) NOT NULL default '',
  `rlt_created` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`rlt_id`),
  KEY `INDEX_PID` (`rlt_pid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Babel Related Table';

-- --------------------------------------------------------

-- 
-- 表的结构 `babel_savepoint`
-- 

CREATE TABLE `babel_savepoint` (
  `svp_id` int(10) unsigned NOT NULL auto_increment,
  `svp_uid` int(10) unsigned NOT NULL default '0',
  `svp_url` varchar(400) NOT NULL default '',
  `svp_rank` int(10) unsigned NOT NULL default '0',
  `svp_created` int(10) unsigned NOT NULL default '0',
  `svp_lastupdated` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`svp_id`),
  KEY `INDEX_UID` (`svp_uid`),
  KEY `INDEX_URL` (`svp_url`(333))
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Babel Savepoint Table';

-- --------------------------------------------------------

-- 
-- 表的结构 `babel_surprise`
-- 

CREATE TABLE `babel_surprise` (
  `srp_id` int(10) unsigned NOT NULL auto_increment,
  `srp_uid` int(10) unsigned NOT NULL default '0',
  `srp_amount` int(11) NOT NULL default '0',
  `srp_type` int(10) unsigned NOT NULL default '0',
  `srp_memo` text,
  `srp_created` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`srp_id`),
  KEY `INDEX_UID` (`srp_uid`),
  KEY `INDEX_TYPE` (`srp_type`),
  KEY `INDEX_CREATED` (`srp_created`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Babel Surprise Table';

-- --------------------------------------------------------

-- 
-- 表的结构 `babel_topic`
-- 

CREATE TABLE `babel_topic` (
  `tpc_id` int(10) unsigned NOT NULL auto_increment,
  `tpc_pid` int(10) unsigned NOT NULL default '5',
  `tpc_uid` int(10) unsigned NOT NULL default '0',
  `tpc_title` varchar(100) NOT NULL default 'Untitled topic',
  `tpc_description` text,
  `tpc_content` text,
  `tpc_hits` int(10) unsigned NOT NULL default '0',
  `tpc_refs` int(10) unsigned NOT NULL default '0',
  `tpc_posts` int(10) unsigned NOT NULL default '0',
  `tpc_favs` int(10) unsigned NOT NULL default '0',
  `tpc_flag` int(10) unsigned NOT NULL default '0',
  `tpc_created` int(10) unsigned NOT NULL default '0',
  `tpc_lastupdated` int(10) unsigned NOT NULL default '0',
  `tpc_lasttouched` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`tpc_id`),
  KEY `INDEX_PID` (`tpc_pid`),
  KEY `INDEX_UID` (`tpc_uid`),
  KEY `INDEX_POSTS` (`tpc_posts`),
  KEY `INDEX_LASTTOUCHED` (`tpc_lasttouched`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Babel Topic Table';

-- --------------------------------------------------------

-- 
-- 表的结构 `babel_user`
-- 

CREATE TABLE `babel_user` (
  `usr_id` int(10) unsigned NOT NULL auto_increment,
  `usr_gid` int(10) unsigned NOT NULL default '0',
  `usr_nick` varchar(40) NOT NULL default '',
  `usr_password` varchar(40) NOT NULL default '',
  `usr_email` varchar(100) default NULL,
  `usr_full` varchar(40) default NULL,
  `usr_addr` varchar(200) default NULL,
  `usr_telephone` varchar(40) default NULL,
  `usr_identity` varchar(18) default NULL,
  `usr_gender` smallint(6) NOT NULL default '0',
  `usr_birthday` int(10) unsigned NOT NULL default '0',
  `usr_portrait` varchar(40) default NULL,
  `usr_brief` longtext,
  `usr_money` double NOT NULL default '0',
  `usr_width` int(10) unsigned NOT NULL default '1024',
  `usr_hits` int(10) unsigned NOT NULL default '0',
  `usr_logins` int(10) unsigned NOT NULL default '0',
  `usr_api` int(10) unsigned NOT NULL default '0',
  `usr_editor` varchar(20) NOT NULL default 'default',
  `usr_created` int(10) unsigned NOT NULL default '0',
  `usr_lastupdated` int(10) unsigned NOT NULL default '0',
  `usr_lastlogin` int(10) unsigned NOT NULL default '0',
  `usr_lastlogin_ua` varchar(400) default NULL,
  PRIMARY KEY  (`usr_id`),
  KEY `INDEX_GID` (`usr_gid`),
  KEY `INDEX_NICK` (`usr_nick`),
  KEY `INDEX_PASSWORD` (`usr_password`),
  KEY `INDEX_EMAIL` (`usr_email`),
  KEY `INDEX_API` (`usr_api`),
  KEY `INDEX_PORTRAIT` (`usr_portrait`),
  KEY `INDEX_HITS` (`usr_hits`),
  KEY `INDEX_LASTLOGIN` (`usr_lastlogin`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Babel User Table';

-- --------------------------------------------------------

-- 
-- 表的结构 `babel_zen_project`
-- 

CREATE TABLE `babel_zen_project` (
  `zpr_id` int(10) unsigned NOT NULL auto_increment,
  `zpr_uid` int(10) unsigned NOT NULL default '0',
  `zpr_private` int(10) unsigned NOT NULL default '0',
  `zpr_title` varchar(100) NOT NULL,
  `zpr_progress` int(10) unsigned NOT NULL default '0',
  `zpr_created` int(10) unsigned NOT NULL default '0',
  `zpr_lastupdated` int(10) unsigned NOT NULL default '0',
  `zpr_lasttouched` int(10) unsigned NOT NULL default '0',
  `zpr_completed` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`zpr_id`),
  KEY `INDEX_UID` (`zpr_uid`),
  KEY `INDEX_PRIVATE` (`zpr_private`),
  KEY `INDEX_PROGRESS` (`zpr_progress`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Babel ZEN Project Table';

-- --------------------------------------------------------

-- 
-- 表的结构 `babel_zen_task`
-- 

CREATE TABLE `babel_zen_task` (
  `zta_id` int(10) unsigned NOT NULL auto_increment,
  `zta_uid` int(10) unsigned NOT NULL default '0',
  `zta_pid` int(10) unsigned NOT NULL default '0',
  `zta_title` varchar(100) NOT NULL,
  `zta_progress` int(10) unsigned NOT NULL default '0',
  `zta_created` int(10) unsigned NOT NULL default '0',
  `zta_lastupdated` int(10) unsigned NOT NULL default '0',
  `zta_completed` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`zta_id`),
  KEY `INDEX_UID` (`zta_uid`),
  KEY `INDEX_PID` (`zta_pid`),
  KEY `INDEX_PROGRESS` (`zta_progress`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Babel ZEN Task Table';

CREATE TABLE `babel_user_portrait` (
  `urp_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `urp_filename` varchar(40) DEFAULT NULL,
  `urp_content` blob NOT NULL,
  PRIMARY KEY (`urp_id`),
  UNIQUE KEY `INDEX_FILENAME` (`urp_filename`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

INSERT INTO `babel_node`(`nod_pid`, `nod_level`, `nod_name`, `nod_title`) VALUES(1, 0, 'planescape', '异域');

INSERT INTO `babel_node`(`nod_pid`, `nod_level`, `nod_name`, `nod_title`) VALUES(1, 1, 'limbo', '混沌海');
