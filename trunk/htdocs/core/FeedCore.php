<?php
/* Project Babel
 *
 * Author: Livid Liu <v2ex.livid@mac.com>
 * File: /htdocs/core/FeedCore.php
 * Usage: Feed logic
 * Format: 1 tab indent(4 spaces), LF, UTF-8, no-BOM
 *  
 * Subversion Keywords:
 *
 * $Id$
 * $Date$
 * $Revision$
 * $Author$
 * $URL$
 *
 * Copyright (C) 2006 Livid Liu <v2ex.livid@mac.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software Foundation,
 * Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

if (V2EX_BABEL == 1) {
	/* most important thing */
	require('core/Settings.php');
	
	/* 3rdParty PEAR cores */
	ini_set('include_path', BABEL_PREFIX . '/libs/pear' . PATH_SEPARATOR . ini_get('include_path'));
	require_once('Cache/Lite.php');
	require_once('HTTP/Request.php');
	require_once('Crypt/Blowfish.php');
	require_once('Mail.php');
	require_once('Benchmark/Timer.php');
	
	/* 3rdParty cores */
	require(BABEL_PREFIX . '/libs/smarty/libs/Smarty.class.php');
	
	/* built-in cores */
	require_once('core/UserCore.php');
	require_once('core/ValidatorCore.php');
	require('core/Vocabularies.php');
	require('core/Utilities.php');
	require('core/NodeCore.php');
	require('core/GeoCore.php');
	require('core/TopicCore.php');
} else {
	die('<strong>Project Babel</strong><br /><br />Made by V2EX | software for internet');
}

/* S Feed class */

class Feed {
	var $db;
	var $s;
	
	/* S module: constructor and destructor */

	public function __construct() {
		$this->db = mysql_connect(BABEL_DB_HOSTNAME . ':' . BABEL_DB_PORT, BABEL_DB_USERNAME, BABEL_DB_PASSWORD);
		mysql_select_db(BABEL_DB_SCHEMATA);
		mysql_query("SET NAMES utf8");
		mysql_query("SET CHARACTER SET utf8");
		mysql_query("SET COLLATION_CONNECTION='utf8_general_ci'");
		
		session_start();
		$this->User = new User('', '', $this->db);
		
		$this->Validator = new Validator($this->db, $this->User);
		
		global $CACHE_LITE_OPTIONS_SHORT;
		$this->cs = new Cache_Lite($CACHE_LITE_OPTIONS_SHORT);
		
		$this->restricted = get_restricted($this->cs);
		
		$this->s = new Smarty();
		$this->s->template_dir = BABEL_PREFIX . '/tpl';
		$this->s->compile_dir = BABEL_PREFIX . '/tplc';
		$this->s->cache_dir = BABEL_PREFIX . '/cache/smarty';
		$this->s->config_dir = BABEL_PREFIX . '/cfg';
		$this->s->caching = SMARTY_CACHING;
		
		$this->s->assign('site_lang', BABEL_LANG);
		$this->s->assign('site_base', 'http://' . BABEL_DNS_NAME . '/');
		header('Content-Type: text/xml;charset=utf-8');
	}
	
	public function __destruct() {
		mysql_close($this->db);
	}
	
	/* E module: constructor and destructor */
	
	/* S public modules */

	public function vxFeed() {
		$this->s->assign('site_url', 'http://' . BABEL_DNS_NAME . '/');
		$sql = 'SELECT usr_id, usr_nick, usr_gender, usr_portrait, tpc_id, tpc_title, tpc_content, tpc_posts, tpc_created, nod_id, nod_title, nod_name FROM babel_user, babel_topic, babel_node WHERE tpc_uid = usr_id AND tpc_pid = nod_id AND tpc_pid NOT IN ' . BABEL_NODES_POINTLESS . ' ORDER BY tpc_created DESC LIMIT 20';
		$rs = mysql_query($sql);
		$Topics = array();
		$i = 0;
		while ($Topic = mysql_fetch_object($rs)) {
			$i++;
			$Topics[$i] = $Topic;
			$Topics[$i]->tpc_title = htmlspecialchars($Topics[$i]->tpc_title, ENT_NOQUOTES);
			$Topics[$i]->tpc_content = htmlspecialchars(format_ubb($Topics[$i]->tpc_content), ENT_NOQUOTES);
			$Topics[$i]->tpc_pubdate = date('r', $Topics[$i]->tpc_created);
		}
		$this->s->assign('feed_title', 'Latest from ' . Vocabulary::site_name);
		$this->s->assign('feed_description', Vocabulary::meta_description);
		$this->s->assign('feed_category', Vocabulary::meta_category);
		$this->s->assign('a_topics', $Topics);
		$this->s->display('feed/rss2.smarty');
	}
	
	public function vxFeedDenied() {
		$this->s->assign('site_url', 'http://' . BABEL_DNS_NAME . '/');
		$this->s->assign('feed_title', '访问被拒绝');
		$this->s->assign('feed_description', '[Project Babel] Feed Generator - 缺少访问特定资源的权限');
		$this->s->assign('feed_category', Vocabulary::meta_category);
		$Topics = array();
		$i = 0; $i++;
		$Topics[$i]->tpc_title = '访问被拒绝';
		$Topics[$i]->tpc_content = '[Project Babel] Feed Generator - 缺少访问特定资源的权限';
		$Topics[$i]->tpc_pubdate = date('r', time());
		$this->s->assign('a_topics', $Topics);
		$this->s->display('feed/rss2_denied.smarty');
	}
	
	public function vxFeedBoard($Node) {
		if (!check_node_permission($Node->nod_id, $this->User, $this->restricted)) {
			$this->vxFeedDenied();
		} else {
			$this->s->assign('site_url', 'http://' . BABEL_DNS_NAME . '/go/' . $Node->nod_name);
			switch ($Node->nod_level) {
				case 2:
				default:
					$sql = "SELECT usr_id, usr_nick, usr_gender, usr_portrait, tpc_id, tpc_title, tpc_content, tpc_posts, tpc_created, nod_id, nod_title, nod_name FROM babel_user, babel_topic, babel_node WHERE tpc_uid = usr_id AND tpc_pid = nod_id AND tpc_pid = {$Node->nod_id} ORDER BY tpc_created DESC LIMIT 20";
					break;
				case 1:
					$sql = "SELECT usr_id, usr_nick, usr_gender, usr_portrait, tpc_id, tpc_title, tpc_content, tpc_posts, tpc_created, nod_id, nod_title, nod_name FROM babel_user, babel_topic, babel_node WHERE tpc_uid = usr_id AND tpc_pid = nod_id AND tpc_pid IN (SELECT nod_id FROM babel_node WHERE nod_pid = {$Node->nod_id}) ORDER BY tpc_created DESC LIMIT 20";
					break;
			}
			$rs = mysql_query($sql);
			$Topics = array();
			$i = 0;
			while ($Topic = mysql_fetch_object($rs)) {
				$i++;
				$Topics[$i] = $Topic;
				$Topics[$i]->tpc_title = htmlspecialchars($Topics[$i]->tpc_title, ENT_NOQUOTES);
				$Topics[$i]->tpc_content = htmlspecialchars(format_ubb($Topics[$i]->tpc_content), ENT_NOQUOTES);
				$Topics[$i]->tpc_pubdate = date('r', $Topics[$i]->tpc_created);
			}
			$this->s->assign('feed_title', 'Latest from ' . Vocabulary::site_name . "'s " . $Node->nod_title);
			$this->s->assign('feed_description', Vocabulary::meta_description);
			$this->s->assign('feed_category', Vocabulary::meta_category);
			$this->s->assign('a_topics', $Topics);
			$this->s->display('feed/rss2.smarty');
		}
	}
	
	public function vxFeedUser($User) {
		$this->s->assign('site_url', 'http://' . BABEL_DNS_NAME . '/u/' . urlencode($User->usr_nick));
		$sql = "SELECT usr_id, usr_nick, usr_gender, usr_portrait, tpc_id, tpc_title, tpc_content, tpc_posts, tpc_created, nod_id, nod_title, nod_name FROM babel_topic, babel_node, babel_user WHERE tpc_uid = {$User->usr_id} AND tpc_uid = usr_id AND tpc_pid = nod_id AND tpc_pid NOT IN " . BABEL_NODES_POINTLESS . " ORDER BY tpc_created DESC LIMIT 20";
		$rs = mysql_query($sql);
		$Topics = array();
		$i = 0;
		while ($Topic = mysql_fetch_object($rs)) {
			$i++;
			$Topics[$i] = $Topic;
			$Topics[$i]->tpc_title = htmlspecialchars($Topics[$i]->tpc_title, ENT_NOQUOTES);
			$Topics[$i]->tpc_content = htmlspecialchars(format_ubb($Topics[$i]->tpc_content), ENT_NOQUOTES);
			$Topics[$i]->tpc_pubdate = date('r', $Topics[$i]->tpc_created);
		}
		$this->s->assign('feed_title', 'Latest from ' . Vocabulary::site_name . ": " . make_plaintext($User->usr_nick));
		$this->s->assign('feed_description', Vocabulary::meta_description);
		$this->s->assign('feed_category', Vocabulary::meta_category);
		$this->s->assign('a_topics', $Topics);
		$this->s->display('feed/rss2.smarty');
	}
	
	public function vxFeedGeo($Geo) {
		$this->s->assign('site_url', 'http://' . BABEL_DNS_NAME . '/geo/' . urlencode($Geo->geo->geo));
		$geo_real = mysql_real_escape_string($Geo->geo->geo);
		$sql = "SELECT usr_id, usr_nick, usr_gender, usr_portrait, tpc_id, tpc_title, tpc_content, tpc_posts, tpc_created, nod_id, nod_title, nod_name FROM babel_topic, babel_node, babel_user WHERE tpc_uid IN (SELECT usr_id FROM babel_user WHERE usr_geo = '{$geo_real}') AND tpc_uid = usr_id AND tpc_pid = nod_id AND tpc_pid NOT IN " . BABEL_NODES_POINTLESS . " ORDER BY tpc_created DESC LIMIT 20";
		$rs = mysql_query($sql);
		$Topics = array();
		$i = 0;
		while ($Topic = mysql_fetch_object($rs)) {
			$i++;
			$Topics[$i] = $Topic;
			$Topics[$i]->tpc_title = htmlspecialchars($Topics[$i]->tpc_title, ENT_NOQUOTES);
			$Topics[$i]->tpc_content = htmlspecialchars(format_ubb($Topics[$i]->tpc_content), ENT_NOQUOTES);
			$Topics[$i]->tpc_pubdate = date('r', $Topics[$i]->tpc_created);
		}
		$this->s->assign('feed_title', 'Latest from ' . Vocabulary::site_name . ": " . $Geo->geo->name->cn);
		$this->s->assign('feed_description', Vocabulary::meta_description);
		$this->s->assign('feed_category', Vocabulary::meta_category);
		$this->s->assign('a_topics', $Topics);
		$this->s->display('feed/rss2.smarty');
	}
	
	public function vxFeedTopic($topic_id) {
		$Topic = new Topic($topic_id, $this->db, 0);
		$Board = new Node($Topic->tpc_pid, $this->db);
		
		$sql = "SELECT COUNT(pst_id) FROM babel_post WHERE pst_tid = {$Topic->tpc_id}";
		$rs = mysql_query($sql);
		$count = mysql_result($rs, 0, 0);
		mysql_free_result($rs);		
		
		$sql = 'SELECT pst_id, pst_title, pst_content, pst_created, usr_id, usr_nick FROM babel_post, babel_user WHERE pst_uid = usr_id AND pst_tid = ' . $Topic->tpc_id . ' ORDER BY pst_id ASC';
		$rs = mysql_query($sql);
		$i = 0;
		$Posts = array();
		while ($Post = mysql_fetch_object($rs)) {
			$i++;
			$Posts[$i] = $Post;
			$Posts[$i]->pst_title = htmlspecialchars('#' . $i . ' - ' . $Posts[$i]->pst_title, ENT_NOQUOTES);
			$Posts[$i]->pst_content = htmlspecialchars(format_ubb($Posts[$i]->pst_content), ENT_NOQUOTES);
			$Posts[$i]->pst_pubdate = date('r', $Posts[$i]->pst_created);
			$Posts[$i]->usr_nick = htmlspecialchars($Posts[$i]->usr_nick, ENT_NOQUOTES);
			if ($i == 1) {
				$latest = $Post->pst_created;
			}
			if ($i > BABEL_TPC_PAGE) {
				if (($i % BABEL_TPC_PAGE) > 0) {
					$page = floor($i / BABEL_TPC_PAGE) + 1;
				} else {
					$page = intval($i / BABEL_TPC_PAGE);
				}
				$Posts[$i]->link = 'http://' . BABEL_DNS_NAME . '/topic/view/' . $Topic->tpc_id . '/' . $page . '.html#p' . $Post->pst_id;
			} else {
				$Posts[$i]->link = 'http://' . BABEL_DNS_NAME . '/topic/view/' . $Topic->tpc_id . '.html#p' . $Post->pst_id;
			}
		}
		mysql_free_result($rs);
		$Posts = array_reverse($Posts, true);
		$description = htmlspecialchars('截至 ' . date('Y-n-j G:i:s T', $latest) . ' ，主题 [ <a href="http://' . BABEL_DNS_NAME . '/topic/view/' . $Topic->tpc_id . '.html" target="_blank">' . $Topic->tpc_title . '</a> ] 共收到来自 ' . count($Topic->tpc_followers) . ' 名会员的 ' . $count . ' 篇回复。', ENT_NOQUOTES);
		$this->s->assign('site_url', 'http://' . BABEL_DNS_NAME . '/topic/view/' . $Topic->tpc_id . '.html');
		$this->s->assign('feed_title', 'Latest replies to ' . make_plaintext($Topic->tpc_title));
		$this->s->assign('feed_description', $description);
		$this->s->assign('feed_category', make_plaintext($Board->nod_title));
		$this->s->assign('topic', $Topic);
		$this->s->assign('board', $Board);
		$this->s->assign('a_posts', $Posts);
		$this->s->display('feed/rss2_topic.smarty');
	}
	
	/* E public modules */
	
}

/* E Feed class */
?>
