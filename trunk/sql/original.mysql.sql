/* Project Babel
*  Author: Livid Torvalds
*  File: /sql/babel.mysql.sql
*  Usage: Database structure for MySQL Database Server
*  Format: Native MySQLdump file
*/

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

DROP TABLE IF EXISTS `babel_channel`;
CREATE TABLE `babel_channel` (
  `chl_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `chl_pid` int(10) unsigned NOT NULL default '0',
  `chl_title` varchar(200) NOT NULL default '',
  `chl_url` varchar(200) NOT NULL default '',
  `chl_created` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY (`chl_id`),
  KEY `INDEX_PID` (`chl_pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Babel Channel Table';

DROP TABLE IF EXISTS `babel_message`;
CREATE TABLE `babel_message` (
  `msg_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Babel Message Table';

DROP TABLE IF EXISTS `babel_favorite`;
CREATE TABLE `babel_favorite` (
  `fav_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Babel Favorite Table';

DROP TABLE IF EXISTS `babel_online`;
CREATE TABLE `babel_online` (
  `onl_hash` char(32) default NULL,
  `onl_nick` varchar(40) default NULL,
  `onl_ua` varchar(200) default NULL,
  `onl_ip` varchar(15) default '0.0.0.0',
  `onl_uri` varchar(200) default '/',
  `onl_ref` varchar(200) default '/',
  `onl_created` int(10) unsigned NOT NULL default '0',
  `onl_lastmoved` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`onl_hash`),
  KEY `INDEX_NICK` (`onl_nick`),
  KEY `INDEX_CREATED` (`onl_created`),
  KEY `INDEX_LASTMOVED` (`onl_lastmoved`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Babel Online Table';

DROP TABLE IF EXISTS `babel_user`;
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
  `usr_api` int(10) unsigned NOT NULL default '0',
  `usr_editor` varchar(20) NOT NULL default 'default',
  `usr_created` int(10) unsigned NOT NULL default '0',
  `usr_lastupdated` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`usr_id`),
  KEY `INDEX_GID` (`usr_gid`),
  KEY `INDEX_NICK` (`usr_nick`),
  KEY `INDEX_PASSWORD` (`usr_password`),
  KEY `INDEX_EMAIL` (`usr_email`),
  KEY `INDEX_API` (`usr_api`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Babel User Table';

DROP TABLE IF EXISTS `babel_group`;
CREATE TABLE `babel_group` (
  `grp_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `grp_oid` int(10) unsigned NOT NULL default '0',
  `grp_nick` varchar(40) NOT NULL default '',
  `grp_brief` longtext,
  `grp_created` int(10) unsigned NOT NULL default '0',
  `grp_lastupdated` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`grp_id`),
  KEY `INDEX_OID` (`grp_oid`),
  KEY `INDEX_NICK` (`grp_nick`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Babel Group Table';

DROP TABLE IF EXISTS `babel_node`;
CREATE TABLE `babel_node` (
  `nod_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nod_pid` int(10) unsigned NOT NULL default '5',
  `nod_uid` int(10) unsigned NOT NULL default '1',
  `nod_sid` int(10) unsigned NOT NULL default '5',
  `nod_level` int(10) unsigned NOT NULL default '2',
  `nod_name` varchar(100) NOT NULL default 'node',
  `nod_title` varchar(100) NOT NULL default 'Untitled node',
  `nod_description` text,
  `nod_header` text,
  `nod_footer` text,
  `nod_topics` int(10) unsigned NOT NULL default '0',
  `nod_created` int(10) unsigned NOT NULL default '0',
  `nod_lastupdated` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY (`nod_id`),
  KEY `INDEX_PID` (`nod_pid`),
  KEY `INDEX_UID` (`nod_uid`),
  KEY `INDEX_SID` (`nod_sid`),
  KEY `INDEX_TOPICS` (`nod_topics`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Babel Node Table';

DROP TABLE IF EXISTS `babel_topic`;
CREATE TABLE `babel_topic` (
  `tpc_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tpc_pid` int(10) unsigned NOT NULL default '5',
  `tpc_uid` int(10) unsigned NOT NULL default '0',
  `tpc_title` varchar(100) NOT NULL default 'Untitled topic',
  `tpc_description` text,
  `tpc_content` text,
  `tpc_hits` int(10) unsigned NOT NULL default '0',
  `tpc_refs` int(10) unsigned NOT NULL default '0',
  `tpc_posts` int(10) unsigned NOT NULL default '0',
  `tpc_flag` int(10) unsigned NOT NULL default '0',
  `tpc_created` int(10) unsigned NOT NULL default '0',
  `tpc_lastupdated` int(10) unsigned NOT NULL default '0',
  `tpc_lasttouched` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY (`tpc_id`),
  KEY `INDEX_PID` (`tpc_pid`),
  KEY `INDEX_UID` (`tpc_uid`),
  KEY `INDEX_POSTS` (`tpc_posts`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Babel Topic Table';

DROP TABLE IF EXISTS `babel_post`;
CREATE TABLE `babel_post` (
  `pst_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pst_tid` int(10) unsigned NOT NULL default '5',
  `pst_uid` int(10) unsigned NOT NULL default '0',
  `pst_title` varchar(100) default 'Untitled reply',
  `pst_content` text,
  `pst_created` int(10) unsigned NOT NULL default '0',
  `pst_lastupdated` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY (`pst_id`),
  KEY `INDEX_TID` (`pst_tid`),
  KEY `INDEX_UID` (`pst_uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Babel Post Table';

DROP TABLE IF EXISTS `babel_expense`;
CREATE TABLE `babel_expense` (
  `exp_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `exp_uid` int(10) unsigned NOT NULL default '0',
  `exp_amount` int signed NOT NULL default '0',
  `exp_type` int(10) unsigned NOT NULL default '0',
  `exp_memo` text,
  `exp_created` double NOT NULL default '0',
  PRIMARY KEY (`exp_id`),
  KEY `INDEX_UID` (`exp_uid`),
  KEY `INDEX_TYPE` (`exp_type`),
  KEY `INDEX_CREATED` (`exp_created`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Babel Expense Table';

DROP TABLE IF EXISTS `babel_surprise`;
CREATE TABLE `babel_surprise` (
  `srp_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `srp_uid` int(10) unsigned NOT NULL default '0',
  `srp_amount` int signed NOT NULL default '0',
  `srp_type` int(10) unsigned NOT NULL default '0',
  `srp_memo` text,
  `srp_created` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY (`srp_id`),
  KEY `INDEX_UID` (`srp_uid`),
  KEY `INDEX_TYPE` (`srp_type`),
  KEY `INDEX_CREATED` (`srp_created`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Babel Surprise Table';

DROP TABLE IF EXISTS `babel_foundation`;
CREATE TABLE `babel_foundation` (
  `fdt_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fdt_title` varchar(40) NOT NULL default 'Untitled foundation',
  `fdt_money` int signed NOT NULL default '0',
  `fdt_type` int(10) unsigned NOT NULL default '0',
  `fdt_brief` text,
  `fdt_created` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY (`srp_id`),
  KEY `INDEX_UID` (`srp_uid`),
  KEY `INDEX_TYPE` (`srp_type`),
  KEY `INDEX_CREATED` (`srp_created`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Babel Foundation Table';

INSERT INTO `babel_node`(`nod_pid`, `nod_level`, `nod_name`, `nod_title`) VALUES(1, 0, 'root', 'The Tree of World');

INSERT INTO `babel_node`(`nod_pid`, `nod_level`, `nod_name`, `nod_title`) VALUES(1, 1, 'life', 'Life');

INSERT INTO `babel_node`(`nod_pid`, `nod_level`, `nod_name`, `nod_title`) VALUES(1, 1, 'ent', 'Entertainment');

INSERT INTO `babel_node`(`nod_pid`, `nod_level`, `nod_name`, `nod_title`) VALUES(1, 1, 'geek', 'Geek Playground');

INSERT INTO `babel_node`(`nod_pid`, `nod_level`, `nod_name`, `nod_title`) VALUES(1, 1, 'whatever', 'WhAteVEr');

INSERT INTO `babel_node`(`nod_pid`, `nod_sid`, `nod_level`, `nod_name`, `nod_title`) VALUES(5, 5, 2, 'node6', 'Project Babel');