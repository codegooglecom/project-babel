<?php
/* Project Babel
 *
 * Author: Livid Liu <v2ex.livid@mac.com>
 * File: /htdocs/core/V2EXCore.php
 * Usage: V2EX Page Core Class
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
	/* The most important file */
	require('core/Settings.php');
	
	/* 3rdparty PEAR cores */
	ini_set('include_path', BABEL_PREFIX . DIRECTORY_SEPARATOR . 'libs' . DIRECTORY_SEPARATOR . 'pear' . PATH_SEPARATOR . ini_get('include_path'));
	require_once('Cache/Lite.php');
	require_once('HTTP/Request.php');
	require_once('Crypt/Blowfish.php');
	require_once('Net/Dict.php');
	require_once('Mail.php');
	if (BABEL_DEBUG) {
		require_once('Benchmark/Timer.php');
	}
	
	/* 3rdparty Zend Framework cores */
	ini_set('include_path', BABEL_PREFIX . '/libs/zf/' . ZEND_FRAMEWORK_VERSION . PATH_SEPARATOR . ini_get('include_path'));
	require_once('Zend/Search/Lucene.php');
	require_once('Zend/Cache.php');
	require_once('Zend/Feed.php');
	require_once('Zend/Http/Client.php');
	
	/* 3rdparty cores */
	require(BABEL_PREFIX . '/libs/magpierss/rss_fetch.inc');
	require(BABEL_PREFIX . '/libs/smarty/libs/Smarty.class.php');
	require(BABEL_PREFIX . '/libs/kses/kses.php');
	
	/* built-in cores */
	require('core/Vocabularies.php');
	require('core/Utilities.php');
	require('core/Shortcuts.php');
	require('core/AirmailCore.php');
	require('core/UserCore.php');
	require('core/LanguageCore.php');
	require('core/NodeCore.php');
	require('core/GeoCore.php');
	require('core/ProjectCore.php');
	require('core/TopicCore.php');
	require('core/ChannelCore.php');
	require('core/URLCore.php');
	require('core/ZenCore.php');
	require('core/FunCore.php');
	require('core/ImageCore.php');
	require('core/ValidatorCore.php');
} else {
	die('<strong>Project Babel</strong><br /><br />Made by V2EX | software for internet');
}

/* S Page class */

class Page {
	public $User;
	
	public $lang;
	
	public $db;
	
	public $cs; // Cache Short: 360s
	public $cl; // Cache Long: 7200s
	
	public $Validator;

	public $online_count;
	public $online_count_anon;
	public $online_count_reg;
	
	public $tpc_count;
	public $pst_count;
	public $fav_count;
	public $svp_count;
	public $usr_count;
	
	public $p_msg_count;
	
	public $usr_share;
	
	public $restricted;
	
	/* S module: constructor and destructor */

	public function __construct() {
		if (BABEL_DEBUG) {
			$this->timer = new Benchmark_Timer();
			$this->timer->start();
			if (!isset($_SESSION['babel_debug_log'])) {
				$_SESSION['babel_debug_log'] = array();
			}
		} else {
			error_reporting(0);
		}

		check_env();
		
		require_once(BABEL_PREFIX . '/lang/' . BABEL_LANG . '/lang.php');
		
		$this->lang = new lang();
		
		if (@$this->db = mysql_connect(BABEL_DB_HOSTNAME . ':' . BABEL_DB_PORT, BABEL_DB_USERNAME, BABEL_DB_PASSWORD)) {
			mysql_select_db(BABEL_DB_SCHEMATA);
			mysql_query("SET NAMES utf8");
			mysql_query("SET CHARACTER SET utf8");
			mysql_query("SET COLLATION_CONNECTION='utf8_general_ci'");
			$rs = mysql_query('SELECT nod_id FROM babel_node WHERE nod_id = 1');
			if (@mysql_num_rows($rs) == 1) {
			} else {
				exception_message('world');
			}
		} else {
			exception_message('db');
		}
		
		global $CACHE_LITE_OPTIONS_SHORT;
		$this->cs = new Cache_Lite($CACHE_LITE_OPTIONS_SHORT);
		/* legacy long cache:
		global $CACHE_LITE_OPTIONS_LONG;
		$this->cl = new Cache_Lite($CACHE_LITE_OPTIONS_LONG);
		*/
		global $ZEND_CACHE_OPTIONS_LONG_FRONTEND;
		global $ZEND_CACHE_OPTIONS_LONG_BACKEND;
		global $ZEND_CACHE_OPTIONS_MEMCACHED;
		if (ZEND_CACHE_MEMCACHED_ENABLED == 'yes') {
			$this->cl = Zend_Cache::factory('Core', 'Memcached', $ZEND_CACHE_OPTIONS_LONG_FRONTEND, $ZEND_CACHE_OPTIONS_MEMCACHED);
		} else {
			$this->cl = Zend_Cache::factory('Core', 'File', $ZEND_CACHE_OPTIONS_LONG_FRONTEND, $ZEND_CACHE_OPTIONS_LONG_BACKEND);
		}
		session_start();
		$this->User = new User('', '', $this->db);
		if ($this->User->vxIsLogin()) {
			$sql = "SELECT usr_id, usr_gender, usr_nick, usr_portrait FROM babel_user, babel_friend WHERE usr_id = frd_fid AND frd_uid = {$this->User->usr_id} ORDER BY frd_created ASC";
			$rs = mysql_query($sql);
			$_friends = array();
			while ($_friend = mysql_fetch_array($rs)) {
				$_friends[$_friend['usr_id']] = $_friend;
			}
			mysql_free_result($rs);
			$this->User->usr_friends = $_friends;
		}
		$this->Validator =  new Validator($this->db, $this->User);
		
		if (!isset($_SESSION['babel_ua'])) {
			$_SESSION['babel_ua'] = $this->Validator->vxGetUserAgent();
		}
		
		$sql = 'DELETE FROM babel_online WHERE onl_lastmoved < ' . (time() - BABEL_USR_ONLINE_DURATION);
		mysql_query($sql, $this->db);
		$sql = "SELECT onl_hash FROM babel_online WHERE onl_hash = '" . mysql_real_escape_string(session_id()) . "'";
		$rs = mysql_query($sql, $this->db);
		if (isset($_SERVER['HTTP_REFERER'])) {
			$referer = mysql_real_escape_string($_SERVER['HTTP_REFERER']);
		} else {
			$referer = '';
		}
		if (mysql_num_rows($rs) == 1) {
			$s = mysql_fetch_object($rs);
			mysql_free_result($rs);
			$sql = "UPDATE babel_online SET onl_nick = '" . mysql_real_escape_string($this->User->usr_nick, $this->db) . "', onl_ua = '" . mysql_real_escape_string($_SESSION['babel_ua']['ua'], $this->db) . "', onl_ip = '" . $_SERVER['REMOTE_ADDR'] . "', onl_uri = '" . mysql_real_escape_string($_SERVER['REQUEST_URI']) . "', onl_ref = '" . $referer . "', onl_lastmoved = " . time() . " WHERE onl_hash = '" . mysql_real_escape_string(session_id()) . "'";
			mysql_query($sql, $this->db);
		} else {
			mysql_free_result($rs);
			$sql = "INSERT INTO babel_online(onl_hash, onl_nick, onl_ua, onl_ip, onl_uri, onl_ref, onl_created, onl_lastmoved) VALUES('" . mysql_real_escape_string(session_id()) . "', '" . mysql_real_escape_string($this->User->usr_nick) . "', '" . mysql_real_escape_string($_SESSION['babel_ua']['ua']) . "', '" . $_SERVER['REMOTE_ADDR'] . "', '" . mysql_real_escape_string($_SERVER['REQUEST_URI']) . "', '" . $referer . "', " . time() . ', ' . time() . ')';
			mysql_query($sql, $this->db);
		}
		$this->URL = new URL();
		$this->Geo = new Geo($this->User->usr_geo);
		if ($count_a = $this->cs->get('count')) {
			$count_a = unserialize($count_a);
			$this->pst_count = $count_a['pst_count'];
			$this->tpc_count = $count_a['tpc_count'];
			$this->fav_count = $count_a['fav_count'];
			$this->svp_count = $count_a['svp_count'];
			$this->usr_count = $count_a['usr_count'];
		} else {
			$sql = "SELECT COUNT(pst_id) FROM babel_post";
			$rs = mysql_query($sql, $this->db);
			$this->pst_count = mysql_result($rs, 0, 0);
			mysql_free_result($rs);
		
			$sql = "SELECT COUNT(tpc_id) FROM babel_topic";
			$rs = mysql_query($sql, $this->db);
			$this->tpc_count = mysql_result($rs, 0, 0);
			mysql_free_result($rs);
			
			$sql = "SELECT COUNT(fav_id) FROM babel_favorite";
			$rs = mysql_query($sql, $this->db);
			$this->fav_count = mysql_result($rs, 0, 0);
			mysql_free_result($rs);
			
			$sql = "SELECT COUNT(svp_id) FROM babel_savepoint";
			$rs = mysql_query($sql, $this->db);
			$this->svp_count = mysql_result($rs, 0, 0);
			mysql_free_result($rs);
			
			$sql = "SELECT COUNT(usr_id) FROM babel_user";
			$rs = mysql_query($sql, $this->db);
			$this->usr_count = mysql_result($rs, 0, 0);
			mysql_free_result($rs);
			
			$count_a = array();
			$count_a['pst_count'] = $this->pst_count;
			$count_a['tpc_count'] = $this->tpc_count;
			$count_a['fav_count'] = $this->fav_count;
			$count_a['svp_count'] = $this->svp_count;
			$count_a['usr_count'] = $this->usr_count;
			
			$this->cs->save(serialize($count_a), 'count');
		}
		
		$sql = "SELECT onl_hash FROM babel_online WHERE onl_nick = ''";
		$rs_a = mysql_query($sql, $this->db);
		$sql = "SELECT onl_hash, onl_nick FROM babel_online WHERE onl_nick != ''";
		$rs_b = mysql_query($sql, $this->db);
		$this->online_count_anon = mysql_num_rows($rs_a);
		$this->online_count_reg = mysql_num_rows($rs_b);
		mysql_free_result($rs_a);
		mysql_free_result($rs_b);
		$this->online_count = $this->online_count_anon + $this->online_count_reg;
		
		$this->restricted = get_restricted($this->cs);
		
		header('Content-Type: text/html; charset=UTF-8');
		header('Cache-control: no-cache, must-revalidate');
	}
	
	public function __destruct() {
		if (@$this->db) {
			mysql_close($this->db);
		}
		if (BABEL_DEBUG) {
			$this->timer->stop();
			echo('<div id="debug">Project Babel Debug Information - Generated on ' . date('Y-n-j G:i:s', time()) . '<br /><br />');
			echo str_replace('silver', '#333', str_replace('="1"', '="0" cellpadding="5" cellspacing="0"', $this->timer->getOutput(false, 'html')));
			echo('<br />');
			if (isset($_SESSION['babel_debug_log'])) {
				
				krsort($_SESSION['babel_debug_log']);
				if (count($_SESSION['babel_debug_log']) == 0) {
					echo('Debug log is empty.');
				} else {
					foreach ($_SESSION['babel_debug_log'] as $_time => $_event) {
						echo('<strong>' . date('r', $_time) . '</strong> - ' . $_event . '<br />');
					}
				}
			} else {
				$_SESSION['babel_debug_log'] = array();
				echo('Debug log is empty.');
			}
			echo('</div>');
		}
	}
	
	/* E module: constructor and destructor */
	
	/* S public modules */

	/* S module: meta tags */
	
	public function vxMeta($msgMetaKeywords = Vocabulary::meta_keywords, $msgMetaDescription = Vocabulary::meta_description, $return = '') {
		echo('<meta http-equiv="content-type" content="text/html;charset=utf-8" />');
		echo('<meta http-equiv="cache-control" content="no-cache" />');
		echo('<meta name="keywords" content="' . $msgMetaKeywords . '" />');
		if (strlen($return) > 0) {
			echo('<meta http-equiv="refresh" content="3;URL=' . $return . '" />');
		}
	}
	
	/* E module: meta tags */
	
	/* S module: title tag */
	
	public function vxTitle($msgSiteTitle = '') {
		if ($msgSiteTitle != '') {
			$msgSiteTitle = $msgSiteTitle . ' - ' . Vocabulary::site_name;
		} else {
			$msgSiteTitle = Vocabulary::site_title;
		}
		$_this_time = time();
		$_this_page = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		$_disabled_pages = array('/session/stats.vx', '/login.vx', '/logout.vx');
		if ($this->User->vxIsLogin()) {
			$sql = "SELECT COUNT(msg_id) FROM babel_message WHERE msg_rid = {$this->User->usr_id} AND msg_opened = 0";
			$rs = mysql_query($sql, $this->db);
			$this->p_msg_count = mysql_result($rs, 0, 0);
			mysql_free_result($rs);		
			if ((!in_array($_SERVER['SCRIPT_URL'], $_disabled_pages)) && (substr($_SERVER['SCRIPT_URL'], 0, 12) != '/post/modify') && (substr($_SERVER['SCRIPT_URL'], 0, 13) != '/topic/modify') && (substr($_SERVER['SCRIPT_URL'], 0, 13) != '/topic/update') && (substr($_SERVER['SCRIPT_URL'], 0, 12) != '/post/update') && (substr($_SERVER['SCRIPT_URL'], 0, 12) != '/online/view') && (substr($_SERVER['SCRIPT_URL'], 0, 3) != '/q/') && ($_SERVER['SCRIPT_URL'] != '/')) {
				if (isset($_SESSION['hits'])) {
					$_SESSION['hits'] = intval($_SESSION['hits']) + 1;
					$_SESSION['pages'][$_this_time] = $_this_page;
					$_SESSION['titles'][$_this_time] = $msgSiteTitle;
					if (($_SESSION['hits'] % 10) == 0) {
						$this->User->vxUpdateLogin();
					}
					if (($_SESSION['hits'] % 31) == 0) {
						$_date = getdate();
						if ((preg_match('/Alexa/', $_SESSION['babel_ua']['ua'])) || ($_date['wday'] == 0) || ($_date['hours'] > 17) || ($_date['hours'] < 8)) {
							$_adjust = intval($_SESSION['hits'] / 31);
							$_bonus = 31 / $_adjust;
							$this->User->vxPay($this->User->usr_id, $_bonus, 9, '单次访问达到 ' . $_SESSION['hits'] . ' 个页面的奖励');
						}
					}
					if ($_SESSION['hits'] == 100) {
						$_date = getdate();
						if ((preg_match('/Alexa/', $_SESSION['babel_ua']['ua'])) || ($_date['wday'] == 0) || ($_date['hours'] > 17) || ($_date['hours'] < 8)) {
							$this->User->vxPay($this->User->usr_id, 100, 100, '百页斩！');
						}
					}
					if ($_SESSION['hits'] == 1000) {
						$_date = getdate();
						if ((preg_match('/Alexa/', $_SESSION['babel_ua']['ua'])) || ($_date['wday'] == 0) || ($_date['hours'] > 17) || ($_date['hours'] < 8)) {
							$this->User->vxPay($this->User->usr_id, 1000, 1000, '你踏着一千个页面的残骸冲向了银河系！');
						}
					}
				} else {
					$_SESSION['hits'] = 1;
					$_SESSION['pages'] = array();
					$_SESSION['pages'][$_this_time] = $_this_page;
					$_SESSION['titles'] = array();
					$_SESSION['titles'][$_this_time] = $msgSiteTitle;
				}
			} else {
				if (!isset($_SESSION['hits'])) {
					$_SESSION['hits'] = 0;
				}
			}
		} else {
			if ((!in_array($_SERVER['SCRIPT_URL'], $_disabled_pages)) && (substr($_SERVER['SCRIPT_URL'], 0, 12) != '/post/modify') && (substr($_SERVER['SCRIPT_URL'], 0, 13) != '/topic/modify') && (substr($_SERVER['SCRIPT_URL'], 0, 13) != '/topic/update') && (substr($_SERVER['SCRIPT_URL'], 0, 12) != '/post/update') && (substr($_SERVER['SCRIPT_URL'], 0, 12) != '/online/view') && (substr($_SERVER['SCRIPT_URL'], 0, 3) != '/q/') && ($_SERVER['SCRIPT_URL'] != '/')) {
				if (isset($_SESSION['hits'])) {
					$_SESSION['hits'] = intval($_SESSION['hits']) + 1;
					$_SESSION['pages'][$_this_time] = $_this_page;
					$_SESSION['titles'][$_this_time] = $msgSiteTitle;
				} else {
					$_SESSION['hits'] = 1;
					$_SESSION['pages'] = array();
					$_SESSION['pages'][$_this_time] = $_this_page;
					$_SESSION['titles'] = array();
					$_SESSION['titles'][$_this_time] = $msgSiteTitle;
				}
			} else {
				if (!isset($_SESSION['hits'])) {
					$_SESSION['hits'] = 0;
				}
			}
		}
		echo('<title>' . $msgSiteTitle);
		if ($this->p_msg_count > 0) {
			echo(' (' . $this->p_msg_count . ')');
		}
		echo('</title>');
	}
	
	/* E module: title tag */
	
	/* S module: body tag start */
	
	public function vxBodyStart() {
		echo('<body>');
	}
	
	/* E module: body tag end */
	
	/* S module: body tag end */
	
	public function vxBodyEnd() {
		echo('</body></html>');
	}
	
	/* E module: body tag end */
	
	/* S module: link and script tags */
	
	public function vxLink($feedURL = BABEL_FEED_URL) {
		echo('<link href="/favicon.ico" rel="shortcut icon" />');
		echo('<link rel="stylesheet" type="text/css" href="/css/themes/' . BABEL_THEME . '/css_babel.css" />');
		echo('<link rel="stylesheet" type="text/css" href="/css/themes/' . BABEL_THEME . '/css_extra.css" />');
		echo('<link rel="stylesheet" type="text/css" href="/css/themes/' . BABEL_THEME . '/css_zen.css" />');
		echo('<link rel="stylesheet" type="text/css" href="/css/lightbox.css" media="screen" />');
		echo('<link rel="alternate" type="application/rss+xml" title="' . Vocabulary::site_name . ' RSS" href="' . $feedURL . '" />');
		echo('<script type="text/javascript" src="/js/babel.js"></script>');
		echo('<script type="text/javascript" src="/js/babel_zen.js"></script>');
		echo('<script type="text/javascript" src="' . CDN_UI . 'js/prototype.js"></script>');
		echo('<script type="text/javascript" src="' . CDN_UI . 'js/scriptaculous.js?load=effects"></script>');
		echo('<script type="text/javascript" src="' . CDN_UI . 'js/lightbox.js"></script>');
	}
	
	/* E module: link and script tags */
	
	/* S module: page headers */
	
	public function vxHead($msgSiteTitle = '', $return = '', $feedURL = BABEL_FEED_URL) {
		echo('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml" lang="zh-CN">');
		echo('<head>');
		$this->vxMeta(Vocabulary::meta_keywords, Vocabulary::meta_description, $return);
		$this->vxTitle($msgSiteTitle);
		$this->vxLink($feedURL);
		if (MINT_LOCATION != '') {
			echo('<script src="' . MINT_LOCATION . '" type="text/javascript"></script>');
		}
		echo('</head>');
	}
	
	public function vxHeadMini($title) {
		echo('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml" lang="zh-CN">');
		echo('<head>');
		$this->vxMeta(Vocabulary::meta_keywords, Vocabulary::meta_description);
		echo('<meta http-equiv="refresh" content="90;URL=/sidebar.html" />');
		$this->vxTitle($title);
		echo('<link href="/favicon.ico" rel="shortcut icon" />');
		echo('<link rel="stylesheet" type="text/css" href="/css/themes/' . BABEL_THEME . '/css_sidebar.css" />');
		echo('<link rel="alternate" type="application/rss+xml" title="' . Vocabulary::site_name . ' RSS" href="' . BABEL_FEED_URL . '" />');
		if (MINT_LOCATION != '') {
			echo('<script src="' . MINT_LOCATION . '" type="text/javascript"></script>');
		}
		echo('</head>');
	}
	
	/* E module: page headers */
	
	/* S module: div#top tag */
	
	public function vxTop($msgBanner = Vocabulary::site_banner, $keyword = '') {
		global $GOOGLE_AD_LEGAL;
		
		if ($this->User->usr_id == 0) {
			if ($_SESSION['babel_ua']['KHTML_DETECTED'] || $_SESSION['babel_ua']['OPERA_DETECTED'] || $_SESSION['babel_ua']['GECKO_DETECTED']) {
				echo('<div id="login" class="login_modern">');
			} else {
				if ($_SESSION['babel_ua']['MSIE_DETECTED'] && $_SESSION['babel_ua']['version'] == '7.0') {
					echo('<div id="login" class="login_modern">');
				} else {
					echo('<div id="login" class="login_legacy">');
				}
			}
			echo('<div class="inner"><img src="/img/login_caption.gif" /><br />');
			echo('<form action="/auth" method="get"><input type="text" name="usr" class="s" maxlength="200" id="boxLogin" /><input type="password" name="password" class="s" maxlength="32" /><br /><br /><input type="submit" value=" 登  录 " class="b" />&nbsp;&nbsp;<a href="#;" onclick="closeLogin();" class="g">取消</a>&nbsp;&nbsp;<a href="/signup.html" class="g">注册</a></form>');
			echo('</div></div>');
			echo('<div id="top" style="display: none;">');
			echo('<div id="top_left">' . $msgBanner . '&nbsp;&nbsp;');
			if ($this->User->usr_sw_shell) {
				echo('<form action="/locator.php" method="get" onsubmit="return V2EXShell();"><input type="search" name="go" class="top_go" id="boxGo" autosave="V2EX Go" results="20" onmouseover="this.focus();" /></form>');
			}
			/* Old login.
			echo('</div><div id="top_right"><a name="top"></a><a href="http://' . BABEL_DNS_NAME . '/signup.html" class="top">注册</a>&nbsp;|&nbsp;<a href="/passwd.vx" class="top">找回密码</a>&nbsp;|&nbsp;<a href="/new_features.html" class="top">新功能!</a>&nbsp;|&nbsp;<a href="#;" class="top" onclick="swLogin();">登录</a></div>');
			*/
			echo('</div>');
			echo('<div id="top_right"><a href="http://' . BABEL_DNS_NAME . '/signup.html" class="top">' . $this->lang->register() . '</a>&nbsp;|&nbsp;<a href="/passwd.vx" class="top">' . $this->lang->password_recovery() . '</a>&nbsp;|&nbsp;<a href="/login" class="top">' . $this->lang->login() . '</a></div>');
			echo('</div>');
		} else {
			$sql = "SELECT COUNT(tpc_id) FROM babel_topic WHERE tpc_uid = {$this->User->usr_id}";
			$rs = mysql_query($sql, $this->db);
			if ($this->tpc_count == 0) {
				$this->usr_share = 0;
			} else {
				$this->usr_share = (mysql_result($rs, 0, 0) / $this->tpc_count) * 100;
			}
			mysql_free_result($rs);
			echo('<div id="top" style="display: none;">');
			echo('<div id="top_left">' . $msgBanner . '&nbsp;&nbsp;');
			if ($this->User->usr_sw_shell) {
				echo('<form action="/locator.php" method="get" onsubmit="return V2EXShell();"><input type="search" name="go" class="top_go" id="boxGo" autosave="V2EX Go" results="20" onmouseover="this.focus();" /></form>');
			}
			echo('</div>');
			echo('<div id="top_right"><a href="/u/' . urlencode($this->User->usr_nick) . '" class="top">' . $this->User->usr_nick . '</a>&nbsp;|&nbsp;<a href="/user/modify.vx" target="_self" class="top">' . $this->lang->settings() . '</a>&nbsp;|&nbsp;<a href="/new_features.html" class="top">' . $this->lang->new_features() . '</a>&nbsp;|&nbsp;<a href="/logout.vx" class="top" target="_self">' . $this->lang->logout() . '</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="/expense/view.vx" class="top" target="_self">' . $this->lang->copper(intval($this->User->usr_money)) . '</a>&nbsp;&nbsp;|&nbsp;&nbsp;');
			printf("<a href=\"/topic/archive/user/{$this->User->usr_nick}\" class=\"top\"><small>%.3f%%</small></a>", $this->usr_share);
			echo('</div>');
			echo('</div>');
		}
		if ($this->User->usr_sw_shell == 1) {
			echo('<script type="text/javascript">setTimeout("focusGo();", 500);</script>');
		}
		
		/* nav menu start: */
		echo('<div id="top_banner">');
		echo('<div id="top_banner_logo"><a href="/"><img src="' . CDN_UI . 'img/top_metal_logo.png" border="0" alt="' . Vocabulary::site_name . '" /></a></div>');
		echo('</div>');
		echo('<div id="nav">');
		echo('<ul id="nav_menu">');
		echo('<li class="top"><a href="/" class="top">&nbsp;&nbsp;&nbsp;<strong>' . Vocabulary::site_name . '</strong>&nbsp;&nbsp;&nbsp;</a>');
		echo('<ul>');
		echo('<li><a href="/new_features.html" class="nav">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;关于 ' . Vocabulary::site_name . '</a></li>');
		echo('<li><a href="/" class="nav">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . Vocabulary::site_name . ' 首页</a></li>');
		echo('<li><div class="sep">&nbsp;</div></li>');
		echo('<li><a href="/topic/latest.html" class="nav">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;最新主题列表</a></li>');
		echo('<li><a href="/topic/answered/latest.html" class="nav">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;最新回复列表</a></li>');
		echo('<li><a href="/topic/fresh.html" class="nav">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;最新 virgin 主题</a></li>');	
		if ($this->User->vxIsLogin()) {
			echo('<li><div class="sep">&nbsp;</div></li>');
			echo('<li><a href="/online/view.vx" class="nav">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $this->online_count . ' 人在线</a></li>');
			if ($this->User->usr_id == 1) {
				echo('<li><a href="/status.vx" class="nav">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;系统状态</a></li>');
			}
		} else {
			echo('<li><div class="sep">&nbsp;</div></li>');
			echo('<li><a href="/login.vx" class="nav">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $this->lang->login() . '</a></li>');
			echo('<li><a href="/signup.html" class="nav">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $this->lang->register() . '</a></li>');
			echo('<li><a href="/passwd.vx" class="nav">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $this->lang->password_recovery() . '</a></li>');
		}
		if ($this->User->vxIsLogin()) {
			echo('<li><div class="sep">&nbsp;</div></li>');
			echo('<li><a href="/logout.vx" class="nav">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $this->lang->logout() . '</a></li>');
		}
		echo('</ul>');
		echo('</li>');
		if ($this->User->vxIsLogin()) {
			echo('<li class="top"><a href="/u/' . urlencode($this->User->usr_nick) . '" class="top">&nbsp;&nbsp;&nbsp;' . make_plaintext($this->User->usr_nick) . '&nbsp;&nbsp;&nbsp;</a>');
			echo('<ul>');
			echo('<li><a href="/u/' . urlencode($this->User->usr_nick) . '" class="nav">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;我的 ' . Vocabulary::site_name . ' 主页</a></li>');
			echo('<li><a href="/zen/' . urlencode($this->User->usr_nick) . '" class="nav">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ZEN</a></li>');
			echo('<li><a href="/topic/archive/user/' . urlencode($this->User->usr_nick) . '" class="nav">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;我创建的所有主题</a></li>');
			echo('<li><a href="/topic/favorite.vx" class="nav">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;我的收藏夹</a></li>');
			echo('<li><a href="/expense/view.vx" class="nav">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;消费记录</a></li>');
			if ($_SESSION['hits'] > 0) {
				echo('<li><a href="/session/stats.vx" class="nav">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;本次访问了 ' . $_SESSION['hits'] . ' 个页面</a></li>');
			}
			echo('<li><div class="sep">&nbsp;</div></li>');
			echo('<li><a href="/geo/' . $this->User->usr_geo . '" class="nav">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="' . CDN_UI . 'img/icons/silk/world.png" align="absmiddle" border="0" /> ' . $this->Geo->map['name'][$this->User->usr_geo] . '</a></li>');
			echo('<li><div class="sep">&nbsp;</div></li>');
			echo('<li><a href="/user/modify.vx" class="nav">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;上传头像</a></li>');
			echo('<li><a href="/user/modify.vx#settings" class="nav">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $this->lang->settings() . '</a></li>');
			echo('<li><a href="/user/move.vx" class="nav">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;修改我的所在地</a></li>');
			if ($_favs = $this->cs->get('user_favs_' . $this->User->usr_id)) {
				$_favs = unserialize($_favs);
				if (count($_favs) > 0) {
					echo('<li><div class="sep">&nbsp;</div></li>');
					foreach ($_favs as $Fav) {
						switch ($Fav->fav_type) {
							case 1:
								echo('<li><a href="/board/view/' . $Fav->fav_res . '.html" class="nav">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="' . CDN_UI . 'img/icons/silk/table_multiple.png" align="absmiddle" border="0" />&nbsp;' . make_plaintext($Fav->fav_title) . '</a></li>');
								break;
							case 2:
								echo('<li><a href="/channel/view/' . $Fav->fav_res . '.html" class="nav">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="' . CDN_UI . 'img/icons/silk/newspaper.png" align="absmiddle" border="0" />&nbsp;' . Channel::vxTrimKijijiTitle(make_plaintext($Fav->fav_title)) . '</a></li>');
								break;
						}
					}
				}
			}
			echo('</ul>');
			echo('</li>');
		}
		if ($nav = $this->cs->get('nav')) {
		} else {
			$sql = "SELECT nod_id, nod_name, nod_title FROM babel_node WHERE nod_level = 1 ORDER BY nod_weight DESC";
			$rs = mysql_query($sql);
			$nav = '';
			while ($Section = mysql_fetch_array($rs)) {
				$nav .= '<li class="top"><a href="/go/' . $Section['nod_name'] . '" class="top">&nbsp;&nbsp;&nbsp;' . make_plaintext($Section['nod_title']) . '&nbsp;&nbsp;&nbsp;</a>';
				$sql = 'SELECT nod_id, nod_name, nod_title, count(tpc_id) AS nod_topics FROM babel_node, babel_topic WHERE tpc_pid = nod_id AND nod_sid = ' . $Section['nod_id'] . ' GROUP BY nod_id ORDER BY nod_topics DESC LIMIT 8';
				$rs_boards = mysql_query($sql);
				$nav .= '<ul>';
				while ($Node = mysql_fetch_array($rs_boards)) {
					$nav .= '<li><a href="/go/' . $Node['nod_name'] . '" class="nav">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . make_plaintext($Node['nod_title']) . '</a></li>';
				}
				$nav .= '<li><div class="sep">&nbsp;</div></li>';
				$nav .= '<li><a href="/topic/new/' . $Section['nod_id'] . '.vx" class="nav">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;在' . make_plaintext($Section['nod_title']) . '创建新主题</a></li>';
				$nav .= '</ul></li>';
				$Section = null;
			}
			$this->cs->save($nav, 'nav');
			mysql_free_result($rs);
		}
		echo $nav;
		/*
		echo('<li class="top"><a href="#;" class="top">&nbsp;&nbsp;&nbsp;查看&nbsp;&nbsp;&nbsp;</a>');
		echo('<ul>');
		echo('<li><a href="/search.vx" class="nav">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="' . CDN_UI . 'img/icons/flag/cn.png" border="0" align="absmiddle" /> Simplified Chinese</a></li>');
		echo('<li><a href="/search.vx" class="nav">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="' . CDN_UI . 'img/icons/flag/us.png" border="0" align="absmiddle" /> American English</a></li>');
		echo('<li><a href="/search.vx" class="nav">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="' . CDN_UI . 'img/icons/flag/fr.png" border="0" align="absmiddle" /> French</a></li>');
		echo('<li><a href="/search.vx" class="nav">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="' . CDN_UI . 'img/icons/flag/jp.png" border="0" align="absmiddle" /> Japanese</a></li>');
		echo('<li><a href="/search.vx" class="nav">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="' . CDN_UI . 'img/icons/flag/tw.png" border="0" align="absmiddle" /> Traditional Chinese</a></li>');
		echo('<li><a href="/search.vx" class="nav">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="' . CDN_UI . 'img/icons/flag/de.png" border="0" align="absmiddle" /> Deutsch</a></li>');
		echo('<li><a href="/search.vx" class="nav">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="' . CDN_UI . 'img/icons/flag/kr.png" border="0" align="absmiddle" /> Korean</a></li>');
		echo('</li>');
		echo('</ul></li>');
		*/
		echo('<li class="top"><a href="#" class="top">&nbsp;&nbsp;&nbsp;工具&nbsp;&nbsp;&nbsp;</a>');
		echo('<ul>');
		echo('<li><a href="/search.vx" class="nav">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="' . CDN_UI . 'img/icons/silk/zoom.png" border="0" align="absmiddle" /> 高级搜索</a></li>');
		echo('<li><a href="/man.html" class="nav">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="' . CDN_UI . 'img/icons/silk/book_open.png" border="0" align="absmiddle" /> 参考文档搜索</a></li>');
		
		/*
		if (@BABEL_AT == 'topic_view') {
			echo('<li><div class="sep">&nbsp;</div></li>');
			echo('<li><a href="" class="nav">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;添加本主题到收藏夹</a></li>');
		}
		*/
		echo('</ul>');
		echo('</li>');
		
		/* Gmail: */
		/*
		$client = new Zend_Http_Client('https://mail.google.com/mail/feed/atom');
		$client->setAuth('', '');
		$response = $client->request();
		$f = new MagpieRSS($response->getBody(), 'UTF-8', 'UTF-8');
		echo('<li class="top">');
		echo('<a href="#;" class="top">');
		echo('&nbsp;&nbsp;<img src="/img/gmail.png" align="top" style="margin-bottom: -2px;" border="0" /> ' . $f->channel['fullcount'] . '&nbsp;&nbsp;');
		echo('</a>');
		echo('<ul style="width: 30em;">');
		foreach ($f->items as $e) {
			echo('<li style="width: 30em;"><a style="width: 30em;" href="' . $e['link'] . '" class="nav" target="_blank">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $e['author_name'] . '<span class="tip_gmail"> - ' . mb_substr($e['title'], 0, 30, 'UTF-8') . '</span></a>');
		}
		echo('</ul>');
		echo('</li>');
		*/
		/* Gmail. */
		/*
		echo('.. <a href="http://www.v2ex.com/remix/babel" class="nav"><small>dev</small></a> <a href="http://www.v2ex.com/man.html" class="nav"><small>ref</small></a> .. <a href="/home/style/remix.html" class="nav" title="home/remix"><small>R</small></a> <a href="/home/style/shuffle.html" class="nav" title="home/shuffle"><small>S</small></a> .. <a href="/session/stats.vx" class="nav" title="本次访问页面数">' . $_SESSION['hits'] . '</a> <a href="/online/view.vx" class="nav" title="当前在线总数">' . $this->online_count . '</a>');
		*/
		echo('</ul>');
		/*
		echo(' <img src="' . CDN_UI . 'img/icons/flag/us.png" align="absmiddle" alt="American English" />');
		echo(' <img src="' . CDN_UI . 'img/icons/flag/cn.png" align="absmiddle" alt="Simplified Chinese" />');
		echo(' <img src="' . CDN_UI . 'img/icons/flag/jp.png" align="absmiddle" alt="Japanese" />');
		echo(' <img src="' . CDN_UI . 'img/icons/flag/fr.png" align="absmiddle" alt="French" />');
		echo(' <img src="' . CDN_UI . 'img/icons/flag/tw.png" align="absmiddle" alt="Traditional Chinese" />');
		echo(' <img src="' . CDN_UI . 'img/icons/flag/de.png" align="absmiddle" alt="Deutsch" />');
		echo(' <img src="' . CDN_UI . 'img/icons/flag/kr.png" align="absmiddle" alt="Korean" />');
		*/
		echo('</div>');
		/* nav menu end. */
		/* We don't need to mimic OS X any more!
		echo('<div style="position: absolute; top: 0px; left: 0px;"><img src="/img/c_l.gif" /></div>');
		echo('<div style="position: absolute; top: 0px; right: 0px;"><img src="/img/c_r.gif" /></div>');
		*/
		/* No Google any more!
		echo('<div id="search">');
		include(BABEL_PREFIX . '/res/google_search.php');
		echo('</div>');
		*/
		echo('<div style="clear: both; height: 2px;">');
	}
	
	public function vxTopV1($msgBanner = Vocabulary::site_banner, $keyword = '') {
		global $GOOGLE_AD_LEGAL;
		if ($this->User->usr_id == 0) {
			echo('<div id="top"><div id="top_left">' . $msgBanner . '</div><div id="top_right"><a name="top"></a><a href="http://' . BABEL_DNS_NAME . '/signup.html" class="top">注册</a>&nbsp;|&nbsp;<a href="/passwd.vx" class="top">找回密码</a>&nbsp;|&nbsp;<a href="/new_features.html"><strong>新功能!</strong></a>&nbsp;|&nbsp;<a href="http://' . BABEL_DNS_NAME . '/login.vx" class="top">登录</a></div>');
		} else {
			$sql = "SELECT COUNT(tpc_id) FROM babel_topic WHERE tpc_uid = {$this->User->usr_id}";
			$rs = mysql_query($sql, $this->db);
			if ($this->tpc_count == 0) {
				$this->usr_share = 0;
			} else {
				$this->usr_share = (mysql_result($rs, 0, 0) / $this->tpc_count) * 100;
			}
			mysql_free_result($rs);
			echo('<div id="top"><div id="top_left">' . $msgBanner . '</div><div id="top_right"><a name="top"></a>欢迎，<small><a href="/u/' . urlencode($this->User->usr_nick) . '" class="top">' . $this->User->usr_nick . '</a></small>&nbsp;|&nbsp;<a href="/user/modify.vx" target="_self" class="top">修改信息</a>&nbsp;|&nbsp;<a href="/new_features.html"><strong>新功能!</strong></a>&nbsp;|&nbsp;<a href="/logout.vx" class="top" target="_self">登出</a><br /><br />你口袋里有' . $this->User->usr_money_a['str'] . '&nbsp;|&nbsp;<a href="/expense/view.vx" class="top" target="_self">消费记录</a><br /><br />');
			printf("你的主题数在社区所占比率 %.3f%%", $this->usr_share);
			echo('</div>');
		}
		echo('<div id="top_center">');
		echo('</div>');
		
		// echo('<div id="top_blimp"><img src="/img/blimp.png" border="0" /></div>');
		echo('</div>');
		echo('<div id="nav">');
		if ($nav = $this->cs->get('nav')) {
		} else {
			$sql = "SELECT nod_id, nod_name, nod_title from babel_node WHERE nod_level = 1 ORDER BY nod_weight DESC";
			$rs = mysql_query($sql);
			$nav = '';
			while ($Section = mysql_fetch_array($rs)) {
				$nav .= '<a href="/go/' . $Section['nod_name'] . '" class="nav">' . make_plaintext($Section['nod_title']) . '</a> ';
				$Section = null;
			}
			$this->cs->save($nav, 'nav');
			mysql_free_result($rs);
		}
		echo $nav;
		echo('.. <a href="http://www.v2ex.com/remix/babel" class="nav">开发者中心</a> <a href="http://www.v2ex.com/man.html" class="nav">参考文档藏经阁</a> .. <small><a href="/home/style/remix.html" class="nav">home/remix</a> <a href="/home/style/shuffle.html" class="nav">home/shuffle</a></small> .. <a href="/session/stats.vx" class="nav">' . $_SESSION['hits'] . '</a></div>');
		echo('<div id="search">');
		include(BABEL_PREFIX . '/res/google_search.php');
		echo('</div>');
	}
	
	/* E module: div#top tag */
	
	/* S module: div#bottom tag */
	
	public function vxBottom($msgCopyright = Vocabulary::site_copyright) {
		echo('<div id="bottom"><small>' . $msgCopyright . '</small><br /><a href="/community_guidelines.vx">' . Vocabulary::term_community_guidelines . '</a> | <a href="http://io.v2ex.com/v2ex-doc/" target="_blank">帮助</a> | <a href="http://io.v2ex.com/project-babel/project-babel-downloads.html" target="_blank">下载本站程序</a><br /><a href="/rules.vx">' . Vocabulary::term_rules . '</a> | <a href="/terms.vx">' . Vocabulary::term_terms . '</a> | <a href="/privacy.vx">' . Vocabulary::term_privacy . '</a> | <a href="/policies.vx">' . Vocabulary::term_policies . '</a><br /><a href="http://labs.v2ex.com/" target="_blank" class="var"><img src="/img/beta_jtp.gif" alt="Beta * V2EX Labs" align="absmiddle" border="0" /></a><br /></span></div>');
	}
	
	/* E module: div#bottom tag */
	
	/* S module: Menu block */
	
	public function vxMenu($options = '') {
		if ($options != '') {
			$_module_friends = $options['modules']['friends'];
			$_module_links = $options['modules']['links'];
			$_module_new_members = $options['modules']['new_members'];
			$_module_stats = $options['modules']['stats'];
			$_module_fav = $options['modules']['fav'];
			$_module_extra_links = $options['modules']['extra_links'];
			$_module_logins = $options['modules']['logins'];
			$_module_online = $options['modules']['online'];
		} else {
			$_module_friends = true;
			$_module_links = true;
			$_module_new_members = true;
			$_module_stats = true;
			$_module_fav = true;
			$_module_extra_links = true;
			$_module_logins = true;
			$_module_online = true;
		}
		
		global $GOOGLE_AD_LEGAL;
		echo('<div id="menu" align="center">');
		if ($this->User->vxIsLogin()) {
			echo('<div class="menu_inner" align="left"><ul class="menu">');
		
			echo('<li><img src="' . CDN_UI . 'img/icons/silk/email.png" align="absmiddle" />&nbsp;<a href="javascript:openMessage();" class="menu">我的消息');
			if ($this->p_msg_count > 0) {
				echo(' <small class="fade">(' . $this->p_msg_count . ')</small>');
			}
			echo('</a></li>');
			echo('<li><img src="' . CDN_UI . 'img/icons/silk/comments.png" align="absmiddle" />&nbsp;<a href="/topic/archive/user/' . urlencode($this->User->usr_nick) . '" class="menu">我创建的所有主题</a></li>');
			// echo('<li><img src="' . CDN_UI . 'img/icons/silk/calendar.png" align="absmiddle" />&nbsp;<a href="/m/' . urlencode($this->User->usr_nick) . '" class="menu">我的印迹</a></li>');
			echo('<li><img src="' . CDN_UI . 'img/icons/silk/clock.png" align="absmiddle">&nbsp;<a href="/zen/' . urlencode($this->User->usr_nick) . '" class="menu">ZEN</a> <span class="tip_i"><small>alpha</small></span></li>');
			echo('<li><img src="' . CDN_UI . 'img/icons/silk/house.png" align="absmiddle" />&nbsp;<a href="/u/' . urlencode($this->User->usr_nick) . '" class="menu">我的 ' . Vocabulary::site_name . ' 主页</a></li>');
			echo('<li><img src="' . CDN_UI . 'img/icons/silk/coins_delete.png" align="absmiddle" />&nbsp;<a href="/expense/view.vx" class="menu">消费记录</a></li>');
			if ($this->User->usr_sw_top_wealth) {
				echo('<li><img src="' . CDN_UI . 'img/icons/silk/coins_add.png" align="absmiddle" />&nbsp;<a href="/expense/view.vx" class="menu">社区财富排行</a></li>');
			}
			echo('<li><img src="' . CDN_UI . 'img/icons/silk/world.png" align="absmiddle" />&nbsp;<a href="/geo/' . $this->User->usr_geo . '" class="menu">' . $this->Geo->map['name'][$this->User->usr_geo] . '</a> <span class="tip_i"><small>portal</small></span></li>');
			echo('<li>');
			_v_hr();
			echo('<img src="' . CDN_UI . 'img/icons/silk/key_go.png" align="absmiddle" /> <a href="/logout" class="menu">' . $this->lang->logout() . '</a></li>');
			echo('</ul></div>');
		} else {
			echo('<div class="menu_inner" align="left"><ul class="menu">');
			echo('<li><img src="' . CDN_UI . 'img/icons/silk/key.png" align="absmiddle" /> <a href="/login" class="menu">' . $this->lang->login() . '</a></li>');
			echo('<li><img src="' . CDN_UI . 'img/icons/silk/emoticon_smile.png" align="absmiddle" /> <a href="/signup.html" class="menu">' . $this->lang->register() . '</a></li>');
			echo('</ul>');
			echo('</div>');
		}
		if ($this->User->vxIsLogin() && $_module_fav) {
			$fimg = '<img src="' . CDN_UI . 'img/icons/silk/star.png" align="absmiddle" />';
			$sql = "SELECT COUNT(*) FROM babel_favorite WHERE fav_uid = {$this->User->usr_id}";
			$rs = mysql_query($sql, $this->db);
			$my_fav_total = mysql_result($rs, 0, 0);
			mysql_free_result($rs);
			
			
			if ($_favs = $this->cs->get('user_favs_' . $this->User->usr_id)) {
				$_favs = unserialize($_favs);
				if (count($_favs) > 0) {
					echo('<div class="menu_fav" align="left">');
					echo($fimg);
					echo(' <a href="/topic/favorite.vx" class="menu">我的收藏夹</a><table width="99%" cellpadding="0" cellspacing="0" border="0" class="fav">');
					foreach ($_favs as $Fav) {
						switch ($Fav->fav_type) {
							case 1:
								echo('<tr><td><a href="/board/view/' . $Fav->fav_res . '.html"><img src="' . CDN_UI . 'img/icons/silk/table_multiple.png" align="absmiddle" border="0" /></a>&nbsp;<a href="/board/view/' . $Fav->fav_res . '.html">' . make_plaintext($Fav->fav_title) . '</a></td></tr>');
								break;
							case 2:
								echo('<tr><td><a href="/channel/view/' . $Fav->fav_res . '.html"><img src="' . CDN_UI . 'img/icons/silk/newspaper.png" align="absmiddle" border="0" /></a>&nbsp;<a href="/channel/view/' . $Fav->fav_res . '.html">' . Channel::vxTrimKijijiTitle(make_plaintext($Fav->fav_title)) . '</a></td></tr>');
								break;
						}
						$Fav = null;
					}
					echo('</table></div>');
				} else {
					echo('<div class="menu_fav" align="left">');
					echo($fimg);
					echo(' <a href="/topic/favorite.vx" class="menu">我的收藏夹</a></div>');
				}
			} else {
				$sql = "SELECT fav_title, fav_res, fav_type FROM babel_favorite WHERE fav_uid = {$this->User->usr_id} AND fav_type IN (1,2) ORDER BY fav_created DESC";
				$rs = mysql_query($sql, $this->db);
				$my_fav_nodes = mysql_num_rows($rs);
				$_favs = array();
				if ($my_fav_nodes > 0) {
					echo('<div class="menu_fav" align="left">');
					echo($fimg);
					echo(' <a href="/topic/favorite.vx" class="menu">我的收藏夹</a><table width="99%" cellpadding="0" cellspacing="0" border="0" class="fav">');
					while ($Fav = mysql_fetch_object($rs)) {
						switch ($Fav->fav_type) {
							case 1:
								echo('<tr><td><a href="/board/view/' . $Fav->fav_res . '.html"><img src="' . CDN_UI . 'img/icons/silk/table_multiple.png" align="absmiddle" border="0" /></a>&nbsp;<a href="/board/view/' . $Fav->fav_res . '.html">' . make_plaintext($Fav->fav_title) . '</a></td></tr>');
								break;
							case 2:
								echo('<tr><td><a href="/channel/view/' . $Fav->fav_res . '.html"><img src="' . CDN_UI . 'img/icons/silk/newspaper.png" align="absmiddle" border="0" /></a>&nbsp;<a href="/channel/view/' . $Fav->fav_res . '.html">' . Channel::vxTrimKijijiTitle(make_plaintext($Fav->fav_title)) . '</a></td></tr>');
								break;
						}
						$_favs[] = $Fav;
						$Fav = null;
					}
					echo('</table></div>');
				} else {
					echo('<div class="menu_fav" align="left">');
					echo($fimg);
					echo(' <a href="/topic/favorite.vx" class="menu">我的收藏夹</a></div>');
				}
				$this->cs->save(serialize($_favs), 'user_favs_' . $this->User->usr_id);
				mysql_free_result($rs);
			}
		}
		
		if ($this->User->vxIsLogin() && $_module_friends) {
			if (count($this->User->usr_friends) > 0) {
				echo('<div class="menu_inner" align="left">');
				echo('<img src="' . CDN_UI . 'img/icons/silk/heart.png" align="absmiddle" />');
				echo('&nbsp;我的朋友们');
				_v_hr();
				echo('<table cellpadding="0" cellspacing="0" border="0">');
				echo('<tr><td>');
				$i = 0;
				foreach ($this->User->usr_friends as $_friend) {
					$i++;
					$img_p = $_friend['usr_portrait'] ? CDN_P . 'p/' . $_friend['usr_portrait'] . '_n.jpg' : CDN_P . 'p_' . $_friend['usr_gender'] . '_n.gif';
					echo ('<a href="/u/' . urlencode($_friend['usr_nick']) . '" class="var" title="' . $_friend['usr_nick'] . '"><img src="' . $img_p . '" align="absmiddle" class="mp" /></a>&nbsp;');
					if (($i % 5) == 0) {
						echo('<br />');
					}
				}
				echo('</td></tr>');
				echo('</table>');
				_v_hr();
				echo('<img src="' . CDN_UI . 'img/icons/silk/heart_add.png" align="absmiddle" />&nbsp;<a href="/who/connect/' . $this->User->usr_nick . '">谁把我加为好友？</a>');
				echo('</div>');
			}
		}
		
		echo('<div class="menu_inner" align="left"><ul class="menu">');
		echo('<li><img src="' . CDN_UI . 'img/icons/silk/zoom.png" align="absmiddle" />&nbsp;<a href="/search.vx" class="menu">搜索</a></li>');
		echo('<li><img src="' . CDN_UI . 'img/icons/silk/feed.png" align="absmiddle" />&nbsp;<a href="' . BABEL_FEED_URL . '" class="menu" target="_blank">RSS 种子输出</a></li>');
		echo('<li><img src="' . CDN_UI . 'img/icons/silk/weather_sun.png" align="absmiddle" />&nbsp;<a href="/topic/fresh.html" class="menu">最新 virgin 主题</a></li>');
		echo('<li><img src="' . CDN_UI . 'img/icons/silk/award_star_gold_1.png" align="absmiddle" />&nbsp;<a href="/topic/top.html" class="menu">' . Vocabulary::term_toptopic . '</a></li>');
		if ($_module_new_members) {
			echo('<li><img src="' . CDN_UI . 'img/icons/silk/user_add.png" align="absmiddle" />&nbsp;最新注册会员<ul class="items">');
			$sql = 'SELECT usr_id, usr_nick, usr_gender, usr_portrait, usr_created FROM babel_user ORDER BY usr_created DESC LIMIT 5';
			$rs = mysql_query($sql, $this->db);
			$c = '';
			while ($User = mysql_fetch_object($rs)) {
				$img_p = $User->usr_portrait ? '/img/p/' . $User->usr_portrait . '_n.jpg' : CDN_IMG . 'p_' . $User->usr_gender . '_n.gif';
				$c = $c . '<li><a href="/u/' . urlencode($User->usr_nick) . '"><img src="' . $img_p . '" align="absmiddle" border="0" class="portrait" /> ' . $User->usr_nick . '</a>&nbsp;<small class="fade">' . make_desc_time($User->usr_created) . '</small></li>';
			}
			mysql_free_result($rs);
			echo $c;
			echo('</ul></li>');
		}
		if ($_module_online) {
			echo('<li><img src="' . CDN_UI . 'img/icons/silk/user_comment.png" align="absmiddle" />&nbsp;<a href="/online/view.vx" class="menu">在线总数 <small>' . $this->online_count . '</small></a><ul class="items">');
			echo('<li>游客 <small>' . $this->online_count_anon . '</small></li>');
			echo('<li>注册会员 <small>' . $this->online_count_reg . '</small></li></ul></li>');
		}
		/* if ($_module_links) {
			echo('<li><img src="' . CDN_IMG . 'pico_web.gif" align="absmiddle" />&nbsp;友情链接<ul class="items">');
			$x = simplexml_load_file(BABEL_PREFIX . '/res/links.xml');
			foreach ($x->xpath('//link') as $link) {
				echo '<li><a href="' . $link->url . '" target="_blank" class="menu">' . $link->name . '</a></li>';
			}
			echo('</ul></li>');
		} */
		if ($_module_logins) {
			echo('<li><img src="' . CDN_UI . 'img/icons/silk/user_go.png" align="absmiddle" />&nbsp;<a href="/user/logins.html" class="menu">' . Vocabulary::term_userlogins . '</a></li>');
		}
		if ($_module_stats) {
			echo('<li><img src="' . CDN_UI . 'img/icons/silk/user_gray.png" align="absmiddle" />&nbsp;<a href="/who/join.html" class="menu">注册会员总数</a> <a href="/who/join.html" class="t"><small>' . $this->usr_count . '</small></a><ul class="items">');
			echo('<li>讨论 <small>' . ($this->tpc_count + $this->pst_count) . '</small></li>');
			echo('<li>收藏 <small>' . $this->fav_count . '</small></li>');
			echo('<li>据点 <small>' . $this->svp_count . '</small></li>');
			echo('</ul></li>');
		}
		if ($_module_extra_links) {
			echo('<li><img src="' . CDN_UI . 'img/icons/silk/cog_go.png" align="absmiddle" />&nbsp;<a href="/remix/babel" class="menu" target="_self"><small>Developer Zone</small></a></li>');
			echo('<li><img src="' . CDN_UI . 'img/icons/silk/cog_go.png" align="absmiddle" />&nbsp;<small><a href="http://technorati.com/claim/5qwbf37cs2" class="menu" rel="me">Technorati Profile</a></small></li>');
			if ($this->User->usr_id == 1) {
				echo('<li><img src="' . CDN_UI . 'img/icons/silk/cog_go.png" align="absmiddle" />&nbsp;<a href="/status.vx" class="menu" target="_self"><small>System Status</small></a></li>');
			}
		}
		
		echo('</ul>');
		_v_hr();
		echo('<a href="http://www.spreadfirefox.com/?q=affiliates&amp;id=197201&amp;t=218"><img border="0" alt="Firefox 2" title="Firefox 2" src="' . CDN_UI . 'img/ff2o80x15.gif" /></a> ');
		echo(' <a href="http://www.pageflakes.com/?source=d736779a-49d4-46a7-a918-a70ad0b8cbd8"><img border="0" alt="Pageflakes" title="Pageflakes" src="' . CDN_UI . 'img/80x15/pageflakes.gif" /></a>');
		_v_hr();
		echo('<span class="tip_i"><small>Hosted by <a href="' . HOST_LINK . '" target="_blank" style="color: ' . rand_color() . '" class="var">' . HOST_COMPANY . '</a></small></span>');
		echo('</div>');
		
		/*
		echo('<script language="javascript" src="/js/awstats_misc_tracker.js" type="text/javascript"></script>
<noscript><img src="/js/awstats_misc_tracker.js?nojs=y" height="0" width="0" border="0" style="display: none" alt="Made By Livid" /></noscript>');
		*/
		echo('</div>');
	}
	
	/* E module: Menu block */
	
	/* S module: Main Container block logic */
	
	public function vxContainer($module, $options = array()) {
		$_menu_options = array();
		$_menu_options['modules'] = array();
		$_menu_options['modules']['friends'] = true;
		$_menu_options['modules']['links'] = true;
		$_menu_options['modules']['extra_links'] = true;
		$_menu_options['modules']['new_members'] = true;
		$_menu_options['modules']['stats'] = true;
		$_menu_options['modules']['fav'] = true;
		$_menu_options['modules']['logins'] = true;
		$_menu_options['modules']['friends'] = false;
		$_menu_options['modules']['online'] = false;
		echo('<div id="wrap">');
		switch ($module) {
			default:
			case 'home':
				$this->vxSidebar($show = false);
				$this->vxMenu();
				$this->vxHome($options);
				break;
			
			case 'hot':
				$this->vxSidebar();
				$this->vxMenu();
				$this->vxHot();
				break;
				
			case 'new_features':
				$this->vxSidebar();
				$this->vxMenu();
				$this->vxNewFeatures();
				break;
			
			case 'timtowtdi':
				$_menu_options['modules']['friends'] = false;
				$_menu_options['modules']['links'] = false;
				$_menu_options['modules']['new_members'] = false;
				$_menu_options['modules']['stats'] = false;
				$_menu_options['modules']['logins'] = false;
				$this->vxSidebar();
				$this->vxMenu($_menu_options);
				$this->vxTIMTOWTDI();
				break;
			
			case 'topic_latest':
				$_menu_options['modules']['links'] = false;
				$_menu_options['modules']['new_members'] = false;
				$this->vxSidebar();
				$this->vxMenu($_menu_options);
				$this->vxTopicLatest();
				break;
			
			case 'topic_answered_latest':
				$_menu_options['modules']['links'] = false;
				$_menu_options['modules']['new_members'] = false;
				$this->vxSidebar();
				$this->vxMenu($_menu_options);
				$this->vxTopicAnsweredLatest();
				break;
			
			case 'fav_latest':
				$_menu_options['modules']['links'] = false;
				$_menu_options['modules']['new_members'] = false;
				$this->vxSidebar();
				$this->vxMenu($_menu_options);
				$this->vxFavLatest();
				break;
			
			case 'user_logins':
				$_menu_options['modules']['links'] = false;
				$_menu_options['modules']['new_members'] = false;
				$_menu_options['modules']['extra_links'] = false;
				$_menu_options['modules']['logins'] = false;
				$this->vxSidebar();
				$this->vxMenu($_menu_options);
				$this->vxUserLogins();
				break;
			
			case 'session_stats':
				$_menu_options['modules']['friends'] = false;
				$_menu_options['modules']['links'] = false;
				$_menu_options['modules']['new_members'] = false;
				$_menu_options['modules']['stats'] = false;
				$this->vxSidebar();
				$this->vxMenu($_menu_options);
				$this->vxSessionStats();
				break;
			
			case 'search':
				$_menu_options['modules']['friends'] = false;
				$_menu_options['modules']['links'] = false;
				$this->vxSidebar();
				$this->vxMenu($_menu_options);
				$this->vxSearchSubstance();
				break;
				
			case 'denied':
				$_menu_options['modules']['friends'] = false;
				$_menu_options['modules']['links'] = false;
				$_menu_options['modules']['new_members'] = false;
				$_menu_options['modules']['stats'] = false;
				$this->vxSidebar();
				$this->vxMenu($_menu_options);
				$this->vxDenied();
				break;
				
			case 'topic_erase_denied':
				$_menu_options['modules']['friends'] = false;
				$_menu_options['modules']['links'] = false;
				$_menu_options['modules']['new_members'] = false;
				$_menu_options['modules']['stats'] = false;
				$this->vxSidebar();
				$this->vxMenu($_menu_options);
				$this->vxTopicEraseDenied($options);
				break;
				
			case 'board_view_denied':
				$_menu_options['modules']['friends'] = false;
				$_menu_options['modules']['links'] = false;
				$_menu_options['modules']['new_members'] = false;
				$_menu_options['modules']['stats'] = false;
				$_menu_options['modules']['logins'] = false;
				$_menu_options['modules']['online'] = false;
				$this->vxSidebar();
				$this->vxMenu($_menu_options);
				$this->vxBoardViewDenied($options);
				break;

			case 'login':
				$_menu_options['modules']['friends'] = false;
				$_menu_options['modules']['links'] = false;
				$_menu_options['modules']['new_members'] = false;
				$_menu_options['modules']['stats'] = false;
				$this->vxSidebar();
				$this->vxMenu($_menu_options);
				$this->vxLogin($options);
				break;
				
			case 'logout':
				$_menu_options['modules']['friends'] = false;
				$_menu_options['modules']['links'] = false;
				$_menu_options['modules']['new_members'] = false;
				$_menu_options['modules']['stats'] = false;
				$_menu_options['modules']['fav'] = false;
				$_menu_options['modules']['logins'] = false;
				$_menu_options['modules']['online'] = false;
				$this->vxSidebar();
				$this->vxMenu($_menu_options);
				$this->vxLogout();
				break;
				
			case 'passwd':
				$_menu_options['modules']['friends'] = false;
				$_menu_options['modules']['links'] = false;
				$_menu_options['modules']['new_members'] = false;
				$_menu_options['modules']['stats'] = false;
				$_menu_options['modules']['fav'] = false;
				$_menu_options['modules']['logins'] = false;
				$_menu_options['modules']['online'] = false;
				$this->vxSidebar();
				$this->vxMenu($_menu_options);
				$this->vxPasswd($options);
				break;
			
			case 'status':
				$_menu_options['modules']['friends'] = false;
				$_menu_options['modules']['links'] = false;
				$_menu_options['modules']['new_members'] = false;
				$_menu_options['modules']['stats'] = false;
				$this->vxSidebar();
				$this->vxMenu($_menu_options);
				$this->vxStatus();
				break;
			
			case 'jobs_kijiji':
				$_menu_options['modules']['friends'] = false;
				$_menu_options['modules']['links'] = false;
				$_menu_options['modules']['new_members'] = false;
				$_menu_options['modules']['stats'] = false;
				$this->vxSidebar();
				$this->vxMenu($_menu_options);
				$this->vxJobsKijiji();
				break;
			
			case 'community_guidelines':
				$_menu_options['modules']['friends'] = false;
				$_menu_options['modules']['links'] = false;
				$_menu_options['modules']['new_members'] = false;
				$_menu_options['modules']['stats'] = false;
				$this->vxSidebar();
				$this->vxMenu($_menu_options);
				$this->vxCommunityGuidelines();
				break;
			
			case 'partners':
				$_menu_options['modules']['friends'] = false;
				$_menu_options['modules']['links'] = false;
				$_menu_options['modules']['new_members'] = false;
				$_menu_options['modules']['stats'] = false;
				$this->vxSidebar();
				$this->vxMenu($_menu_options);
				$this->vxPartners();
				break;
			
			case 'rules':
				$_menu_options['modules']['friends'] = false;
				$_menu_options['modules']['links'] = false;
				$_menu_options['modules']['new_members'] = false;
				$_menu_options['modules']['stats'] = false;
				$this->vxSidebar();
				$this->vxMenu($_menu_options);
				$this->vxRules();
				break;
			
			case 'terms':
				$_menu_options['modules']['friends'] = false;
				$_menu_options['modules']['links'] = false;
				$_menu_options['modules']['new_members'] = false;
				$_menu_options['modules']['stats'] = false;
				$this->vxSidebar();
				$this->vxMenu($_menu_options);
				$this->vxTerms();
				break;

			case 'privacy':
				$_menu_options['modules']['friends'] = false;
				$_menu_options['modules']['links'] = false;
				$_menu_options['modules']['new_members'] = false;
				$_menu_options['modules']['stats'] = false;
				$this->vxSidebar();
				$this->vxMenu($_menu_options);
				$this->vxPrivacy();
				break;

			case 'policies':
				$_menu_options['modules']['friends'] = false;
				$_menu_options['modules']['links'] = false;
				$_menu_options['modules']['new_members'] = false;
				$_menu_options['modules']['stats'] = false;
				$this->vxSidebar();
				$this->vxMenu($_menu_options);
				$this->vxPolicies();
				break;
				
			case 'out_of_money':
				$_menu_options['modules']['friends'] = false;
				$_menu_options['modules']['links'] = false;
				$_menu_options['modules']['new_members'] = false;
				$_menu_options['modules']['stats'] = false;
				$this->vxSidebar();
				$this->vxMenu($_menu_options);
				$this->vxSorry('money');
				break;
			
			case 'geo_home':
				$_menu_options['modules']['friends'] = false;
				$_menu_options['modules']['links'] = false;
				$_menu_options['modules']['new_members'] = false;
				$_menu_options['modules']['stats'] = false;
				$this->vxSidebar();
				$this->vxMenu($_menu_options);
				$this->vxGeoHome($options);
				break;
			
			case 'signup':
				$_menu_options['modules']['friends'] = false;
				$_menu_options['modules']['links'] = false;
				$_menu_options['modules']['new_members'] = false;
				$_menu_options['modules']['stats'] = false;
				$this->vxSidebar();
				$this->vxMenu($_menu_options);
				$this->vxSignup();
				break;

			case 'user_home':
				$_menu_options['modules']['new_members'] = false;
				$_menu_options['modules']['stats'] = false;
				$this->vxSidebar();
				$this->vxMenu();
				$this->vxUserHome($options);
				break;

			case 'user_create':
				$_menu_options['modules']['friends'] = false;
				$_menu_options['modules']['links'] = false;
				$_menu_options['modules']['new_members'] = false;
				$_menu_options['modules']['stats'] = false;
				$this->vxSidebar();
				$this->vxMenu($_menu_options);
				$this->vxUserCreate($options);
				break;
				
			case 'user_modify':
				$_menu_options['modules']['friends'] = false;
				$_menu_options['modules']['links'] = false;
				$_menu_options['modules']['new_members'] = false;
				$_menu_options['modules']['stats'] = false;
				$this->vxSidebar();
				$this->vxMenu($_menu_options);
				$this->vxUserModify();
				break;
				
			case 'user_update':
				$_menu_options['modules']['friends'] = false;
				$_menu_options['modules']['links'] = false;
				$_menu_options['modules']['new_members'] = false;
				$_menu_options['modules']['stats'] = false;
				$this->vxSidebar();
				$this->vxMenu($_menu_options);
				$this->vxUserUpdate($options);
				break;
			
			case 'user_move':
				$_menu_options['modules']['friends'] = false;
				$_menu_options['modules']['links'] = false;
				$_menu_options['modules']['new_members'] = false;
				$_menu_options['modules']['stats'] = false;
				$_menu_options['modules']['fav'] = false;
				$_menu_options['modules']['logins'] = false;
				$_menu_options['modules']['online'] = false;
				$this->vxSidebar();
				$this->vxMenu($_menu_options);
				$this->vxUserMove();
				break;
			
			case 'user_topics':
				$_menu_options['modules']['friends'] = false;
				$_menu_options['modules']['links'] = false;
				$_menu_options['modules']['new_members'] = false;
				$_menu_options['modules']['stats'] = false;
				$this->vxSidebar();
				$this->vxMenu($_menu_options);
				$this->vxUserTopics($options);
				break;
				
			case 'topic_top':
				$_menu_options['modules']['friends'] = false;
				$_menu_options['modules']['links'] = false;
				$_menu_options['modules']['new_members'] = false;
				$_menu_options['modules']['stats'] = false;
				$this->vxSidebar();
				$this->vxMenu($_menu_options);
				$this->vxTopicTop();
				break;

			case 'topic_fresh':
				$_menu_options['modules']['friends'] = false;
				$_menu_options['modules']['links'] = false;
				$_menu_options['modules']['new_members'] = false;
				$_menu_options['modules']['stats'] = false;
				$this->vxSidebar();
				$this->vxMenu($_menu_options);
				$this->vxTopicFresh();
				break;
				
			case 'topic_favorite':
				$_menu_options['modules']['friends'] = false;
				$_menu_options['modules']['links'] = false;
				$_menu_options['modules']['new_members'] = false;
				$this->vxSidebar();
				$this->vxMenu($_menu_options);
				$this->vxTopicFavorite();
				break;
				
			case 'channel_view':
				$_menu_options['modules']['friends'] = false;
				$_menu_options['modules']['links'] = false;
				$_menu_options['modules']['new_members'] = false;
				$this->vxSidebar();
				$this->vxMenu($_menu_options);
				$this->vxChannelView($options);
				break;

			case 'board_view':
				$_menu_options['modules']['friends'] = false;
				$_menu_options['modules']['links'] = false;
				$_menu_options['modules']['new_members'] = false;
				$this->vxSidebar();
				$this->vxMenu($_menu_options);
				$this->vxBoardView($options['board_id']);
				break;
				
			case 'who_fav_node':
				$_menu_options['modules']['friends'] = false;
				$_menu_options['modules']['links'] = false;
				$_menu_options['modules']['new_members'] = false;
				$this->vxSidebar();
				$this->vxMenu($_menu_options);
				$this->vxWhoFavNode($options['node_id'], $options['node_level']);
				break;
			
			case 'who_fav_topic':
				$_menu_options['modules']['friends'] = false;
				$_menu_options['modules']['links'] = false;
				$_menu_options['modules']['new_members'] = false;
				$this->vxSidebar();
				$this->vxMenu($_menu_options);
				$this->vxWhoFavTopic($options);
				break;
				
			case 'who_settle_geo':
				$_menu_options['modules']['friends'] = false;
				$_menu_options['modules']['links'] = false;
				$_menu_options['modules']['new_members'] = false;
				$this->vxSidebar();
				$this->vxMenu($_menu_options);
				$this->vxWhoSettleGeo($options);
				break;
			
			case 'who_connect_user':
				$_menu_options['modules']['friends'] = false;
				$_menu_options['modules']['links'] = false;
				$_menu_options['modules']['new_members'] = false;
				$_menu_options['modules']['stats'] = false;
				$_menu_options['modules']['fav'] = false;
				$_menu_options['modules']['logins'] = false;
				$_menu_options['modules']['online'] = false;
				$this->vxSidebar();
				$this->vxMenu($_menu_options);
				$this->vxWhoConnectUser($options);
				break;
			
			case 'topic_new':
				$_menu_options['modules']['friends'] = false;
				$_menu_options['modules']['links'] = false;
				$_menu_options['modules']['new_members'] = false;
				$_menu_options['modules']['stats'] = false;
				$this->vxSidebar();
				$this->vxMenu($_menu_options);
				$this->vxTopicNew($options);
				break;
				
			case 'topic_create':
				$_menu_options['modules']['friends'] = false;
				$_menu_options['modules']['links'] = false;
				$_menu_options['modules']['new_members'] = false;
				$_menu_options['modules']['stats'] = false;
				$this->vxSidebar();
				$this->vxMenu($_menu_options);
				$this->vxTopicCreate($options);
				break;
			
			case 'topic_modify':
				$_menu_options['modules']['friends'] = false;
				$_menu_options['modules']['links'] = false;
				$_menu_options['modules']['new_members'] = false;
				$_menu_options['modules']['stats'] = false;
				$this->vxSidebar();
				$this->vxMenu($_menu_options);
				$this->vxTopicModify($options);
				break;
				
			case 'topic_update':
				$_menu_options['modules']['friends'] = false;
				$_menu_options['modules']['links'] = false;
				$_menu_options['modules']['new_members'] = false;
				$_menu_options['modules']['stats'] = false;
				$this->vxSidebar();
				$this->vxMenu($_menu_options);
				$this->vxTopicUpdate($options);
				break;
				
			case 'post_create':
				$_menu_options['modules']['friends'] = false;
				$_menu_options['modules']['links'] = false;
				$_menu_options['modules']['new_members'] = false;
				$_menu_options['modules']['stats'] = false;
				$this->vxSidebar();
				$this->vxMenu($_menu_options);
				$this->vxPostCreate($options);
				break;
			
			case 'post_modify':
				$_menu_options['modules']['friends'] = false;
				$_menu_options['modules']['links'] = false;
				$_menu_options['modules']['new_members'] = false;
				$_menu_options['modules']['stats'] = false;
				$this->vxSidebar();
				$this->vxMenu($_menu_options);
				$this->vxPostModify($options);
				break;
				
			case 'post_update':
				$_menu_options['modules']['friends'] = false;
				$_menu_options['modules']['links'] = false;
				$_menu_options['modules']['new_members'] = false;
				$_menu_options['modules']['stats'] = false;
				$this->vxSidebar();
				$this->vxMenu($_menu_options);
				$this->vxPostUpdate($options);
				break;

			case 'topic_view':
				$_menu_options['modules']['friends'] = false;
				$_menu_options['modules']['links'] = false;
				$_menu_options['modules']['new_members'] = false;
				$this->vxSidebar();
				$this->vxMenu($_menu_options);
				$this->vxTopicView($options['topic_id']);
				break;
			
			case 'topic_move':
				$_menu_options['modules']['friends'] = false;
				$_menu_options['modules']['links'] = false;
				$_menu_options['modules']['new_members'] = false;
				$this->vxSidebar();
				$this->vxMenu($_menu_options);
				$this->vxTopicMove($options['topic_id']);
				break;
			
			case 'topic_archive_user':
				$_menu_options['modules']['friends'] = false;
				$_menu_options['modules']['links'] = false;
				$_menu_options['modules']['new_members'] = false;
				$_menu_options['modules']['stats'] = false;
				$this->vxSidebar();
				$this->vxMenu($_menu_options);
				$this->vxTopicArchiveUser($options);
				break;
				
			case 'section_view':
				$_menu_options['modules']['friends'] = false;
				$_menu_options['modules']['links'] = false;
				$_menu_options['modules']['new_members'] = false;
				$this->vxSidebar();
				$this->vxMenu($_menu_options);
				$this->vxSectionView($options['section_id']);
				break;
				
			case 'expense_view':
				$_menu_options['modules']['friends'] = false;
				$_menu_options['modules']['links'] = false;
				$_menu_options['modules']['new_members'] = false;
				$_menu_options['modules']['stats'] = false;
				$this->vxSidebar();
				$this->vxMenu($_menu_options);
				$this->vxExpenseView();
				break;
				
			case 'online_view':
				$_menu_options['modules']['friends'] = false;
				$_menu_options['modules']['links'] = false;
				$_menu_options['modules']['new_members'] = false;
				$_menu_options['modules']['stats'] = false;
				$this->vxSidebar();
				$this->vxMenu($_menu_options);
				$this->vxOnlineView();
				break;
				
			case 'who_join':
				$this->vxSidebar();
				$this->vxMenu($_menu_options);
				$this->vxWhoJoin();
				break;
			
			case 'mobile':
				$_menu_options['modules']['friends'] = false;
				$_menu_options['modules']['links'] = false;
				$_menu_options['modules']['new_members'] = false;
				$_menu_options['modules']['stats'] = false;
				$this->vxSidebar();
				$this->vxMenu($_menu_options);
				$this->vxMobile();
				break;
				
			case 'man':
				$_menu_options['modules']['friends'] = false;
				$_menu_options['modules']['links'] = false;
				$_menu_options['modules']['new_members'] = false;
				$_menu_options['modules']['stats'] = false;
				$this->vxSidebar();
				$this->vxMenu($_menu_options);
				$this->vxMan();
				break;
				
			case 'zen':
				$_menu_options['modules']['friends'] = false;
				$_menu_options['modules']['links'] = false;
				$_menu_options['modules']['new_members'] = false;
				$_menu_options['modules']['stats'] = false;
				$this->vxSidebar();
				$this->vxMenu($_menu_options);
				$this->vxZen($options);
				break;
			
			case 'zen2':
				$_menu_options['modules']['friends'] = false;
				$_menu_options['modules']['links'] = false;
				$_menu_options['modules']['new_members'] = false;
				$_menu_options['modules']['stats'] = false;
				$this->vxSidebar();
				$this->vxMenu($_menu_options);
				$this->vxZen2($options);
				break;
			
			case 'user_graphic':
				$_menu_options['modules']['friends'] = false;
				$_menu_options['modules']['links'] = false;
				$_menu_options['modules']['new_members'] = false;
				$_menu_options['modules']['stats'] = false;
				$this->vxSidebar();
				$this->vxMenu($_menu_options);
				$this->vxUserGraphic($options);
				break;
				
			case 'project_view':
				$_menu_options['modules']['friends'] = false;
				$_menu_options['modules']['links'] = false;
				$_menu_options['modules']['new_members'] = false;
				$this->vxSidebar();
				$this->vxMenu($_menu_options);
				$this->vxProjectView($options);
				break;
		}
		echo('</div>');
	}
	
	/* E module: Main Container block logic */
	
	/* S module: div#sidebar tag */
	
	public function vxSidebar($show = true) {
		echo("<div id=\"sidebar\"></div>");
	}
	
	/* E module: div#sidebar tag */
	
	/* S module: Home bundle */
	
	public function vxHomeBundle($style) {
		$this->vxHead();
		$this->vxBodyStart();
		$this->vxTop();
		$this->vxContainer('home', $options = $style);
	}
	
	/* E module: Home bundle */
	
	/* S module: Hot block */
	
	public function vxHot() {
		$o = '<div id="main">';
		
		$o .= '<div class="blank" align="left">';
		
		$o .= _vo_ico_map();
		
		$o .= ' <a href="/">' . Vocabulary::site_name . '</a> &gt; ' . Vocabulary::term_hottopic . '</div>';
		
		$sql = "SELECT tpc_id, tpc_title, tpc_posts, nod_id, nod_name, nod_title, usr_id, usr_nick, usr_portrait, usr_gender FROM babel_topic, babel_node, babel_user WHERE tpc_uid = usr_id AND tpc_flag IN (0, 2) AND tpc_pid = nod_id AND tpc_posts > 10 ORDER BY tpc_lasttouched DESC LIMIT 100";
		
		$rs = mysql_query($sql);
		
		while ($Hot = mysql_fetch_object($rs))  {
			$img_p = $Hot->usr_portrait ? CDN_IMG . 'p/' . $Hot->usr_portrait . '_n.jpg' : CDN_IMG . 'p_' . $Hot->usr_gender . '_n.gif';
			$o .= '<div class="blank" align="left">';
			$o .= '<span class="text_large"><img src="' . $img_p . '" align="absmiddle" class="portrait" />&nbsp;<a href="/topic/view/' . $Hot->tpc_id . '.html" style="color: ' . rand_color() . '" class="var">' . make_plaintext($Hot->tpc_title) . '</a></span><span class="tip_i"><hr size="1" color="#EEE" style="color: #EEE; background-color: #EEE; height: 1px; border: 0;" />... <a href="/topic/view/' . $Hot->tpc_id . '.html#reply" class="t">' . $Hot->tpc_posts . ' 篇回复</a> | <a href="/topic/view/' . $Hot->tpc_id . '.html#replyForm" class="t">我要参与讨论</a> | 浏览讨论区 <a href="/go/' . $Hot->nod_name . '" class="t">' . $Hot->nod_title . '</a> | <a href="/topic/archive/user/' . $Hot->usr_nick . '" class="t">' . make_plaintext($Hot->usr_nick) . '</a> 的个人专辑';
			$o .= '</span></div>';
		}
		
		mysql_free_result($rs);
		
		$o .= '</div>';
		
		echo $o;
	}
	
	/* E module: Hot block */
	
	/* S module: Home block */
	
	public function vxHome($style) {
		$o = '<div id="main">';
		
		if ($_SESSION['hits'] < 10) {
			$o .= file_get_contents(BABEL_PREFIX . '/res/hot.html');
		}

		if ($style != 'remix') {		
			$sql = "SELECT tpc_id, tpc_title, tpc_posts, nod_id, nod_name, nod_title, usr_id, usr_nick, usr_portrait, usr_gender FROM babel_topic, babel_node, babel_user WHERE tpc_uid = usr_id AND tpc_pid = nod_id AND tpc_flag IN (0, 2) AND tpc_posts > 10 ORDER BY tpc_lasttouched DESC LIMIT 1";			
			$rs = mysql_query($sql);
			if (mysql_num_rows($rs) == 1) {
				$Hot = mysql_fetch_object($rs);
				$img_p = $Hot->usr_portrait ? CDN_IMG . 'p/' . $Hot->usr_portrait . '_n.jpg' : CDN_IMG . 'p_' . $Hot->usr_gender . '_n.gif';
				$o .= '<div class="blank" align="left">';
				$o .= '<h1 class="ititle"><img src="' . $img_p . '" align="absmiddle" class="portrait" />&nbsp;<a href="/topic/view/' . $Hot->tpc_id . '.html" style="color: ' . rand_color() . ';" class="var">' . make_plaintext($Hot->tpc_title) . '</a></h1><span class="tip_i"> <hr size="1" color="#EEE" style="color: #EEE; background-color: #EEE; height: 1px; border: 0;" />... <a href="/topic/view/' . $Hot->tpc_id . '.html#reply" class="t">' . $Hot->tpc_posts . ' 篇回复</a> | <a href="/topic/view/' . $Hot->tpc_id . '.html#replyForm" class="t">我要参与讨论</a> | 浏览讨论区 <a href="/go/' . $Hot->nod_name . '" class="t">' . $Hot->nod_title . '</a> | <a href="/topic/archive/user/' . $Hot->usr_nick . '" class="t">' . make_plaintext($Hot->usr_nick) . '</a> 的个人专辑 | <a href="/hot.html" class="t">浏览更多最热话题</a>';
				$o .= '</span></div>';
				$_SESSION['babel_hot'] = array();
				$_SESSION['babel_hot']['title'] = $Hot->tpc_title;
				$_SESSION['babel_hot']['id'] = $Hot->tpc_id;
				$_SESSION['babel_hot']['posts'] = $Hot->tpc_posts;
			}
			mysql_free_result($rs);
		}
		
		/* vxHomeLatest() for single or vxHomeLatestTabs() for multi. */
		
		/* It's up to you. */
		
		if ($style != 'remix') {
			$o .= $this->vxHomeLatestTabs();
		}
		
		
		switch ($style) {
			default:
			case 'remix':
				$o = $o . '<div class="blank" align="left">';
				if (isset($_GET['go'])) {
					$go = strtolower($_GET['go']);
					$go = $this->Validator->vxExistBoardName($go);
					if ($go) {
						$o .= $this->vxHomeGenerateRemix($go);
					} else {
						$o .= $this->vxHomeGenerateRemix();
					}
				} else {
					$_SESSION['babel_home_style'] = 'remix';
					$go = false;
					$o .= $this->vxHomeGenerateRemix();
				}
				$o = $o . '</div>';
				break;
			case 'shuffle':
				$go = false;
				if ($this->User->usr_sw_shuffle_cloud == 1) {
					$o = $o . '<div class="blank" align="left">';
					$_seed = rand(1, 200);		
					$_SESSION['babel_home_style'] = 'shuffle';
					if ($_o = $this->cl->load('home_' . $_seed)) {
						$o = $o . $_o;
					} else {
						$_o = $this->vxHomeGenerateV2EX();
						$o = $o . $_o;
						$this->cl->save($_o, 'home_' . $_seed);
					}
					$o = $o . '</div>';
				}
				break;
		}
		
		
		if (!$go) {
			$o .= $this->vxHomePortraits();
		}
		// latest favorite
		
		if (!$go) {
			$o = $o . '<div class="blank"><img src="' . CDN_UI . 'img/icons/silk/star.png" align="absmiddle" /> 在过去的几分钟里，我们在 ' . Vocabulary::site_name . ' 收藏了 ... <a href="/fav/latest.html" class="var" style="color: ' . rand_color() . '">浏览最新的 100 个收藏</a>';
			
			if ($_SESSION['babel_ua']['GECKO_DETECTED'] || $_SESSION['babel_ua']['KHTML_DETECTED'] || $_SESSION['babel_ua']['OPERA_DETECTED']) {
				$hack_width = 'width="100%" ';
			} else {
				$hack_width = 'width="99%" ';
			}
			$o = $o . '<table ' . $hack_width . 'cellpadding="0" cellspacing="0" border="0" class="fav">';
			
			$sql = 'SELECT usr_id, usr_gender, usr_nick, usr_portrait, fav_id, fav_type, fav_title, fav_author, fav_res, fav_created FROM babel_favorite, babel_user WHERE fav_uid = usr_id ORDER BY fav_created DESC LIMIT 10';
			
			$rs = mysql_query($sql, $this->db);
			
			$items = array(0 => '主题', 1 => '讨论区', 2 => '频道');
			$items_p = array(0 => 'mico_topic.gif', 1 => 'mico_gear.gif', 2 => 'mico_news.gif');
			$items_n = array(0 => 'topic', 1 => 'board', 2 => 'channel');
	
			while ($Fav = mysql_fetch_object($rs)) {
				
				$img_p = $Fav->usr_portrait ? CDN_P . 'p/' . $Fav->usr_portrait . '_n.jpg' : CDN_P . 'p_' . $Fav->usr_gender . '_n.gif';
				
				$css_color = rand_color();
				
				$o = $o . '<tr><td align="left">&nbsp;<img src="' . $img_p . '" alt="' . $Fav->usr_nick . '" align="absmiddle" class="portrait" />&nbsp;<a href="/u/' . urlencode($Fav->usr_nick) . '" class="var" style="color: ' . $css_color . ';">' . make_plaintext($Fav->usr_nick) . '</a> 收藏了' . $items[$Fav->fav_type] . ' <span class="tip_i">[ <img src="' . CDN_IMG . $items_p[$Fav->fav_type] . '" align="absmiddle" /> <a href="/' . $items_n[$Fav->fav_type] . '/view/' . $Fav->fav_res . '.html" style="color: ' . $css_color . ';" class="var">' . make_plaintext($Fav->fav_title) . '</a> ] ... ' . make_descriptive_time($Fav->fav_created) . '</span></td></tr>';
				
				$Fav = null;
			}
			
			mysql_free_result($rs);
			
			$o = $o . '</table>';
			
			$o = $o . '</div>';
			
			$o .= $this->vxHomeTools();
		}
		$o = $o . '</div>';
		
		echo $o;
		
	}
	
	/* E module: Home block */
	
	/* S module: Home Tools */
	
	private function vxHomeTools() {
		$o = '<div class="blank"><span class="tip_i"><img src="' . CDN_UI . 'img/icons/silk/world.png" align="absmiddle" class="map" />&nbsp;<a href="http://www.v2ex.com/mobile.html">手机号码所在地查询</a> ... <img src="' . CDN_UI . 'img/icons/silk/book_open.png" align="absmiddle" class="map" />&nbsp;<a href="http://www.v2ex.com/man.html">参考文档藏经阁</a> ... <img src="' . CDN_UI . 'img/icons/silk/control_fastforward.png" align="absmiddle" class="map" />&nbsp;<a href="http://www.v2ex.com/timtowtdi.html">节约时间！</a></span></div>';
		return $o;
	}
	
	/* E module: Home Tools */
	
	/* S module: Home Latest */
	
	private function vxHomeLatest($items = 20) {
		$l = '<div class="blank"><img src="' . CDN_IMG . 'pico_fresh.gif" class="portrait" align="absmiddle" /> 所有讨论区最新活跃主题 ... <a href="/topic/latest.html" class="var" style="color: ' . rand_color() . '">最新创建</a> ... <a href="/topic/answered/latest.html"  class="var" style="color: ' . rand_color() . '">最新回复</a> ... <img src="' . CDN_UI .  'img/icons/silk/feed.png" align="absmiddle" /> <a href="/feed/v2ex.rss" target="_blank" class="var" style="color: ' . rand_color() . ';">RSS 2.0 聚合</a>';
		
		if ($_SESSION['babel_ua']['GECKO_DETECTED'] || $_SESSION['babel_ua']['KHTML_DETECTED'] || $_SESSION['babel_ua']['OPERA_DETECTED']) {
			$hack_width = 'width="100%" ';
		} else {
			$hack_width = 'width="99%" ';
		}
		$l = $l . '<table ' . $hack_width . 'cellpadding="0" cellspacing="0" border="0" class="fav">';
		
		$sql = 'SELECT usr_id, usr_nick, usr_gender, usr_portrait, tpc_id, tpc_title, tpc_posts, tpc_created, tpc_lasttouched, nod_id, nod_title, nod_name FROM babel_user, babel_topic, babel_node WHERE tpc_uid = usr_id AND tpc_pid = nod_id AND tpc_flag IN (0, 2) ORDER BY tpc_lasttouched DESC LIMIT ' . $items;
		
		$rs = mysql_query($sql, $this->db);
		
		while ($Fresh = mysql_fetch_object($rs)) {
			$img_p = $Fresh->usr_portrait ? CDN_IMG . 'p/' . $Fresh->usr_portrait . '_n.jpg' : CDN_IMG . 'p_' . $Fresh->usr_gender . '_n.gif';
			$css_color = rand_color();
			$l = $l . '<tr><td align="left"><span class="tip_i">&nbsp;<img src="' . $img_p . '" alt="' . $Fresh->usr_nick . '" align="absmiddle" class="portrait" />&nbsp;<a href="/u/' . urlencode($Fresh->usr_nick) . '" class="var" style="color: ' . rand_color() . ';">' . make_plaintext($Fresh->usr_nick) . '</a> ... <a href="/go/' . $Fresh->nod_name . '">' . make_plaintext($Fresh->nod_title) . '</a> ... [ <a href="/topic/view/' . $Fresh->tpc_id . '.html" style="color: ' . $css_color . ';" class="var">' . make_plaintext($Fresh->tpc_title) . '</a> ] ... ' . make_descriptive_time($Fresh->tpc_lasttouched);
			
			$_o = $Fresh->tpc_posts ? '，' . $Fresh->tpc_posts . ' 篇回复' : '，尚无回复';
			
			$l = $l . $_o;
			
			$l = $l . '</span></td></tr>';
		
			$Fresh = null;
		}
		
		mysql_free_result($rs);
		
		$l = $l . '</table>';
		$l = $l . '</div>';

		return $l;
	}
	
	/* E module: Home Latest */
	
	/* S module: Home Latest Tabs */
	
	/*
	 *
	 * This module is interchangeable with vxHomeLatest(), both of them are
	 * called from vxHome()
	 *
	 */
	
	public function vxHomeLatestTabs() {
		$o = '<script src="/js/babel_home_tabs.js" type="text/javascript"> </script>';
		$o .= '<div align="left" class="blank">';
		
		if ($this->User->vxIsLogin()) {
			$img_p = $this->User->usr_portrait ? CDN_IMG . 'p/' . $this->User->usr_portrait . '_n.jpg' : CDN_IMG . 'p_' . $this->User->usr_gender . '_n.gif';
			
			$o = $o . '<span class="text"><img src="' . $img_p . '" align="absmiddle" class="portrait" /> 欢迎，<strong>' . $this->User->usr_nick . '</strong>！';
			
			if ($url = $this->cs->get('fsu_' . $this->User->usr_id)) {
				$F = unserialize($url);
				$o .= (rand(0, 1) == 1) ? '<span class="tip">或许你会对来自朋友 [ <small><a href="/u/' . urlencode($F['usr_nick']) . '">' . $F['usr_nick'] . '</a></small> ] 的 [ <small><a href="http://' . $F['svp_url'] . '" target="_blank" re="nofollow external">http://' . $F['svp_url'] . '</a></small> ] 感兴趣吧？</span>' : '';
			} else {
				$sql = "SELECT usr_nick, svp_url FROM babel_user, babel_savepoint WHERE usr_id = svp_uid AND svp_uid IN (SELECT frd_fid FROM babel_friend WHERE frd_uid = {$this->User->usr_id}) ORDER BY rand() LIMIT 1";
				$rs = mysql_query($sql);
				if ($F = mysql_fetch_array($rs)) {
					mysql_free_result($rs);
					$this->cs->save(serialize($F), 'fsu_' . $this->User->usr_id);
					$o .= (rand(0, 1) == 1) ? '<span class="tip">或许你会对来自朋友 [ <small><a href="/u/' . urlencode($F['usr_nick']) . '">' . $F['usr_nick'] . '</a></small> ] 的 [ <small><a href="http://' . $F['svp_url'] . '" target="_blank" re="nofollow external">http://' . $F['svp_url'] . '</a></small> ] 感兴趣吧？</span>' : '';
				} else {
					mysql_free_result($rs);
					$sql = "SELECT COUNT(*) FROM babel_savepoint WHERE svp_uid = {$this->User->usr_id}";
					$this->User->svp_count = mysql_result(mysql_query($sql), 0, 0);
					if ($this->User->svp_count == 0) {
						$o .= '<span class="tip_i">还没有添加自己的网上据点？现在 [ <a href="/u/' . urlencode($this->User->usr_nick) . '#svp">添加一个</a> ] 吧，让更多人知道你的网站！</span>';
					}
				}
			}
			
			$o .= '</span>';
		} else {
			$o = $o . '<span class="text">欢迎来到 ' . Vocabulary::site_name . '！如果你已经注册，请<a href="/login.vx">登录</a>，如果还没有，' . Vocabulary::site_name . ' 欢迎你的<a href="/signup.html">加入</a> ...</span>';
		}
		$o .= '<ul class="tabs">';
		$o .= '<li class="normal" id="home_tab_latest" onclick="switchHomeTab(' . "'latest', '', ''" . ')">最新讨论</li>';
		$sql = 'SELECT nod_id, nod_name, nod_title FROM babel_node WHERE nod_level = 1 ORDER BY nod_weight DESC';
		$rs = mysql_query($sql, $this->db);
		while ($Node = mysql_fetch_object($rs)) {
			$o .= '<li class="normal" id="home_tab_section_' . $Node->nod_id . '" onclick="switchHomeTab(' . "'section', {$Node->nod_id}, '{$Node->nod_name}'" . ')">' . $Node->nod_title . '</li>';
			$Node = null;
		}
		mysql_free_result($rs);
		/* FIX: virgin tab
		$o .= '<li class="normal" id="home_tab_virgin" onclick="switchHomeTab(' . "'virgin', '', ''" . ')">未回复</li>';
		*/
		$o .= '</ul>';
		$o .= '<div id="home_tab_top"></div>';
		$o .= '<div id="home_tab_content"></div>';
		
		if (!isset($_SESSION['babel_home_tab'])) {
			$_SESSION['babel_home_tab'] = 'latest';
		}
		if ($_SESSION['babel_home_tab'] == 'latest') {
			$o .= '<script type="text/javascript">initHomeTabs("latest");</script>';
		} else {
			$o .= '<script type="text/javascript">initHomeTabs("' . $_SESSION['babel_home_tab'] . '");</script>';
		}
		$o .= '</div>';
		return $o;
	}
	
	/* E module: Home Latest Tabs */
	
	/* S module: Topic Latest */
	
	public function vxTopicLatest() {
		if ($l = $this->cs->get('set_topic_latest')) {
			echo $l;
		} else {
			$l = '<div id="main">';
			$l .= '<div class="blank" align="left">';
			$l .= _vo_ico_map();
			$l .= ' <a href="/">' . Vocabulary::site_name . '</a> &gt; ' . Vocabulary::term_latesttopic . '</div>';
			$l .= '<div class="blank"><img src="' . CDN_IMG . 'pico_fresh.gif" class="portrait" align="absmiddle" /> 所有讨论区最新的 100 个主题 ... ';
			
			if ($_SESSION['babel_ua']['GECKO_DETECTED'] || $_SESSION['babel_ua']['KHTML_DETECTED'] || $_SESSION['babel_ua']['OPERA_DETECTED']) {
				$hack_width = 'width="100%" ';
			} else {
				$hack_width = 'width="99%" ';
			}
			$l = $l . '<table ' . $hack_width . 'cellpadding="0" cellspacing="0" border="0" class="fav">';
			
			$sql = 'SELECT usr_id, usr_nick, usr_gender, usr_portrait, tpc_id, tpc_title, tpc_posts, tpc_hits, tpc_created, nod_id, nod_title, nod_name FROM babel_user, babel_topic, babel_node WHERE tpc_uid = usr_id AND tpc_pid = nod_id ORDER BY tpc_created DESC LIMIT 100';
			
			$rs = mysql_query($sql, $this->db);
			
			$hits = 0;
			$replies = 0;
			
			while ($Fresh = mysql_fetch_object($rs)) {
				$hits = $hits + $Fresh->tpc_hits;
				$replies = $replies + $Fresh->tpc_posts;
				
				$img_p = $Fresh->usr_portrait ? CDN_IMG . 'p/' . $Fresh->usr_portrait . '_n.jpg' : CDN_IMG . 'p_' . $Fresh->usr_gender . '_n.gif';
				$css_color = rand_color();
				$l = $l . '<tr><td align="left">&nbsp;<img src="' . $img_p . '" alt="' . $Fresh->usr_nick . '" align="absmiddle" class="portrait" />&nbsp;<a href="/u/' . urlencode($Fresh->usr_nick) . '" class="t">' . make_plaintext($Fresh->usr_nick) . '</a> 在 <a href="/go/' . $Fresh->nod_name . '">' . make_plaintext($Fresh->nod_title) . '</a> 发表了: <a href="/topic/view/' . $Fresh->tpc_id . '.html" style="color: ' . $css_color . ';" class="var">' . make_plaintext($Fresh->tpc_title) . '</a> <span class="tip_i">... ' . make_descriptive_time($Fresh->tpc_created);
				
				$_o = $Fresh->tpc_posts ? '，' . $Fresh->tpc_posts . ' 篇回复' : '，尚无回复';
				
				$_o .= '，' . $Fresh->tpc_hits . ' 次点击';
				
				$l = $l . $_o;
				
				$l = $l . '</span></td></tr>';
			
				$Fresh = null;
			}
			
			mysql_free_result($rs);
			
			$l .= '<tr><td align="left"><span class="tip_i">这 100 篇主题共有 ' . $replies . ' 篇回复，被点击了 ' . $hits . ' 次 ...</span></td></tr>';
			
			$l = $l . '</table>';
			$l = $l . '</div></div>';
	
			echo $l;
			$this->cs->save($l, 'set_topic_latest');
		}
	}
	
	/* E module: Topic Latest */

	/* S module: Topic Answered Latest */
	
	public function vxTopicAnsweredLatest() {
		if ($l = $this->cs->get('set_topic_answered_latest')) {
			echo $l;
		} else {
			$l = '<div id="main">';
			$l .= '<div class="blank" align="left">';
			$l .= _vo_ico_map();
			$l .= ' <a href="/">' . Vocabulary::site_name . '</a> &gt; ' . Vocabulary::term_latest_answered_topic . '</div>';
			$l .= '<div class="blank"><img src="' . CDN_IMG . 'pico_fresh.gif" class="portrait" align="absmiddle" /> 所有讨论区最新被回复的 100 个主题 ... ';
			
			if ($_SESSION['babel_ua']['GECKO_DETECTED'] || $_SESSION['babel_ua']['KHTML_DETECTED'] || $_SESSION['babel_ua']['OPERA_DETECTED']) {
				$hack_width = 'width="100%" ';
			} else {
				$hack_width = 'width="99%" ';
			}
			$l = $l . '<table ' . $hack_width . 'cellpadding="0" cellspacing="0" border="0" class="fav">';
			
			$sql = 'SELECT usr_id, usr_nick, usr_gender, usr_portrait, tpc_id, tpc_title, tpc_posts, tpc_hits, tpc_created, tpc_lasttouched, nod_id, nod_title, nod_name FROM babel_user, babel_topic, babel_node WHERE tpc_uid = usr_id AND tpc_pid = nod_id AND tpc_posts > 0 ORDER BY tpc_lasttouched DESC LIMIT 100';
			
			$rs = mysql_query($sql, $this->db);
			
			$hits = 0;
			$replies = 0;
			
			while ($Fresh = mysql_fetch_object($rs)) {
				$hits = $hits + $Fresh->tpc_hits;
				$replies = $replies + $Fresh->tpc_posts;
				
				$img_p = $Fresh->usr_portrait ? CDN_IMG . 'p/' . $Fresh->usr_portrait . '_n.jpg' : CDN_IMG . 'p_' . $Fresh->usr_gender . '_n.gif';
				$css_color = rand_color();
				$l = $l . '<tr><td align="left">&nbsp;<img src="' . $img_p . '" alt="' . $Fresh->usr_nick . '" align="absmiddle" class="portrait" />&nbsp;<a href="/u/' . urlencode($Fresh->usr_nick) . '" class="t">' . make_plaintext($Fresh->usr_nick) . '</a> 在 <a href="/go/' . $Fresh->nod_name . '">' . make_plaintext($Fresh->nod_title) . '</a> ... <a href="/topic/view/' . $Fresh->tpc_id . '.html" style="color: ' . $css_color . ';" class="var">' . make_plaintext($Fresh->tpc_title) . '</a> <span class="tip_i">... ' . make_descriptive_time($Fresh->tpc_lasttouched);
				
				$_o = $Fresh->tpc_posts ? '，' . $Fresh->tpc_posts . ' 篇回复' : '，尚无回复';
				
				$_o .= '，' . $Fresh->tpc_hits . ' 次点击';
				
				$l = $l . $_o;
				
				$l = $l . '</span></td></tr>';
			
				$Fresh = null;
			}
			
			mysql_free_result($rs);
			
			$l .= '<tr><td align="left"><span class="tip_i">这 100 篇主题共有 ' . $replies . ' 篇回复，被点击了 ' . $hits . ' 次 ...</span></td></tr>';
			
			$l = $l . '</table>';
			$l = $l . '</div></div>';
	
			echo $l;
			$this->cs->save($l, 'set_topic_answered_latest');
		}
	}
	
	/* E module: Topic Answered Latest */

	/* S module: Fav Latest */
	
	public function vxFavLatest() {
		if ($o = $this->cs->get('set_fav_latest')) {
			echo $o;
		} else {
			$o = '<div id="main">';
			$o .= '<div class="blank" align="left">';
			$o .= _vo_ico_map();
			$o .= ' <a href="/">' . Vocabulary::site_name . '</a> &gt; ' . Vocabulary::term_latestfav . '</div>';
			
			$o = $o . '<div class="blank"><img src="' . CDN_IMG . 'pico_star.gif" class="portrait" align="absmiddle" /> 最过去的几分钟里，我们在 ' . Vocabulary::site_name . ' 的最新 100 个收藏 ...';
			
			if ($_SESSION['babel_ua']['GECKO_DETECTED'] || $_SESSION['babel_ua']['KHTML_DETECTED'] || $_SESSION['babel_ua']['OPERA_DETECTED']) {
				$hack_width = 'width="100%" ';
			} else {
				$hack_width = 'width="99%" ';
			}
			$o = $o . '<table ' . $hack_width . 'cellpadding="0" cellspacing="0" border="0" class="fav">';
			
			$sql = 'SELECT usr_id, usr_gender, usr_nick, usr_portrait, fav_id, fav_type, fav_title, fav_author, fav_res, fav_created FROM babel_favorite, babel_user WHERE fav_uid = usr_id ORDER BY fav_created DESC LIMIT 100';
			
			$rs = mysql_query($sql, $this->db);
			
			$items = array(0 => '主题', 1 => '讨论区', 2 => '频道');
			$items_p = array(0 => 'mico_topic.gif', 1 => 'mico_gear.gif', 2 => 'mico_news.gif');
			$items_n = array(0 => 'topic', 1 => 'board', 2 => 'channel');
	
			while ($Fav = mysql_fetch_object($rs)) {
				
				$img_p = $Fav->usr_portrait ? CDN_IMG . 'p/' . $Fav->usr_portrait . '_n.jpg' : CDN_IMG . 'p_' . $Fav->usr_gender . '_n.gif';
				
				$css_color = rand_color();
				
				$o = $o . '<tr><td align="left">&nbsp;<img src="' . $img_p . '" alt="' . $Fav->usr_nick . '" align="absmiddle" class="portrait" />&nbsp;<a href="/u/' . urlencode($Fav->usr_nick) . '" class="var" style="color: ' . $css_color . ';">' . make_plaintext($Fav->usr_nick) . '</a> 收藏了' . $items[$Fav->fav_type] . ' <span class="tip_i">[ <img src="' . CDN_IMG . $items_p[$Fav->fav_type] . '" align="absmiddle" /> <a href="/' . $items_n[$Fav->fav_type] . '/view/' . $Fav->fav_res . '.html" style="color: ' . $css_color . ';" class="var">' . make_plaintext($Fav->fav_title) . '</a> ] ... ' . make_descriptive_time($Fav->fav_created) . '</span></td></tr>';
				
				$Fav = null;
			}
			
			mysql_free_result($rs);
			
			$o = $o . '</table>';
			
			$o = $o . '</div></div>';
			
			echo $o;
			$this->cs->save($o, 'set_fav_latest');
		}
	}
	
	/* E module: Fav Latest */
	
	/* S module: Session Stats */
	
	public function vxSessionStats() {
		$s = '<div id="main">';
		$s .= '<div class="blank" align="left">';
		$s .= _vo_ico_map();
		$s .= ' <a href="/">' . Vocabulary::site_name . '</a> &gt; ' . Vocabulary::term_sessionstats . '</div>';
		$s .= '<div class="blank"><img src="' . CDN_IMG . 'pico_tuser.gif" class="portrait" align="absmiddle" /> ' . Vocabulary::term_sessionstats . ' ... ';
		if ($_SESSION['babel_ua']['GECKO_DETECTED'] || $_SESSION['babel_ua']['KHTML_DETECTED'] || $_SESSION['babel_ua']['OPERA_DETECTED']) {
			$hack_width = 'width="100%" ';
		} else {
			$hack_width = 'width="99%" ';
		}
		$s .= '<table ' . $hack_width . 'cellpadding="0" cellspacing="0" border="0" class="fav">';
		
		$i = 0;
		$pages = array_reverse($_SESSION['pages'], true);
		foreach ($pages as $time => $page) {
			$i++;
			$css_color = rand_color();
			$s .= '<tr><td align="left">&nbsp;<small class="fade">' . $i . '.</small> <a href="http://' . $page . '" style="color: ' . $css_color . '" class="var">' . $_SESSION['titles'][$time] . '</a> <span class="tip_i">... ' . make_descriptive_time($time);
			$s .= '</span></td></tr>';
			if ($i == count($pages)) { $_time_start = $time; }
		}
		$_duration = time() - $_time_start;
		$_stay = intval($_duration / count($pages));
		$s .= '<tr><td align="left"><span class="tip_i">本次访问到目前为止持续时间 ' . $_duration . ' 秒，一共访问了至少 ' . $i . ' 个页面，在每个页面上的平均停留时间 ' . $_stay . ' 秒 ...</span></td></tr>';
		
		$s .= '</table>';
		$s .= '</div></div>';

		echo $s;
	}
	
	/* E module: User Logins */
	
	/* S module: User Logins */
	
	public function vxUserLogins() {
		if ($o = $this->cs->get('set_user_logins_f')) {
			$l = '<div id="main">';
			$l .= '<div class="blank" align="left">';
			$l .= _vo_ico_map();
			$l .= ' <a href="/">' . Vocabulary::site_name . '</a> &gt; 24 小时内的' . Vocabulary::term_userlogins . '</div>';
			$l .= '<div class="blank"><img src="' . CDN_UI . 'img/icons/silk/user_go.png" align="absmiddle" /> 24 小时内的' . Vocabulary::term_userlogins . ' ... ';
			if ($_SESSION['babel_ua']['GECKO_DETECTED'] || $_SESSION['babel_ua']['KHTML_DETECTED'] || $_SESSION['babel_ua']['OPERA_DETECTED']) {
				$hack_width = 'width="100%" ';
			} else {
				$hack_width = 'width="99%" ';
			}
			$l = $l . '<table ' . $hack_width . 'cellpadding="0" cellspacing="0" border="0" class="fav">';
			$l = $l . $o;
			echo $l;
		} else {
			$l = '';
			$o = '<div id="main">';
			$o .= '<div class="blank" align="left">';
			$o .= _vo_ico_map();
			$o .= ' <a href="/">' . Vocabulary::site_name . '</a> &gt; 24 小时内的' . Vocabulary::term_userlogins . '</div>';
			$o .= '<div class="blank"><img src="' . CDN_UI . 'img/icons/silk/user_go.png" align="absmiddle" /> 24 小时内的' . Vocabulary::term_userlogins . ' ... ';
			if ($_SESSION['babel_ua']['GECKO_DETECTED'] || $_SESSION['babel_ua']['KHTML_DETECTED'] || $_SESSION['babel_ua']['OPERA_DETECTED']) {
				$hack_width = 'width="100%" ';
			} else {
				$hack_width = 'width="99%" ';
			}
			$o .= '<table ' . $hack_width . 'cellpadding="0" cellspacing="5" border="0" class="fav">';
			
			$_time = time() - 86400;
			$sql = 'SELECT usr_id, usr_nick, usr_gender, usr_portrait, usr_logins, usr_lastlogin, usr_created FROM babel_user WHERE usr_logins > 0 AND usr_lastlogin > ' . $_time . ' ORDER BY usr_lastlogin DESC';
			
			$rs = mysql_query($sql, $this->db);
			
			$i = 0;
			$ts_month = time() - (86400 * 31);
			while ($Login = mysql_fetch_object($rs)) {
				$i++;
				if ($i == 1) {
					$l .= '<tr>';
				}
				if ($i > 1 && (($i % 3) == 1)) {
					$l .= '<tr>';
				}
				$img_p = $Login->usr_portrait ? CDN_IMG . 'p/' . $Login->usr_portrait . '_n.jpg' : CDN_IMG . 'p_' . $Login->usr_gender . '_n.gif';
				$css_color = rand_color();
				$l .= '<td align="left">&nbsp;<img src="' . $img_p . '" alt="' . $Login->usr_nick . '" align="absmiddle" class="portrait" />&nbsp;<a href="/u/' . urlencode($Login->usr_nick) . '" class="t">' . make_plaintext($Login->usr_nick) . '</a> <span class="tip_i"><small>- ' . make_desc_time($Login->usr_lastlogin) . ' ago</small>';
				if ($Login->usr_logins > 100) {
					$_o = '<small class="fade"> - ' . $Login->usr_logins . ' logins';
				} else {
				
					if ($Login->usr_created > $ts_month) {
						$_o = '<small class="lime"> - ' . $Login->usr_logins . ' logins';
					} else {
						$_o = '<small> - ' . $Login->usr_logins . ' logins';
					}
				}
				
				$l = $l . $_o;
				
				$l = $l . '</small></span></td>';
				$Fresh = null;
				if ($i > 1 && (($i % 3) == 0)) {
					echo('</tr>');
				}
			}
			
			if (($i % 3) != 0) {
				echo('</tr>');
			}
			
			mysql_free_result($rs);

			$l .= '<tr><td align="left" colspan="3">' . _vo_hr() . '<span class="tip"><img src="' . CDN_UI . 'img/icons/silk/information.png" align="absmiddle" /> 24 小时内共有 ' . $i . ' 位会员登录进入过 ' . Vocabulary::site_name . ' ...</span></td></tr>';
			
			$l = $l . '</table>';
			$l = $l . '</div></div>';
			
			$this->cs->save($l, 'set_user_logins');
			$l = $o . $l;
			echo $l;
		}
	}
	
	/* E module: User Logins */	
	
	/* S module: Home Portraits */
	
	private function vxHomePortraits() {
		$o = '';
		
		$o .= '<div class="blank" align="left"><img src="' . CDN_UI . 'img/icons/silk/group.png" align="absmiddle" /> 会员头像展示 ...';
		if ($_SESSION['babel_ua']['GECKO_DETECTED'] || $_SESSION['babel_ua']['KHTML_DETECTED'] || $_SESSION['babel_ua']['OPERA_DETECTED']) {
			$hack_width = 'width="100%" ';
		} else {
			$hack_width = 'width="99%" ';
		}
		$o .= '<table ' . $hack_width . 'cellpadding="0" cellspacing="0" border="0" class="fav">';
		$o .= '<tr><td>';
		switch ($this->User->usr_width) {
			case 800:
				$p_count = 4;
				break;
			case 640:
				$p_count = 3;
				break;
			case 1024:
			default:
				$p_count = 7;
				break;
			case 1280:
			case 1400:
			case 1600:
			case 1920:
			case 2560:
				$p_count = 9;
				break;
		}
		$ts_month = time() - 86400 * 31;
		$sql = "SELECT usr_id, usr_nick, usr_geo, usr_portrait FROM babel_user WHERE usr_portrait != '' AND usr_hits > 100 AND usr_lastlogin > {$ts_month} ORDER BY rand() LIMIT {$p_count}";
		$rs = mysql_query($sql);
		
		$i = 0;
		
		while ($User = mysql_fetch_object($rs)) {
			$i++;
			$img_p = $User->usr_portrait ? '/img/p/' . $User->usr_portrait . '.jpg' : '/img/p_' . $User->usr_gender . '.gif';
			$o .= '<a href="/u/' . urlencode($User->usr_nick) . '" class="friend"><img src="' . $img_p . '" class="portrait" /><br />' . $User->usr_nick . '<div class="tip">' . $this->Geo->map['name'][$User->usr_geo] . '</div></a>';
		}
		
		mysql_free_result($rs);
		$o .= '</td></tr>';
		$o .= '</table>';
		$o .= '</div>';
		
		return $o;
	}
	
	/* E module: Home Portrait Show */
	
	/* S module: Home Generate logic for V2EX Remix */
	
	private function vxHomeGenerateRemix($go = false) {
		$o = '';
		
		if ($_SESSION['babel_ua']['GECKO_DETECTED'] || $_SESSION['babel_ua']['KHTML_DETECTED'] || $_SESSION['babel_ua']['OPERA_DETECTED']) {
			$hack_width = 'width="100%" ';
		} else {
			$hack_width = 'width="99%" ';
		}
		
		$o = $o . '<table cellpadding="0" cellspacing="0" border="0" ' . $hack_width . ' class="fav">';
		if ($go) {
			$o .= '<tr><td align="left" class="section_odd"><span class="text_large"><img src="' . CDN_IMG . 'ico_board.gif" align="absmiddle" class="home" /><a href="/">V2EX</a> / <a href="/go/' . $go->sect_name . '">' . $go->sect_title . '</a> / ' . $go->nod_title . '&nbsp;<a href="/go/' . $go->nod_name . '"><img src="' . CDN_UI . 'img/icons/silk/shape_move_forwards.png" border="0" align="absmiddle" /></a>&nbsp;&nbsp;</span><span class="tip_i">' . $go->nod_header . '</span><br />';
			
			$o .= $this->vxHomeSectionRemix($go->nod_id, $go->nod_level);
			
			$o .= '阅读讨论区 <a href="/go/' . $go->nod_name . '" class="t">' . $go->nod_title . '</a> 的全部主题 | <a href="/topic/new/' . $go->nod_id . '.vx" rel="nofollow" class="t">创建新主题</a> | 使用 <a href="/feed/board/' . $go->nod_name . '.rss" class="t">RSS</a> 订阅 | <a href="/go/' . $go->nod_name . '" class="var"><img src="' . CDN_UI . 'img/icons/silk/shape_move_forwards.png" align="absmiddle" border="0" /></a>&nbsp;<a href="/go/' . $go->nod_name . '" class="t">切换到正常模式</a>';
			$o .= '</td></tr>';
		} else {
			$sql = 'SELECT nod_id, nod_name, nod_title FROM babel_node WHERE nod_level = 1 ORDER BY nod_weight DESC, nod_id ASC';
			
			$rs = mysql_query($sql);
			
			$i = 0;
			while ($Node = mysql_fetch_object($rs)) {
				$i++;
				$class = 'section_odd';
			
				$o .= '<tr><td align="left" class="' . $class . '"><span class="text_large"><img src="' . CDN_IMG . 's/' . $Node->nod_name . '.gif" align="absmiddle" class="home" /><a href="/go/' . $Node->nod_name . '" target="_self" class="section">' . $Node->nod_title . '</a>&nbsp;|&nbsp;</span><span class="text">';
				
				$sql = "SELECT nod_id, nod_name, nod_title, nod_topics FROM babel_node WHERE nod_pid = {$Node->nod_id} ORDER BY nod_topics DESC LIMIT 6";
				
				$rs_boards = mysql_query($sql);
				
				while ($Board = mysql_fetch_object($rs_boards)) {
					$o .= '&nbsp;&nbsp;<a href="/remix/' . $Board->nod_name . '" class="g">' . $Board->nod_title . '</a>';
				}
				
				$o .= '</span><br />' . $this->vxHomeSectionRemix($Node->nod_id) . '</td></tr>';
			}
		}
		
		$o .= '</table>';
		return $o;
	}
	
	/* E module: Home Generate logic for V2EX Remix */
	
	/* S module: Home Generate logic for V2EX */
	
	private function vxHomeGenerateV2EX() {
		$o = '';
		
		$o = $o . '<table cellpadding="0" cellspacing="0" border="0" class="fav">';
		
		$sql = 'SELECT nod_id, nod_name, nod_title FROM babel_node WHERE nod_level = 1 ORDER BY nod_weight DESC, nod_id ASC';
		
		$rs = mysql_query($sql);
		
		$i = 0;
		while ($Node = mysql_fetch_object($rs)) {
			$i++;
			if (($i % 2) == 0) {
				$class = 'section_even';
			} else {
				$class = 'section_odd';
			}
		
			$o .= '<tr><td align="left" class="' . $class . '"><span class="text_large"><img src="' . CDN_IMG . 's/' . $Node->nod_name . '.gif" align="absmiddle" class="home" /><a href="/go/' . $Node->nod_name . '" target="_self" class="section">' . $Node->nod_title . '</a>&nbsp;|&nbsp;</span><span class="text">';
			
			$sql = "SELECT nod_id, nod_name, nod_title, nod_topics FROM babel_node WHERE nod_pid = {$Node->nod_id} ORDER BY nod_topics DESC LIMIT 6";
			
			$rs_boards = mysql_query($sql);
			
			while ($Board = mysql_fetch_object($rs_boards)) {
				$o .= '&nbsp;&nbsp;<a href="/go/' . $Board->nod_name . '" class="g">' . $Board->nod_title . '</a>';
			}
			$_topics = 40;
			$o .= '</span><br />' . $this->vxHomeSection($Node->nod_id, $_topics) . '</td></tr>';
		}
		
		$o .= '</table>';
		return $o;
	}
	
	/* E module: Home Generate logic for V2EX */
	
	/* S module: Search bundle */
	
	public function vxSearchBundle() {
		$this->vxHead($msgSiteTitle = Vocabulary::action_search);
		$this->vxBodyStart();
		$this->vxTop();
		$this->vxContainer('search');
	}
	
	/* E module: Search bundle */
	
	/* S module: Search Substance block */
	
	public function vxSearchSubstance() {
		$err = array();
		$err['too_common'] = 0;
		
		$stage = 0;
		
		$query_verified = array();
		$query_task = array();
		$query_common = array();
		
		$style_search_highlight = '<span class="text_matched">\1</span>';
		
		$stop_words = array('the', '的', '我');

		if (isset($_GET['q'])) {
			$query = trim($_GET['q']);
			if (strlen($query) > 0) {
				if (get_magic_quotes_gpc()) {
					$query = stripslashes($query);
				}
				$stage = 1;
			}
		}
		
		if ($stage == 1) {
			$query_hash = md5($query);
			$query_splitted = explode(' ', $query);
			foreach ($query_splitted as $query_keyword) {
				if (!in_array($query_keyword, $query_verified)) {
					$query_verified[] = $query_keyword;
					if (in_array($query_keyword, $stop_words)) {
						$query_common[] = $query_keyword;
					} else {
						$query_task[] = $query_keyword;
					}
				}
			}
			$count_verified = count($query_verified);
			$count_task = count($query_task);
			$count_common = count($query_common);
			
			if ($count_task > 0) {
				$stage = 2;
			} else {
				if ($count_common > 0) {
					$stage = 3;
				}
			}
		}
		
		if ($stage == 2) {
			if ($result_a = $this->cl->load('k_search_' . $query_hash)) {
				$time_start = microtime_float();
				$result_a = unserialize($result_a);
				$count_result = count($result_a);
			} else {
				$time_start = microtime_float();
				
				// get topics
				$i = 0;
				$sql = "SELECT DISTINCT tpc_id, tpc_title, tpc_description, tpc_content, tpc_uid, tpc_lasttouched, usr_nick FROM babel_topic, babel_post, babel_user WHERE (";
				foreach ($query_task as $task) {
					$task = mysql_real_escape_string($task, $this->db);
					$i++;
					if ($i == 1) {
						$sql = $sql . '(';
					} else {
						$sql = $sql . ' OR (';
					}
					$sql = $sql . "tpc_title LIKE '%{$task}%'"; 
					$sql = $sql . " OR tpc_description LIKE '%{$task}%'"; 
					$sql = $sql . " OR tpc_content LIKE '%{$task}%'";
					$sql = $sql . ')';
					$sql = $sql . ' OR (';
					$sql = $sql . "pst_content LIKE '%{$task}%'";
					$sql = $sql . ')';
				}
				$sql = $sql . ")";
				$sql = $sql . " AND (tpc_uid = usr_id AND tpc_id = pst_tid)";
				$sql = $sql . " ORDER BY tpc_created DESC";
				$rs = mysql_query($sql, $this->db);
				$count_matched = mysql_num_rows($rs);
			
				// get ads
				if (KIJIJI_LEGACY_API_SEARCH_ENABLED) {
					if ($x = $this->cl->load('k_search_ads_' . $query_hash)) {
						$x = simplexml_load_string($x);
						$count_ads = $x->Body->return_ad_count;
					} else {
						if (preg_match('/[a-z0-9]/i', $query)) {
							$count_ads = 0;
						} else {
							$req_kijiji =& new HTTP_Request("http://shanghai.kijiji.com.cn/classifieds/ClassiApiSearchAdExCommand");
							$req_kijiji_input = '<?xml version="1.0" encoding="UTF-8" ?><SOAP:Envelope xmlns:SOAP="http://www.w3.org/2003/05/soap-envelope" ><SOAP:Header ><m:command xmlns:m="http://www.kijiji.com/soap">search_ad_ex</m:command><m:version xmlns:m="http://www.kijiji.com/soap">1</m:version></SOAP:Header><SOAP:Body><m:search_options xmlns:m="http://www.kijiji.com/soap">					<sub_area_id></sub_area_id><neighborhood_id></neighborhood_id><date_duration>40</date_duration><category_id></category_id><load_image>true</load_image><keyword>' . $query . '</keyword><return_ad_count>100</return_ad_count></m:search_options></SOAP:Body></SOAP:Envelope>';
							
							$req_kijiji->setMethod(HTTP_REQUEST_METHOD_POST);
							$req_kijiji->addPostData("xml", $req_kijiji_input);
							if (!PEAR::isError($req_kijiji->sendRequest())) {
								$rt_kijiji = $req_kijiji->getResponseBody();
								$rt_kijiji = str_replace('SOAP:', '', $rt_kijiji);					
								$rt_kijiji = str_replace('m:', '', $rt_kijiji);
							} else {
								$rt_kijiji = '';
							}
							
							if ($rt_kijiji != '') {
								$this->cl->save($rt_kijiji, 'k_search_ads_' . $query_hash);
								$x = simplexml_load_string($rt_kijiji);
								$count_ads = $x->Body->return_ad_count;
							} else {
								$count_ads = 0;
							}
						}
					}
				} else {
					$count_ads = 0;
				}
				
				// total
				$count_result = $count_ads + $count_matched;
				
				// the remix
				
				if ($count_result > 0) {
					$result_a = array();
					
					// db
					$unique_a = array();
					if ($count_matched > 0) {
						while ($Topic = mysql_fetch_object($rs)) {
							$Result = null;
							$Result->title = $Topic->tpc_title;
							$Result->type = 0;
							$Result->author = $Topic->usr_nick;
							$Result->excerpt = make_excerpt_topic($Topic, $query_task, $style_search_highlight);
							$Result->url = '/topic/view/' . $Topic->tpc_id . '.html';
							$Result->timestamp = $Topic->tpc_lasttouched;
							$Result->uid = $Topic->tpc_uid;
							$Result->hash = sha1($Result->title . $Result->author . $Result->excerpt);
							if (!in_array($Result->hash, $unique_a)) {
								$result_a[$Result->timestamp] = $Result;
								$unique_a[$Result->timestamp] = $Result->hash;
							}
						}
						mysql_free_result($rs);
						
						krsort($result_a, SORT_NUMERIC);
					} else {
						mysql_free_result($rs);
					}
					// xml
					
					if ($count_ads > 0) {
						
						for ($i = 0; $i < $count_ads; $i++) {
							$Result = null;
							$Result->title = strval($x->Body->ad[$i]->title);
							
							$Result->type = 1;
							$Result->author = '客齐集';
							$Result->excerpt = make_excerpt_ad($x->Body->ad[$i]->description, $query_task, $style_search_highlight);
							$Result->url = strval($x->Body->ad[$i]->view_ad_url);
							$Result->timestamp = format_api_date($x->Body->ad[$i]->start_date);
							$Result->uid = 0;
							$Result->hash = sha1($Result->title . $Result->author . $Result->excerpt);
							if (isset($x->Body->ad[$i]->img_url[0])) {
								$_excerpt = '<img src="' . strval($x->Body->ad[$i]->img_url[0]) . '" width="75" height="75" class="thumbnail" align="left" />' . $Result->excerpt;
								$Result->excerpt = $_excerpt;
							}
							if (!in_array($Result->hash, $unique_a)) {
								$result_a[$Result->timestamp] = $Result;
								$unique_a[$Result->timestamp] = $Result->hash;
							}
						}
						
						krsort($result_a, SORT_NUMERIC);
					}
					$this->cl->save(serialize($result_a), 'k_search_' . $query_hash);
				}
			}

			// page
			
			$p = array();
			$p['base'] = '/q/' . implode('+', $query_task) .'/';
			$p['ext'] = '.html';
			$p['items'] = $count_result;
			$p['size'] = BABEL_MSG_PAGE;
			$p['span'] = BABEL_PG_SPAN;
			if (($p['items'] % $p['size']) == 0) {
				$p['total'] = $p['items'] / $p['size'];
			} else {
				$p['total'] = floor($p['items'] / $p['size']) + 1;
			}
			if (isset($_GET['p'])) {
				$p['cur'] = intval($_GET['p']);
			} else {
				$p['cur'] = 1;
			}
			if ($p['cur'] < 1) {
				$p['cur'] = 1;
			}
			if ($p['cur'] > $p['total']) {
				$p['cur'] = $p['total'];
			}
			if (($p['cur'] - $p['span']) >= 1) {
				$p['start'] = $p['cur'] - $p['span'];
			} else {
				$p['start'] = 1;
			}
			
			if (($p['cur'] + $p['span']) <= $p['total']) {
				$p['end'] = $p['cur'] + $p['span'];
			} else {
				$p['end'] = $p['total'];
			}
			
			$p['offset'] = ($p['cur'] - 1) * $p['size'];
			
			if ($count_result > $p['size']) {
				$result_b = array_slice($result_a, $p['offset'], $p['size'], true);
			} else {
				$result_b = $result_a;
			}
			
			$time_end = microtime_float();
			$time_elapsed = $time_end - $time_start;
		}
		echo('<div id="main"><div class="blank" align="left">');
		_v_ico_map();
		echo(' <a href="/">' . Vocabulary::site_name . '</a> &gt; ' . Vocabulary::action_search . '</div><div class="blank" align="left"><span class="text_large"><img src="' . CDN_IMG . 'ico_search.gif" class="home" align="absmiddle" />' . Vocabulary::action_search . '</span><form action="/search.php" method="get">');
		if ($stage == 2) {
			$query_return = make_single_return($query);
			echo('<input type="text" name="q" id="k_search_q" onmouseover="this.focus()" class="search" value="' . $query_return . '"/>');
		} else {
			echo('<input type="text" name="q" id="k_search_q" onmouseover="this.focus()" class="search" />');
		}
		switch ($stage) {
			case 2:
				printf("<br /><span class=\"tip\">搜索为你找到了 %d 条匹配“%s”的结果，耗时 %.3f 秒</span>", $count_result, make_plaintext(implode(' ', $query_verified)), $time_elapsed);
				break;
			case 3:
				echo('<br /><span class="tip">你所查询的关键字“' . implode(' ', $query_common) . '”太常见</span>');
				break;
			case 0:
			case 1:
			default:
				echo('<span class="tip"></span>');
				break;
		}
		echo('<br /><br /><input type="image" src="' . CDN_IMG . 'graphite/search.gif" /></form></div>');
		
		if (($stage == 2) && ($count_result > 0)) {
			echo('<table width="100%" border="0" cellpadding="0" cellspacing="2" class="board">');
			if (DICT_API_ENABLED == 'yes') {
				if (preg_match('/[a-z0-9]/i', $query)) {
					$d = new Net_Dict;
					$d->setCache(true, 'file', array('cache_dir' => BABEL_PREFIX . '/cache/dict/'));
					$defs_a = $d->define($query, 'xdict');
					if (!PEAR::isError($defs_a)) {
						if (count($defs_a) > 0) {
							echo('<tr><td colspan="2" height="18" class="shead">&nbsp;');
							echo(format_def(mb_convert_encoding($defs_a[0]['definition'], 'UTF-8', 'GBK')));
							if (preg_match('/^[a-zA-Z]+$/', $query)) {
								echo('<span class="tip_i"><small> ... learn more on <a href="http://' . strtolower($query) . '.livid.cn/" target="_blank" class="t">http://' . strtolower($query) . '.livid.cn/</a></small></span>');
							}
							echo('</td></tr>');
						} else {
							$this->vxSearchSubstanceSpell($query, $d);
						}
					} else {
						$this->vxSearchSubstanceSpell($query, $d, 1);
					}
				}
			}
			if ($p['total'] > 1) {
				echo('<tr><td align="left" height="30" class="hf" colspan="2" style="border-bottom: 1px solid #CCC;">');
				$this->vxDrawPages($p);
				echo('</td></tr>');
			}
			
			$j = 0;
			foreach ($result_b as $Result) {
				$j++;
				if ($j == 1) {
					echo('<tr><td colspan="2" height="10"></td></tr>');
				}
				if ($Result->type == 1) {
					$img = 'mico_ad.gif';
				} else {
					if ($Result->uid == $this->User->usr_id) {
						$img = 'star_active.png';
					} else {
						$img = 'mico_topic.gif';
					}
				}
				echo('<tr><td width="24" height="18" valign="top" align="center" class="star"><img src="' . CDN_IMG . $img . '" /></td>');
				if ($Result->type == 1) {
					$_target = '_blank';
				} else {
					$_target = '_self';
				}
				echo('<td height="18" class="star"><a href="' . $Result->url . '" class="blue" target="' . $_target . '">' . make_plaintext($Result->title) . '</a> - <a href="/u/' . urlencode($Result->author) . '">' . make_plaintext($Result->author) . '</a></td></tr>');
				if (strlen($Result->excerpt) > 0) {
					echo('<tr><td width="24"></td><td class="hf"><span class="excerpt">');
					echo ($Result->excerpt);
					echo('</span></td></tr>');
				}
				echo('<tr><td width="24"></td><td valign="top"><span class="tip"><span class="green">');
				if ($Result->type == 0) {
					echo($_SERVER['SERVER_NAME'] . $Result->url);
				} else {
					echo($Result->url);
				}
				echo(' - ' . date('Y年n月j日', $Result->timestamp) . '</span></td></tr>');
				echo('<tr><td colspan="2" height="10"></td></tr>');
			}
			if ($p['total'] > 1) {
				echo('<tr><td align="left" height="30" class="hf" colspan="2" style="border-top: 1px solid #CCC;">');
				$this->vxDrawPages($p);
				echo('</td></tr>');
			}
			echo('</table>');
		} else {
			if (isset($query)) {
				if (DICT_API_ENABLED == 'yes') {
					if (preg_match('/[a-z0-9]/i', $query)) {
						$d = new Net_Dict;
						$d->setCache(true, 'file', array('cache_dir' => BABEL_PREFIX . '/cache/dict/'));
						$defs_a = @$d->define($query);
						if (!PEAR::isError($defs_a)) {
							if (count($defs_a) > 0) {
								echo('<table width="100%" border="0" cellpadding="0" cellspacing="2" class="board">');
								echo('<tr><td class="shead">&nbsp;' . format_def(mb_convert_encoding($defs_a[0]['definition'], 'UTF-8', 'GBK')));
								if (preg_match('/^[a-zA-Z]+$/', $query)) {
									echo('<span class="tip_i"><small> ... learn more on <a href="http://' . strtolower($query) . '.livid.cn/" target="_blank" class="top">http://' . strtolower($query) . '.livid.cn/</a></small></span>');
								}
								echo('</td></tr>');
								echo('</table>');
							} else {
								$this->vxSearchSubstanceSpell($query, $d, 0);
							}
						} else {
							$this->vxSearchSubstanceSpell($query, $d, 0);
						}
					}
				}
			}
		}
		echo('</div>');
	}
	
	private function vxSearchSubstanceSpell($word, $d, $style = 1, $s = 'lev', $stop = 0) {
		$words_a = $d->match($word, $s);
		if (!PEAR::isError($words_a)) {
			if ($style == 0) {
				echo('<table width="100%" border="0" cellpadding="0" cellspacing="2" class="board">');
				echo('<tr><td class="shead"><span class="tip">或许你要拼的单词是');
				$i = 0;
				foreach ($words_a as $w) {
					$i++;
					if ($i < 5) {
						$css_color = rand_color();
						if ($i == 1) {$sign = '&nbsp;&gt;&nbsp;';} else {$sign = '&nbsp;/&nbsp;';}
						echo($sign . '<a href="/q/' . $w['word'] . '" style="color: ' . $css_color . '" class="var">' . $w['word'] . '</a>');
					}
				}
				echo('</span></td></tr>');
				echo('</table>');
			} else {
				echo('<tr><td colspan="2" class="shead"><span class="tip">或许你要拼的单词是');
				$i = 0;
				foreach ($words_a as $w) {
					$i++;
					if ($i < 5) {
						$css_color = rand_color();
						if ($i == 1) {$sign = '&nbsp;&gt;&nbsp;';} else {$sign = '&nbsp;/&nbsp;';}
						echo($sign . '<a href="/q/' . $w['word'] . '" style="color: ' . $css_color . '" class="var">' . $w['word'] . '</a>');
					}
				}
				echo('</span></td></tr>');
			}
		} else {
			if ($stop != 1) {
				$this->vxSearchSubstanceSpell($word, $d, $style, 'soundex', 1);
			}
		}
	}
	
	/* E module: Search Substance block */
	
	/* S module: Denied bundle */
	
	public function vxDeniedBundle() {
		$this->vxHead($msgSiteTitle = Vocabulary::term_accessdenied);
		$this->vxBodyStart();
		$this->vxTop();
		$this->vxContainer('denied');
	}
	
	public function vxTopicEraseDeniedBundle($Topic) {
		$this->vxHead($msgSiteTitle = Vocabulary::term_accessdenied);
		$this->vxBodyStart();
		$this->vxTop();
		$this->vxContainer('topic_erase_denied', $Topic);
	}
	
	public function vxBoardViewDeniedBundle($Board) {
		$this->vxHead($msgSiteTitle = Vocabulary::term_accessdenied);
		$this->vxBodyStart();
		$this->vxTop();
		$this->vxContainer('board_view_denied', $Board);
	}
	
	/* E module: Denied bundle */
	
	/* S module: Denied block */
	
	public function vxDenied() {
		echo('<div id="main">');
		echo('<div class="blank" align="left">');
		_v_ico_map();
		echo(' <a href="/">' . Vocabulary::site_name . '</a> &gt; <strong>' . Vocabulary::term_accessdenied . '</strong></div>');
		echo('<div class="blank" align="left"><span class="text_large"><img src="' . CDN_IMG . 'ico_bomb.gif" align="absmiddle" class="home" />Access Denied</span><br />你在一个你不应该到达的地方，停止你的任何无意义的尝试吧<br /><br />我知道我正位于一个战场，因此我将会为一切的杀戮和战争做好准备</div>');
		echo('</div>');
	}
	
	public function vxTopicEraseDenied($Topic) {
		echo('<div id="main">');
		echo('<div class="blank" align="left">');
		_v_ico_map();
		echo(' <a href="/">' . Vocabulary::site_name . '</a> &gt; <strong>' . Vocabulary::term_accessdenied . '</strong></div>');
		echo('<div class="blank" align="left"><span class="text_large"><img src="/img/ico_bomb.gif" align="absmiddle" class="home" />本主题的擦除功能被禁止</span><br />你不能对本主题进行擦除，是由于以下原因：');
		_v_hr();
		if ($this->User->usr_id != $Topic->tpc_uid) {
			echo('<div class="geo_home_entry_odd">&nbsp;&nbsp;&nbsp;&nbsp;<img src="/img/gt.gif" align="absmiddle" />&nbsp;你所要擦除的主题并不属于你</div>');
		}
		if ((time() - $Topic->tpc_created) > (86400 * 31)) {
			echo('<div class="geo_home_entry_odd">&nbsp;&nbsp;&nbsp;&nbsp;<img src="/img/gt.gif" align="absmiddle" />&nbsp;该主题创建于一个月前，你不能对创建时间超过一个月的主题进行擦除</div>');
		}
		if ($Topic->tpc_posts > 2) {
			echo('<div class="geo_home_entry_odd">&nbsp;&nbsp;&nbsp;&nbsp;<img src="/img/gt.gif" align="absmiddle" />&nbsp;该主题已有 ' . $Topic->tpc_posts . ' 个回复，你不能擦除已有至少 1 个回复的主题</div>');
		}
		_v_hr();
		echo('<img src="/img/pico_left.gif" align="absmiddle" /> 返回主题 <a href="/topic/view/' . $Topic->tpc_id . '.html" class="t">' . make_plaintext($Topic->tpc_title) . '</a>');
		_v_d_e();
		_v_d_e();
	}
	
	public function vxBoardViewDenied($Board) {
		$Section = new Node($Board->nod_sid, $this->db);
		echo('<div id="main">');
		echo('<div class="blank" align="left">');
		_v_ico_map();
		echo(' <a href="/">' . Vocabulary::site_name . '</a> &gt; <a href="/go/' . $Section->nod_name . '">' . make_plaintext($Section->nod_title) . '</a> &gt; <a href="/go/' . $Board->nod_name . '">' . make_plaintext($Board->nod_title) . '</a> &gt; <strong>' . Vocabulary::term_accessdenied . '</strong></div>');
		echo('<div class="blank" align="left"><img src="/img/icons/silk/stop.png" align="absmiddle" /> 对讨论区的访问被禁止');
		_v_hr();
		echo('你不能访问本讨论区中的内容，是由于以下原因：');
		_v_hr();
		if ($this->User->vxIsLogin()) {
			echo('<div class="geo_home_entry_odd">&nbsp;&nbsp;&nbsp;&nbsp;<img src="/img/gt.gif" align="absmiddle" />&nbsp;你没有访问本讨论区的授权</div>');
		} else {
			echo('<div class="geo_home_entry_odd">&nbsp;&nbsp;&nbsp;&nbsp;<img src="/img/gt.gif" align="absmiddle" />&nbsp;你尚未登录，请先 <a href="/login"><img src="/img/graphite/login.gif" border="0" alt="' . $this->lang->login() . '" align="absmiddle" /></a></div>');
		}
		_v_hr();
		echo('<img src="/img/pico_left.gif" align="absmiddle" /> <a href="/">返回首页</a>');
		_v_d_e();
		_v_d_e();
	}
	
	/* E module: Denied block */
	
	/* S module: Section View block */
	
	public function vxSectionView($section_id) {
		global $GOOGLE_AD_LEGAL;
		$Node = new Node($section_id, $this->db);
		echo('<div id="main">');
		echo('<div class="blank">');
		_v_ico_map();
		echo(' <a href="/">' . Vocabulary::site_name . '</a> &gt; ' . $Node->nod_title . '</div>');
		echo('<div class="blank" align="left"><span class="text_large"><img src="' . CDN_IMG . 's/' . $Node->nod_name . '.gif" align="absmiddle" align="" class="ico" />' . $Node->nod_title . '</span></div>');
		echo('<table width="100%" border="0" cellpadding="0" cellspacing="2" class="board">');
		echo('<tr><td width="360" align="left" class="hf" valign="top">' . $Node->nod_header . '</td><td align="right" class="hf" colspan="2">');
		_v_btn_l('创建新主题', '/topic/new/' . $Node->nod_id . '.vx');
		echo('</td></tr>');
		echo('<tr>');
		
		// The latest topics
		
		$sql = "SELECT nod_id FROM babel_node WHERE nod_sid = {$section_id}";
		$rs = mysql_query($sql, $this->db);
		$board_count = mysql_num_rows($rs);
		$board_ids = '';
		$i = 0;
		while ($Board = mysql_fetch_object($rs)) {
			$i++;
			if ($i == $board_count) {
				$board_ids = $board_ids . $Board->nod_id;
			} else {
				$board_ids = $board_ids . $Board->nod_id . ', ';
			}
		}
		mysql_free_result($rs);
		
		echo('<td align="left" valign="top" class="container">');
		echo('<table width="100%" cellpadding="0" cellspacing="0" border="0" class="drawer">');
		
		echo('<tr><td height="18" class="blue">最新主题</td></tr>');
		$sql = "SELECT tpc_id, tpc_pid, tpc_uid, tpc_title, tpc_hits, tpc_posts, tpc_created FROM babel_topic WHERE tpc_pid IN ({$board_ids}) AND tpc_flag IN (0, 2) AND tpc_pid NOT IN " . BABEL_NODES_POINTLESS . " ORDER BY tpc_lasttouched DESC LIMIT 60";
		$rs = mysql_query($sql, $this->db);
		$i = 0;
		while ($Topic = mysql_fetch_object($rs)) {
			$i++;
			$css_font_size = $this->vxGetItemSize($Topic->tpc_posts);
			if ($Topic->tpc_posts > 3) {
				$css_color = rand_color();
			} else {
				$css_color = rand_gray(2, 4);
			}
			if ((time() - $Topic->tpc_created) < 86400) {
				$img_star = 'bunny.gif';
			} else {
				if ($Topic->tpc_uid == $this->User->usr_id) {
					$img_star = 'star_active.png';
				} else {
					$img_star = 'star_inactive.png';
				}
			}
			$feedback = '<small class="aqua">' . $Topic->tpc_hits . '</small>/<small class="fade">' . $Topic->tpc_posts . '</small>';
			if (($i % 2) == 0) {
				echo('<tr><td class="even" height="20"><img src="' . CDN_IMG . $img_star . '" align="absmiddle" />&nbsp;<a href="/topic/view/' . $Topic->tpc_id . '.html" target="_self" style="font-size: ' . $css_font_size . 'px; color: ' . $css_color . ';" class="var">' . make_plaintext($Topic->tpc_title) . '</a>&nbsp;');
				echo('<span class="tip_i"><small class="aqua">... ' . $feedback . '</small></span>');
			} else {
				echo('<tr><td class="odd" height="20"><img src="' . CDN_IMG . $img_star . '" align="absmiddle" />&nbsp;<a href="/topic/view/' . $Topic->tpc_id . '.html" target="_self" style="font-size: ' . $css_font_size . 'px; color: ' . $css_color . ';" class="var">' . make_plaintext($Topic->tpc_title) . '</a>&nbsp;');
				echo('<span class="tip_i"><small class="aqua">... ' . $feedback . '</small></span>');
			}
			echo('</td></tr>');
		}
		mysql_free_result($rs);
		
		echo('<tr><td height="18" class="orange">最热主题 Top 10</td></tr>');
		$sql = "SELECT tpc_id, tpc_pid, tpc_uid, tpc_title, tpc_hits, tpc_posts FROM babel_topic WHERE tpc_pid IN ({$board_ids}) AND tpc_flag IN (0, 2) ORDER BY tpc_posts DESC LIMIT 10";
		$rs = mysql_query($sql, $this->db);
		$i = 0;
		while ($Topic = mysql_fetch_object($rs)) {
			$i++;
			$css_font_size = $this->vxGetItemSize($Topic->tpc_posts);
			if ($Topic->tpc_posts > 3) {
				$css_color = rand_color();
			} else {
				$css_color = rand_gray(2, 4);
			}
			if ($Topic->tpc_uid == $this->User->usr_id) {
				$img_star = 'star_active.png';
			} else {
				$img_star = 'star_inactive.png';
			}
			$feedback = '<small class="aqua">' . $Topic->tpc_hits . '</small>/<small class="fade">' . $Topic->tpc_posts . '</small>';
			if (($i % 2) == 0) {
				echo('<tr><td class="even" height="20"><img src="' . CDN_IMG . $img_star . '" align="absmiddle" />&nbsp;<a href="/topic/view/' . $Topic->tpc_id . '.html" target="_self" style="font-size: ' . $css_font_size . 'px; color: ' . $css_color . ';" class="var">' . make_plaintext($Topic->tpc_title) . '</a>');
				echo('<span class="tip_i"><small class="aqua">... ' . $feedback . '</small></span>');
			} else {
				echo('<tr><td class="odd" height="20"><img src="' . CDN_IMG . $img_star . '" align="absmiddle" />&nbsp;<a href="/topic/view/' . $Topic->tpc_id . '.html" target="_self" style="font-size: ' . $css_font_size . 'px; color: ' . $css_color . ';" class="var">' . make_plaintext($Topic->tpc_title) . '</a>');
				echo('<span class="tip_i"><small class="aqua">... ' . $feedback . '</small></span>');
			}
			echo('</td></tr>');
		}
		mysql_free_result($rs);
		echo('</table></td>');
		// The best boards
		
		echo('<td width="25%" align="left" valign="top" class="container" style="border-left: 1px solid #CCC;"><table width="100%" cellpadding="0" cellspacing="0" border="0" class="drawer"><tr><td height="18" class="orange">热门讨论区</td></tr>');
		$sql = "SELECT nod_id, nod_name, nod_title, nod_topics FROM babel_node WHERE nod_sid = {$section_id} ORDER BY nod_topics DESC, nod_created ASC LIMIT 80";
		$rs = mysql_query($sql, $this->db);
		$i = 0;
		while ($Board = mysql_fetch_object($rs)) {
			$css_font_size = $this->vxGetMenuSize($Board->nod_topics);
			$css_color = rand_color();
			$i++;
			if (($i % 2) == 0) {
				echo('<tr><td class="even" height="20"><a href="/go/' . $Board->nod_name . '" style="font-size: ' . $css_font_size . 'px; color: ' . $css_color . ';" target="_self" class="var">' . $Board->nod_title . '</a>&nbsp;<small class="grey">... ' . $Board->nod_topics . '</small></td></tr>');
			} else {
				echo('<tr><td class="odd" height="20"><a href="/go/' . $Board->nod_name . '" style="font-size: ' . $css_font_size . 'px; color: ' . $css_color . ';" target="_self" class="var">' . $Board->nod_title . '</a>&nbsp;<small class="grey">... ' . $Board->nod_topics . '</small></td></tr>');
			}
		}
		mysql_free_result($rs);
		echo('</table></td>');
		
		// Random boards
		
		echo('<td width="25%" align="left" valign="top" class="container" style="border-left: 1px solid #CCC;"><table width="100%" cellpadding="0" cellspacing="0" border="0" class="drawer"><tr><td height="18" class="apple">随机讨论区</td></tr>');
		$sql = "SELECT nod_id, nod_title, nod_name, nod_topics FROM babel_node WHERE nod_sid = {$section_id} ORDER BY rand() LIMIT 80";
		$rs = mysql_query($sql, $this->db);
		$i = 0;
		while ($Board = mysql_fetch_object($rs)) {
			$css_font_size = $this->vxGetMenuSize($Board->nod_topics);
			$css_color = rand_color();
			$i++;
			if (($i % 2) == 0) {
				echo('<tr><td class="even" height="20"><a href="/go/' . $Board->nod_name . '" style="font-size: ' . $css_font_size . 'px; color: ' . $css_color . ';" class="var" target="_self">' . $Board->nod_title . '</a>&nbsp;<small class="grey">... ' . $Board->nod_topics . '</small></td></tr>');
			} else {
				echo('<tr><td class="odd" height="20"><a href="/go/' . $Board->nod_name . '" style="font-size: ' . $css_font_size . 'px; color: ' . $css_color . ';" class="var" target="_self">' . $Board->nod_title . '</a>&nbsp;<small class="grey">... ' . $Board->nod_topics . '</small></td></tr>');
			}
		}
		mysql_free_result($rs);
		echo('</table></td>');
		echo('</tr>');
		echo('<tr><td colspan="3" align="left" class="hf" valign="top">' . $Node->nod_footer . '</td></tr>');
		
		/* S ultimate cool Flickr */

		if ($this->User->usr_id == 1) {
			$f = Image::vxFlickrBoardBlock($Node->nod_name, $this->User->usr_width, 3);
			echo $f;
			$this->cl->save($f, 'board_flickr_' . $Node->nod_name);
		} else {
			if ($f = $this->cl->load('board_flickr_' . $Node->nod_name)) {
				echo $f;
			} else {
				$f = Image::vxFlickrBoardBlock($Node->nod_name, $this->User->usr_width, 3);
				echo $f;
				$this->cl->save($f, 'board_flickr_' . $Node->nod_name);
			}
		}
		
		/* E ultimate cool Flickr */
		
		echo('</table>');
		echo('<div class="_hh"></div>');
		echo('</div>');
	}
	
	/* E module: Section View block */
	
	/* S module: Status block */
	
	public function vxStatus() {
		echo('<div id="main">');
		echo('<div class="blank">');
		_v_ico_map();
		echo(' <a href="/">' . Vocabulary::site_name . '</a> &gt; ' . Vocabulary::term_status . '</div>');
		echo('<div class="blank" align="left"><span class="text_large"><img src="' . CDN_IMG . 'ico_tv.gif" align="absmiddle" class="home" />' . Vocabulary::term_status . '</span>');
		$rs = mysql_query('SHOW STATUS', $this->db);
		$status = array();
		while ($row = mysql_fetch_assoc($rs)) {
			$status[$row['Variable_name']] = $row['Value'];
		}
		mysql_free_result($rs);
		$rs = mysql_query('SHOW VARIABLES', $this->db);
		while ($row = mysql_fetch_assoc($rs)) {
			$status[$row['Variable_name']] = $row['Value'];
		}
		mysql_free_result($rs);

		if ($_SESSION['babel_ua']['GECKO_DETECTED'] || $_SESSION['babel_ua']['KHTML_DETECTED'] || $_SESSION['babel_ua']['OPERA_DETECTED']) {
			$hack_width = 'width="100%" ';
		} else {
			$hack_width = 'width="99%" ';
		}
		
		echo('<table ' . $hack_width . 'cellpadding="0" cellspacing="0" border="0" class="fav">');
		
		echo('<tr><td colspan="2" align="left"><span class="text_large"><img src="' . CDN_IMG . 'ico_db.gif" align="absmiddle" class="home" />数据库子系统 MySQL ' . mysql_get_server_info($this->db) . '</span></td></tr>');
		
		echo('<tr><td colspan="2" align="left"><span class="tip">数据库系统信息</span></td></tr>');
		
		echo('<tr><td width="150" align="right" class="section_even">服务器字符集</td><td class="section_even">' . $status['collation_server'] . '</td></tr>');
		echo('<tr><td width="150" align="right" class="section_odd">当前数据库字符集</td><td class="section_odd">' . $status['collation_database'] . '</td></tr>');
		echo('<tr><td width="150" align="right" class="section_even">运转时间</td><td class="section_even">' . $status['Uptime'] . ' 秒');
		if ($status['Uptime'] > 86400) {
			echo ('（' . intval($status['Uptime'] / 86400) . ' 天）');
		}
		echo('</td></tr>');
		
		echo('<tr><td colspan="2" align="left" class="section_odd"><span class="tip">性能数据</span></td></tr>');
		
		
		echo('<tr><td width="150" align="right" class="section_even">线程创建数量</td><td class="section_even">' . $status['Threads_created'] . '（每分钟 ');
		printf("%.2f", $status['Threads_created'] / ($status['Uptime'] / 60));
		echo('）</td></tr>');
		
		echo('<tr><td width="150" align="right" class="section_odd">已处理的查询数量</td><td class="section_odd">' . $status['Questions'] . '（每分钟 ');
		printf("%.2f", $status['Questions'] / ($status['Uptime'] / 60));
		echo('）</td></tr>');
		
		echo('<tr><td width="150" align="right" class="section_even">可用缓存内存</td><td class="section_even">');
		printf("%dKB",  floatval($status['Qcache_free_memory'] / 1024));
		echo('</td></tr>');
		echo('<tr><td width="150" align="right" class="section_odd">缓存中的查询数据</td><td class="section_odd">' . $status['Qcache_queries_in_cache'] . '</td></tr>');
		
		echo('<tr><td width="150" align="right" class="section_even">插入缓存的查询数量</td><td class="section_even">' . $status['Qcache_inserts'] . '（每分钟 ');
		printf("%.2f", $status['Qcache_inserts'] / ($status['Uptime'] / 60));
		echo('）</td></tr>');
		
		echo('<tr><td width="150" align="right" class="section_odd">命中缓存的查询数量</td><td class="section_odd">' . $status['Qcache_hits'] . '（每分钟 ');
		printf("%.2f", $status['Qcache_hits'] / ($status['Uptime'] / 60));
		echo('）</td></tr>');

		echo('<tr><td width="150" align="right" class="section_even">无法缓存的查询数量</td><td class="section_even">' . $status['Qcache_not_cached'] . '（每分钟 ');
		printf("%.2f", $status['Qcache_not_cached'] / ($status['Uptime'] / 60));
		echo('）</td></tr>');
		
		echo('<tr><td width="150" align="right" class="section_odd">缓存命中率</td><td class="section_odd">');
		printf("%.3f%%", ($status['Qcache_hits'] / $status['Questions']) * 100);
		echo('</td></tr>');
		
		echo('<tr><td colspan="2" align="left" class="section_odd"><span class="text_large"><img src="' . CDN_IMG . 'ico_mac.gif" align="absmiddle" class="home" />基础架构 ');
		$flag_win = false;
		if (strtolower(PHP_OS) == 'winnt') {
			echo 'Windows NT';
			$flag_win = true;
		} else {
			echo shell_exec('uname -s');
		}
		echo('</span></td></tr>');
		
		echo('<tr><td colspan="2" align="left" class="section_even"><small><strong>OS</strong>: ');
		echo $flag_win ? 'Windows NT' : shell_exec('uname -a');
		echo('</small></td></tr>');
		echo('<tr><td colspan="2" align="left" class="section_odd"><small><strong>Machine Architecture</strong>: ');
		echo $flag_win ? 'x86' : shell_exec('uname -m');
		echo('</small></td></tr>');
		echo('<tr><td colspan="2" align="left" class="section_even"><small><strong>Uptime</strong>: ');
		echo $flag_win ? 'unknown' : shell_exec('uptime');
		echo('</small></td></tr>');
		echo('</table>');
		echo('</div>');
		echo('</div>');
	}
	
	/* E module: Status block */
	
	/* S module: Jobs/Kijiji block */
	
	public function vxJobsKijiji() {
		echo('<div id="main">');
		echo('<div class="blank" align="left"><span class="text_large"><img src="' . CDN_IMG . 'ico_hiring.gif" align="absmiddle" class="home" />' . Vocabulary::term_jobs_kijiji . '</span><br />');
		echo("客齐集是全球电子商务领袖 eBay 于 2005 年初成立的全资子公司，中国区办公室设于上海。<br /><br />");
		echo('客齐集专注于创造一个氛围良好的网上社区，大家居住于这个社区中，互相帮助，免费发布与个人生活息息相关的个人广告，或者是寻求同伴和交流。客齐集为实现这一目标而默默创造着。<br /><br />');
		echo('<img src="' . CDN_IMG . 'open.gif" align="left" style="margin-right: 10px;" />');
		echo('在经过了一年多的发展之后，我们发现为了实现这一目标，我们需要更多的伙伴来加入我们。如果你是一位经验丰富的程序设计师，或者是极具艺术灵感的计算机美术设计师，同时认同我们的奋斗目标，则我们非常欢迎你的加入！<br /><br />');
		echo('以下是目前我们开放招聘的职位的描述及条件，如果你觉得自己能够胜任这份工作，则在每个职位的描述的末尾你可以看到一个电子邮件地址，你可以将你的简历及薪资要求发到那个电子邮件地址。在简历中请附上你的电话号码，如果我们觉得你确实适合某个职位，则我们将在你投简历之后的一个星期内用电话的方式通知你进行面试，感谢你的参与和配合。<br /><br />');
		echo('<span class="tip">特别提示 － 如果你是在 V2EX 社区中看到下面的职位描述而投的简历，请在邮件中特别注明，我们将优先处理来自 V2EX 社区的简历</span>');
		echo('</div>');
		$jobs_path = BABEL_PREFIX . '/jobs';
		$jobs = scandir(BABEL_PREFIX . '/jobs');
		foreach ($jobs as $job) {
			if (!in_array($job, array('.', '..', '.svn', 'archive'))) {
				$x = simplexml_load_file($jobs_path . '/' . $job);
				echo('<div class="blank" align="left">');
				echo('<span class="text_large">' . $x->title . '</span><br /><br />');
				echo($x->description);
				echo('<br /><br />');
				echo('<ul class="menu">');
				echo('<li>Responsibilities Will Include:</li>');
				echo('<ul class="items">');
				foreach ($x->xpath('//rs') as $rs) {
					echo('<li>' . $rs . '</li>');
				}
				echo('</ul>');
				echo('<li>Job Requirements:</li>');
				echo('<ul class="items">');
				foreach ($x->xpath('//rq') as $rs) {
					echo('<li>' . $rs . '</li>');
				}
				echo('</ul>');
				echo('</ul>');
				echo('Please send your resume in English & Chinese to <a href="mailto:' . $x->mailto . '">' . $x->mailto . '</a>');
				if (strval($x->fulltime) == 'yes') {
					echo('<br /><br /><small><span class="tip">This is a full-time position in Shanghai. We do not have internships available.</span></small>');
				}
				echo('</div>');
			}
		}
	}
	
	/* E module: Jobs block */
	
	/* S module: Community Guidelines block */
	
	public function vxCommunityGuidelines() {
		echo('<div id="main">');
		echo('<div class="blank">');
		_v_ico_map();
		echo(' <a href="/">' . Vocabulary::site_name . '</a> &gt; ' . Vocabulary::term_community_guidelines . '</div>');
		echo('<div class="blank">');
		include(BABEL_PREFIX . '/res/community_guidelines.html');
		echo('</div>');
	}
	
	/* E module: Community Guidelines block */
	
	/* S module: Partners block */
	
	public function vxPartners() {
		echo('<div id="main">');
		echo('<div class="blank">');
		_v_ico_map();
		echo(' <a href="/">' . Vocabulary::site_name . '</a> &gt; ' . Vocabulary::term_partners . '</div>');
		echo('<div class="blank">');
		include(BABEL_PREFIX . '/res/partners.html');
		echo('</div>');
	}
	
	/* E module: Partners block */
	
	/* S module: New Features block */
	
	public function vxNewFeatures() {
		echo('<div id="main">');
		echo('<div class="blank">');
		_v_ico_map();
		echo(' <a href="/">' . Vocabulary::site_name . '</a> &gt; ' . Vocabulary::term_newfeatures . '</div>');
		include(BABEL_PREFIX . '/res/new_features.html');
		echo('</div>');
	}
	
	/* E module: New Features block */
	
	/* S module: There is more than one way to do it block */
	
	public function vxTIMTOWTDI() {
		echo('<div id="main">');
		echo('<div class="blank">');
		_v_ico_map();
		echo(' <a href="/">' . Vocabulary::site_name . '</a> &gt; ' . $this->lang->timtowtdi() . '</div>');
		include(BABEL_PREFIX . '/res/timtowtdi.html');
		echo('</div>');
	}
	
	/* E module: There is more than one way to do it block */
	
	/* S module: Rules block */
	
	public function vxRules() {
		echo('<div id="main">');
		echo('<div class="blank">');
		_v_ico_map();
		echo(' <a href="/">' . Vocabulary::site_name . '</a> &gt; ' . Vocabulary::term_rules . '</div>');
		echo('<div class="blank">');
		include(BABEL_PREFIX . '/res/rules.html');
		echo('</div>');
	}
	
	/* E module: Rules block */
	
	/* S module: Terms block */
	
	public function vxTerms() {
		echo('<div id="main">');
		echo('<div class="blank">');
		_v_ico_map();
		echo(' <a href="/">' . Vocabulary::site_name . '</a> &gt; ' . Vocabulary::term_terms . '</div>');
		echo('<div class="blank">');
		include(BABEL_PREFIX . '/res/terms.html');
		echo('</div>');
	}
	
	/* E module: Terms block */
	
	/* S module: Privacy block */
	
	public function vxPrivacy() {
		echo('<div id="main">');
		echo('<div class="blank">');
		_v_ico_map();
		echo(' <a href="/">' . Vocabulary::site_name . '</a> &gt; ' . Vocabulary::term_privacy . '</div>');
		echo('<div class="blank">');
		include(BABEL_PREFIX . '/res/privacy.html');
		echo('</div>');
	}
	
	/* E module: Privacy block */
	
	/* S module: Policies block */
	
	public function vxPolicies() {
		echo('<div id="main">');
		echo('<div class="blank">');
		_v_ico_map();
		echo(' <a href="/">' . Vocabulary::site_name . '</a> &gt; ' . Vocabulary::term_policies . '</div>');
		echo('<div class="blank">');
		include(BABEL_PREFIX . '/res/policies.html');
		echo('</div>');
	}
	
	/* E module: Policies block */
	
	/* S module: Sorry block */
	
	public function vxSorry($what) {
		echo('<div id="main">');
		switch ($what) {
			default:
			case 'money':
				echo('<div class="blank">');
				_v_ico_map();
				echo(' <a href="/">' . Vocabulary::site_name . '</a> &gt; ' . Vocabulary::term_out_of_money . '</div>');
				echo('<div class="blank">');
				include(BABEL_PREFIX . '/res/sorry_money.html');
				break;
		}
		echo('</div>');
	}
	
	/* E module: Sorry block */
	
	/* S module: Signup block */
	
	public function vxSignup() {
		Image::vxGenConfirmCode();
		echo('<div id="main">');
		echo('<div class="blank">');
		_v_ico_map();
		echo(' <a href="/">' . Vocabulary::site_name . '</a> &gt; ' . $this->lang->register() . '</div>');
		echo('<div class="blank" align="left">');
		echo('<span class="text_large"><img src="' . CDN_IMG . 'ico_id.gif" align="absmiddle" class="home" />会员注册信息填写</span>');
		echo('<table cellpadding="0" cellspacing="0" border="0" class="form">');
		echo('<form action="/user/create.vx" method="post" id="usrNew">');
		echo('<tr><td width="200" align="right">电子邮件</td><td width="200" align="left"><input tabindex="1" type="text" maxlength="100" class="sl" name="usr_email" /></td>');
		echo('<td width="150" rowspan="8" valign="middle" align="right"><input tabindex="7" type="image" src="' . CDN_IMG . 'silver/btn_signup.gif" alt="' . Vocabulary::action_signup . '" tabindex="5" /></td></tr>');
		echo('<tr><td width="200" align="right">昵称</td><td align="left"><input tabindex="2" type="text" maxlength="20" class="sl" name="usr_nick" /></td></tr>');
		echo('<tr><td width="200" align="right">密码</td><td align="left"><input tabindex="3" type="password" maxlength="32" class="sl" name="usr_password" /></td></tr>');
		echo('<tr><td width="200" align="right">重复密码</td><td align="left"><input tabindex="4" type="password" maxlength="32" class="sl" name="usr_confirm" /></td></tr>');
		echo('<tr><td width="200" align="right" valign="top">性别</td><td align="left"><select tabindex="5" maxlength="20" size="6" name="usr_gender"><option value="0" selected="selected">未知</option><option value="1">男性</option><option value="2">女性</option><option value="5">女性改（变）为男性</option><option value="6">男性改（变）为女性</option><option value="9">未说明</option></select></td></tr>');
		echo('<tr><td width="200" align="right">确认码</td><td align="left"><input tabindex="6" type="password" maxlength="32" class="sl" name="c" /></td></tr><tr><td width="200" align="right"></td><td align="left"><div class="important"><img src="/c/' . rand(1111,9999) . '.' . rand(1111,9999) . '.png" /><ol class="items"><li>请按照上图输入确认码</li><li>确认码不区分大小写</li><li>确认码中不包含数字</li><li>专为人类设计</li></ul></div></td></tr>');
		echo('</form></table></div>');
		echo('<div class="blank"><img src="' . CDN_IMG . 'ico_tip.gif" align="absmiddle" class="ico" />点击“注册新会员”，即表示你同意我们的 [ <a href="/terms.vx">' . Vocabulary::term_terms . '</a> ] 和 [ <a href="/privacy.vx">' . Vocabulary::term_privacy . '</a> ]</div>');
		echo('</div>');
	}
	
	/* E module: Signup block */
	
	/* S module: Login block */
	
	public function vxLogin($rt) {
		echo('<div id="main">');
		echo('<div class="blank" align="left">');
		_v_ico_map();
		echo(' <a href="/">' . Vocabulary::site_name . '</a> &gt; ' . Vocabulary::action_login . '</div>');
		switch ($rt['target']) {
		
			// default
			
			default:
			case 'welcome':
				if (strlen($rt['return']) > 0) {
					echo('<div class="blank" align="left"><span class="text_large"><img src="' . CDN_IMG . 'ico_important.gif" align="absmiddle" class="home" />你所请求的页面需要你先进行登录</span>');
				} else {
					echo('<div class="blank" align="left"><span class="text_large"><img src="' . CDN_IMG . 'ico_id.gif" align="absmiddle" class="home" />会员登录</span>');
				}
				echo('<table cellpadding="0" cellspacing="0" border="0" class="form">');
				echo('<form action="http://' . BABEL_DNS_NAME . '/login.vx" method="post" id="Login">');
				if (strlen($rt['return']) > 0) {
					echo('<input type="hidden" name="return" value="' . make_single_return($rt['return']) . '" />');
				}
				echo('<tr><td width="200" align="right">电子邮件或昵称</td><td width="200" align="left"><input type="text" maxlength="100" class="sl" name="usr" tabindex="1" /></td><td width="150" rowspan="2" valign="middle" align="right"><input type="image" src="' . CDN_IMG . 'graphite/login.gif" alt="' . Vocabulary::action_login . '" /></td></tr><tr><td width="200" align="right">密码</td><td align="left"><input type="password" maxlength="32" class="sl" name="usr_password" tabindex="2" /></td></tr></form></table></div>');
				echo('<div class="blank"><img src="' . CDN_IMG . 'ico_important.gif" align="absmiddle" class="ico" /><a href="/passwd.vx">忘记密码了？点这里找回你的密码</a></div>');
				echo('<div class="blank"><img src="' . CDN_IMG . 'ico_tip.gif" align="absmiddle" class="ico" />会话有效时间为一个月，超过此时间之后你将需要重新登录</div>');
				break;
			
			// ok
			
			case 'ok':
				$this->User->vxUpdateLogin();
				$p = array();
				$sql = "SELECT COUNT(tpc_id) FROM babel_topic WHERE tpc_uid = {$this->User->usr_id}";
				$rs = mysql_query($sql, $this->db);
				$p['items'] = mysql_result($rs, 0, 0);
				mysql_free_result($rs);
				echo('<div class="blank" align="left"><span class="text_large"><img src="' . CDN_IMG . 'ico_login.gif" align="absmiddle" class="home" />欢迎回来，' . $this->User->usr_nick . '</span><br />你一共在 ' . Vocabulary::site_name . ' 社区创建了 ' . $p['items'] . ' 个主题，下面是你最新创建或被回复了的一些！</div>');
				if ($p['items'] > 0) {
					echo('<table width="100%" border="0" cellpadding="0" cellspacing="2" class="board">');
					$sql = "SELECT nod_id, nod_title, tpc_id, tpc_pid, tpc_uid, tpc_title, tpc_hits, tpc_posts, tpc_created, tpc_lastupdated, tpc_lasttouched FROM babel_node, babel_topic WHERE tpc_pid = nod_id AND tpc_uid = {$this->User->usr_id} ORDER BY tpc_posts DESC, tpc_lasttouched DESC, tpc_created DESC LIMIT 20";
					$rs = mysql_query($sql, $this->db);
					$i = 0;
					while ($Topic = mysql_fetch_object($rs)) {
						$i++;
						$css_color = rand_color();
						echo('<tr>');
						echo('<td width="24" height="24" align="center" valign="middle" class="star"><img src="' . CDN_IMG . 'star_active.png" /></td>');
						if ($i % 2 == 0) {
							$css_class = 'even';
						} else {
							$css_class = 'odd';
						}
						echo('<td class="' . $css_class . '" height="24" align="left"><a href="/topic/view/' . $Topic->tpc_id . '.html" style="color: ' . $css_color . ';" class="var" target="_self">' . make_plaintext($Topic->tpc_title) . '</a>&nbsp;');
						if ($Topic->tpc_posts > 0) {
							echo('<small class="fade">(' . $Topic->tpc_posts . ')</small>');
						}
						echo('<small class="grey">+' . $Topic->tpc_hits . '</small>');
						echo('</td>');
						echo('<td class="' . $css_class . '" width="120" height="24" align="left"><a href="/board/view/' . $Topic->nod_id . '.html">' . $Topic->nod_title . '</a></td>');
						if ($Topic->tpc_lasttouched > $Topic->tpc_created) {
							echo('<td class="' . $css_class . '" width="120" height="24" align="left"><small class="time">' . make_descriptive_time($Topic->tpc_lasttouched) . '</small></td>');
						} else {
							echo('<td class="' . $css_class . '" width="120" height="24" align="left"><small class="time">' . make_descriptive_time($Topic->tpc_created) . '</small></td>');
						}
						echo('</tr>');
					}
					mysql_free_result($rs);
					echo('</table>');
				}
				break;
				
			// something wrong

			case 'error':
				echo('<div class="blank" align="left"><span class="text_large"><img src="' . CDN_IMG . 'ico_important.gif" align="absmiddle" class="home" />对不起，你刚才提交的信息里有些错误</span><table cellpadding="0" cellspacing="0" border="0" class="form"><form action="http://' . BABEL_DNS_NAME . '/login.vx" method="post" id="Login">');
				if (strlen($rt['return']) > 0) {
					echo('<input type="hidden" name="return" value="' . make_single_return($rt['return']) . '" />');
				}
				if ($rt['usr_error'] != 0) {
					echo('<tr><td width="200" align="right" valign="top">电子邮件或昵称</td><td align="left"><div class="error"><input type="text" maxlength="100" class="sl" name="usr" tabindex="1" value="' . make_single_return($rt['usr_value']) . '" />&nbsp;<img src="' . CDN_IMG . 'sico_error.gif" align="absmiddle" /><br />' . $rt['usr_error_msg'][$rt['usr_error']] . '</div>');
				} else {
					echo('<tr><td width="200" align="right">电子邮件或昵称</td><td align="left"><input type="text" maxlength="100" class="sl" name="usr" tabindex="1" value="' . make_single_return($rt['usr_value']) .  '" />');
				}
				echo('<td width="150" rowspan="2" valign="middle" align="right"><input type="image" src="' . CDN_IMG . 'graphite/login.gif" alt="' . Vocabulary::action_login . '" /></td></tr>');
				if ($rt['usr_password_error'] > 0 && $rt['usr_error'] == 0) {
					echo('<tr><td width="200" align="right" valign="top">密码</td><td align="left"><div class="error"><input type="password" maxlength="32" class="sl" name="usr_password" tabindex="2" />&nbsp;<img src="' . CDN_IMG . 'sico_error.gif" align="absmiddle" /><br />' . $rt['usr_password_error_msg'][$rt['usr_password_error']] . '</div></td></tr>');
				} else {
					echo('<tr><td width="200" align="right">密码</td><td align="left"><input type="password" maxlength="32" class="sl" name="usr_password" tabindex="2" /></td></tr>');
				}
				echo('</form></table></div>');
				echo('<div class="blank"><img src="/img/ico_important.gif" align="absmiddle" class="ico" /><a href="/passwd.vx">忘记密码了？点这里找回你的密码</a></div>');
				echo('<div class="blank"><img src="/img/ico_tip.gif" align="absmiddle" class="ico" />会话有效时间为一个月，超过此时间之后你将需要重新登录</div>');
				break;
		}
		echo('</div>');
	}
	
	/* E module: Login block */
	
	/* S module: Logout block */
	
	public function vxLogout() {
		echo('<div id="main">');
		echo('<div class="blank" align="left">');
		_v_ico_map();
		echo(' <a href="/">' . Vocabulary::site_name . '</a> &gt; ' . Vocabulary::action_logout . '</div>');
		echo('<div class="blank" align="left"><span class="text_large"><img src="/img/ico_logout.gif" align="absmiddle" class="home" />你已经从 ' . Vocabulary::site_name . ' 登出</span><br />感谢你访问 ' . Vocabulary::site_name . '，你现在已经从 ' . Vocabulary::site_name . ' 完全登出，没有任何的个人信息被留在你当前使用过的计算机上。');
		_v_hr();
		echo('<img src="/img/pico_right.gif" align="absmiddle" />&nbsp;<a href="/login.vx">重新登录</a></div>');
		echo('</div>');
	}
	
	/* E module: Logout block */
	
	/* S module: Passwd block */
	
	public function vxPasswd($options) {
		echo('<div id="main">');
		echo('<div class="blank" align="left">');
		_v_ico_map();
		echo(' <a href="/">' . Vocabulary::site_name . '</a> &gt; ' . Vocabulary::action_passwd . '</div>');
		switch ($options['mode']) {
			default:
			case 'get':
				echo('<div class="blank" align="left"><span class="text_large"><img src="/img/ico_recover.gif" align="absmiddle" class="home" />通过电子邮件找回密码</span>');
				echo('<table cellpadding="0" cellspacing="0" border="0" class="form">');
				echo('<form action="/passwd.vx" method="post" id="form_passwd">');
				echo('<tr><td width="200" align="right">电子邮件</td><td width="200" align="left"><input type="text" maxlength="100" class="sl" name="usr" tabindex="1" /></td><td width="150" rowspan="2" valign="middle" align="right">');
				_v_btn_f('找回密码', 'form_passwd');
				echo('</td></tr></form></table>');
				_v_hr();
				echo('<img src="/img/ico_tip.gif" align="absmiddle" class="home" />你可以通过输入注册时候使用的电子邮件地址来找回密码<br />
		如果你输入的电子邮件地址确实存在的话，我们将试着向你注册时候使用的电子邮件地址发送一封包含特殊指令的邮件，点击邮件中的地址将让可以让你复位密码，在每 24 小时内，复位密码功能（包括发送邮件）只能使用 5 次<br /><br />如果你确信无法收到我们发送给你的邮件，请你向我们的技术支持 ' . BABEL_AM_SUPPORT . ' 发送一封邮件详细描述你所遇到的问题</div>');
				break;
			case 'key':
				echo('<div class="blank" align="left"><span class="text_large"><img src="/img/ico_recover.gif" align="absmiddle" class="home" />请输入新密码</span>');
				echo('<table cellpadding="0" cellspacing="0" border="0" class="form">');
				echo('<form action="/passwd.vx" method="post" id="form_passwd">');
				echo('<input type="hidden" value="' . $options['key'] . '" name="key" />');
				echo('<tr><td width="200" align="right">新密码</td><td width="200" align="left"><input type="password" maxlength="100" class="sl" name="usr_password" tabindex="1" /></td><td width="150" rowspan="2" valign="middle" align="right">');
				_v_btn_f('重设密码', 'form_passwd');
				echo('</td></tr><tr><td width="200" align="right">重复密码</td><td align="left"><input type="password" tabindex="2" maxlength="32" class="sl" name="usr_confirm" /></td></tr></form></table>');
				_v_hr();
				echo('<img src="/img/ico_tip.gif" align="absmiddle" class="ico" />请输入新密码两遍之后，点击 [ 重设密码 ] 为会员 <span class="tip"><em>' . $options['target']->usr_nick . '</em></span> 重新设置密码</div>');
				break;
			case 'reset':
				if ($options['rt']['errors'] == 0) {
					echo('<div class="blank" align="left"><span class="text_large"><img src="/img/ico_smile.gif" class="home" align="absmiddle" />密码已经更新，现在请使用新密码登录</span>');
					echo('<table cellpadding="0" cellspacing="0" border="0" class="form">');
					echo('<form action="/login.vx" method="post" id="form_login">');
					echo('<tr><td width="200" align="right">电子邮件或昵称</td><td width="200" align="left"><input type="text" maxlength="100" class="sl" name="usr" tabindex="1" /></td><td width="150" rowspan="2" valign="middle" align="right"><input type="image" src="/img/graphite/login.gif" alt="' . Vocabulary::action_login . '" /></td></tr><tr><td width="200" align="right">密码</td><td align="left"><input type="password" maxlength="32" class="sl" name="usr_password" tabindex="2" /></td></tr></form></table>');
					_v_hr();
					echo('<img src="/img/ico_tip.gif" align="absmiddle" class="ico" />会话有效时间为一个月，超过此时间之后你将需要重新登录</div>');
				} else {
					echo('<span class="text_large"><img src="/img/ico_recover.gif" align="absmiddle" class="home" />请输入新密码</span>');
					echo('<table cellpadding="0" cellspacing="0" border="0" class="form">');
					echo('<form action="/passwd.vx" method="post" id="form_passwd">');
					echo('<input type="hidden" value="' . $options['key'] . '" name="key" />');
					/* S result: usr_password and usr_confirm */
					
					/* pswitch:
					a => p0 c0
					b => p1 c1
					c => p1 c0
					d => p0 c1 */
					
					switch ($options['rt']['pswitch']) {
						default:
						case 'a':
							echo('<tr><td width="200" align="right">新密码</td><td width="200" align="left"><div class="error"><input type="password" maxlength="32" class="sl" name="usr_password" tabindex="1" />&nbsp;<img src="/img/sico_error.gif" align="absmiddle" /><br />' . $options['rt']['usr_password_error_msg'][$options['rt']['usr_password_error']] . '</div></td><td width="150" rowspan="2" valign="middle" align="right">');
							_v_btn_f('重设密码', 'form_passwd');
							echo('</td></tr>');
							echo('<tr><td width="200" align="right">重复密码</td><td width="200" align="left"><input type="password" maxlength="32" class="sl" name="usr_confirm" tabindex="2" /></td></tr>');
							break;
						case 'b':
							if ($options['rt']['usr_password_error'] == 0) {
								if ($options['rt']['usr_confirm_error'] != 0) {
									echo('<tr><td width="200" align="right">新密码</td><td width="200" align="left"><input type="password" maxlength="32" class="sl" name="usr_password" value="' . make_single_return($options['rt']['usr_password_value']) . '" tabindex="1" /></td><td width="150" rowspan="2" valign="middle" align="right">');
									_v_btn_f('重设密码', 'form_passwd');
									echo('</td></tr>');
									echo('<tr><td width="200" align="right">重复新密码</td><td width="200" align="left"><div class="error"><input type="password" maxlength="32" class="sl" name="usr_confirm" tabindex="2" />&nbsp;<img src="/img/sico_error.gif" align="absmiddle" /><br />' . $options['rt']['usr_confirm_error_msg'][$options['rt']['usr_confirm_error']] . '</div></td></tr>');
								} else {
									echo('<tr><td width="200" align="right">新密码</td><td width="200" align="left""><input type="password" maxlength="32" class="sl" name="usr_password" value="' . make_single_return($options['rt']['usr_password_value']) . '" tabindex="1" />&nbsp;<img src="/img/sico_ok.gif" align="absmiddle" alt="ok" /></td><td width="150" rowspan="2" valign="middle" align="right">');
									_v_btn_f('重设密码', 'form_passwd');
									echo('</td></tr>');
									echo('<tr><td width="200" align="right">重复新密码</td><td width="200" align="left""><input type="password" maxlength="32" class="sl" name="usr_confirm" value="' . make_single_return($options['rt']['usr_confirm_value']) . '" tabindex="2" />&nbsp;<img src="/img/sico_ok.gif" align="absmiddle" alt="ok" /></td></tr>');
								}
							} else {
								echo('<tr><td width="200" align="right">新密码</td><td width="200" align="left"><div class="error"><input type="password" maxlength="32" class="sl" name="usr_password" tabindex="1" />&nbsp;<img src="/img/sico_error.gif" align="absmiddle" /><br />' . $options['rt']['usr_password_error_msg'][$options['rt']['usr_password_error']] . '</div></td><td width="150" rowspan="2" valign="middle" align="right">');
								_v_btn_f('重设密码', 'form_passwd');
								echo('</td></tr>');
								echo('<tr><td width="200" align="right">重复新密码</td><td width="200" align="left"><input type="password" maxlength="32" class="sl" name="usr_confirm" tabindex="2" /></td></tr>');
							}
							break;
						case 'c':
							echo('<tr><td width="200" align="right">新密码</td><td width="200" align="left"><input type="password" maxlength="32" class="sl" name="usr_password" value="' . make_single_return($options['rt']['usr_password_value']) . '" tabindex="1" /></td><td width="150" rowspan="2" valign="middle" align="right">');
							_v_btn_f('重设密码', 'form_passwd');
							echo('</td></tr>');
							echo('<tr><td width="200" align="right">重复新密码</td><td width="200" align="left"><div class="error"><input type="password" maxlength="32" class="sl" name="usr_confirm" tabindex="2" />&nbsp;<img src="/img/sico_error.gif" align="absmiddle" /><br />' . $options['rt']['usr_confirm_error_msg'][$options['rt']['usr_confirm_error']] . '</div></td></tr>');
							break;
						case 'd':
							echo('<tr><td width="200" align="right">新密码</td><td width="200" align="left"><div class="error"><input type="password" maxlength="32" class="sl" name="usr_password" tabindex="1" />&nbsp;<img src="/img/sico_error.gif" align="absmiddle" /><br />' . $options['rt']['usr_password_error_msg'][$options['rt']['usr_password_error']] . '</div></td><td width="150" rowspan="2" valign="middle" align="right">');
							_v_btn_f('重设密码', 'form_passwd');
							echo('</td></tr>');
							echo('<tr><td width="200" align="right">重复新密码</td><td width="200" align="left"><input type="password" maxlength="32" class="sl" name="usr_confirm" value="' . make_single_return($options['rt']['usr_confirm_value']) . '" tabindex="2" /></td></tr>');
							break;
					}
					
					/* E result: usr_password and usr_confirm */
					
					echo('</form></table>');
					_v_hr();
					echo('<img src="/img/ico_tip.gif" align="absmiddle" class="ico" />请输入新密码两遍之后，点击 [ 重设密码 ] 为会员 <span class="tip"><em>' . $options['target']->usr_nick . '</em></span> 重新设置密码</div>');
				}
				break;
			case 'post':
				$rt = array();
				$rt['err'] = 0;
				$rt['ok'] = false;
				$rt['err_msg'] = array(1 => '请输入电子邮件地址', 2 => '只能在 24 小时内取回密码 ' . BABEL_PASSWD_LIMIT . ' 次', 3 => '电子邮件地址不正确');
				
				if (isset($_POST['usr'])) {
					$usr = trim($_POST['usr']);
					if (strlen($usr) > 0) {
						$usr = mysql_real_escape_string(strtolower($usr), $this->db);
						$sql = "SELECT usr_id, usr_email, usr_password FROM babel_user WHERE usr_email = '{$usr}'";
						$rs = mysql_query($sql, $this->db) or die(mysql_error());
						if (mysql_num_rows($rs) == 1) {
							$O = mysql_fetch_object($rs);
							mysql_free_result($rs);
							$rt['target'] = new User($O->usr_email, $O->usr_password, $this->db, false);
							$rt['key'] = $this->vxPasswdKey($rt['target']);
							$_now = time();
							$_oneday = $_now - 86400;
							$sql = "SELECT COUNT(pwd_id) FROM babel_passwd WHERE pwd_uid = {$rt['target']->usr_id} AND pwd_created > {$_oneday}";
							$rs = mysql_query($sql, $this->db);
							$_count = intval(mysql_result($rs, 0, 0)) + 1;
							$rs = mysql_free_result($rs);
							if ($_count > BABEL_PASSWD_LIMIT) {
								$rt['err'] = 2;
							} else {
								$sql = "INSERT INTO babel_passwd(pwd_uid, pwd_hash, pwd_ip, pwd_created) VALUES({$rt['target']->usr_id}, '{$rt['key']}', '{$_SERVER['REMOTE_ADDR']}', {$_now})";
								mysql_query($sql, $this->db);
								
								if (mysql_affected_rows($this->db) == 1) {
									$mail = array();
									$mail['subject'] = '[' . Vocabulary::site_name . ' 密码找回] 找回你在 ' . Vocabulary::site_name . ' 的密码';
									$mail['body'] = "{$rt['target']->usr_nick}，你好！\n\n你刚才在 " . Vocabulary::site_name . " 申请找回你丢失的密码，因此我们发送此邮件给你。\n\n请点击下面的链接地址（或将此链接地址复制到浏览器地址栏中访问），然后设置你的新密码：\n\nhttp://" . BABEL_DNS_NAME . "/passwd/" . $rt['key'] . "\n\n此链接地址有效时间为 24 小时。\n\n如果这次密码找回申请不是由你提起的，你可以安全地忽略此邮件。这不会对你的原来的密码造成任何影响。\n\n作为一个安全提示，此次密码找回申请是由 IP 地址 " . $_SERVER['REMOTE_ADDR'] . " 提起的。" . BABEL_AM_SIGNATURE;
									$am = new Airmail($rt['target']->usr_email, $mail['subject'], $mail['body']);
									$am->vxSend();
									$am = null;
									$rt['ok'] = true;
								}
							}
							$O = null;
						} else {
							mysql_free_result($rs);
							$rt['err'] = 3;
						}
					} else {
						$rt['err'] = 1;
					}
				} else {
					$rt['err'] = 1;
				}
				
				if ($rt['err'] > 0) {
					echo('<div class="blank" align="left"><span class="text_large"><img src="/img/ico_important.gif" align="absmiddle" class="home" />出了一点问题</span>');
					echo('<table cellpadding="0" cellspacing="0" border="0" class="form">');
					echo('<form action="/passwd.vx" method="post" id="form_passwd">');
					echo('<tr><td width="200" align="right" valign="top">电子邮件</td><td width="200" align="left"><div class="error"><input type="text" maxlength="100" class="sl" name="usr" tabindex="1" />&nbsp;<img src="/img/sico_error.gif" align="absmiddle" /><br />' . $rt['err_msg'][$rt['err']] . '</div></td><td width="150" rowspan="2" valign="middle" align="right">');
					_v_btn_f('找回密码', 'form_passwd');
					echo('</td></tr></form></table>');
					_v_hr();
					echo('<img src="/img/ico_tip.gif" align="absmiddle" class="home" />你可以通过输入注册时候使用的电子邮件地址来找回密码<br />
		如果你输入的电子邮件地址确实存在的话，我们将试着向你注册时候使用的电子邮件地址发送一封包含特殊指令的邮件，点击邮件中的地址将让可以让你复位密码，在每 24 小时内，复位密码功能（包括发送邮件）只能使用 5 次<br /><br />由于电子邮件传输存在一些网络方面的延迟，因此如果你在点击了 [ 找回密码 ] 后无法收到邮件，请你稍微多等待几分钟。如果你确信无法收到我们发送给你的邮件，请你向我们的技术支持 ' . BABEL_AM_SUPPORT . ' 发送一封邮件详细描述你所遇到的问题</div>');
				} else {
					echo('<span class="text_large"><img src="/img/ico_recover.gif" align="absmiddle" class="home" />密码找回邮件已经发送</span>');
					echo('<br />现在请到你注册时候使用的电子邮箱中接收一封我们刚刚发送给你的的邮件，点击邮件中的链接地址即可复位密码<br /><br />邮件中的链接地址的有效时间为 24 小时，超过此时间后邮件中的链接地址将变得无效，然后你将需要重新提起密码回复申请');
				}
				break;
		}
		echo('</div>');
	}
	
	private function vxPasswdKey($User) {
		$a = rand(1000, 9999) * $User->usr_id + time();
		$b = rand(1000, 9999) + $User->usr_id;
		$c = rand(1000, 9999) * $User->usr_money;
		$d = rand(1000, 9999) + $User->usr_money;
		
		$e = substr(sha1($a), rand(0, 10), 9) . substr(md5($b), rand(0, 10), 9) . substr(sha1($c), rand(0, 10), 9) . substr(md5($d), rand(0, 10), 9);
		
		$s = strlen($e);
		
		$f = array();
		
		for ($i = 0; $i < $s; $i = $i + 3) {
			$f[] = substr($e, $i, 3);
		}
		
		$e = implode('-', $f);
	
		return $e;
	}
	
	/* E module: Passwd block */
	
	/* S module: User Home block */
	
	public function vxUserHome($options) {	
		$O =& $options['target'];
		if ($O->usr_id != $this->User->usr_id) {
			$this->User->vxAddHits($O->usr_id);
		}
		
		$img_p = $O->usr_portrait ? '/img/p/' . $O->usr_portrait . '.jpg' : '/img/p_' . $O->usr_gender . '.gif';
		
		$img_p_n = $O->usr_portrait ? '/img/p/' . $O->usr_portrait . '_n.jpg' : '/img/p_' . $O->usr_gender . '_n.gif';
		
		$sql = "SELECT onl_uri, onl_ip, onl_created, onl_lastmoved FROM babel_online WHERE onl_nick = '{$O->usr_nick}' ORDER BY onl_lastmoved DESC LIMIT 1";
		
		$rs = mysql_query($sql, $this->db);

		if ($Online = mysql_fetch_object($rs)) {
			$_flag_online = true;
			$_o = '当前在线，于 ' . make_descriptive_time($Online->onl_created) . '进入 ' . Vocabulary::site_name . '，最后活动时间是在 ' . make_descriptive_time($Online->onl_lastmoved);
		} else {
			$_flag_online = false;
			$_o = '当前不在线';
		}
		
		mysql_free_result($rs);
		
		echo('<div id="main">');
		echo('<div class="blank" align="left">');
		_v_ico_map();
		echo(' <a href="/">' . Vocabulary::site_name . '</a> ');
		if ($options['mode'] == 'random') {
			echo('&gt; 茫茫人海 ');
		}
		echo('&gt; ' . $O->usr_nick);
		if ($_flag_online) {
			echo(' <span class="tip_i">... <small class="lime">online now</small> @ <a href="' . $Online->onl_uri . '" class="t">' . $Online->onl_uri . '</a></span>');
		} else {
			echo(' <span class="tip_i">...</span> <small class="na">disconnected</small>');
		}
		echo('</div>');
		
		echo('<div class="blank"><span class="text"><img src="' . $img_p_n . '" class="portrait" align="absmiddle" /> ' . Vocabulary::site_name . ' 的第 <strong>' . $O->usr_id . '</strong> 号会员，' . $_o . '</span>');
		
		if ($_SESSION['babel_ua']['GECKO_DETECTED'] || $_SESSION['babel_ua']['KHTML_DETECTED'] || $_SESSION['babel_ua']['OPERA_DETECTED']) {
			
			$hack_width = 'width="100%" ';
		} else {
			$hack_width = 'width="99%" ';
		}
		
		echo('<table ' . $hack_width . 'cellpadding="0" cellspacing="0" border="0" class="fav">');
		echo('<tr>');
		
		$txt_gender = array();
		$txt_gender[0] = '性别未知，';
		$txt_gender[1] = '男，';
		$txt_gender[2] = '女，';
		$txt_gender[5] = '出生的时候是个女孩子，后来把性别改成了男的，';
		$txt_gender[6] = '出生的时候是个男孩子，后来把性别改成了女的，';
		$txt_gender[9] = '她，或者他，想对自己的性别保密，';
		
		$txt = $txt_gender[$O->usr_gender];
		
		if ($count_u = $this->cs->get('count_u_' . $O->usr_id)) {
			$count_u = unserialize($count_u);
		} else {
			$count_u = array();
			$sql = "SELECT count(tpc_id) AS tpc_count FROM babel_topic WHERE tpc_uid = {$O->usr_id}";
			
			$rs_count = mysql_query($sql, $this->db);
			
			$o_count = mysql_fetch_object($rs_count);
			mysql_free_result($rs_count);
			$count_u['tpc_count'] = $o_count->tpc_count;
			$o_count = null;
			
			$sql = "SELECT count(pst_id) AS pst_count FROM babel_post WHERE pst_uid = {$O->usr_id}";
			
			$rs_count = mysql_query($sql, $this->db);
			
			$o_count = mysql_fetch_object($rs_count);
			mysql_free_result($rs_count);
			$count_u['pst_count'] = $o_count->pst_count;
			$o_count = null;
			$this->cs->save(serialize($count_u), 'count_u_' . $O->usr_id);
		}
		
		$sql = "SELECT tpc_id, tpc_title, tpc_posts, tpc_created, nod_id, nod_title FROM babel_topic, babel_node WHERE tpc_pid = nod_id AND tpc_uid = {$O->usr_id} AND tpc_flag IN (0, 2) ORDER BY tpc_created DESC LIMIT 10";
		
		$rs_created = mysql_query($sql, $this->db);
		
		if ($_followed = $this->cs->get('babel_user_' . $O->usr_id . '_followed')) {
			$_followed = unserialize($_followed);
		} else {
			$sql = "SELECT DISTINCT pst_tid FROM babel_post WHERE pst_uid = {$O->usr_id} ORDER BY pst_created DESC LIMIT 10";
			$rs = mysql_query($sql, $this->db);
			if (mysql_num_rows($rs) > 0) {
				$_tids = array();
				while ($_a = mysql_fetch_array($rs)) {
					$_tids[] = $_a['pst_tid'];
				}
				mysql_free_result($rs);
				$_tids = implode(',', $_tids);
				$sql = "SELECT pst_id FROM babel_post WHERE pst_tid IN ({$_tids})";
				$rs = mysql_query($sql, $this->db);
				$_pids = array();
				while($_a = mysql_fetch_array($rs)) {
					$_pids[] = $_a['pst_id'];
				}
				mysql_free_result($rs);
				$_pids = implode(',', $_pids);
				$sql = "SELECT tpc_id, tpc_title, tpc_posts, tpc_lasttouched, nod_id, nod_title, pst_id, pst_created FROM ((babel_topic JOIN babel_node ON tpc_pid = nod_id) JOIN babel_post ON pst_tid = tpc_id) WHERE tpc_flag IN (0, 2) AND pst_id IN ({$_pids}) AND pst_uid = {$O->usr_id} ORDER BY pst_created DESC";
				$rs = mysql_query($sql, $this->db);
				$_followed = array();
				$i = 0;
				$_tids = array();
				while ($_t = mysql_fetch_array($rs)) {
					$i++;
					if (!in_array(strval($_t['tpc_id']), $_tids)) {
						$_tids[] = strval($_t['tpc_id']);
						$_followed[$i] = array();
						$_followed[$i]['tpc_id'] = $_t['tpc_id'];
						$_followed[$i]['tpc_title'] = $_t['tpc_title'];
						$_followed[$i]['tpc_title_plain'] = make_plaintext($_t['tpc_title']);
						$_followed[$i]['tpc_posts'] = $_t['tpc_posts'];
						$_followed[$i]['tpc_lasttouched'] = $_t['tpc_lasttouched'];
						$_followed[$i]['nod_id'] = $_t['nod_id'];
						$_followed[$i]['nod_title'] = $_t['nod_title'];
						$_followed[$i]['nod_title_plain'] = make_plaintext($_t['nod_title']);
						$_followed[$i]['pst_id'] = $_t['pst_id'];
						$_followed[$i]['pst_created'] = $_t['pst_created'];
					}
					unset($_t);
				}
			} else {
				mysql_free_result($rs);
				$_followed = array();
			}
			$this->cs->save(serialize($_followed), 'babel_user_' . $O->usr_id . '_followed');
		}

		$txt .= '在' . date(' Y 年 n 月', $O->usr_created) . '的时候来到 ' . Vocabulary::site_name . '，在过去创建了 <a href="/topic/archive/user/' . $O->usr_nick . '">' . $count_u['tpc_count'] . '</a> 个主题，发表了 ' . $count_u['pst_count'] . ' 篇回复，所在地为 [ <a href="/geo/' . $O->usr_geo . '" class="o">' . $this->Geo->map['name'][$O->usr_geo] . '</a> ]'; 
		
		if ($this->User->usr_id == $O->usr_id) {
			$txt .= '<br /><span class="tip_i">你正在察看的是自己的页面，你可以把它的地址发给你的朋友，和他们共享你在 ' . Vocabulary::site_name . ' 获得的快乐！</span>';
		}
		
		echo('<td width="95" align="left" valign="top" class="section_even"><img src="' . $img_p . '" class="portrait" /></td><td align="left" valign="top" class="section_even"><span class="text_large">' . $O->usr_nick . '</span>');
		
		echo('<span class="excerpt"><br /><br />' . $txt . '</span></td>');
		echo('</tr>');
		
		if ($this->User->usr_id == $O->usr_id) {
			echo('<tr><td colspan="2" align="center" class="section_odd"><img src="/img/icons/silk/house.png" align="absmiddle" />&nbsp;你的 V2EX 主页地址&nbsp;&nbsp;&nbsp;<input type="text" class="sll" onclick="this.select()" value="http://' . BABEL_DNS_NAME . '/u/' . urlencode($O->usr_nick) . '" readonly="readonly" />&nbsp;&nbsp;&nbsp;<span class="tip_i">... 本页一共被访问了 ' . $O->usr_hits . ' 次</span></td></tr>');
		}
		
		if ($O->usr_brief != '') {
			echo('<tr><td colspan="2" align="center" class="section_even"><span class="text_large"><img src="/img/quote_left.gif" align="absmiddle" />&nbsp;' . make_plaintext($O->usr_brief) . '&nbsp;<img src="/img/quote_right.gif" align="absmiddle" /></span></td></tr>');
		}
		
		echo('<tr><td colspan="2" align="center" class="section_odd"><span class="tip_i"><img src="' . CDN_UI . 'img/icons/silk/clock.png" align="absmiddle" alt="ZEN" />&nbsp;<a href="/zen/' . $O->usr_nick . '" class="var" style="color: ' . rand_color() . ';">' . $O->usr_nick . ' 的 ZEN</a>&nbsp;&nbsp;|&nbsp;&nbsp;<img src="' . CDN_UI . 'img/icons/silk/comments.png" alt="Topics" align="absmiddle" />&nbsp;<a href="/topic/archive/user/' . $O->usr_nick . '" class="var" style="color: ' . rand_color() . ';">' . $O->usr_nick . ' 的所有主题</a>&nbsp;&nbsp;|&nbsp;&nbsp;<img src="' . CDN_UI . 'img/icons/silk/heart_add.png" align="absmiddle" />&nbsp;<a href="/who/connect/' . urlencode($O->usr_nick) . '" class="var" style="color: ' . rand_color() . ';">谁把 ' . $O->usr_nick . ' 加为好友</a>&nbsp;&nbsp;|&nbsp;&nbsp;<img src="' . CDN_UI . 'img/icons/silk/feed.png" align="absmiddle" alt="RSS" />&nbsp;<a href="/feed/user/' . urlencode($O->usr_nick) . '" class="var" style="color: ' . rand_color() . '">RSS 种子输出</a></span></tr>');
		
		echo('<tr><td colspan="2" align="left" class="section_odd"><span class="text_large"><img src="/img/ico_savepoint.gif" align="absmiddle" class="home" />' . $O->usr_nick . ' 的网上据点<a name="svp" /></span></td></tr>');
		
		$msgs = array(0 => '新据点添加失败，你可以再试一次，或者是到 <a href="/go/babel">Developer Corner</a> 向我们报告错误', 1 => '新据点添加成功', 2 => '你刚才想添加的据点已经存在于你的列表中', 3 => '目前，每个人只能添加至多 ' . BABEL_SVP_LIMIT . ' 个据点，你可以试着删除掉一些过去添加的，我们正在扩展系统的能力以支持更多的据点', 4 => '要删除的据点不存在', 5 => '你不能删除别人的据点', 6 => '据点删除成功', 7 => '据点删除失败，你可以再试一次，或者是到 <a href="/go/babel">Developer Corner</a> 向我们报告错误', 9 => '不需要输入前面的 http:// 协议名称，直接添加网址就可以了，比如 www.livid.cn 这样的地址');
		
		if (isset($_GET['msg'])) {
			echo('<tr><td colspan="2" class="section_odd"><div class="notify">');
			$msg = intval($_GET['msg']);
			switch ($msg) {
				case 0:
					echo $msgs[0];
					break;
				case 1:
					echo $msgs[1];
					break;
				case 2:
					echo $msgs[2];
					break;
				case 3:
					echo $msgs[3];
					break;
				case 4:
					echo $msgs[4];
					break;
				case 5:
					echo $msgs[5];
					break;
				case 6:
					echo $msgs[6];
					break;
				case 7:
					echo $msgs[7];
					break;
				default:
					echo $msgs[9];
					break;
			}
			echo('</div></td></tr>');
			$savepoints = array();
			$sql = "SELECT svp_id, svp_url, svp_rank FROM babel_savepoint WHERE svp_uid = {$O->usr_id} ORDER BY svp_url";
			$rs = mysql_query($sql, $this->db);
			while ($Savepoint = mysql_fetch_object($rs)) {
				$savepoints[$Savepoint->svp_id] = $Savepoint;
			}
			mysql_free_result($rs);
			$this->cs->save(serialize($savepoints), 'babel_user_savepoints_' . $O->usr_nick);		
		} else {
			if ($savepoints = $this->cs->get('babel_user_savepoints_' . $O->usr_nick)) {
				$savepoints = unserialize($savepoints);
			} else {
				$savepoints = array();
				$sql = "SELECT svp_id, svp_url, svp_rank FROM babel_savepoint WHERE svp_uid = {$O->usr_id} ORDER BY svp_url";
				$rs = mysql_query($sql, $this->db);
				while ($Savepoint = mysql_fetch_object($rs)) {
					$savepoints[$Savepoint->svp_id] = $Savepoint;
				}
				mysql_free_result($rs);
				$this->cs->save(serialize($savepoints), 'babel_user_savepoints_' . $O->usr_nick);
			}
		}
		
		$i = 0;
		foreach ($savepoints as $svp_id => $S) {
			$i++;
			$css_color = rand_color();
			$css_class = $i % 2 ? 'section_even' : 'section_odd';
			$o = $this->Validator->vxGetURLHost($S->svp_url);
			echo('<tr><td colspan="2" align="left" class="' . $css_class . '"><span class="svp"><img src="' . CDN_UI . 'img/fico_' . $o['type'] . '.gif" align="absmiddle" />&nbsp;&nbsp;<a href="http://' . htmlspecialchars(strip_quotes($S->svp_url)) . '" target="_blank" rel="external nofollow" style="color: ' . $css_color . '" class="var">http://' . htmlspecialchars(strip_quotes($S->svp_url)) . '</a>&nbsp;&nbsp;</span>');
			if ($this->User->usr_id == $O->usr_id) {
				echo('<span class="tip_i"> ... <a href="/savepoint/erase/' . $S->svp_id . '.vx" class="g">X</a></span>');
			}
			echo('</td></tr>');
		}

		if ($this->User->vxIsLogin() && $this->User->usr_id == $O->usr_id) {
			$i++;
			$css_class = $i % 2 ? 'section_even' : 'section_odd';
			echo('<form action="/recv/savepoint.vx" method="post"><tr><td colspan="2" align="left" class="' . $css_class . '">你可以为自己添加一个新的网上据点&nbsp;&nbsp;<span class="tip_i">http://&nbsp;<input type="text" onmouseover="this.focus();" name="url" class="sll" />&nbsp;&nbsp;<input type="image" align="absmiddle" src="/img/silver/sbtn_add.gif" /></span><div class="notify" style="margin-top: 5px;">');
			echo $msgs[9];
			echo('</div></td></tr></form>');
		}
		
		echo('<tr><td colspan="2" align="left" class="section_odd"><span class="text_large"><img src="/img/ico_friends.gif" align="absmiddle" class="home" />' . $O->usr_nick . ' 的朋友们</span>');
		
		if (isset($_GET['do'])) {
			$do = strtolower(make_single_safe($_GET['do']));
			if (!in_array($do, array('add', 'remove'))) {
				$do = false;
			}
		} else {
			$do = false;
		}
		
		if ($this->User->usr_id != $O->usr_id && $this->User->vxIsLogin()) {
			if ($do) {
				if ($do == 'add') {
					$sql = "SELECT frd_id, frd_uid, frd_fid FROM babel_friend WHERE frd_uid = {$this->User->usr_id} AND frd_fid = {$O->usr_id}";
					$rs = mysql_query($sql);
					if (mysql_num_rows($rs) == 0) {
						mysql_free_result($rs);
						$sql = "INSERT INTO babel_friend(frd_uid, frd_fid, frd_created, frd_lastupdated) VALUES({$this->User->usr_id}, {$O->usr_id}, " . time() . ", " . time() . ")";
						mysql_query($sql);
						$txt_friend = '<span class="tip_i">&nbsp;&nbsp;&nbsp;你已经把 ' . $O->usr_nick . ' 加为了好友</span>';
					} else {
						mysql_free_result($rs);
						$txt_friend = '<span class="tip">&nbsp;&nbsp;&nbsp;<a href="/friend/remove/' . $O->usr_nick . '" class="g">把 ' . $O->usr_nick . ' 从好友列表中去掉</a></span>';
					}
				}
				if ($do == 'remove') {
					$sql = "SELECT frd_id, frd_uid, frd_fid FROM babel_friend WHERE frd_uid = {$this->User->usr_id} AND frd_fid = {$O->usr_id}";
					$rs = mysql_query($sql);
					if (mysql_num_rows($rs) == 1) {
						mysql_free_result($rs);
						$sql = "DELETE FROM babel_friend WHERE frd_uid = {$this->User->usr_id} AND frd_fid = {$O->usr_id}";
						mysql_query($sql);
						$txt_friend = '<span class="tip_i">&nbsp;&nbsp;&nbsp;你已经把 ' . $O->usr_nick . ' 移出了好友列表</span>';
					} else {
						mysql_free_result($rs);
						$txt_friend = '<span class="tip">&nbsp;&nbsp;&nbsp;<a href="/friend/connect/' . $O->usr_nick . '" class="g">把 ' . $O->usr_nick . ' 加为好友！</a></span>';
					}
				}
			} else {
				$sql = "SELECT frd_id, frd_uid, frd_fid FROM babel_friend WHERE frd_uid = {$this->User->usr_id} AND frd_fid = {$O->usr_id}";
				$rs = mysql_query($sql);
				
				if (mysql_num_rows($rs) == 1) {
					$txt_friend = '<span class="tip">&nbsp;&nbsp;&nbsp;<a href="/friend/remove/' . $O->usr_nick . '" class="g">把 ' . $O->usr_nick . ' 从好友列表中去掉</a></span>';
				} else {
					$txt_friend = '<span class="tip">&nbsp;&nbsp;&nbsp;<a href="/friend/connect/' . $O->usr_nick . '" class="g">把 ' . $O->usr_nick . ' 加为好友！</a></span>';
				}
			}
		} else {
			$txt_friend = '&nbsp;&nbsp;';
		}
		
		if ($this->User->vxIsLogin() && $O->usr_id != $this->User->usr_id) {
			$txt_msg = '<span class="tip">&nbsp;&nbsp;<a href="#;" class="g" onclick="sendMessage(' . $O->usr_id . ');">向 ' . $O->usr_nick . ' 发送短消息</a></span>';
		} else {
			$txt_msg = '&nbsp;&nbsp;';
		}

		echo $txt_friend;
		echo $txt_msg;

		if ($this->User->vxIsLogin() && $this->User->usr_id == 1) {
			$sql = "SELECT usr_id, usr_password FROM babel_user WHERE usr_password = 'DISABLED' AND usr_id = {$O->usr_id}";
			$_rs = mysql_query($sql);
			if ($_u = mysql_fetch_object($_rs)) {
				$txt_duid = '<span class="tip_i">&nbsp;&nbsp;该会员已经被暂时禁止</span>';
			} else {
				if ($O->usr_id != 1) {
					$txt_duid = '<span class="tip">&nbsp;&nbsp;<a href="#;" onclick="if (confirm(' . "'确认要暂时禁止该会员？'" . ')) { location.href=' . "'/d/uid/{$O->usr_id}'; } else { return false; }" . '" class="g">暂时禁止该会员</a></span>';
				} else {
					$txt_duid = '';
				}
			}
			$_rs = null;
			
			if ($O->usr_id != 1) {
				$txt_dtuid = '<span class="tip">&nbsp;&nbsp;<a href="#;" onclick="if (confirm(' . "'确认要擦除该会员的所有 0 回复主题？'" . ')) { location.href=' . "'/dt/uid/{$O->usr_id}'; } else { return false; }" . '" class="g">擦除该会员的所有 0 回复主题</a></span>';
			} else {
				$txt_dtuid = '';
			}
			
			echo $txt_duid . $txt_dtuid;
		}
		
		echo('</td></tr>');
		
		echo ('<tr><td colspan="2">');
		
		$edges = array();
		for ($i = 1; $i < 1000; $i++) {
			$edges[] = ($i * 5) + 1;
		}
		
		$sql = "SELECT usr_id, usr_geo, usr_gender, usr_nick, usr_portrait, usr_hits, frd_created FROM babel_user, babel_friend WHERE usr_id = frd_fid AND frd_uid = {$O->usr_id} ORDER BY frd_created ASC";
		$sql_hash = md5($sql);
		
		if ($friends = $this->cs->get('babel_sql_' . $sql_hash)) {
			$friends = unserialize($friends);
		} else {
			$friends = array();
			$rs = mysql_query($sql, $this->db);
			while ($Friend = mysql_fetch_object($rs)) {
				$friends[$Friend->usr_id] = $Friend;
			}
			mysql_free_result($rs);
			$this->cs->save(serialize($friends), 'babel_sql_' . $sql_hash);
		}
		
		$i = 0;
		$s = 0;
		foreach ($friends as $usr_id => $Friend) {
			$i++;
			if (in_array($i, $edges)) {
				echo('<tr><td colspan="2">');
			}
			$img_p = $Friend->usr_portrait ? CDN_P . 'p/' . $Friend->usr_portrait . '.jpg' : CDN_P . 'p_' . $Friend->usr_gender . '.gif';
			echo('<a href="/u/' . urlencode($Friend->usr_nick) . '" class="friend"><img src="' . $img_p . '" class="portrait" /><br />' . $Friend->usr_nick);
			if ($Friend->usr_geo != 'earth') {
				echo('<div class="tip">' . $this->Geo->map['name'][$Friend->usr_geo] . '</div>');
			}
			echo('</a>');
			if (($i % 5) == 0) {
				echo ('</td></tr>');
			}
		}
		
		echo('<tr><td colspan="2" align="left" class="section_odd"><span class="text_large"><img src="/img/ico_topic.gif" align="absmiddle" class="home"/>' . $O->usr_nick . ' 最近创建的主题</span>');
		echo('<table cellpadding="0" cellspacing="0" border="0" class="fav" width="100%">');
		$i = 0;
		while ($Topic = mysql_fetch_object($rs_created)) {
			$i++;
			$css_color = rand_color();
			$css_td_class = $i % 2 ? 'section_even' : 'section_odd';
			$txt_fresh = $Topic->tpc_posts ? $Topic->tpc_posts . ' 篇回复' : '尚无回复';
			echo('<tr><td align="left" class="' . $css_td_class . '">[ <a href="/board/view/' . $Topic->nod_id . '.html" class="var" style="color: ' . $css_color . '">' . $Topic->nod_title . '</a> ]&nbsp;:&nbsp;<a href="/topic/view/' . $Topic->tpc_id . '.html">' . $Topic->tpc_title . '</a> <span class="tip_i">... ' . make_descriptive_time($Topic->tpc_created) . '，' . $txt_fresh . '</span></td></tr>');
		}
		echo('</table>');
		echo('</td></tr>');
		
		echo('<tr><td colspan="2" align="left" class="section_odd"><span class="text_large"><img src="/img/ico_followed.gif" align="absmiddle" class="home"/>' . $O->usr_nick . ' 最近参与的讨论</span>');
		echo('<table cellpadding="0" cellspacing="0" border="0" class="fav" width="100%">');
		
		$i = 0;
		foreach ($_followed as $_reply)  {
			$i++;
			$css_color = rand_color();
			$css_td_class = $i % 2 ? 'section_odd' : 'section_even';
			$txt_fresh = $_reply['tpc_posts'] ? $_reply['tpc_posts'] . ' 篇回复' : '尚无回复';
			echo('<tr><td align="left" class="' . $css_td_class . '">[ <a href="/board/view/' . $_reply['nod_id'] . '.html" class="var" style="color: ' . $css_color . '">' . $_reply['nod_title_plain'] . '</a> ]&nbsp;:&nbsp;<a href="/topic/view/' . $_reply['tpc_id'] . '.html">' . $_reply['tpc_title_plain'] . '</a> <span class="tip_i">... ' . make_descriptive_time($_reply['pst_created']) . '，' . $txt_fresh . '</span></td></tr>');
		}
		
		echo('</table>');
		echo('</td></tr>');
		
		echo('<tr><td colspan="2" align="left" class="section_odd"><span class="text_large"><img src="/img/ico_cc.gif" align="absmiddle" class="home"/>' . $O->usr_nick . ' 的成分分析</span>');
		echo('</td></tr>');
		
		if ($c = $this->cl->load('cc_' . $O->usr_id)) {
			$c = unserialize($c);
		} else {
			$f = new Fun();
			$c = $f->vxGetComponents($O->usr_nick);
			$f = null;
			$this->cl->save(serialize($c), 'cc_' . $O->usr_id);
		}

		echo('<tr><td colspan="2" align="left" class="section_odd">');
		
		echo('<ul class="items">');
		foreach($c['c'] as $C) {
			echo('<li>' . $C . '</li>');
		}
		echo('</ul>');
		
		echo('</td></tr>');
		
		echo('<tr><td colspan="2" align="left" class="section_even" style="padding-left: 20px;">');
		
		echo('<span class="tip">' . $c['s'] . '</span>');
		
		echo('</td></tr>');
		
		echo('<tr><td colspan="2" align="left" class="section_odd"><span class="tip"><img src="/img/icons/silk/user_go.png" align="absmiddle" /> ' . make_plaintext($O->usr_nick) . ' 的最后登录时间 ' . date('Y-n-j G:i:s', $O->usr_lastlogin) . '，总登录次数 ' . $O->usr_logins . ' 次。</span></td></tr>');
		if ($O->usr_lastlogin_ua != '') {
			echo('<tr><td colspan="2" align="left" class="section_odd"><span class="tip_i"><img src="/img/icons/silk/computer.png" align="absmiddle" /> 上次访问时所用浏览器 <small>' . make_plaintext($O->usr_lastlogin_ua) . '</small></span></td></tr>');
		}
		
		echo('</table>');
		echo('</div>');
		
		echo('</div>');
	}
	
	/* E module: User Home block */
	
	/* S module: User Create block */
	
	public function vxUserCreate($rt) {
		echo('<div id="main">');
		echo('<div class="blank">');
		_v_ico_map();
		echo(' <a href="/">' . Vocabulary::site_name . '</a> &gt; ' . Vocabulary::action_signup . '</div>');

		if ($rt['errors'] != 0) {
			echo('<div class="blank" align="left"><span class="text_large"><img src="/img/ico_important.gif" align="absmiddle" class="home" />对不起，你刚才提交的信息里有些错误</span><table cellpadding="0" cellspacing="0" border="0" class="form"><form action="/user/create.vx" method="post" id="usrNew">');

			/* result: usr_email */
			if ($rt['usr_email_error'] != 0) {
				echo('<tr><td width="200" align="right" valign="top">电子邮件</td><td align="left"><div class="error"><input type="text" tabindex="1" maxlength="100" class="sl" name="usr_email" value="' . make_single_return($rt['usr_email_value']) . '" />&nbsp;<img src="/img/sico_error.gif" align="absmiddle" /><br />' . $rt['usr_email_error_msg'][$rt['usr_email_error']] . '</div></td>');
			} else {
				echo('<tr><td width="200" align="right">电子邮件</td><td align="left"><input type="text" tabindex="1" maxlength="100" class="sl" name="usr_email" value="' . make_single_return($rt['usr_email_value']) . '" />&nbsp;<img src="/img/sico_ok.gif" align="absmiddle" /></td>');
			}
			
			/* cell: submit button */
			echo('<td width="150" rowspan="8" valign="middle" align="right"><input type="image" tabindex="7" src="/img/silver/btn_signup.gif" alt="' . Vocabulary::action_signup . '" /></td></tr>');
			
			/* result: usr_nick */
			if ($rt['usr_nick_error'] != 0) {
				echo('<tr><td width="200" align="right" valign="top">昵称</td><td align="left"><div class="error"><input type="text" tabindex="2" maxlength="20" class="sl" name="usr_nick" value="' . make_single_return($rt['usr_nick_value']) . '" />&nbsp;<img src="/img/sico_error.gif" align="absmiddle" /><br />' . $rt['usr_nick_error_msg'][$rt['usr_nick_error']] . '</div></td></tr>');
			} else {
				echo('<tr><td width="200" align="right">昵称</td><td align="left"><input type="text" tabindex="2" maxlength="20" class="sl" name="usr_nick" value="' . make_single_return($rt['usr_nick_value']) . '" />&nbsp;<img src="/img/sico_ok.gif" align="absmiddle" /></td></tr>');
			}
			
			/* result: usr_password */
			if ($rt['usr_password_error'] != 0) {
				echo('<tr><td width="200" align="right" valign="top">密码</td><td align="left"><div class="error"><input type="password" tabindex="3" maxlength="32" class="sl" name="usr_password" value="' . make_single_return($rt['usr_password_value']) . '"/>&nbsp;<img src="/img/sico_error.gif" align="absmiddle" /><br />' . $rt['usr_password_error_msg'][$rt['usr_password_error']] . '</td></tr>');
			} else {
				if ($rt['usr_confirm_error'] != 0) {
					echo('<tr><td width="200" align="right">密码</td><td align="left"><input type="password" tabindex="3" maxlength="32" class="sl" name="usr_password" value="' . make_single_return($rt['usr_password_value']) . '" /></td></tr>');
				} else {
					echo('<tr><td width="200" align="right">密码</td><td align="left"><input type="password" tabindex="3" maxlength="32" class="sl" name="usr_password" value="' . make_single_return($rt['usr_password_value']) . '" />&nbsp;<img src="/img/sico_ok.gif" align="absmiddle" alt="ok" /></td></tr>');
				}
			}
			
			/* result: usr_confirm */
			if ($rt['usr_password_error'] == 0) {
				if ($rt['usr_confirm_error'] != 0) {
					echo('<tr><td width="200" align="right" valign="top">重复密码</td><td align="left""><div class="error"><input type="password" tabindex="4" maxlength="32" class="sl" name="usr_confirm" value="' . make_single_return($rt['usr_confirm_value']) . '" />&nbsp;<img src="/img/sico_error.gif" align="absmiddle" /><br />' . $rt['usr_confirm_error_msg'][$rt['usr_confirm_error']] . '</div></td></tr>');
				} else {
					echo('<tr><td width="200" align="right">重复密码</td><td align="left""><input type="password" tabindex="4" maxlength="32" class="sl" name="usr_confirm" value="' . make_single_return($rt['usr_confirm_value']) . '" />&nbsp;<img src="/img/sico_ok.gif" align="absmiddle" alt="ok" /></td></tr>');
				}
			} else {
				echo('<tr><td width="200" align="right">重复密码</td><td align="left""><input type="password" tabindex="4" maxlength="32" class="sl" name="usr_confirm" /></td></tr>');
			}

			/* result: usr_gender */
			echo('<tr><td width="200" align="right" valign="top">性别</td><td align="left"><select tabindex="5" maxlength="20" size="6" name="usr_gender">');
			$gender_a = array(0 => '未知', 1 => '男性', 2 => '女性', 5 => '女性改变为男性', 6 => '男性改变为女性', 9 => '未说明');
			foreach ($gender_a as $c => $g) {
				if ($c == $rt['usr_gender_value']) {
					echo('<option value="' . $c . '" selected="selected">' . $g . '</option>');
				} else {
					echo('<option value="' . $c . '">' . $g . '</option>');
				}
			}
			echo('</select></td></tr>');
			
			/* S result: c */
			
			if ($rt['c_error'] > 0) {
				echo('<tr><td width="200" align="right">确认码</td><td align="left"><input tabindex="6" type="password" maxlength="32" class="sl" name="c" /></td></tr><tr><td width="200" align="right"></td><td align="left"><div class="error"><img src="/c/' . rand(1111,9999) . '.' . rand(1111,9999) . '.png" /><br /><img src="/img/sico_error.gif" align="absmiddle" />&nbsp;' . $rt['c_error_msg'][$rt['c_error']] . '</div></td></tr>');
			} else {
				echo('<tr><td width="200" align="right">确认码</td><td align="left"><input tabindex="6" type="password" maxlength="32" class="sl" name="c" value="' . $rt['c_value'] . '" />&nbsp;<img src="/img/sico_ok.gif" alt="ok" align="absmiddle" /></td></tr><tr><td width="200" align="right"></td><td align="left"><img src="/c/' . rand(1111,9999) . '.' . rand(1111,9999) . '.png" /></td></tr>');
			}
			/* E result: c */			
			echo('</form></table></div>');
			echo('<div class="blank"><img src="/img/ico_tip.gif" align="absmiddle" class="home" />点击“注册新会员”，即表示你同意 ' . Vocabulary::site_name . ' 的使用条款和隐私权规则<br /><br />电子邮件地址将作为你登录时候使用的识别之一，这里的大部分功能依赖于一个真实的电子邮件地址，因此一个真实的电子邮件地址很有必要，而至于昵称，则可以任意设置随心换</div>');
		} else {
			$mail = array();
			$mail['subject'] = "{$this->User->usr_nick} 你好，欢迎来到 " . Vocabulary::site_name;
			$mail['body'] = "{$this->User->usr_nick}，你好！\n\n" . Vocabulary::site_name . " 欢迎你的到来，你或许会对 " . Vocabulary::site_name . " 这个名字感到好奇吧？\n\n" . Vocabulary::site_name . " 是两个短句的缩写，way too extreme 和 way to explore，前者关于一种生活的态度，后者关于我们每天都会产生然后又失去的好奇心。So is V2EX，希望你喜欢。\n\n目前看来，V2EX 是一个普普通通不足为奇的社区（或者说论坛），不过，我们正在修建一个有着透明玻璃的怪物博物馆，不久的将来，每天都会有各种怪物可以玩，也是相当开心的事情吧。\n\nEnjoy!" . BABEL_AM_SIGNATURE;
			
			$am = new Airmail($this->User->usr_email, $mail['subject'], $mail['body'], $this->db);
			$am->vxSend();
			$am = null;
			
			echo('<div class="blank" align="left"><span class="text_large"><img src="/img/ico_smile.gif" align="absmiddle" class="home" />' . $this->User->usr_nick . '，恭喜你！注册成功</span>');
			echo('<table cellpadding="0" cellspacing="0" border="0" class="form"><tr><td width="200" align="right" valign="top">电子邮件</td><td align="left">' . $this->User->usr_email . '</td></tr><tr><td width="200" align="right" valign="top">昵称</td><td align="left">' . $this->User->usr_nick . '</td></tr><tr><td width="200" align="right" valign="top">密码</td><td align="left"><div class="important">');
			$max = rand(1, 6) * 4;
			for ($i = 1; $i <= $max; $i++) {
				echo($i == 0) ? '':'&nbsp;&nbsp;';
				echo('<strong style="font-weight: ' . rand(1, 8) . '00; font-size: ' . rand(8,28) . 'px; border: 2px solid ' . rand_color(4, 5) . '; background-color: ' . rand_color(3, 5) . '; color: ' . rand_color(0, 2) . ';font-family: ' . rand_font() . ';">' . $rt['usr_password_value'] . '</strong>');
				echo (($i % 4 == 0) && ($i != 1)) ? '<br />':'';
			}
			echo('<br /><br />在你更改密码之前，你将使用这个长度为 ' . mb_strlen($rt['usr_password_value'], 'utf-8') . ' 个字符的密码进行登录，请花些时间记住这个密码</div></td></tr></table></div>');
			
			echo('<div class="blank" align="left">');
			echo('<span class="text_large"><img src="/img/ico_smile.gif" align="absmiddle" class="home" />上传头像</span>');
			echo('<table cellpadding="0" cellspacing="0" border="0" class="form">');
			echo('<form enctype="multipart/form-data" action="/recv/portrait.vx" method="post" id="usrPortrait">');
			echo('<tr><td width="200" align="right">现在的样子</td><td width="200" align="left">');
			if ($this->User->usr_portrait != '') {
				echo('<img src="/img/p/' . $this->User->usr_portrait . '.' . BABEL_PORTRAIT_EXT . '?' . rand(1000, 9999) . '" alt="' . $this->User->usr_nick . '" class="portrait" />&nbsp;&nbsp;<img src="/img/p/' . $this->User->usr_portrait . '_s.' . BABEL_PORTRAIT_EXT . '?' . rand(1000, 9999) . '" class="portrait" />&nbsp;&nbsp;<img src="/img/p/' . $this->User->usr_portrait . '_n.' . BABEL_PORTRAIT_EXT . '?' . rand(1000, 9999) . '" class="portrait" />');
			} else {
				echo('<img src="/img/p_' . $this->User->usr_gender . '.gif" alt="' . $this->User->usr_nick . '" class="portrait" />&nbsp;&nbsp;<img src="/img/p_' . $this->User->usr_gender . '_s.gif" alt="' . $this->User->usr_nick . '" class="portrait" />&nbsp;&nbsp;<img src="/img/p_' . $this->User->usr_gender . '_n.gif" alt="' . $this->User->usr_nick . '" class="portrait" />');
			}
			echo('</td>');
			echo('<td width="150" rowspan="2" valign="middle" align="right"><input tabindex="2" type="image" src="/img/silver/btn_pupload.gif" /></td></tr>');
			echo('</tr>');
			echo('<tr><td width="200" align="right">选择一张你最喜欢的图片</td><td width="200" align="left"><input tabindex="1" type="file" name="usr_portrait" /></td>');
			echo('</tr>');
			echo('</form>');
			echo('</table>');
			echo('</div>');
			echo('<div class="blank"><img src="/img/ico_tip.gif" align="absmiddle" class="ico" />推荐你选择一张尺寸大于 100 x 100 像素的图片，系统会自动截取中间的部分并调整大小</div>');
			
			echo('<div class="blank"><img src="/img/ico_tip.gif" align="absmiddle" class="ico" />你现在已经使用电子邮件地址为 ' . $this->User->usr_email . ' 的会员的身份登录</div>');
		}
		echo('</div>');
	}
	
	/* E module: User Create block */
	
	/* S module: User Modify block */
	
	public function vxUserModify() {
		echo('<div id="main">');
		echo('<div class="blank">');
		_v_ico_map();
		echo(' <a href="/">' . Vocabulary::site_name . '</a> &gt; <a href="/u/' . urlencode($this->User->usr_nick) . '">' . make_plaintext($this->User->usr_nick) . '</a> &gt; ' . Vocabulary::action_modifyprofile . '</div>');
		echo('<div class="blank" align="left">');
		echo('<span class="text_large"><img src="/img/ico_smile.gif" align="absmiddle" class="home" />上传头像</span>');
		echo('<table cellpadding="0" cellspacing="0" border="0" class="form">');
		echo('<form enctype="multipart/form-data" action="/recv/portrait.vx" method="post" id="form_user_portrait">');
		echo('<tr><td width="200" align="right">现在的样子</td><td width="200" align="left">');
		if ($this->User->usr_portrait != '') {
			echo('<img src="/img/p/' . $this->User->usr_portrait . '.' . BABEL_PORTRAIT_EXT . '?' . rand(1000, 9999) . '" alt="' . $this->User->usr_nick . '" class="portrait" />&nbsp;&nbsp;<img src="/img/p/' . $this->User->usr_portrait . '_s.' . BABEL_PORTRAIT_EXT . '?' . rand(1000, 9999) . '" class="portrait" />&nbsp;&nbsp;<img src="/img/p/' . $this->User->usr_portrait . '_n.' . BABEL_PORTRAIT_EXT . '?' . rand(1000, 9999) . '" class="portrait" />');
		} else {
			echo('<img src="/img/p_' . $this->User->usr_gender . '.gif" alt="' . $this->User->usr_nick . '" class="portrait" />&nbsp;&nbsp;<img src="/img/p_' . $this->User->usr_gender . '_s.gif" alt="' . $this->User->usr_nick . '" class="portrait" />&nbsp;&nbsp;<img src="/img/p_' . $this->User->usr_gender . '_n.gif" alt="' . $this->User->usr_nick . '" class="portrait" />');
		}
		echo('</td>');

		echo('<td width="150" rowspan="4" valign="middle" align="right">');
		
		_v_btn_f('上传头像', 'form_user_portrait');
		
		echo('</td></tr>');
		
		echo('<tr><td width="200" align="right">选择一张你喜欢的图片</td><td width="200" align="left"><input tabindex="1" type="file" name="usr_portrait" size="14" /></td></tr>');
		
		if (IM_ENABLED) {
			echo('<tr><td width="200" align="right">对上传的图片做特效处理</td><td width="200" align="left"><input checked="checked" type="radio" name="fx" value="none" />&nbsp;&nbsp;不做任何修改</td></tr>');
			echo('<tr><td width="200" align="right"></td><td width="200" align="left"><input type="radio" name="fx" value="lividark" />&nbsp;&nbsp;Lividark GFX <span class="tip_i"><a href="http://www.livid.cn/img/lividark_resized.jpg" rel="lightbox" title="GFX: Lividark">查看例图</a></span></td></tr>');
			echo('<tr><td width="200" align="right"></td><td width="200" align="left"><input type="radio" name="fx" value="memory" />&nbsp;&nbsp;Memory GFX <span class="tip_i"><a href="http://www.livid.cn/img/memory_resized.jpg" rel="lightbox" title="GFX: Memory">查看例图</a></span></td></tr>');
		}
		echo('</form>');
		echo('<tr><td height="10" colspan="2"></td></tr>');
		echo('</table>');
		echo('<hr size="1" color="#DDD" style="color: #DDD; background-color: #DDD; height: 1px; border: 0;" />');
		echo('<img src="/img/icons/silk/information.png" align="absmiddle" /> 推荐你选择一张尺寸大于 100 x 100 像素的图片，系统会自动截取中间的部分并调整大小');
		echo('</div>');
		
		echo('<div class="blank" align="left">');
		echo('<span class="text_large"><img src="/img/ico_geo.gif" align="absmiddle" class="home" />会员所在地修改</span>');
		echo('<table cellpadding="0" cellspacing="0" border="0" class="form">');
		echo('<tr><td width="200" align="right">当前所在地</td><td width="200" align="left"><a href="/geo/' . $this->User->usr_geo . '" class="o">' . $this->Geo->map["name"][$this->User->usr_geo] . '</a></td>');
		
		echo('<td width="150" rowspan="2" valign="middle" align="right">');
		
		_v_btn_l('修改所在地', '/user/move.vx');
		
		echo('</td></tr>');
		
		$Geo = new Geo($this->User->usr_geo, $this->Geo->map);
		$geo_md5 = md5($Geo->geo->geo);
		$geos_all_children = $Geo->vxGetRecursiveChildrenArray('', true);
		$geos_all_children_sql = implode(',', $geos_all_children);
		
		if ($geo_count = $this->cs->get('babel_geo_settle_count_' . $geo_md5)) {
			$geo_count = intval($geo_count);
		} else {
			$sql = "SELECT COUNT(*) FROM babel_user WHERE usr_geo IN ({$geos_all_children_sql})";
			$rs = mysql_query($sql, $this->db);
			$geo_count = intval(mysql_result($rs, 0, 0));
			mysql_free_result($rs);
			$this->cs->save(strval($geo_count), 'babel_geo_settle_count_' . $geo_md5);
		}
		
		echo('<tr><td width="200" align="right">在' . $this->Geo->map['name'][$this->User->usr_geo] . '的人数</td><td width="200" align="left"><a href="/who/settle/' . $this->User->usr_geo . '" class="t">' . $geo_count . '</a></td>');
		echo('<tr><td height="10" colspan="2"></td></tr>');
		echo('</table>');
		echo('<hr size="1" color="#DDD" style="color: #DDD; background-color: #DDD; height: 1px; border: 0;" />');
		echo('<img src="/img/icons/silk/information.png" align="absmiddle" /> 如果你之前没有进行过任何设置，那么默认所在地就是地球');
		echo('</div>');
		
		echo('<div class="blank" align="left">');
		echo('<span class="text_large"><a name="settings"><img src="/img/ico_conf.gif" align="absmiddle" class="home" border="0" /></a>会员信息修改</span>');
		echo('<table cellpadding="0" cellspacing="0" border="0" class="form">');
		echo('<form action="/user/update.vx" method="post" id="form_user_info">');
		echo('<tr><td width="200" align="right">真实姓名</td><td width="200" align="left"><input tabindex="1" type="text" maxlength="80" class="sl" name="usr_full" value="' . make_single_return($this->User->usr_full) . '" /></td>');
		
		// S button:
		echo('<td width="150" rowspan="14" valign="middle" align="right">');
		
		_v_btn_f('修改', 'form_user_info');
		
		echo('</td></tr>');
		// E button.
		
		echo('<tr><td width="200" align="right">昵称</td><td align="left"><input tabindex="2" type="text" maxlength="20" class="sl" name="usr_nick" value="' . make_single_return($this->User->usr_nick) . '" /></td></tr>');
		echo('<tr><td width="200" align="right">自我简介</td><td align="left"><input tabindex="3" type="text" maxlength="100" class="sl" name="usr_brief" value="' . make_single_return($this->User->usr_brief) . '" /></td></tr>');
		echo('<tr><td width="200" align="right">家庭住址</td><td align="left"><input tabindex="4" type="text" maxlength="100" class="sl" name="usr_addr" value="' . make_single_return($this->User->usr_addr) . '" /></td></tr>');
		echo('<tr><td width="200" align="right">电话</td><td align="left"><input tabindex="5" type="text" maxlength="40" class="sl" name="usr_telephone" value="' . make_single_return($this->User->usr_telephone) . '" /></td></tr>');
		echo('<tr><td width="200" align="right">身份证号码</td><td align="left"><input tabindex="6" type="text" maxlength="18" class="sl" name="usr_identity" value="' . make_single_return($this->User->usr_identity) . '" /></td></tr>');
		/* result: usr_gender */
		echo('<tr><td width="200" align="right" valign="top">性别</td><td align="left"><select tabindex="7" maxlength="20" size="6" name="usr_gender">');
		$gender_a = array(0 => '未知', 1 => '男性', 2 => '女性', 5 => '女性改（变）为男性', 6 => '男性改（变）为女性', 9 => '未说明');
		foreach ($gender_a as $c => $g) {
			if ($c == $this->User->usr_gender) {
				echo('<option value="' . $c . '" selected="selected">' . $g . '</option>');
			} else {
				echo('<option value="' . $c . '">' . $g . '</option>');
			}
		}
		echo('</select></td></tr>');
		/* result: usr_width */
		$x = simplexml_load_file(BABEL_PREFIX . '/res/valid_width.xml');
		$w = $x->xpath('/array/width');
		$ws = array();
		while(list( , $width) = each($w)) {
			$ws[] = strval($width);
		}
		echo('<tr><td width="200" align="right" valign="top">常用屏幕宽度</td><td align="left"><select tabindex="8" maxlength="20" size="' . count($ws) . '" name="usr_width">');
		foreach ($ws as $width) {
			if ($width == $this->User->usr_width) {
				echo('<option value="' . $width . '" selected="selected">' . $width . '</option>');
			} else {
				echo('<option value="' . $width . '">' . $width . '</option>');
			}
		}
		echo('</select></td></tr>');
		
		// switch: top_wealth
		
		echo('<tr><td width="200" align="right" valign="middle"><small>参加社区财富排行</small></td><td align="left">');
		if ($this->User->usr_sw_top_wealth == 1) {
			echo('<input type="checkbox" name="usr_sw_top_wealth" tabindex="9" checked="checked" /> 参加');
		} else {
			echo('<input type="checkbox" name="usr_sw_top_wealth" tabindex="9" /> 参加');
		}
		echo('</td></tr>');
		
		// switch: shuffle_cloud
		
		echo('<tr><td width="200" align="right" valign="middle"><small>' . Vocabulary::term_shuffle_cloud . '</small></td><td align="left">');
		if ($this->User->usr_sw_shuffle_cloud == 1) {
			echo('<input type="checkbox" name="usr_sw_shuffle_cloud" tabindex="9" checked="checked" /> 开启');
		} else {
			echo('<input type="checkbox" name="usr_sw_shuffle_cloud" tabindex="9" /> 开启');
		}
		echo('</td></tr>');
		
		echo('<tr><td width="200" align="right" valign="middle"><small>V2EX Shell</small></td><td align="left">');
		if ($this->User->usr_sw_shell == 1) {
			echo('<input type="checkbox" name="usr_sw_shell" tabindex="9" checked="checked" /> 开启');
		} else {
			echo('<input type="checkbox" name="usr_sw_shell" tabindex="9" /> 开启');
		}
		echo('</td></tr>');
		echo('<tr><td width="200" align="right" valign="middle"><small>邮件通知自己的主题的新回复</small></td><td align="left">');
		if ($this->User->usr_sw_notify_reply == 1) {
			echo('<input type="checkbox" name="usr_sw_notify_reply" tabindex="10" checked="checked" /> 开启');
		} else {
			echo('<input type="checkbox" name="usr_sw_notify_reply" tabindex="10" /> 开启');
		}
		echo('</td></tr>');
		echo('<tr><td width="200" align="right" valign="middle"><small>邮件通知我参与过的主题的新回复</small></td><td align="left">');
		if ($this->User->usr_sw_notify_reply_all == 1) {
			echo('<input type="checkbox" name="usr_sw_notify_reply_all" tabindex="11" checked="checked" /> 开启');
		} else {
			echo('<input type="checkbox" name="usr_sw_notify_reply_all" tabindex="11" /> 开启');
		}
		echo('</td></tr>');
		echo('<tr><td width="200" align="right">用于接收通知的邮箱</td><td align="left"><input tabindex="12" type="text" maxlength="100" class="sl" name="usr_email_notify" value="' . make_single_return($this->User->usr_email_notify) . '" /></td></tr>');
		echo('<tr><td width="200" align="right">新密码</td><td align="left"><input tabindex="13" type="password" maxlength="32" class="sl" name="usr_password_new" /></td></tr>');
		echo('<tr><td width="200" align="right">重复新密码</td><td align="left"><input tabindex="14" type="password" maxlength="32" class="sl" name="usr_confirm_new" /></td></tr>');
		echo('<tr><td height="10" colspan="2"></td></tr>');
		echo('</form></table>');
		echo('<hr size="1" color="#DDD" style="color: #DDD; background-color: #DDD; height: 1px; border: 0;" />');
		echo('<img src="/img/icons/silk/information.png" align="absmiddle" /> 如果你不打算修改密码的话，就不要在密码框处填入任何信息');
		echo('</div>');
		echo('</div>');
	}
	
	/* E module: User Modify block */
	
	/* S module: User Update block */
	
	public function vxUserUpdate($rt) {
		echo('<div id="main">');
		echo('<div class="blank">');
		_v_ico_map();
		echo(' <a href="/">' . Vocabulary::site_name . '</a> &gt; <a href="/u/' . urlencode($this->User->usr_nick) . '">' . make_plaintext($this->User->usr_nick) . '</a> &gt; ' . Vocabulary::action_modifyprofile . '</div>');

		if ($rt['errors'] != 0) {
			echo('<div class="blank" align="left"><span class="text_large"><img src="/img/ico_important.gif" align="absmiddle" class="home" />对不起，你刚才提交的信息里有些错误</span><table cellpadding="0" cellspacing="0" border="0" class="form"><form action="/user/update.vx" method="post" id="form_user_info">');

			/* result: usr_email */
			if ($rt['usr_full_error'] != 0) {
				echo('<tr><td width="200" align="right" valign="top">真实姓名</td><td width="200" align="left"><div class="error"><input type="text" maxlength="100" class="sl" name="usr_full" value="' . make_single_return($rt['usr_full_value']) . '" />&nbsp;<img src="/img/sico_error.gif" align="absmiddle" /><br />' . $rt['usr_full_error_msg'][$rt['usr_full_error']] . '</div></td>');
			} else {
				echo('<tr><td width="200" align="right">真实姓名</td><td width="200" align="left"><input type="text" maxlength="100" class="sl" name="usr_full" value="' . make_single_return($rt['usr_full_value']) . '" />&nbsp;<img src="/img/sico_ok.gif" align="absmiddle" /></td>');
			}
			
			/* cell: submit button */
			
			echo('<td width="150" rowspan="13" valign="middle" align="right">');
			_v_btn_f('修改', 'form_user_info');
			echo('</td></tr>');
			
			/* result: usr_nick */
			if ($rt['usr_nick_error'] != 0) {
				echo('<tr><td width="200" align="right" valign="top">昵称</td><td width="200" align="left"><div class="error"><input type="text" maxlength="20" class="sl" name="usr_nick" value="' . make_single_return($rt['usr_nick_value']) . '" />&nbsp;<img src="/img/sico_error.gif" align="absmiddle" /><br />' . $rt['usr_nick_error_msg'][$rt['usr_nick_error']] . '</div></td></tr>');
			} else {
				echo('<tr><td width="200" align="right">昵称</td><td width="200" align="left"><input type="text" maxlength="20" class="sl" name="usr_nick" value="' . make_single_return($rt['usr_nick_value']) . '" />&nbsp;<img src="/img/sico_ok.gif" align="absmiddle" /></td></tr>');
			}
			
			/* result: usr_brief */
			if ($rt['usr_brief_error'] != 0) {
				echo('<tr><td width="200" align="right" valign="top">自我简介</td><td width="200" align="left"><div class="error"><input type="text" maxlength="200" class="sl" name="usr_brief" value="' . make_single_return($rt['usr_brief_value']) . '" />&nbsp;<img src="/img/sico_error.gif" align="absmiddle" /><br />' . $rt['usr_brief_error_msg'][$rt['usr_brief_error']] . '</div></td></tr>');
			} else {
				echo('<tr><td width="200" align="right">自我简介</td><td width="200" align="left"><input type="text" maxlength="200" class="sl" name="usr_brief" value="' . make_single_return($rt['usr_brief_value']) . '" />&nbsp;<img src="/img/sico_ok.gif" align="absmiddle" /></td></tr>');
			}
			
			/* result: usr_addr */
			if ($rt['usr_addr_error'] != 0) {
				echo('<tr><td width="200" align="right" valign="top">家庭住址</td><td width="200" align="left"><div class="error"><input type="text" maxlength="100" class="sl" name="usr_addr" value="' . make_single_return($rt['usr_addr_value']) . '" />&nbsp;<img src="/img/sico_error.gif" align="absmiddle" /><br />' . $rt['usr_addr_error_msg'][$rt['usr_addr_error']] . '</div></td></tr>');
			} else {
				echo('<tr><td width="200" align="right">家庭住址</td><td width="200" align="left"><input type="text" maxlength="100" class="sl" name="usr_addr" value="' . make_single_return($rt['usr_addr_value']) . '" />&nbsp;<img src="/img/sico_ok.gif" align="absmiddle" /></td></tr>');
			}
			
			/* result: usr_telephone */
			if ($rt['usr_telephone_error'] != 0) {
				echo('<tr><td width="200" align="right" valign="top">电话号码</td><td width="200" align="left"><div class="error"><input type="text" maxlength="40" class="sl" name="usr_telephone" value="' . make_single_return($rt['usr_telephone_value']) . '" />&nbsp;<img src="/img/sico_error.gif" align="absmiddle" /><br />' . $rt['usr_telephone_error_msg'][$rt['usr_telephone_error']] . '</div></td></tr>');
			} else {
				echo('<tr><td width="200" align="right">电话号码</td><td width="200" align="left"><input type="text" maxlength="40" class="sl" name="usr_telephone" value="' . make_single_return($rt['usr_telephone_value']) . '" />&nbsp;<img src="/img/sico_ok.gif" align="absmiddle" /></td></tr>');
			}
			
			/* result: usr_identity */
			if ($rt['usr_identity_error'] != 0) {
				echo('<tr><td width="200" align="right" valign="top">身份证号码</td><td width="200" align="left"><div class="error"><input type="text" maxlength="18" class="sl" name="usr_identity" value="' . make_single_return($rt['usr_identity_value']) . '" />&nbsp;<img src="/img/sico_error.gif" align="absmiddle" /><br />' . $rt['usr_identity_error_msg'][$rt['usr_identity_error']] . '</div></td></tr>');
			} else {
				echo('<tr><td width="200" align="right">身份证号码</td><td width="200" align="left"><input type="text" maxlength="18" class="sl" name="usr_identity" value="' . make_single_return($rt['usr_identity_value']) . '" />&nbsp;<img src="/img/sico_ok.gif" align="absmiddle" /></td></tr>');
			}
			
			/* result: usr_gender */
			echo('<tr><td width="200" align="right" valign="top">性别</td><td align="left"><select tabindex="6" maxlength="20" size="6" name="usr_gender">');
			
			foreach ($this->User->usr_gender_a as $c => $g) {
				if ($c == $rt['usr_gender_value']) {
					echo('<option value="' . $c . '" selected="selected">' . $g . '</option>');
				} else {
					echo('<option value="' . $c . '">' . $g . '</option>');
				}
			}
			echo('</select></td></tr>');
			
			/* result: usr_width */
			echo('<tr><td width="200" align="right" valign="top">常用屏幕宽度</td><td align="left"><select tabindex="7" maxlength="20" size="' . count($rt['usr_width_array']) . '" name="usr_width">');
			foreach ($rt['usr_width_array'] as $width) {
				if ($width == $rt['usr_width_value']) {
					echo('<option value="' . $width . '" selected="selected">' . $width . '</option>');
				} else {
					echo('<option value="' . $width . '">' . $width . '</option>');
				}
			}
			echo('</select></td></tr>');
			
			echo('<tr><td width="200" align="right" valign="middle"><small>参加社区财富排行</small></td><td align="left">');
			if ($rt['usr_sw_top_wealth_value'] == 1) {
				echo('<input type="checkbox" name="usr_sw_top_wealth" tabindex="8" checked="checked" /> 参加');
			} else {
				echo('<input type="checkbox" name="usr_sw_top_wealth" tabindex="8" /> 参加');
			}
			echo('</td></tr>');
			
			echo('<tr><td width="200" align="right" valign="middle"><small>' . Vocabulary::term_shuffle_cloud . '</small></td><td align="left">');
			if ($rt['usr_sw_shuffle_cloud_value'] == 1) {
				echo('<input type="checkbox" name="usr_sw_shell" tabindex="8" checked="checked" /> 开启');
			} else {
				echo('<input type="checkbox" name="usr_sw_shell" tabindex="8" /> 开启');
			}
			echo('</td></tr>');
			
			echo('<tr><td width="200" align="right" valign="middle"><small>V2EX Shell</small></td><td align="left">');
			if ($rt['usr_sw_shell_value'] == 1) {
				echo('<input type="checkbox" name="usr_sw_shell" tabindex="9" checked="checked" /> 开启');
			} else {
				echo('<input type="checkbox" name="usr_sw_shell" tabindex="9" /> 开启');
			}
			echo('</td></tr>');
			
			echo('<tr><td width="200" align="right" valign="middle"><small>邮件通知自己的主题的新回复</small></td><td align="left">');
			if ($rt['usr_sw_notify_reply_value'] == 1) {
				echo('<input type="checkbox" name="usr_sw_notify_reply" tabindex="10" checked="checked" /> 开启');
			} else {
				echo('<input type="checkbox" name="usr_sw_notify_reply" tabindex="10" /> 开启');
			}
			echo('</td></tr>');
			
			echo('<tr><td width="200" align="right" valign="middle"><small>邮件通知我参与过的主题的新回复</small></td><td align="left">');
			if ($rt['usr_sw_notify_reply_all_value'] == 1) {
				echo('<input type="checkbox" name="usr_sw_notify_reply_all" tabindex="11" checked="checked" /> 开启');
			} else {
				echo('<input type="checkbox" name="usr_sw_notify_reply_all" tabindex="11" /> 开启');
			}
			echo('</td></tr>');
			
			if ($rt['usr_email_notify_error'] != 0) {
				echo('<tr><td width="200" align="right" valign="top">用于接收通知的邮箱</td><td width="200" align="left"><div class="error"><input type="text" maxlength="100" class="sl" name="usr_email_notify" value="' . make_single_return($rt['usr_email_notify_value']) . '" />&nbsp;<img src="/img/sico_error.gif" align="absmiddle" /><br />' . $rt['usr_email_notify_error_msg'][$rt['usr_email_notify_error']] . '</div></td>');
			} else {
				echo('<tr><td width="200" align="right">用于接收通知的邮箱</td><td width="200" align="left"><input type="text" maxlength="100" class="sl" name="usr_email_notify" value="' . make_single_return($rt['usr_email_notify_value']) . '" />&nbsp;<img src="/img/sico_ok.gif" align="absmiddle" /></td>');
			}
			
			/* S result: usr_password and usr_confirm */
			
			/* pswitch:
			a => p0 c0
			b => p1 c1
			c => p1 c0
			d => p0 c1 */
			
			switch ($rt['pswitch']) {
				default:
				case 'a':
					echo('<tr><td width="200" align="right">新密码</td><td width="200" align="left"><input type="password" maxlength="32" class="sl" name="usr_password_new" /></td></tr>');
					echo('<tr><td width="200" align="right">重复密码</td><td width="200" align="left"><input type="password" maxlength="32" class="sl" name="usr_confirm_new" /></td></tr>');
					break;
				case 'b':
					if ($rt['usr_password_error'] == 0) {
						if ($rt['usr_confirm_error'] != 0) {
							echo('<tr><td width="200" align="right">新密码</td><td width="200" align="left"><input type="password" maxlength="32" class="sl" name="usr_password_new" value="' . make_single_return($rt['usr_password_value']) . '" /></td></tr>');
							echo('<tr><td width="200" align="right">重复新密码</td><td width="200" align="left"><div class="error"><input type="password" maxlength="32" class="sl" name="usr_confirm_new" />&nbsp;<img src="/img/sico_error.gif" align="absmiddle" /><br />' . $rt['usr_confirm_error_msg'][$rt['usr_confirm_error']] . '</div></td></tr>');
						} else {
							echo('<tr><td width="200" align="right">新密码</td><td width="200" align="left""><input type="password" maxlength="32" class="sl" name="usr_password_new" value="' . make_single_return($rt['usr_password_value']) . '" />&nbsp;<img src="/img/sico_ok.gif" align="absmiddle" alt="ok" /></td></tr>');
							echo('<tr><td width="200" align="right">重复新密码</td><td width="200" align="left""><input type="password" maxlength="32" class="sl" name="usr_confirm_new" value="' . make_single_return($rt['usr_confirm_value']) . '" />&nbsp;<img src="/img/sico_ok.gif" align="absmiddle" alt="ok" /></td></tr>');
						}
					} else {
						echo('<tr><td width="200" align="right">新密码</td><td width="200" align="left"><div class="error"><input type="password" maxlength="32" class="sl" name="usr_password_new" />&nbsp;<img src="/img/sico_error.gif" align="absmiddle" /><br />' . $rt['usr_password_error_msg'][$rt['usr_password_error']] . '</div></td></tr>');
					echo('<tr><td width="200" align="right">重复新密码</td><td width="200" align="left"><input type="password" maxlength="32" class="sl" name="usr_confirm_new" /></td></tr>');
					}
					break;
				case 'c':
					echo('<tr><td width="200" align="right">新密码</td><td width="200" align="left"><input type="password" maxlength="32" class="sl" name="usr_password_new" value="' . make_single_return($rt['usr_password_value']) . '" /></td></tr>');
					echo('<tr><td width="200" align="right">重复新密码</td><td width="200" align="left"><div class="error"><input type="password" maxlength="32" class="sl" name="usr_confirm_new" />&nbsp;<img src="/img/sico_error.gif" align="absmiddle" /><br />' . $rt['usr_confirm_error_msg'][$rt['usr_confirm_error']] . '</div></td></tr>');
					break;
				case 'd':
					echo('<tr><td width="200" align="right">新密码</td><td width="200" align="left"><div class="error"><input type="password" maxlength="32" class="sl" name="usr_password_new" />&nbsp;<img src="/img/sico_error.gif" align="absmiddle" /><br />' . $rt['usr_password_error_msg'][$rt['usr_password_error']] . '</div></td></tr>');
					echo('<tr><td width="200" align="right">重复新密码</td><td width="200" align="left"><input type="password" maxlength="32" class="sl" name="usr_confirm_new" value="' . make_single_return($rt['usr_confirm_value']) . '" /></td></tr>');
					break;
			}
			
			/* E result: usr_password and usr_confirm */
			
			echo('</form></table>');
			_v_hr();
			echo('<img src="/img/icons/silk/information.png" align="absmiddle" /> 如果你不打算修改密码的话，就不要在密码框处填入任何信息</div>');
		} else {
			echo('<div class="blank" align="left"><span class="text_large"><img src="/img/ico_smile.gif" align="absmiddle" class="home" />' . make_plaintext($rt['usr_nick_value']) . ' 的会员信息修改成功</span>');
			echo('<table cellpadding="0" cellspacing="0" border="0" class="form">');
			echo('<tr><td width="200" align="right" valign="middle">真实姓名</td><td align="left">' . make_plaintext($rt['usr_full_value']) . '</td>');
			echo('<td width="150" rowspan="14" valign="middle" align="right">');
			_v_btn_l('重新修改', '/user/modify.vx');
			echo('</td>');
			echo('</tr>');
			echo('<tr><td width="200" align="right" valign="middle">昵称</td><td align="left">' . make_plaintext($rt['usr_nick_value']) . '</td></tr>');
			echo('<tr><td width="200" align="right" valign="middle">自我简介</td><td align="left">' . make_plaintext($rt['usr_brief_value']) . '</td></tr>');
			echo('<tr><td width="200" align="right" valign="middle">家庭住址</td><td align="left">' . make_plaintext($rt['usr_addr_value']) . '</td></tr>');
			echo('<tr><td width="200" align="right" valign="middle">电话号码</td><td align="left">' . make_plaintext($rt['usr_telephone_value']) . '</td></tr>');
			echo('<tr><td width="200" align="right" valign="middle">身份证号码</td><td align="left">' . make_plaintext($rt['usr_identity_value']) . '</td></tr>');
			echo('<tr><td width="200" align="right" valign="middle">性别</td><td align="left">' . $this->User->usr_gender_a[$rt['usr_gender_value']] . '</td></tr>');
			echo('<tr><td width="200" align="right" valign="middle">常用屏幕宽度</td><td align="left">' . $rt['usr_width_value'] . '</td></tr>');
			
			/* start: switches */
			echo('<tr><td width="200" align="right" valign="middle"><small>参加社区财富排行</small></td><td align="left">');
			echo $rt['usr_sw_top_wealth_value'] ? '参加' : '不参加'; 
			echo('</td></tr>');
			echo('<tr><td width="200" align="right" valign="middle"><small>' . Vocabulary::term_shuffle_cloud . '</small></td><td align="left">');
			echo $rt['usr_sw_shuffle_cloud_value'] ? '开启' : '关闭'; 
			echo('</td></tr>');
			echo('<tr><td width="200" align="right" valign="middle"><small>V2EX Shell</small></td><td align="left">');
			echo $rt['usr_sw_shell_value'] ? '开启' : '关闭'; 
			echo('</td></tr>');
			echo('<tr><td width="200" align="right" valign="middle"><small>邮件通知自己的主题的新回复</small></td><td align="left">');
			echo $rt['usr_sw_notify_reply_value'] ? '开启' : '关闭'; 
			echo('</td></tr>');
			echo('<tr><td width="200" align="right" valign="middle"><small>邮件通知我参与过的主题的新回复</small></td><td align="left">');
			echo $rt['usr_sw_notify_reply_all_value'] ? '开启' : '关闭'; 
			echo('</td></tr>');
			/* end: switches */
			echo('<tr><td width="200" align="right" valign="middle">用于接收通知的邮箱</td><td align="left">' . make_plaintext($rt['usr_email_notify_value']) . '</td></tr>');
			if ($rt['usr_password_touched'] == 1) {
				echo('<tr><td width="200" align="right" valign="top">新密码</td><td align="left"><div class="important">');
				$max = rand(1, 6) * 4;
				for ($i = 1; $i <= $max; $i++) {
					echo($i == 0) ? '':'&nbsp;&nbsp;';
					echo('<strong style="font-weight: ' . rand(1, 8) . '00; font-size: ' . rand(8,28) . 'px; border: 2px solid ' . rand_color(4, 5) . '; background-color: ' . rand_color(3, 5) . '; color: ' . rand_color(0, 2) . ';font-family: ' . rand_font() . ';">' . $rt['usr_password_value'] . '</strong>');
					echo (($i % 4 == 0) && ($i != 1)) ? '<br />':'';
				}
				echo('<br /><br />在你下次更改密码之前，你将使用这个长度为 ' . mb_strlen($rt['usr_password_value'], 'utf-8') . ' 个字符的密码进行登录，请花些时间记住这个密码</div></td></tr>');
			}
			echo('</table></div>');
			if ($rt['pswitch'] == 'b') {
				echo('<div class="blank" align="left"><span class="text_large"><img src="/img/ico_tip.gif" align="absmiddle" class="home" />修改密码之后你现在将需要重新登录</span>');
				echo('<table cellpadding="0" cellspacing="0" border="0" class="form">');
				echo('<form action="/login.vx" method="post" id="Login">');
				echo('<tr><td width="200" align="right">电子邮件或昵称</td><td width="200" align="left"><input type="text" maxlength="100" class="sl" name="usr" tabindex="1" /></td><td width="150" rowspan="2" valign="middle" align="right"><input type="image" src="/img/graphite/login.gif" alt="' . Vocabulary::action_login . '" /></td></tr><tr><td width="200" align="right">密码</td><td align="left"><input type="password" maxlength="32" class="sl" name="usr_password" tabindex="2" /></td></tr></form></table></div>');
			} else {
				echo('<div class="blank" align="left"><img src="/img/ico_tip.gif" align="absmiddle" class="ico" />' . make_plaintext($this->User->usr_nick) . ' <span class="tip">&lt; ' . $this->User->usr_email . ' &gt;</span> 的会员信息已经更新</div>');
			}
		}
		echo('</div>');
	}
	
	/* E module: User Update block */
	
	/* S module: User Move block */
	
	public function vxUserMove() {
		if (isset($_GET['geo'])) {
			$geo = strtolower(make_single_safe($_GET['geo']));
			if (get_magic_quotes_gpc()) {
				$geo = stripslashes($geo);
			}
			if (!$this->Geo->vxIsExist($geo)) {
				$geo = $this->User->usr_geo;
			}
		} else {
			$geo = $this->User->usr_geo;
		}
		$geo_md5 = md5($geo);
		$geo_real = mysql_real_escape_string($geo, $this->db);
		$Geo = new Geo($geo, $this->Geo->map);
		$geos_all_children = $Geo->vxGetRecursiveChildrenArray('', true);
		$geos_all_children_sql = implode(',', $geos_all_children);
		echo('<div id="main">');
		echo('<div class="blank" align="left">');
		_v_ico_map();
		echo(' <a href="/">' . Vocabulary::site_name . '</a> &gt; <a href="/u/' . urlencode($this->User->usr_nick) . '">' . make_plaintext($this->User->usr_nick) . '</a> &gt; <a href="/user/modify.vx">' . Vocabulary::action_modifyprofile . '</a> &gt; ' . Vocabulary::action_modifygeo);
		echo('</div>');
		
		echo('<div class="blank">');
		
		echo('当前浏览到 &gt; ');
		
		$geo_route = $Geo->vxGetRoute();
		$i = 0;
		foreach ($geo_route as $g => $n) {
			$i++;
			if ($g == $geo) {
				if ($i == 1) {
					echo($n);
				} else {
					echo(' &gt; ' . $n);
				}
			} else {
				if ($i == 1) {
					echo('<a href="/user/move/' . $g . '" class="o">' . $n . '</a>');
				} else {
					echo(' &gt; <a href="/user/move/' . $g . '" class="o">' . $n . '</a>');
				}
			}
		}
		if ($geo == $this->User->usr_geo) {
			echo(' <img src="/img/geo_here.gif" align="absmiddle" />');
		} else {
			echo(' <a href="/user/settle/' . $geo . '" class="var"><img src="/img/geo_set.gif" align="absmiddle" border="0" /></a>');
		}
		_v_hr();

		$geos_children = $Geo->vxGetChildrenArray($geo);
		if (count($geos_children) > 0) {
			$len_total = 0;
			foreach ($geos_children as $elem) {
				$len_total = $len_total + mb_strlen($elem, 'UTF-8');
			}
			$len_avg = floor($len_total / count($geos_children));
			switch ($len_avg) {
				case 2:
				default:
					$br = 12;
					break;
				case 3:
					$br = 10;
					break;
				case 4;
					$br = 8;
					break;
				case 5:
					$br = 6;
					break;
				case 6:
					$br = 4;
					break;
			}
			echo('<img src="/img/gt.gif" align="absmiddle" /> 下属于' . $Geo->geo->name->cn . '的区域');
			echo('<blockquote>');
			$i = 0;
			foreach ($geos_children as $g => $n) {
				$i++;
				$css_color = rand_color();
				echo('<a href="/user/move/' . $g . '" class="var" style="color: ' . $css_color . ';">' . $n . '</a>&nbsp; ');
				if ($i % $br == 0) {
					echo('<br />');
				}
			}
			echo('</blockquote>');
			_v_hr();
		}
		
		$geos_parallel = $Geo->vxGetParallelArray($geo);
		if (count($geos_parallel) > 0) {
			$len_total = 0;
			foreach ($geos_parallel as $elem) {
				$len_total = $len_total + mb_strlen($elem, 'UTF-8');
			}
			$len_avg = floor($len_total / count($geos_parallel));
			switch ($len_avg) {
				case 2:
				default:
					$br = 12;
					break;
				case 3:
					$br = 10;
					break;
				case 4;
					$br = 8;
					break;
				case 5:
					$br = 6;
					break;
				case 6:
					$br = 4;
					break;
			}
			echo('<img src="/img/gt.gif" align="absmiddle" /> 与' . $Geo->geo->name->cn . '平行的区域');
			echo('<blockquote>');			
			$i = 0;
			foreach ($geos_parallel as $g => $n) {
				$i++;
				$css_color = rand_color();
				echo('<a href="/user/move/' . $g . '" class="var" style="color: ' . $css_color . ';">' . $n . '</a>&nbsp; ');					
				if ($i % $br == 0) {
					echo('<br />');
				}
			}
			echo('</blockquote>');
		}
		
		_v_hr();
		
		if ($Geo->geo->geo != $this->User->usr_geo) {
			echo('<img src="/img/gt.gif" align="absmiddle" /> 我当前设置好的所在地 &gt <a href="/user/move/' . $this->User->usr_geo . '" class="o">' . $this->Geo->map['name'][$this->User->usr_geo] . '</a>');
			_v_hr();
		}
		
		echo('<div class="geo_home_middle">');
		if (mb_strlen($Geo->geo->description->cn, 'UTF-8') > 0) {
			echo('<span class="geo_home_desc">' . $Geo->geo->description->cn . '</span>');
		} else {
			echo ('<span class="tip_i"><small>no description yet ...</small></span>');
		}
		echo('</div>');
		
		_v_hr();
		
		echo('<img src="/img/ico_tip.gif" align="absmiddle" class="ico" />浏览到你想要设置为所在地的区域之后，请点击地名右侧的蓝色条幅完成设置</span>');
		_v_hr();
		if ($geo_count = $this->cs->get('babel_geo_settle_count_' . $geo_md5)) {
			$geo_count = intval($geo_count);
		} else {
			$sql = "SELECT COUNT(*) FROM babel_user WHERE usr_geo IN ({$geos_all_children_sql})";
			$rs = mysql_query($sql, $this->db);
			$geo_count = intval(mysql_result($rs, 0, 0));
			mysql_free_result($rs);
			$this->cs->save(strval($geo_count), 'babel_geo_settle_count_' . $geo_md5);
		}
		echo('<img src="/img/gt.gif" align="absmiddle" /> 有 <a href="/who/settle/' . $geo . '" class="t">&nbsp;' . $geo_count . '&nbsp;</a> 人在' . $Geo->geo->name->cn . '。');
		echo('</div>');
		echo('</div>');
	}
	
	/* E module: User Move block */
	
	/* S module: Topic Favorite block */
	
	public function vxTopicFavorite() {
		$p = array();
		$p['base'] = '/topic/favorite/';
		$p['ext'] = '.vx';
		$sql = "SELECT COUNT(fav_id) FROM babel_favorite WHERE fav_uid = {$this->User->usr_id}";
		$rs = mysql_query($sql, $this->db);
		$p['items'] = mysql_result($rs, 0, 0);
		mysql_free_result($rs);
		echo('<div id="main">');
		echo('<div class="blank" align="left">');
		_v_ico_map();
		echo(' <a href="/">' . Vocabulary::site_name . '</a> &gt; ' . Vocabulary::term_favorite . '</div>');
		echo('<div class="blank" align="left">');
		echo('<span class="text_large"><img src="/img/ico_star.gif" align="absmiddle" class="home" />' . Vocabulary::term_favorite . '</span>');
		echo('<br />目前你共在 <a href="/">' . Vocabulary::site_name . '</a> 社区收藏了 ' . $p['items'] . ' 个项目');
		echo('</div>');
		echo('<table width="100%" border="0" cellpadding="0" cellspacing="2" class="board">');
		if ($p['items'] > 0) {
			$p['size'] = BABEL_NOD_PAGE;
			$p['span'] = BABEL_PG_SPAN;
			if (($p['items'] % $p['size']) == 0) {
				$p['total'] = $p['items'] / $p['size'];
			} else {
				$p['total'] = floor($p['items'] / $p['size']) + 1;
			}
			if (isset($_GET['p'])) {
				$p['cur'] = intval($_GET['p']);
			} else {
				$p['cur'] = 1;
			}
			if ($p['cur'] < 1) {
				$p['cur'] = 1;
			}
			if ($p['cur'] > $p['total']) {
				$p['cur'] = $p['total'];
			}
			if (($p['cur'] - $p['span']) >= 1) {
				$p['start'] = $p['cur'] - $p['span'];
			} else {
				$p['start'] = 1;
			}
			if (($p['cur'] + $p['span']) <= $p['total']) {
				$p['end'] = $p['cur'] + $p['span'];
			} else {
				$p['end'] = $p['total'];
			}
				$p['sql'] = ($p['cur'] - 1) * $p['size'];
			if ($p['total'] > 1) {
				echo('<tr><td align="left" class="hf" colspan="4">');
				$this->vxDrawPages($p);
				echo('</td></tr>');
			}
			$sql = "SELECT fav_title, fav_author, fav_res, fav_type, fav_created FROM babel_favorite WHERE fav_uid = {$this->User->usr_id} ORDER BY fav_created DESC LIMIT {$p['sql']},{$p['size']}";
			$rs = mysql_query($sql, $this->db);
			$i = 0;
			while ($Fav = mysql_fetch_object($rs)) {
				$i++;
				echo('<tr>');
				switch ($Fav->fav_type) {
					default:
					case 0:
						echo('<td width="24" height="24" align="center" valign="middle" class="star"><img src="/img/mico_topic.gif" /></td>');
						break;
					case 1:
						echo('<td width="24" height="24" align="center" valign="middle" class="star"><img src="/img/mico_gear.gif" /></td>');
						break;
					case 2:
						echo('<td width="24" height="24" align="center" valign="middle" class="star"><img src="/img/mico_news.gif" /></td>');
						break;
				}
				if ($i % 2 == 0) {
					$css_class = 'even';
				} else {
					$css_class = 'odd';
				}
				echo('<td class="' . $css_class . '" height="24" align="left">');
				switch ($Fav->fav_type) {
					default:
					case 0:
						echo('<a href="/topic/view/' . $Fav->fav_res . '.html" target="_self">' . make_plaintext($Fav->fav_title) . '</a>&nbsp;');
						break;
					case 1:
						echo('<a href="/board/view/' . $Fav->fav_res . '.html" target="_self">' . make_plaintext($Fav->fav_title) . '</a>&nbsp;');
						break;
					case 2:
						echo('<a href="/channel/view/' . $Fav->fav_res . '.html" target="_self">' . make_plaintext($Fav->fav_title) . '</a>&nbsp;');
						break;
				}
				echo('</td>');
					echo('<td class="' . $css_class . '" width="120" height="24" align="left">');
				switch ($Fav->fav_type) {
					default:
					case 0:
						echo(make_plaintext($Fav->fav_author));
						break;
					case 1:
						$section_a = explode(':', $Fav->fav_author);
						echo('<a href="/section/view/' . $section_a[1] . '.html">' . $section_a[0] . '</a>');
						break;
					case 2:
						$board_a = explode(':', $Fav->fav_author);
						echo('<a href="/board/view/' . $board_a[1] . '.html">' . $board_a[0] . '</a>');
						break;
				}
				echo('</td>');
				echo('<td class="' . $css_class . '" width="120" height="24" align="left"><small class="time">' . make_descriptive_time($Fav->fav_created) . '</small></td>');
				echo('</tr>');
			}
			mysql_free_result($rs);
			if ($p['total'] > 1) {
				echo('<tr><td align="left" class="hf" colspan="4">');
				$this->vxDrawPages($p);
				echo('</td></tr>');
			}
			echo('<tr><td align="left" class="hf" colspan="4">如何将不喜欢的主题移出收藏？<span class="text"><br /><br />如果你想把曾经收藏过的一篇主题从收藏中移出的话，你可以点击主题正文下面的“我不再喜欢这篇主题“按钮，然后你可以将这篇主题从收藏中移出来啦！</span></td></tr>');
		} else {
			echo('<tr><td align="left" class="hf">你现在还没有收藏任何喜欢的主题？<span class="text"><br /><br />如果你在 <a href="/">' . Vocabulary::site_name . '</a> 社区看到一篇你非常喜欢的主题，你可以点击主题正文下面的“我喜欢这篇主题“按钮，然后你可以将这篇主题收藏起来啦！</span></td></tr>');
		}
		echo('</table>');
		echo('</div>');
	}
	
	/* E module: Topic Favorite block */
	
	/* S module: Topic Fresh block */
	
	public function vxTopicFresh() {
		$p = array();
		$p['base'] = '/topic/fresh/';
		$p['ext'] = '.html';
		
		$sql = "SELECT COUNT(tpc_id) FROM babel_topic WHERE tpc_posts = 0";
		$rs = mysql_query($sql, $this->db);
		$p['items'] = mysql_result($rs, 0, 0);
		mysql_free_result($rs);
		
		$today = make_today_unix();
		$sql = "SELECT COUNT(tpc_id) FROM babel_topic WHERE tpc_posts = 0 AND tpc_created > {$today}";
		$rs = mysql_query($sql, $this->db);
		$count_virgin_today = mysql_result($rs, 0, 0);
		mysql_free_result($rs);
		
		echo('<div id="main">');
		echo('<div class="blank" align="left">');
		_v_ico_map();
		echo(' <a href="/">' . Vocabulary::site_name . '</a> &gt; ' . Vocabulary::action_freshtopic . '</div>');
		echo('<div class="blank" align="left">');
		echo('<img src="' . CDN_UI . 'img/icons/silk/weather_sun.png" align="absmiddle" /> ' . Vocabulary::action_freshtopic . '');
		echo(' | <span class="tip_i">整个社区中目前共有 ' . $p['items'] . ' 个 virgin 主题，其中今天到目前为止有 ' . $count_virgin_today . ' 个</span>');
		_v_hr();
		echo('因为有你对' . Vocabulary::term_virgin_topic . '的关怀，这里才变得更加美好！');
		echo('</div>');
		echo('<table width="100%" border="0" cellpadding="0" cellspacing="0" class="board">');
		if ($p['items'] > 0) {
			$p['size'] = BABEL_NOD_PAGE;
			$p['span'] = BABEL_PG_SPAN;
			if (($p['items'] % $p['size']) == 0) {
				$p['total'] = $p['items'] / $p['size'];
			} else {
				$p['total'] = floor($p['items'] / $p['size']) + 1;
			}
			if (isset($_GET['p'])) {
				$p['cur'] = intval($_GET['p']);
			} else {
				$p['cur'] = 1;
			}
			if ($p['cur'] < 1) {
				$p['cur'] = 1;
			}
			if ($p['cur'] > $p['total']) {
				$p['cur'] = $p['total'];
			}
			if (($p['cur'] - $p['span']) >= 1) {
				$p['start'] = $p['cur'] - $p['span'];
			} else {
				$p['start'] = 1;
			}
			if (($p['cur'] + $p['span']) <= $p['total']) {
				$p['end'] = $p['cur'] + $p['span'];
			} else {
				$p['end'] = $p['total'];
			}
				$p['sql'] = ($p['cur'] - 1) * $p['size'];
			if ($p['total'] > 1) {
				echo('<tr><td align="left" class="hf" colspan="4">');
				$this->vxDrawPages($p);
				echo('</td></tr>');
			}
			$sql = "SELECT tpc_id, tpc_pid, tpc_uid, tpc_title, tpc_hits, tpc_posts, tpc_created, tpc_lastupdated, tpc_lasttouched, usr_id, usr_nick, usr_gender, usr_portrait FROM babel_topic, babel_user WHERE tpc_uid = usr_id AND tpc_posts = 0 ORDER BY tpc_lasttouched DESC, tpc_created DESC LIMIT {$p['sql']},{$p['size']}";
			$rs = mysql_query($sql, $this->db);
			$i = 0;
			while ($Topic = mysql_fetch_object($rs)) {
				$i++;
				$img_p = $Topic->usr_portrait ? '/img/p/' . $Topic->usr_portrait . '_n.jpg' : '/img/p_' . $Topic->usr_gender . '_n.gif';
				echo('<tr>');
				if ($Topic->usr_id == $this->User->usr_id) {
					$dot = 'green';
				} else {
					$dot = 'gray';
				}
				echo('<td width="24" height="30" align="center" valign="middle" class="star"><img src="' . CDN_UI . 'img/dot_' . $dot . '.png" /></td>');
				if ($i % 2 == 0) {
					$css_class = 'even';
				} else {
					$css_class = 'odd';
				}
				echo('<td class="' . $css_class . '" height="30" align="left"><a href="/topic/view/' . $Topic->tpc_id . '.html" target="_self">' . make_plaintext($Topic->tpc_title) . '</a>&nbsp;');
				echo('<span class="tip_i"><small> ... viewed ' . $Topic->tpc_hits . ' times</small></span>');
				echo('</td>');
				echo('<td class="' . $css_class . '" width="120" height="30" align="left"><a href="/u/' . urlencode($Topic->usr_nick) . '"><img src="' . $img_p . '" class="portrait" align="absmiddle" border="0" /> ' . $Topic->usr_nick . '</a></td>');
				if ($Topic->tpc_lasttouched > $Topic->tpc_created) {
					echo('<td class="' . $css_class . '" width="120" height="30" align="left"><small class="time">' . make_descriptive_time($Topic->tpc_lasttouched) . '</small></td>');
				} else {
					echo('<td class="' . $css_class . '" width="120" height="30" align="left"><small class="time">' . make_descriptive_time($Topic->tpc_created) . '</small></td>');
				}
				echo('</tr>');
			}
			mysql_free_result($rs);
			if ($p['total'] > 1) {
				echo('<tr><td align="left" class="hf" colspan="4">');
				$this->vxDrawPages($p);
				echo('</td></tr>');
			}
		}
		echo('</table>');
		echo('</div>');
	}
	
	/* E module: Topic Fresh block */
	
	/* S module: Channel View block */
	
	public function vxChannelView($Channel) {
		$Node = new Node($Channel->chl_pid, $this->db);
		$Section = $Node->vxGetNodeInfo($Node->nod_sid);
		if ($this->User->vxIsLogin()) {
			$sql = "SELECT fav_id FROM babel_favorite WHERE fav_uid = {$this->User->usr_id} AND fav_type = 2 AND fav_res = {$Channel->chl_id}";
			$rs = mysql_query($sql, $this->db);
			if (mysql_num_rows($rs) == 1) {
				$Fav = mysql_result($rs, 0, 0);
			} else {
				$Fav = 0;
			}
			mysql_free_result($rs);
		} else {
			$Fav = 0;
		}
		echo('<div id="main">');
		echo('<div class="blank" align="left">');
		_v_ico_map();
		echo(' <a href="/">' . Vocabulary::site_name . '</a> &gt; <a href="/go/' . $Section->nod_name . '" target="_self">' . $Section->nod_title . '</a> &gt; <a href="/go/' . $Node->nod_name . '" target="_self">' . $Node->nod_title . '</a> &gt; ' . make_plaintext($Channel->chl_title));
		echo('</div>');
		echo('<div class="blank" align="left">');
		echo('<span class="text_large">');
		if ($Fav > 0) {
			$nod_ico = 'star';
		} else {
			$nod_ico = 'channel';
		}
		echo('<img src="/img/ico_' . $nod_ico . '.gif" align="absmiddle" class="home" />' . make_plaintext($Channel->chl_title));
		/* S: add to favorite */
		if ($this->User->vxIsLogin()) {
			if ($Fav > 0) {
				echo('<div id="chlFav" style="font-size: 12px; display: inline; margin-left: 10px;"><input type="image" onclick="removeFavoriteChannel(' . $Fav . ')" src="/img/icons/silk/lightning_minus.png" align="absmiddle" /></div>');
			} else {
				echo('<div id="chlFav" style="font-size: 12px; display: inline; margin-left: 10px;"><input type="image" onclick="addFavoriteChannel(' . $Channel->chl_id . ')" src="/img/icons/silk/lightning_add.png" align="absmiddle" /></div>');
			}
		}
		/* E: add to favorite */
		echo('</span>');
		echo('<br />本频道中共有 ' . count($Channel->rss->items) . ' 条消息');
		echo('，返回讨论版 <a href="/go/' . $Node->nod_name . '" class="nod">' . $Node->nod_title . '</a>');
		if ($Fav > 0) {
			echo('，你已经收藏了此频道');
		}
		if (!$Node->vxDrawChannels($Node->nod_id, $Channel->chl_id)) {
			echo('，无其他相关频道');
		}
		echo('</div>');
		
		echo('<div class="blank"><span class="rss_t">');
		if (isset($Channel->rss->image['url'])) {
			echo('<img src="' . $Channel->rss->image['url'] . '" align="absmiddle" style="margin: 5px 5px 0px 5px;" alt="' . $Channel->rss->channel['title'] . '" />&nbsp;&nbsp;');
		} else {
			echo('<img src="/img/icons/silk/feed.png" align="absmiddle" />&nbsp;&nbsp;');
		}
		if (isset($Channel->rss->channel['link'])) {
			echo('<a href="' . $Channel->rss->channel['link'] . '" target="_blank">' . make_plaintext($Channel->chl_title) . '</a> <img src="/img/ext.png" align="absmiddle" />');
		} else {
			echo(make_plaintext($Channel->chl_title));
		}
		
		if (isset($Channel->rss->channel['description'])) {
			echo('&nbsp;&nbsp;&nbsp;<span class="tip_i">' . strip_tags($Channel->rss->channel['description']) . '</span>');
		}
		echo('</span>');
		echo('<div></div>');
		_v_hr();
		$i = 0;
		foreach ($Channel->rss->items as $Item) {
			$i++;
			$css_color = rand_color(0, 2);
			echo('<div id="rss_' . $i . '" style="border: 2px solid #FFF; -moz-border-radius: 7px;"><div class="rss_entry_title" id="rss_entry_' . $i . '"><span class="rss_t"><a href="' . $Item['link'] . '" target="_blank" style="color: ' . $css_color . '" class="var">' . make_plaintext($Item['title']) . '</a>&nbsp;<img src="/img/ext.png" align="absmiddle" /></span>');
			
			if (isset($Item['pubdate'])) { // RSS 2.0
				$int_time = strtotime($Item['pubdate']);
			} else {
				if (isset($Item['dc']['date'])) { // RSS 0.9
					$int_time = strtotime($Item['dc']['date']);
				} else {
					if (isset($Item['created'])) { // Atom
						$int_time = strtotime($Item['created']);
					} else {
						if (isset($Item['issued'])) { // Atom
							$int_time = strtotime($Item['issued']);
						} else {
							if (isset($Item['published'])) { // Atom
								$int_time = strtotime($Item['published']);
							}	
						}
					}
				}
			}
			
			if ($int_time > 0 && $int_time < time()) {
				echo('<br /><small>Published on ');
				echo(date('Y-n-j G:i:s', $int_time));
				if (isset($Item['author'])) {
					echo(' by ' . $Item['author']);
				}
				echo('</small>');
			}
			echo('</div>');
			/* S: content */
			if (isset($Item['description'])) {
				if (isset($Item['content']['encoded'])) {
					$txt_content = trim($Item['content']['encoded']);
				} else {
					$txt_content = trim($Item['description']);
				}
				if (!preg_match('/(<br >)|(<table>)|(<div>)|(<p>)|(<\/p>)|(<p >)|(<br \/>)|(<br>)|(<br\/>)/i', $txt_content)) {
					$txt_content = nl2br($txt_content);
				} else {
					$txt_content = make_safe_display($txt_content);
				}
			} else {
				if (isset($Item['content']['encoded'])) {
					$txt_content = trim($Item['content']['encoded']);
					if (!preg_match('/(<br >)|(<table>)|(<div>)|(<p>)|(<\/p>)|(<p >)|(<br \/>)|(<br>)|(<br\/>)/i', $txt_content)) {
						$txt_content = nl2br($txt_content);
					} else {
						$txt_content = make_safe_display($txt_content);
					}	
				} else {
					$txt_content = '<a href="' . $Item['link'] . '" target="_blank">read more on ' . $Item['link'] . '</a>';
				}
			}
			echo '<div class="rss_entry_content">' . $txt_content . '</div></div>';
			_v_hr();
			/* E: content */
		}
		echo('<div id="rss_bottom"><span class="tip_i"><small>' . $i . ' items</small></span></div>');
		_v_hr();
		echo('<img src="/img/icons/silk/feed.png" align="absmiddle" /> 欢迎使用 RSS 阅读器订阅本页种子 <a href="' . $Channel->chl_url . '" rel="nofollow external">' . $Channel->chl_url . '</a>');
		
		echo('</div>');
		/* This part is not quite useful but it's really cool.
		echo('<script type="text/javascript" src="/js/babel_rss_scroll.js"> </script>');
		echo('<script type="text/javascript">');
		echo('offsets = calcOffsets(' . $i . ');');
		echo('window.onscroll = function() { checkPosition(' . $i . ', offsets); }');
		echo('</script>');
		*/
		echo('</div>');
	}
	
	/* E module: Channel View block */
	
	/* S module: Board View block */
	
	public function vxBoardView($board_id) {
		global $GOOGLE_AD_LEGAL;
		
		$Node = new Node($board_id, $this->db);
		$Section = $Node->vxGetNodeInfo($Node->nod_sid);
		if ($this->User->vxIsLogin()) {
			$sql = "SELECT fav_id FROM babel_favorite WHERE fav_uid = {$this->User->usr_id} AND fav_type = 1 AND fav_res = {$Node->nod_id}";
			$rs = mysql_query($sql, $this->db);
			if (mysql_num_rows($rs) == 1) {
				$Fav = mysql_result($rs, 0, 0);
			} else {
				$Fav = 0;
			}
			mysql_free_result($rs);
		} else {
			$Fav = 0;
		}
		echo('<div id="main">');
		echo('<div class="blank">');
		_v_ico_map();
		echo(' <a href="/">' . Vocabulary::site_name . '</a> &gt; <a href="/go/' . $Section->nod_name . '" target="_self">' . $Section->nod_title . '</a> &gt; ' . $Node->nod_title);
		echo('</div>');
		echo('<div class="blank" align="left">');
		echo('<span class="text_large">');
		if ($Fav > 0) {
			$nod_ico = 'star';
		} else {
			$nod_ico = 'board';
		}
		echo('<img src="/img/ico_' . $nod_ico . '.gif" align="absmiddle" class="home" />' . $Node->nod_title);
		/* S: add to favorite */
		if ($this->User->vxIsLogin()) {
			if ($Fav > 0) {
				echo('<div id="nodFav" style="font-size: 12px; display: inline; margin-left: 10px;"><input type="image" onclick="removeFavoriteNode(' . $Fav . ')" src="/img/icons/silk/lightning_delete.png" align="absmiddle" /></div>');
			} else {
				echo('<div id="nodFav" style="font-size: 12px; display: inline; margin-left: 10px;"><input type="image" onclick="addFavoriteNode(' . $Node->nod_id . ')" src="/img/icons/silk/lightning_add.png" align="absmiddle" /></div>');
			}
		}
		echo('<div style="font-size: 12px; display: inline;">&nbsp;<a href="/remix/' . $Node->nod_name . '"><img src="/img/icons/silk/shape_move_backwards.png" border="0" align="absmiddle" /></a></div>');
		/* E: add to favorite */
		echo('</span>');
		echo('<br />本讨论区中共有 ' . $Node->nod_topics . ' 个主题');
		/* S: how many favs */
		echo $Node->nod_favs ? '，共有 <a href="/who/fav/node/' . $Node->nod_name . '" class="t">&nbsp;' . $Node->nod_favs . '&nbsp;</a> 人收藏了此讨论区' : '，无人收藏此讨论区';
		echo('，<a href="/go/' . $Node->nod_name . '" class="var"><img src="/img/icons/silk/shape_move_backwards.png" align="absmiddle" border="0" /></a>&nbsp;<a href="/remix/' . $Node->nod_name . '" class="t">切换到 REMIX 模式</a>');
		/* E: how many favs */
		if (!$Node->vxDrawChannels()) {
			echo('，无相关频道');
		}
		
		
		$sql = "SELECT rlt_url, rlt_title FROM babel_related WHERE rlt_pid = {$Node->nod_id} ORDER BY rlt_url ASC";
		$rs = mysql_query($sql);
		if (mysql_num_rows($rs) > 0) {
			_v_hr();
			echo('<span class="chl">');
			echo('<img src="' . CDN_UI . 'img/icons/silk/world_go.png" align="absmiddle" alt="' . make_single_return($Node->nod_title) . '" /> ' . make_plaintext($Node->nod_title) . ' 相关网站 <span class="tip_i">');
			while ($Related = mysql_fetch_object($rs)) {
				$css_color = rand_color();
				echo(' ... <a style="color: ' . $css_color . '" class="var" href="' . $Related->rlt_url . '" target="_blank">' . $Related->rlt_title . '</a> <img src="' . CDN_IMG . 'ext.png" border="0" align="absmiddle" />');
				$Related = null;
			}
			echo('</span>');
			echo('</span>');
		}
		
		mysql_free_result($rs);
		echo('</div>');
		
		echo('<table width="100%" border="0" cellpadding="0" cellspacing="0" class="board">');
		echo('<tr><td align="left" class="hf" colspan="3">');
		if (strlen($Node->nod_header) > 0) {
				echo($Node->nod_header);
		}
		echo('</td><td class="hf" align="right">');
		_v_btn_l('创建新主题', '/topic/new/' . $Node->nod_id . '.vx');
		echo('</td></tr>');
		$p = array();
		$p['base'] = '/board/view/' . $board_id . '/';
		$p['ext'] = '.html';
		$sql = "SELECT COUNT(tpc_id) FROM babel_topic WHERE tpc_pid = {$board_id}";
		$rs = mysql_query($sql, $this->db);
		$p['items'] = mysql_result($rs, 0, 0);
		mysql_free_result($rs);
		if ($p['items'] > 0) {
			$p['size'] = BABEL_NOD_PAGE;
			$p['span'] = BABEL_PG_SPAN;
			if (($p['items'] % $p['size']) == 0) {
				$p['total'] = $p['items'] / $p['size'];
			} else {
				$p['total'] = floor($p['items'] / $p['size']) + 1;
			}
			if (isset($_GET['p'])) {
				$p['cur'] = intval($_GET['p']);
			} else {
				$p['cur'] = 1;
			}
			if ($p['cur'] < 1) {
				$p['cur'] = 1;
			}
			if ($p['cur'] > $p['total']) {
				$p['cur'] = $p['total'];
			}
			if (($p['cur'] - $p['span']) >= 1) {
				$p['start'] = $p['cur'] - $p['span'];
			} else {
				$p['start'] = 1;
			}
			if (($p['cur'] + $p['span']) <= $p['total']) {
				$p['end'] = $p['cur'] + $p['span'];
			} else {
				$p['end'] = $p['total'];
			}
			$_SESSION['babel_page_node_' . $Node->nod_id] = $p['cur'];
			$p['sql'] = ($p['cur'] - 1) * $p['size'];
			if ($p['items'] > 0 || $p['total'] > 0) {
				echo('<tr><td align="left" class="hf" colspan="4">');
				if ($p['total'] > 1) {	
					$this->vxDrawPages($p);
				}
				if ($p['items'] > 0) {
					echo('<span class="tip_i">');
					if ($p['total'] > 1) {
					echo(' ... ');
					}
					echo('<img src="/img/icons/silk/feed.png" align="absmiddle" alt="RSS" /> <a href="/feed/board/' . $Node->nod_name . '.rss">RSS 种子输出</a></span>');
				}
				_v_hr();
				echo('</td></tr>');
			}
			// sticky topics
			$sql = "SELECT tpc_id, tpc_pid, tpc_uid, tpc_title, tpc_hits, tpc_posts, tpc_created, tpc_lastupdated, tpc_lasttouched, usr_id, usr_nick, usr_gender, usr_portrait FROM babel_topic, babel_user WHERE tpc_uid = usr_id AND tpc_pid = {$Node->nod_id} AND tpc_flag = 2 ORDER BY tpc_lasttouched DESC, tpc_created DESC";
			$rs = mysql_query($sql, $this->db);
			$i = 0;
			while ($Topic = mysql_fetch_object($rs)) {
				$img_p = $Topic->usr_portrait ? '/img/p/' . $Topic->usr_portrait . '_n.jpg' : '/img/p_' . $Topic->usr_gender . '_n.gif';
				$i++;
				echo('<tr>');
				echo('<td width="24" height="30" align="center" valign="middle" class="star"><img src="' . CDN_UI . 'img/dot_orange.png" /></td>');
				if ($i % 2 == 0) {
					$css_class = 'even';
				} else {
					$css_class = 'odd';
				}
				echo('<td class="' . $css_class . '" height="30" align="left"><a href="/topic/view/' . $Topic->tpc_id . '.html" target="_self">' . make_plaintext($Topic->tpc_title) . '</a>&nbsp;');
				if ($Topic->tpc_posts > 0) {
					echo('<span class="tip_i"><small class="aqua">... ' . $Topic->tpc_posts . ' replies</small> <small>... viewed ' . $Topic->tpc_hits . ' times - sticky</small></span>');
				} else {
					echo('<span class="tip_i"><small>... no reply ... viewed ' . $Topic->tpc_hits . ' times - sticky</small></span>');
				}
				echo('</td>');
				echo('<td class="' . $css_class . '" width="120" height="30" align="left"><a href="/u/' . urlencode($Topic->usr_nick) . '"><img src="' . $img_p . '" class="portrait" align="absmiddle" /> ' . $Topic->usr_nick . '</a></td>');
				if ($Topic->tpc_lasttouched > $Topic->tpc_created) {
					echo('<td class="' . $css_class . '" width="120" height="30" align="left"><small class="time">' . make_descriptive_time($Topic->tpc_lasttouched) . '</small></td>');
				} else {
					echo('<td class="' . $css_class . '" width="120" height="30" align="left"><small class="time">' . make_descriptive_time($Topic->tpc_created) . '</small></td>');
				}
				echo('</tr>');
			}
			mysql_free_result($rs);
			
			// normal topics
			$sql = "SELECT tpc_id, tpc_pid, tpc_uid, tpc_title, tpc_hits, tpc_posts, tpc_created, tpc_lastupdated, tpc_lasttouched, usr_id, usr_nick, usr_gender, usr_portrait FROM babel_topic, babel_user WHERE tpc_uid = usr_id AND tpc_pid = {$Node->nod_id} AND tpc_flag = 0 ORDER BY tpc_lasttouched DESC, tpc_created DESC LIMIT {$p['sql']},{$p['size']}";
			$rs = mysql_query($sql, $this->db);
			while ($Topic = mysql_fetch_object($rs)) {
				$img_p = $Topic->usr_portrait ? '/img/p/' . $Topic->usr_portrait . '_n.jpg' : '/img/p_' . $Topic->usr_gender . '_n.gif';
				$i++;
				echo('<tr>');
				if ($Topic->usr_id == $this->User->usr_id) {
					$dot = 'green';
				} else {
					if ($this->User->vxIsLogin()) {
						if (array_key_exists($Topic->tpc_uid, $this->User->usr_friends)) {
							$dot = 'blue';
						} else {
							if ($Topic->tpc_posts > 10) {
								$dot = 'red';
							} else {
								$dot = 'gray';
							}
						}
					} else {
						if ($Topic->tpc_posts > 10) {
							$dot = 'red';
						} else {
							$dot = 'gray';
						}
					}
				}
				if ($Topic->tpc_posts == 0) {
					$img_dot = CDN_UI . 'img/icons/silk/weather_sun.png';
				} else {
					$img_dot = CDN_UI . 'img/dot_' . $dot . '.png';
				}
				echo('<td width="24" height="30" align="center" valign="middle" class="star"><img src="' . $img_dot . '" align="absmiddle" /></td>');
				if ($i % 2 == 0) {
					$css_class = 'even';
				} else {
					$css_class = 'odd';
				}
				echo('<td class="' . $css_class . '" height="30" align="left"><a href="/topic/view/' . $Topic->tpc_id . '.html" target="_self">' . make_plaintext($Topic->tpc_title) . '</a>&nbsp;');
				if ($Topic->tpc_posts > 0) {
					$plural_posts = $Topic->tpc_posts > 1 ? 'replies' : 'reply';
					echo('<span class="tip_i"><small class="aqua">... ' . $Topic->tpc_posts . ' ' . $plural_posts . '</small> <small>... viewed ' . $Topic->tpc_hits . ' times</small></span>');
				} else {
					echo('<span class="tip_i"><small>... no reply ... viewed ' . $Topic->tpc_hits . ' times</small></span>');
				}
				echo('</td>');
				echo('<td class="' . $css_class . '" width="120" height="30" align="left"><a href="/u/' . urlencode($Topic->usr_nick) . '"><img src="' . $img_p . '" class="portrait" align="absmiddle" /> ' . $Topic->usr_nick . '</a></td>');
				if ($Topic->tpc_lasttouched > $Topic->tpc_created) {
					echo('<td class="' . $css_class . '" width="120" height="30" align="left"><small class="time">' . make_descriptive_time($Topic->tpc_lasttouched) . '</small></td>');
				} else {
					echo('<td class="' . $css_class . '" width="120" height="30" align="left"><small class="time">' . make_descriptive_time($Topic->tpc_created) . '</small></td>');
				}
				echo('</tr>');
			}
			mysql_free_result($rs);
			
			if ($p['items'] > 0 || $p['total'] > 0) {
				echo('<tr><td align="left" class="hf" colspan="4">');
				_v_hr();
				if ($p['total'] > 1) {	
					$this->vxDrawPages($p);
				}
				if ($p['items'] > 0) {
					echo('<span class="tip_i">');
					if ($p['total'] > 1) {
					echo(' ... ');
					}
					echo('<img src="/img/icons/silk/feed.png" align="absmiddle" alt="RSS" /> <a href="/feed/board/' . $Node->nod_name . '.rss">RSS 种子输出</a></span>');
				}
				echo('</td></tr>');
			}
		}
		if (strlen($Node->nod_footer) > 0) {
			echo('<tr><td align="left" class="hf" colspan="4">' . $Node->nod_footer . '</td></tr>');
		}
		
		/* S ultimate cool flickr */
		
		$tag = $Node->nod_name;
		if ($this->User->usr_id == 1) {
			$f = Image::vxFlickrBoardBlock($tag, $this->User->usr_width, 4);
			echo $f;
			$this->cl->save($f, 'go_flickr_' . $tag);
		} else {
			if ($f = $this->cl->load('go_flickr_' . $tag)) {
				echo $f;
			} else {
				$f = Image::vxFlickrBoardBlock($tag, $this->User->usr_width, 4);
				echo $f;
				$this->cl->save($f, 'go_flickr_' . $tag);
			}
		}
		
		/* E ultimate cool Flickr */
		
		/* S ultimate cool Technorati */
		
		if (TN_API_ENABLED) {
			$tn = TN_PREFIX . $Node->nod_name;
			
			$T = fetch_rss($tn);
			echo('<tr><td align="left" class="hf" colspan="4" style="border-top: 1px solid #CCC;">');
			echo('<a href="http://www.technorati.com/tags/' . $Node->nod_name . '"><img src="/img/tn_logo.gif" align="absmiddle" border="0" /></a>&nbsp;&nbsp;&nbsp;<span class="tip_i">以下条目链接到外部的与本讨论主题 [ ' . $Node->nod_title . ' ] 有关的 Blog。</span>');
			echo('</td></tr>');
			$b = count($T->items) > 6 ? 6 : count($T->items);
			for ($i = 0; $i < $b; $i++) {
				$Related = $T->items[$i];
				$css_class = $i % 2 ? 'odd' : 'even';
				$css_color = rand_color();
				@$count = $Related['tapi']['inboundlinks'] + $Related['tapi']['inboundblogs'];
				$css_font_size = '12';
				echo('<tr><td width="24" height="22" align="center"><a href="' . $Related['comments'] . '" target="_blank" rel="nofollow external"><img src="/img/tnico_cosmos.gif" align="absmiddle" border="0" /></a></td>');
				echo('<td class="' . $css_class . '" height="22" align="left">');
				if (isset($Related['title'])) {
					echo '<a href="' . $Related['link'] . '" target="_blank" rel="external nofollow" class="var" style="color: ' . $css_color . '; font-size: ' . $css_font_size . 'px;">' . $Related['title'] . '</a>';
				} else {
					echo '<a href="' . $Related['link'] . '" target="_blank" rel="external nofollow">' . $Related['link'] . '</a>';
				}
				echo('</td>');
				
				echo('<td class="' . $css_class . '" width="120" height="22" align="left">');
				if (isset($Related['tapi']['inboundlinks'])) {
					echo('<span class="tip_i"><small>' . $Related['tapi']['inboundlinks'] . ' inbound links</small></span>');
				}
				echo('</td>');
				echo('<td class="' . $css_class . '" width="120" height="22" align="left"><small class="time">' . make_descriptive_time($Related['date_timestamp']) . '</small></td>');
				echo('</tr>');
			}
		}
		
		/* E ultimate cool technorati */
		echo('</table>');
		echo('<div class="_hh"></div>');
		echo('</div>');
	}
	
	/* E module: Board View block */
	
	/* S module: Who Fav Node block */
	
	public function vxWhoFavNode($node_id, $node_level) {
		global $GOOGLE_AD_LEGAL;
		
		$Node = new Node($node_id, $this->db);
		if ($node_level > 1) {
			$Section = $Node->vxGetNodeInfo($Node->nod_sid);
		}
		
		echo('<div id="main">');
		
		echo('<div class="blank" align="left">');
		_v_ico_map();
		if ($Node->nod_level > 1) {
			echo(' <a href="/">' . Vocabulary::site_name . '</a> &gt; <a href="/go/' . $Section->nod_name . '" target="_self">' . $Section->nod_title . '</a> &gt; <a href="/go/' . $Node->nod_name . '">' . $Node->nod_title . '</a> &gt; 谁收藏了本讨论区？');
		} else {
			echo(' <a href="/">' . Vocabulary::site_name . '</a> &gt; <a href="/go/' . $Section->nod_name . '" target="_self">' . $Section->nod_title . '</a> &gt; 谁收藏了本区域？');
		}
		echo('</div>');
		
		$sql = "SELECT usr_id, usr_geo, usr_gender, usr_nick, usr_portrait, fav_created FROM babel_user, babel_favorite WHERE usr_id = fav_uid AND fav_res = {$Node->nod_id} AND fav_type = 1 ORDER BY fav_created DESC";
		$rs = mysql_query($sql);
		
		echo('<div class="blank">');
		echo('<img src="/img/icons/silk/lightning_add.png" align="absmiddle" /> ');
		if ($Node->nod_level > 1) {
			echo(make_plaintext($Node->nod_title) . ' 讨论区的收藏者');
		} else {
			echo(make_plaintext($Node->nod_title) . ' 区域的收藏者');
		}
		
		echo('<span class="tip_i"> ... 共 ' . mysql_num_rows($rs) .' 人</span>');
		
		if ($_SESSION['babel_ua']['GECKO_DETECTED'] || $_SESSION['babel_ua']['KHTML_DETECTED'] || $_SESSION['babel_ua']['OPERA_DETECTED']) {
			
			$hack_width = 'width="100%" ';
		} else {
			$hack_width = 'width="99%" ';
		}
		
		echo('<table ' . $hack_width . 'cellpadding="0" cellspacing="0" border="0" class="fav">');
		
		$i = 0;
		
		$edges = array();
		for ($i = 1; $i < 1000; $i++) {
			$edges[] = ($i * 5) + 1;
		}

		while ($Fav = mysql_fetch_object($rs)) {
			$i++;
			if (in_array($i, $edges)) {
				echo('<tr><td>');
			}
			$img_p = $Fav->usr_portrait ? '/img/p/' . $Fav->usr_portrait . '.jpg' : '/img/p_' . $Fav->usr_gender . '.gif';
			$img_p_s = $Fav->usr_portrait ? '/img/p/' . $Fav->usr_portrait . '_n.jpg' : '/img/p_' . $Fav->usr_gender . '_n.gif';
			if ($Fav->usr_geo != 'earth') {
				echo('<a href="/u/' . urlencode($Fav->usr_nick) . '" class="friend"><img src="' . $img_p . '" class="portrait" /><br />' . $Fav->usr_nick . '<div class="tip">' . $this->Geo->map['name'][$Fav->usr_geo] . '</div></a>');
			} else {
				echo('<a href="/u/' . urlencode($Fav->usr_nick) . '" class="friend"><img src="' . $img_p . '" class="portrait" /><br />' . $Fav->usr_nick . '</a>');
			}
			if (($i % 5) == 0) {
				echo ('</td></tr>');
			}
		}
		
		mysql_free_result($rs);
		
		echo('</table>');
		
		echo('</div>');

		echo('</div>');
	}
	
	/* E module: Who Fav Node block */
	
	/* S module: Who Fav Topic block */
	
	public function vxWhoFavTopic($Topic) {
		global $GOOGLE_AD_LEGAL;
		
		$Node = new Node($Topic->tpc_pid, $this->db);
		$Section = $Node->vxGetNodeInfo($Node->nod_sid);
		
		echo('<div id="main">');
		
		echo('<div class="blank" align="left">');
		_v_ico_map();
		echo(' <a href="/">' . Vocabulary::site_name . '</a> &gt; <a href="/go/' . $Section->nod_name . '" target="_self">' . $Section->nod_title . '</a> &gt; <a href="/go/' . $Node->nod_name . '">' . $Node->nod_title . '</a> &gt; <a href="/topic/view/' . $Topic->tpc_id . '.html">' . make_plaintext($Topic->tpc_title) . '</a> &gt; 谁收藏了本主题？');
		
		echo('</div>');
		
		$sql = "SELECT usr_id, usr_geo, usr_gender, usr_nick, usr_portrait, fav_created FROM babel_user, babel_favorite WHERE usr_id = fav_uid AND fav_res = {$Topic->tpc_id} AND fav_type = 0 ORDER BY fav_created DESC";
		$rs = mysql_query($sql);
		
		echo('<div class="blank">');
		echo('<img src="/img/icons/silk/lightning_add.png" align="absmiddle" /> ');
		echo(make_plaintext($Topic->tpc_title) . ' 主题的收藏者');
		
		echo('<span class="tip_i"> ... 共 ' . mysql_num_rows($rs) .' 人</span>');
		
		if ($_SESSION['babel_ua']['GECKO_DETECTED'] || $_SESSION['babel_ua']['KHTML_DETECTED'] || $_SESSION['babel_ua']['OPERA_DETECTED']) {
			
			$hack_width = 'width="100%" ';
		} else {
			$hack_width = 'width="99%" ';
		}
		
		echo('<table ' . $hack_width . 'cellpadding="0" cellspacing="0" border="0" class="fav">');
		
		$i = 0;
		
		$edges = array();
		for ($i = 1; $i < 1000; $i++) {
			$edges[] = ($i * 5) + 1;
		}

		while ($Fav = mysql_fetch_object($rs)) {
			$i++;
			if (in_array($i, $edges)) {
				echo('<tr><td>');
			}
			$img_p = $Fav->usr_portrait ? '/img/p/' . $Fav->usr_portrait . '.jpg' : '/img/p_' . $Fav->usr_gender . '.gif';
			$img_p_s = $Fav->usr_portrait ? '/img/p/' . $Fav->usr_portrait . '_n.jpg' : '/img/p_' . $Fav->usr_gender . '_n.gif';
			if ($Fav->usr_geo != 'earth') {
				echo('<a href="/u/' . urlencode($Fav->usr_nick) . '" class="friend"><img src="' . $img_p . '" class="portrait" /><br />' . $Fav->usr_nick . '<div class="tip">' . $this->Geo->map['name'][$Fav->usr_geo] . '</div></a>');
			} else {
				echo('<a href="/u/' . urlencode($Fav->usr_nick) . '" class="friend"><img src="' . $img_p . '" class="portrait" /><br />' . $Fav->usr_nick . '</a>');
			}
			if (($i % 5) == 0) {
				echo ('</td></tr>');
			}
		}
		
		mysql_free_result($rs);
		
		echo('</table>');
		
		echo('</div>');

		echo('</div>');
	}
	
	/* E module: Who Fav Topic block */
	
	/* S module: Who Settle Geo block */
	
	public function vxWhoSettleGeo($geo) {
		global $GOOGLE_AD_LEGAL;
		
		$Geo = new Geo($geo, $this->Geo->map);
		$geos_all_children = $Geo->vxGetRecursiveChildrenArray('', true);
		$geos_all_children_sql = implode(',', $geos_all_children);
		
		echo('<div id="main">');
		
		echo('<div class="blank" align="left">');
		_v_ico_map();
		echo(' <a href="/">' . Vocabulary::site_name . '</a> &gt; ' . Vocabulary::term_region . ' &gt; <a href="/geo/' . $geo . '">' . $Geo->geo->name->cn . '</a> &gt; 谁在' . $Geo->geo->name->cn);
		
		echo('</div>');
		
		if (get_magic_quotes_gpc()) {
			$geo_real = mysql_real_escape_string(stripslashes($geo), $this->db);
		} else {
			$geo_real = mysql_real_escape_string($geo, $this->db);
		}
		
		if ($usr_count = $this->cs->get('babel_geo_settle_' . $Geo->geo->geo)) {
			$usr_count = intval($usr_count);
		} else {
			$sql = "SELECT COUNT(*) FROM babel_user WHERE usr_geo IN ({$geos_all_children_sql})";
			$rs = mysql_query($sql, $this->db);
			$usr_count = mysql_result($rs, 0, 0);
			$this->cs->save(strval($usr_count), 'babel_geo_settle_' . $Geo->geo->geo);
		}
		
		$page_size = 15;
		
		if ($usr_count > $page_size) {
			if (($usr_count % $page_size) == 0) {
				$page_count = intval($usr_count / $page_size);
			} else {
				$page_count = floor($usr_count / $page_size) + 1;
			}
		} else {
			$page_count = 1;
		}
		if (isset($_GET['p'])) {
			$page_current = intval($_GET['p']);
			if ($page_current < 1) {
				$page_current = 1;
			}
			if ($page_current > $page_count) {
				$page_current = $page_count;
			}
		} else {
			$page_current = 1;
		}
		$page_sql = ($page_current - 1) * $page_size;
		
		$sql = "SELECT usr_id, usr_geo, usr_gender, usr_nick, usr_portrait, usr_hits, usr_created FROM babel_user WHERE usr_geo IN ({$geos_all_children_sql}) ORDER BY usr_created DESC LIMIT {$page_sql}, {$page_size}";
		$rs = mysql_query($sql);
		
		echo('<div class="blank">');
		echo('<img src="/img/pico_home.gif" align="absmiddle" class="portrait" /> ');
		echo('谁在<a href="/geo/' . $Geo->geo->geo . '">' . $Geo->geo->name->cn . '</a>');
		
		echo('<span class="tip_i"> ... 共 ' . $usr_count .' 人 ...</span> ');
		
		if ($page_current < $page_count) {
			echo('<a href="/who/settle/' . $Geo->geo->geo . '/' . ($page_current + 1) . '.html" class="t">下一页</a>&nbsp;');
		}
		if ($page_current > 1) {
			echo('&nbsp;<a href="/who/settle/' . $Geo->geo->geo . '/' . ($page_current - 1) . '.html" class="t">上一页</a>');
		}
		
		echo('<span class="tip_i"> ... ' . $page_current . '/' . $page_count . '</span>');
		
		if ($_SESSION['babel_ua']['GECKO_DETECTED'] || $_SESSION['babel_ua']['KHTML_DETECTED'] || $_SESSION['babel_ua']['OPERA_DETECTED']) {
			$hack_width = 'width="100%" ';
		} else {
			$hack_width = 'width="99%" ';
		}
		
		echo('<table ' . $hack_width . 'cellpadding="0" cellspacing="0" border="0" class="fav">');
		
		$i = 0;
		
		$edges = array();
		for ($i = 1; $i < ($page_size * 2); $i++) {
			$edges[] = ($i * 5) + 1;
		}

		while ($Who = mysql_fetch_object($rs)) {
			$i++;
			if (in_array($i, $edges)) {
				echo('<tr><td>');
			}
			$img_p = $Who->usr_portrait ? '/img/p/' . $Who->usr_portrait . '.jpg' : '/img/p_' . $Who->usr_gender . '.gif';
			if ($Who->usr_geo != $Geo->geo->geo) {
				echo('<a href="/u/' . urlencode($Who->usr_nick) . '" class="friend"><img src="' . $img_p . '" class="portrait" /><br />' . $Who->usr_nick . '<div class="tip">' . $this->Geo->map['name'][$Who->usr_geo] . '</div></a>');
			} else {
				echo('<a href="/u/' . urlencode($Who->usr_nick) . '" class="friend"><img src="' . $img_p . '" class="portrait" /><br />' . $Who->usr_nick . '</a>');
			}
			if (($i % 5) == 0) {
				echo ('</td></tr>');
			}
		}
		
		mysql_free_result($rs);
		
		
		echo('</table>');
		
		_v_hr();
		
		if ($page_current < $page_count) {
			echo('&nbsp;&nbsp;&nbsp;<a href="/who/settle/' . $Geo->geo->geo . '/' . ($page_current + 1) . '.html" class="t">下一页</a>');
		}
		if ($page_current > 1) {
			echo('&nbsp;&nbsp;&nbsp;<a href="/who/settle/' . $Geo->geo->geo . '/' . ($page_current - 1) . '.html" class="t">上一页</a>');
		}
		
		echo('<span class="tip_i"> ... ' . $page_current . '/' . $page_count . '</span>');
		
		echo('</div>');

		echo('</div>');
	}
	
	/* E module: Who Settle Geo block */
	
	/* S module: Who Connect User block */
	
	public function vxWhoConnectUser($user_id) {
		global $GOOGLE_AD_LEGAL;
		
		$User = $this->User->vxGetUserInfo($user_id);
		$User->usr_nick_md5 = md5($User->usr_nick);

		echo('<div id="main">');
		
		echo('<div class="blank" align="left">');
		_v_ico_map();
		echo(' <a href="/">' . Vocabulary::site_name . '</a> &gt; <a href="/u/' . urlencode($User->usr_nick) . '">' . make_plaintext($User->usr_nick) . '</a> &gt; 谁把 ' . $User->usr_nick . ' 加为好友？');
		
		echo('</div>');
		
		if ($usr_count = $this->cs->get('babel_who_connect_' . strval(crc32($User->usr_nick)))) {
			$usr_count = intval($usr_count);
			if (BABEL_DEBUG) {
				$_SESSION['babel_debug_log'][time()] = 'Cache hit: ' . 'babel_who_connect_' . strval(crc32($User->usr_nick));
			}
		} else {
			$sql = "SELECT COUNT(frd_uid) FROM babel_friend WHERE frd_fid = {$User->usr_id}";
			$rs = mysql_query($sql, $this->db);
			$usr_count = mysql_result($rs, 0, 0);
			$this->cs->save(strval($usr_count), 'babel_who_connect_' . strval(crc32($User->usr_nick)));
		}
		
		$page_size = 15;
		
		if ($usr_count > $page_size) {
			if (($usr_count % $page_size) == 0) {
				$page_count = intval($usr_count / $page_size);
			} else {
				$page_count = floor($usr_count / $page_size) + 1;
			}
		} else {
			$page_count = 1;
		}
		if (isset($_GET['p'])) {
			$page_current = intval($_GET['p']);
			if ($page_current < 1) {
				$page_current = 1;
			}
			if ($page_current > $page_count) {
				$page_current = $page_count;
			}
		} else {
			$page_current = 1;
		}
		$page_sql = ($page_current - 1) * $page_size;
		
		$sql = "SELECT usr_id, usr_geo, usr_gender, usr_nick, usr_portrait, usr_hits, usr_created FROM babel_user WHERE usr_id IN (SELECT frd_uid FROM babel_friend WHERE frd_fid = {$User->usr_id}) ORDER BY usr_created DESC LIMIT {$page_sql}, {$page_size}";
		$sql_md5 = md5($sql);
		if ($Sources = $this->cl->load('babel_sql_' . $sql_md5)) {
			$Sources = unserialize($Sources);
			if (BABEL_DEBUG) {
				$_SESSION['babel_debug_log'][time()] = 'Cache hit: ' . 'babel_sql_' . $sql_md5;
			}
		} else {
			$rs = mysql_query($sql);
			$Sources = array();
			while ($Who = mysql_fetch_object($rs)) {
				$Sources[] = $Who;
			}
			mysql_free_result($rs);
			$this->cl->save(serialize($Sources), 'babel_sql_' . $sql_md5);
		}
		
		echo('<div class="blank">');
		echo('<img src="/img/icons/silk/heart_add.png" align="absmiddle" /> ');
		echo('谁把 <a href="/u/' . urlencode($User->usr_nick) . '">' . make_plaintext($User->usr_nick) . '</a> 加为好友？');
		
		if ($usr_count > 0) {
			echo('<span class="tip_i"> ... 共 ' . $usr_count .' 人 ...</span> ');
		}
		
		if ($page_current < $page_count) {
			echo('<a href="/who/connect/' . urlencode($User->usr_nick) . '/' . ($page_current + 1) . '.html" class="t">下一页</a>&nbsp;');
		}
		if ($page_current > 1) {
			echo('&nbsp;<a href="/who/connect/' . urlencode($User->usr_nick) . '/' . ($page_current - 1) . '.html" class="t">上一页</a>');
		}
		
		if ($page_count > 1) {
			echo('<span class="tip_i"> ... ' . $page_current . '/' . $page_count . '</span>');
		}
		
		if ($_SESSION['babel_ua']['GECKO_DETECTED'] || $_SESSION['babel_ua']['KHTML_DETECTED'] || $_SESSION['babel_ua']['OPERA_DETECTED']) {
			$hack_width = 'width="100%" ';
		} else {
			$hack_width = 'width="99%" ';
		}
		
		echo('<table ' . $hack_width . 'cellpadding="0" cellspacing="0" border="0" class="fav">');
		
		$i = 0;
		
		$edges = array();
		for ($i = 1; $i < ($page_size * 2); $i++) {
			$edges[] = ($i * 5) + 1;
		}

		foreach ($Sources as $Who) {
			$i++;
			if (in_array($i, $edges)) {
				echo('<tr><td>');
			}
			$img_p = $Who->usr_portrait ? '/img/p/' . $Who->usr_portrait . '.jpg' : '/img/p_' . $Who->usr_gender . '.gif';
			echo('<a href="/u/' . urlencode($Who->usr_nick) . '" class="friend"><img src="' . $img_p . '" class="portrait" /><br />' . $Who->usr_nick . '<div class="tip">' . $this->Geo->map['name'][$Who->usr_geo] . '</div></a>');
			if (($i % 5) == 0) {
				echo ('</td></tr>');
			}
		}
		
		echo('</table>');
		
		_v_hr();
		
		if ($page_current < $page_count) {
			echo('&nbsp;&nbsp;&nbsp;<a href="/who/connect/' . urlencode($User->usr_nick) . '/' . ($page_current + 1) . '.html" class="t">下一页</a>');
		}
		if ($page_current > 1) {
			echo('&nbsp;&nbsp;&nbsp;<a href="/who/connect/' . urlencode($User->usr_nick) . '/' . ($page_current - 1) . '.html" class="t">上一页</a>');
		}
		if ($page_count > 1) {
			echo('<span class="tip_i"> ... ' . $page_current . '/' . $page_count . '</span>');
		}
		
		echo('</div>');

		echo('</div>');
	}
	
	/* E module: Who Connect User block */
	
	/* S module: Topic Archive User block */
	
	public function vxTopicArchiveUser($User) {
		global $GOOGLE_AD_LEGAL;
		
		echo('<div id="main">');
		echo('<div class="blank" align="left">');
		_v_ico_map();
		echo(' <a href="/">' . Vocabulary::site_name . '</a> &gt; <a href="/u/' . urlencode($User->usr_nick) . '" target="_self">' . make_plaintext($User->usr_nick) . '</a> &gt; 所有主题');
		echo('</div>');
		echo('<div class="blank" align="left">');
		echo('<span class="text_large">');
		$ico = 'board';
		echo('<img src="/img/ico_' . $ico . '.gif" align="absmiddle" class="home" /><a href="/u/' . urlencode($User->usr_nick) . '">' . make_plaintext($User->usr_nick) . '</a>');
		echo('</span>');
		$sql = "SELECT SUM(tpc_hits) AS tpc_hits_user_total FROM babel_topic WHERE tpc_uid = {$User->usr_id}";
		$rs = mysql_query($sql, $this->db);
		$count_hits = mysql_result($rs, 0, 0);
		if (!$count_hits) $count_hits = 0;
		mysql_free_result($rs);
		
		$p = array();
		$p['base'] = '/topic/archive/user/' . urlencode($User->usr_nick) . '/';
		$p['ext'] = '.html';
		$sql = "SELECT COUNT(tpc_id) FROM babel_topic WHERE tpc_uid = {$User->usr_id}";
		$rs = mysql_query($sql, $this->db);
		$p['items'] = mysql_result($rs, 0, 0);
		mysql_free_result($rs);
		echo('<br /><span class="tip_i"><a href="/u/' . urlencode($User->usr_nick) . '">' . make_plaintext($User->usr_nick) . '</a>，共创建过 ' . $p['items'] . ' 个主题，');
		if ($this->tpc_count > 0) {
			printf("占整个社区的比率 %.3f%%，", ($p['items'] / $this->tpc_count) * 100);
		}
		echo('这些主题一共被点击过 ' . $count_hits . ' 次。');
		
		echo('</span></div>');
		echo('<table width="100%" border="0" cellpadding="0" cellspacing="2" class="board">');
		echo('<tr><td align="left" class="hf" colspan="3"></td><td class="hf" align="right"></td></tr>');
		if ($p['items'] > 0) {
			$p['size'] = BABEL_NOD_PAGE;
			$p['span'] = BABEL_PG_SPAN;
			if (($p['items'] % $p['size']) == 0) {
				$p['total'] = $p['items'] / $p['size'];
			} else {
				$p['total'] = floor($p['items'] / $p['size']) + 1;
			}
			if (isset($_GET['p'])) {
				$p['cur'] = intval($_GET['p']);
			} else {
				$p['cur'] = 1;
			}
			if ($p['cur'] < 1) {
				$p['cur'] = 1;
			}
			if ($p['cur'] > $p['total']) {
				$p['cur'] = $p['total'];
			}
			if (($p['cur'] - $p['span']) >= 1) {
				$p['start'] = $p['cur'] - $p['span'];
			} else {
				$p['start'] = 1;
			}
			if (($p['cur'] + $p['span']) <= $p['total']) {
				$p['end'] = $p['cur'] + $p['span'];
			} else {
				$p['end'] = $p['total'];
			}
			$p['sql'] = ($p['cur'] - 1) * $p['size'];

			$sql = "SELECT tpc_id, tpc_pid, tpc_uid, tpc_title, tpc_hits, tpc_posts, tpc_created, tpc_lastupdated, tpc_lasttouched, usr_id, usr_nick, usr_gender, usr_portrait FROM babel_topic, babel_user WHERE tpc_uid = usr_id AND tpc_uid = {$User->usr_id} AND tpc_flag IN (0, 2) ORDER BY tpc_lasttouched DESC, tpc_created DESC LIMIT {$p['sql']}, {$p['size']}";
			$sql_hash = md5($sql);
			if ($topics = $this->cl->load('babel_sql_' . $sql_hash)) {
				$topics = unserialize($topics);
			} else {
				$topics = array();
				$rs = mysql_query($sql, $this->db);
				while ($Topic = mysql_fetch_object($rs)) {
					$topics[$Topic->tpc_id] = $Topic;
				}
				mysql_free_result($rs);
				$this->cl->save(serialize($topics), 'babel_sql_' . $sql_hash);
			}
			
			if ($p['items'] > 0 || $p['total'] > 0) {
				echo('<tr><td align="left" class="hf" colspan="4">');
				if ($p['total'] > 1) {	
					$this->vxDrawPages($p);
				}
				if ($p['items'] > 0) {
					echo('<span class="tip_i">');
					if ($p['total'] > 1) {
					echo(' ... ');
					}
					echo('<img src="/img/icons/silk/feed.png" align="absmiddle" alt="RSS" /> <a href="/feed/user/' . urlencode($User->usr_nick) . '">RSS 种子输出</a></span>');
				}
				_v_hr();
				echo('</td></tr>');
			}
			
			$i = 0;
			foreach ($topics as $tpc_id => $Topic) {
				$img_p = $Topic->usr_portrait ? '/img/p/' . $Topic->usr_portrait . '_n.jpg' : '/img/p_' . $Topic->usr_gender . '_n.gif';
				$i++;
				echo('<tr>');
				echo('<td width="24" height="30" align="center" valign="middle" class="star"><img src="/img/star_inactive.png" /></td>');
				if ($i % 2 == 0) {
					$css_class = 'even';
				} else {
					$css_class = 'odd';
				}
				echo('<td class="' . $css_class . '" height="30" align="left"><a href="/topic/view/' . $Topic->tpc_id . '.html" target="_self">' . make_plaintext($Topic->tpc_title) . '</a>&nbsp;');
				if ($Topic->tpc_posts > 0) {
					$plural_posts = $Topic->tpc_posts > 1 ? 'replies' : 'reply';
					echo('<span class="tip_i"><small class="aqua">... ' . $Topic->tpc_posts . ' ' . $plural_posts . '</small> <small>... viewed ' . $Topic->tpc_hits . ' times</small></span>');
				} else {
					echo('<span class="tip_i"><small>... no reply ... viewed ' . $Topic->tpc_hits . ' times</small></span>');
				}
				echo('</td>');
				echo('<td class="' . $css_class . '" width="120" height="30" align="left"><a href="/u/' . urlencode($Topic->usr_nick) . '"><img src="' . $img_p . '" class="portrait" align="absmiddle" /> ' . $Topic->usr_nick . '</a></td>');
				if ($Topic->tpc_lasttouched > $Topic->tpc_created) {
					echo('<td class="' . $css_class . '" width="120" height="30" align="left"><small class="time">' . make_descriptive_time($Topic->tpc_lasttouched) . '</small></td>');
				} else {
					echo('<td class="' . $css_class . '" width="120" height="30" align="left"><small class="time">' . make_descriptive_time($Topic->tpc_created) . '</small></td>');
				}
				echo('</tr>');
			}
			if ($p['items'] > 0 || $p['total'] > 0) {
				echo('<tr><td align="left" class="hf" colspan="4">');
				_v_hr();
				if ($p['total'] > 1) {	
					$this->vxDrawPages($p);
				}
				if ($p['items'] > 0) {
					echo('<span class="tip_i">');
					if ($p['total'] > 1) {
					echo(' ... ');
					}
					echo('<img src="/img/icons/silk/feed.png" align="absmiddle" alt="RSS" /> <a href="/feed/user/' . urlencode($User->usr_nick) . '">RSS 种子输出</a></span>');
				}
				echo('</td></tr>');
			}
		} else {
			echo('<tr><td align="left" class="hf" colspan="4"><span class="tip_i">尚未创建过任何主题 ...</span></td></tr>');
		}
		
		/* S ultimate cool Flickr */
		
		$tag = $User->usr_nick;
		if ($this->User->usr_id == 1) {
			$f = Image::vxFlickrBoardBlock($tag, $this->User->usr_width, 4);
			echo $f;
			$this->cl->save($f, 'go_flickr_' . $tag);
		} else {
			if ($f = $this->cl->load('go_flickr_' . $tag)) {
				echo $f;
			} else {
				$f = Image::vxFlickrBoardBlock($tag, $this->User->usr_width, 4);
				echo $f;
				$this->cl->save($f, 'go_flickr_' . $tag);
			}
		}
		
		/* E ultimate cool Flickr */
		
		/* S ultimate cool Technorati */
		
		if (TN_API_ENABLED) {
			$tn = TN_PREFIX . $User->usr_nick;
			
			if ($T = fetch_rss($tn)) {
				echo('<tr><td align="left" class="hf" colspan="4" style="border-top: 1px solid #CCC;">');
				echo('<a href="http://www.technorati.com/tags/' . $User->usr_nick . '"><img src="/img/tn_logo.gif" align="absmiddle" border="0" /></a>&nbsp;&nbsp;&nbsp;<span class="tip_i">以下条目链接到外部的与本讨论主题 [ ' . $User->usr_nick . ' ] 有关的 Blog。</span>');
				echo('</td></tr>');
				$b = count($T->items) > 6 ? 6 : count($T->items);
				for ($i = 0; $i < $b; $i++) {
					$Related = $T->items[$i];
					if (isset($Related['link'])) {
						$css_class = $i % 2 ? 'odd' : 'even';
						$css_color = rand_color();
						@$count = $Related['tapi']['inboundlinks'] + $Related['tapi']['inboundblogs'];
						$css_font_size = '12';
						if (isset($Related['comments'])) {
							echo('<tr><td width="24" height="22" align="center"><a href="' . $Related['comments'] . '" target="_blank" rel="nofollow external"><img src="/img/tnico_cosmos.gif" align="absmiddle" border="0" /></a></td>');
						} else {
							echo('<tr><td width="24" height="22" align="center"><img src="/img/tnico_cosmos.gif" align="absmiddle" border="0" /></td>');
						}
						echo('<td class="' . $css_class . '" height="22" align="left">');	
						if (isset($Related['title'])) {
							echo '<a href="' . $Related['link'] . '" target="_blank" rel="external nofollow" class="var" style="color: ' . $css_color . '; font-size: ' . $css_font_size . 'px;">' . $Related['title'] . '</a>';
						} else {
							echo '<a href="' . $Related['link'] . '" target="_blank" rel="external nofollow">' . $Related['link'] . '</a>';
						}
						echo('</td>');
						
						echo('<td class="' . $css_class . '" width="120" height="22" align="left">');
						if (isset($Related['tapi']['inboundlinks'])) {
							echo('<span class="tip_i"><small>' . $Related['tapi']['inboundlinks'] . ' inbound links</small></span>');
						}
						echo('</td>');
						if (isset($Related['date_timestamp'])) {
							$t = $Related['date_timestamp'];
						} else {
							$t = time();
						}
						echo('<td class="' . $css_class . '" width="120" height="22" align="left"><small class="time">' . make_descriptive_time($t) . '</small></td>');
						echo('</tr>');
					}
				}
			}
		}
		echo('</table>');
		echo('</div>');
	}

	/* E module: Topic Archive User block */
	
	/* S module: Post Modify block */
	
	public function vxPostModify($Post) {
		$Topic = new Topic($Post->pst_tid, $this->db, 0);
		$Node = new Node($Topic->tpc_pid, $this->db);
		$Section = $Node->vxGetNodeInfo($Node->nod_sid);
		$permit = 0; // Default: modify is not allowed
		if ($this->User->usr_id == $Post->pst_uid) {
			$flag_last = false;
			$sql = "SELECT pst_id FROM babel_post WHERE pst_tid = {$Topic->tpc_id} ORDER BY pst_id ASC";
			$rs = mysql_query($sql, $this->db);
			$i = 0;
			$rank = 0;
			$count = mysql_num_rows($rs);
			while ($_p = mysql_fetch_array($rs)) {
				$i++;
				if (($_p['pst_id'] == $Post->pst_id) && ($i == $count)) {
					$permit = 1;
					$flag_last = true;
				}
				if ($_p['pst_id'] == $Post->pst_id) {
					$rank = $i;
				}
				unset($_p);
			}
			mysql_free_result($rs);
		}
		if ($this->User->usr_id == 1) {
			$permit = 1;
		}
		echo('<div id="main">');
		if ($permit == 1) {
			if (!isset($_SESSION['babel_page_topic'])) {
				$_SESSION['babel_page_topic'] = 1;
			}
			echo('<div class="blank" align="left">');
			_v_ico_map();
			echo(' <a href="/">' . Vocabulary::site_name . '</a> &gt; <a href="/section/view/' . $Section->nod_id . '.html">' . $Section->nod_title . '</a> &gt; <a href="/board/view/' . $Node->nod_id . '.html">' . $Node->nod_title . '</a> &gt; <a href="/topic/view/' . $Topic->tpc_id . '/' . $_SESSION['babel_page_topic'] . '.html">' . make_plaintext($Topic->tpc_title) . '</a> &gt; ' . make_plaintext($Post->pst_title) . ' &gt; ' . Vocabulary::action_modifypost . '</div>');
			echo('<div class="blank" align="left"><span class="text_large"><img src="/img/ico_conf.gif" align="absmiddle" class="home" />' . Vocabulary::action_modifypost . '</span>');
			echo('<table cellpadding="0" cellspacing="0" border="0" class="form">');
			echo('<form action="/post/update/' . $Post->pst_id . '.vx" method="post" id="form_post_modify">');
			echo('<tr><td width="100" align="right">标题</td><td width="400" align="left"><input type="text" class="sll" name="pst_title" value="' . make_single_return($Post->pst_title, 0) . '" /></td></tr>');
			echo('<tr><td width="100" align="right" valign="top">回复内容</td><td width="400" align="left"><textarea rows="15" class="ml" name="pst_content">' . make_multi_return($Post->pst_content, 0) . '</textarea></td></tr>');
			echo('<td width="500" colspan="3" valign="middle" align="right"><span class="tip">');
			_v_btn_f('立即修改', 'form_post_modify');
			echo('</span></td></tr>');
			echo('</form>');
			echo('</table>');
			_v_hr();
			echo('<span class="tip"><img src="/img/pico_left.gif" align="absmiddle" />&nbsp;<a href="/topic/view/' . $Topic->tpc_id . '/' . $_SESSION['babel_page_topic'] . '.html">返回主题 / ' . make_plaintext($Topic->tpc_title) . '</a></span>');
			echo('</div>');
		} else {
			echo('<div class="blank" align="left">');
			_v_ico_map();
			echo(' <a href="/">' . Vocabulary::site_name . '</a> &gt; <a href="/section/view/' . $Section->nod_id . '.html">' . $Section->nod_title . '</a> &gt; <a href="/board/view/' . $Node->nod_id . '.html">' . $Node->nod_title . '</a> &gt; <a href="/topic/view/' . $Topic->tpc_id . '.html">' . make_plaintext($Topic->tpc_title) . '</a> &gt; ' . make_plaintext($Post->pst_title) . ' &gt; ' . Vocabulary::action_modifypost . ' &gt; <strong>本回复的修改功能被禁止</strong></div>');
			echo('<div class="blank" align="left"><span class="text_large"><img src="/img/ico_bomb.gif" align="absmiddle" class="home" />本回复的修改功能被禁止</span><br />你不能对本回复进行修改，是由于以下原因：');
			_v_hr();
			if ($this->User->usr_id != $Post->pst_uid) {
				echo('<div class="geo_home_entry_odd">&nbsp;&nbsp;&nbsp;&nbsp;<img src="/img/gt.gif" align="absmiddle" />&nbsp;你所要修改的回复并不属于你</div>');
			}
			if (!$flag_last) {
				echo('<div class="geo_home_entry_odd">&nbsp;&nbsp;&nbsp;&nbsp;<img src="/img/gt.gif" align="absmiddle" />&nbsp;该主题已有 ' . $i . ' 个回复，你只能修改最后 1 个回复，而你正要试图修改的是第 ' . $rank . ' 个</div>');
			}
			_v_hr();
			echo('<img src="/img/pico_left.gif" align="absmiddle" /> 返回主题 <a href="/topic/view/' . $Topic->tpc_id . '.html" class="t">' . make_plaintext($Topic->tpc_title) . '</a>');
			_v_d_e();
		}
		echo('</div>');
	}
	
	/* E module: Post Modify block */
	
	/* S module: Post Update block */
	
	public function vxPostUpdate($rt) {
		$Post = new Post($rt['post_id'], $this->db);
		$Topic = new Topic($Post->pst_tid, $this->db);
		$Node = new Node($Topic->tpc_pid, $this->db);
		$Section = $Node->vxGetNodeInfo($Node->nod_sid);
		echo('<div id="main">');
		if (!isset($_SESSION['babel_page_topic'])) {
			$_SESSION['babel_page_topic'] = 1;
		}
		if ($rt['permit']) {
			echo('<div class="blank" align="left">');
			_v_ico_map();
			echo(' <a href="/">' . Vocabulary::site_name . '</a> &gt; <a href="/section/view/' . $Section->nod_id . '.html" target="_self">' . $Section->nod_title . '</a> &gt; <a href="/board/view/' . $Node->nod_id . '.html">' . $Node->nod_title . '</a> &gt; <a href="/topic/view/' . $Topic->tpc_id . '/' . $_SESSION['babel_page_topic'] . '.html">' . make_plaintext($Topic->tpc_title) . '</a> &gt; ' . make_plaintext($Post->pst_title) . ' &gt; ' . Vocabulary::action_modifypost . '</div>');
			if ($rt['errors'] > 0) {
				echo('<div class="blank" align="left"><span class="text_large"><img src="/img/ico_important.gif" align="absmiddle" class="home" />' . Vocabulary::msg_submitwrong . '</span>');
				echo('<table cellpadding="0" cellspacing="0" border="0" class="form">');
				echo('<form action="/post/update/' . $Post->pst_id . '.vx" method="post" id="form_post_modify">');
				if ($rt['pst_title_error'] > 0) {
					echo('<tr><td width="100" align="right">回复标题</td><td width="400" align="left"><div class="error"><input type="text" class="sll" name="pst_title" value="' . make_single_return($rt['pst_title_value']) . '" /><br /><img src="/img/sico_error.gif" align="absmiddle" />&nbsp;' . $rt['pst_title_error_msg'][$rt['pst_title_error']] . '</div></td></tr>');
				} else {
					echo('<tr><td width="100" align="right">回复标题</td><td width="400" align="left"><input type="text" class="sll" name="pst_title" value="' . make_single_return($rt['pst_title_value']) . '" /></td></tr>');
				}
				if ($rt['pst_content_error'] > 0) {
					echo('<tr><td width="100" align="right" valign="top">回复内容</td><td width="400" align="left"><div class="error"><textarea rows="15" class="ml" name="pst_content">' . $rt['pst_content_value'] . '</textarea><br /><img src="/img/sico_error.gif" align="absmiddle" />&nbsp;' . make_multi_return($rt['pst_content_error_msg'][$rt['pst_content_error']]) . '</div></td></tr>');
				} else {
					echo('<tr><td width="100" align="right" valign="top">回复内容</td><td width="400" align="left"><textarea rows="15" class="ml" name="pst_content">' . make_multi_return($rt['pst_content_value']) .'</textarea></td></tr>');
				}
				echo('<td width="500" colspan="3" valign="middle" align="right"><span class="tip">');
				_v_btn_f('立即修改', 'form_post_modify');
				echo('</span></td></tr>');
				echo('</form>');
				echo('</table>');
				_v_hr();
				echo('<span class="tip"><img src="/img/pico_left.gif" align="absmiddle" />&nbsp;<a href="/topic/view/' . $Topic->tpc_id . '/' . $_SESSION['babel_page_topic'] . '.html">返回主题 / ' . make_plaintext($Topic->tpc_title) . '</a></span>');
				echo('</div>');
			} else {
				$Topic->vxTouch();
				echo('<div class="blank" align="left"><span class="text_large"><img src="/img/ico_smile.gif" align="absmiddle" class="home" />回复成功修改</span><br />回复 [ <a href="/topic/view/' . $Topic->tpc_id . '.html">' . make_plaintext($Post->pst_title) . '</a> ] 已经成功更新，将在 3 秒内自动转向到你刚才修改的回复所在的主题<br /><br /><img src="/img/pico_right.gif" align="absmiddle" />&nbsp;<a href="/topic/view/' . $Topic->tpc_id . '/' . $_SESSION['babel_page_topic'] . '.html">立刻转到刚才被修改的回复所在的主题 / ' . $Topic->tpc_title . '</a><br /><br />');
				echo('<img src="/img/pico_right.gif" align="absmiddle" />&nbsp;<a href="/go/' . $Node->nod_name . '">转到主题所在讨论区 / ' . make_plaintext($Node->nod_title) . '</a><br /><br />');
				echo('<img src="/img/pico_right.gif" align="absmiddle" />&nbsp;<a href="/go/' . $Section->nod_name . '">转到主题所在区域 / ' . make_plaintext($Section->nod_title) . '</a><br /><br />');
				echo('<span class="tip_i">' . Vocabulary::site_name . ' 感谢你对细节的关注！</span>');
				echo('</div>');
			}
		} else {
			echo('<div class="blank" align="left">');
			_v_ico_map();
			echo(' <a href="/">' . Vocabulary::site_name . '</a> &gt; <a href="/section/view/' . $Section->nod_id . '.html">' . $Section->nod_title . '</a> &gt; <a href="/board/view/' . $Node->nod_id . '.html">' . $Node->nod_title . '</a> &gt; <a href="/topic/view/' . $Topic->tpc_id . '/' . $_SESSION['babel_page_topic'] . '.html">' . make_plaintext($Topic->tpc_title) . '</a> &gt; ' . make_plaintext($Post->pst_title) . ' &gt; ' . Vocabulary::action_modifypost . ' &gt; <strong>本回复的修改功能被禁止</strong></div>');
			echo('<div class="blank" align="left"><span class="text_large"><img src="/img/ico_bomb.gif" align="absmiddle" class="home" />本回复的修改功能被禁止</span><br />你不能对本回复进行修改，是由于以下原因：');
			_v_hr();
			if ($this->User->usr_id != $Post->pst_uid) {
				echo('<div class="geo_home_entry_odd">&nbsp;&nbsp;&nbsp;&nbsp;<img src="/img/gt.gif" align="absmiddle" />&nbsp;你所要修改的回复并不属于你</div>');
			}
			if (!$rt['flag_last']) {
				echo('<div class="geo_home_entry_odd">&nbsp;&nbsp;&nbsp;&nbsp;<img src="/img/gt.gif" align="absmiddle" />&nbsp;该主题已有 ' . $Topic->tpc_posts . ' 个回复，你只能修改最后 1 个回复，而你正要试图修改的是第 ' . $rt['rank'] . ' 个</div>');
			}
			_v_hr();
			echo('<img src="/img/pico_left.gif" align="absmiddle" /> 返回主题 <a href="/topic/view/' . $Topic->tpc_id . '.html" class="t">' . make_plaintext($Topic->tpc_title) . '</a>');
			_v_d_e();
		}
		echo('</div>');
	}
	
	/* E module: Post Update block */
	
	/* S module: Topic Modify block */
	
	public function vxTopicModify($Topic) {
		$Node = new Node($Topic->tpc_pid, $this->db);
		$Section = $Node->vxGetNodeInfo($Node->nod_sid);
		$permit = 0;
		if ($this->User->usr_id == $Topic->tpc_uid) {
			if ((time() - $Topic->tpc_created) < 86400) {
				if ($Topic->tpc_posts < 3) {
					$permit = 1;
				}
			}
		}
		if ($this->User->usr_id == 1) {
			$permit = 1;
		}
		echo('<div id="main">');
		if ($permit == 1) {
			echo('<div class="blank" align="left">');
			_v_ico_map();
			echo(' <a href="/">' . Vocabulary::site_name . '</a> &gt; <a href="/section/view/' . $Section->nod_id . '.html">' . $Section->nod_title . '</a> &gt; <a href="/board/view/' . $Node->nod_id . '.html">' . $Node->nod_title . '</a> &gt; <a href="/topic/view/' . $Topic->tpc_id . '.html">' . make_plaintext($Topic->tpc_title) . '</a> &gt; ' . Vocabulary::action_modifytopic . '</div>');
			echo('<div class="blank" align="left"><span class="text_large"><img src="/img/ico_conf.gif" align="absmiddle" class="home" />' . Vocabulary::action_modifytopic . '</span>');
			echo('<table cellpadding="0" cellspacing="0" border="0" class="form">');
			echo('<form action="/topic/update/' . $Topic->tpc_id . '.vx" method="post" id="form_topic_modify">');
			echo('<tr><td width="100" align="right">标题</td><td width="400" align="left"><input type="text" class="sll" name="tpc_title" value="' . make_single_return($Topic->tpc_title, 0) . '" /></td></tr>');
			echo('<tr><td width="100" align="right" valign="top">主题简介</td><td width="400" align="left"><textarea rows="5" class="ml" name="tpc_description">' . make_multi_return($Topic->tpc_description, 0) . '</textarea></td></tr>');
			echo('<tr><td width="100" align="right" valign="top">主题内容</td><td width="400" align="left"><textarea rows="15" class="ml" name="tpc_content">' . make_multi_return($Topic->tpc_content, 0) . '</textarea></td></tr>');
			echo('<td width="500" colspan="3" valign="middle" align="right"><span class="tip">');
			_v_btn_f('立即修改', 'form_topic_modify');
			echo('</span></td></tr>');
			echo('</form>');
			echo('</table>');
			_v_hr();
			echo('<span class="tip"><img src="/img/pico_left.gif" align="absmiddle" /><a href="/topic/view/' . $Topic->tpc_id . '.html">&nbsp;返回主题 / ' . make_plaintext($Topic->tpc_title) . '</a></span>');
			echo('</div>');
		} else {
			echo('<div class="blank" align="left">');
			_v_ico_map();
			echo(' <a href="/">' . Vocabulary::site_name . '</a> &gt; <a href="/section/view/' . $Section->nod_id . '.html">' . $Section->nod_title . '</a> &gt; <a href="/board/view/' . $Node->nod_id . '.html">' . $Node->nod_title . '</a> &gt; <a href="/topic/view/' . $Topic->tpc_id . '.html">' . make_plaintext($Topic->tpc_title) . '</a> &gt; ' . Vocabulary::action_modifytopic . ' &gt; <strong>本主题的修改功能被禁止</strong></div>');
			echo('<div class="blank" align="left"><span class="text_large"><img src="/img/ico_bomb.gif" align="absmiddle" class="home" />本主题的修改功能被禁止</span><br />你不能对本主题进行修改，是由于以下原因：');
			_v_hr();
			if ($this->User->usr_id != $Topic->tpc_uid) {
				echo('<div class="geo_home_entry_odd">&nbsp;&nbsp;&nbsp;&nbsp;<img src="/img/gt.gif" align="absmiddle" />&nbsp;你所要修改的主题并不属于你</div>');
			}
			if ((time() - $Topic->tpc_created) > 86400) {
				echo('<div class="geo_home_entry_odd">&nbsp;&nbsp;&nbsp;&nbsp;<img src="/img/gt.gif" align="absmiddle" />&nbsp;该主题创建于 24 小时以前，你不能对创建时间超过 24 小时的主题进行修改</div>');
			}
			if ($Topic->tpc_posts > 2) {
				echo('<div class="geo_home_entry_odd">&nbsp;&nbsp;&nbsp;&nbsp;<img src="/img/gt.gif" align="absmiddle" />&nbsp;该主题已有 ' . $Topic->tpc_posts . ' 个回复，你不能修改已有至少 2 个回复的主题</div>');
			}
			_v_hr();
			echo('<img src="/img/pico_left.gif" align="absmiddle" /> 返回主题 <a href="/topic/view/' . $Topic->tpc_id . '.html" class="t">' . make_plaintext($Topic->tpc_title) . '</a>');
			_v_d_e();
		}
		echo('</div>');
	}
	
	/* E module: Topic Modify block */
	
	/* S module: Topic Update block */
	
	public function vxTopicUpdate($rt) {
		$Topic = new Topic($rt['topic_id'], $this->db);
		$Node = new Node($Topic->tpc_pid, $this->db);
		$Section = $Node->vxGetNodeInfo($Node->nod_sid);
		if ($this->User->usr_id == $Topic->tpc_uid) {
			if ((time() - $Topic->tpc_created) < 86400) {
				if ($Topic->tpc_posts < 3) {
					$rt['permit'] = 1;
				} else {
					$rt['permit'] = 0;
				}
			} else {
				$rt['permit'] = 0;
			}
		} else {
			$rt['permit'] = 0;
		}
		if ($this->User->usr_id == 1) {
			$rt['permit'] = 1;
		}
		echo('<div id="main">');
		if ($rt['permit'] == 1) {
			if ($rt['errors'] == 0) {
				$usr_money_a = $this->User->vxParseMoney(abs($rt['exp_amount']));
				echo('<div class="blank" align="left">');
				_v_ico_map();
				echo(' <a href="/">' . Vocabulary::site_name . '</a> &gt; <a href="/section/view/' . $Section->nod_id . '.html">' . $Section->nod_title . '</a> &gt; <a href="/board/view/' . $Node->nod_id . '.html">' . $Node->nod_title . '</a> &gt; <a href="/topic/view/' . $Topic->tpc_id . '.html">' . make_plaintext($Topic->tpc_title) . '</a> &gt; ' . Vocabulary::action_modifytopic . '</div>');
				echo('<div class="blank" align="left"><span class="text_large"><img src="/img/ico_smile.gif" align="absmiddle" class="home" />主题成功修改</span><br />主题 [ <a href="/topic/view/' . $Topic->tpc_id . '.html">' . make_plaintext($Topic->tpc_title) . '</a> ] 已经成功更新，<strong>修改该主题花费了你的' . $usr_money_a['str'] . '</strong>，将在 3 秒内自动转向到你刚才创建的主题<br /><br /><img src="/img/pico_right.gif" align="absmiddle" />&nbsp;<a href="/topic/view/' . $Topic->tpc_id . '.html">立刻转到刚才被修改的主题 / ' . $Topic->tpc_title . '</a><br /><br />');
				echo('<img src="/img/pico_right.gif" align="absmiddle" />&nbsp;<a href="/go/' . $Node->nod_name . '">转到主题所在讨论区 / ' . make_plaintext($Node->nod_title) . '</a><br /><br />');
				echo('<img src="/img/pico_right.gif" align="absmiddle" />&nbsp;<a href="/go/' . $Section->nod_name . '">转到主题所在区域 / ' . make_plaintext($Section->nod_title) . '</a><br /><br />');
				echo('<span class="tip_i">' . Vocabulary::site_name . ' 感谢你对细节的关注！</span>');
				echo('</div>');
			} else {
				echo('<div class="blank" align="left">');
				_v_ico_map();
				echo(' <a href="/">' . Vocabulary::site_name . '</a> &gt; <a href="/section/view/' . $Section->nod_id . '.html">' . $Section->nod_title . '</a> &gt; <a href="/board/view/' . $Node->nod_id . '.html">' . $Node->nod_title . '</a> &gt; <a href="/topic/view/' . $Topic->tpc_id . '.html">' . $Topic->tpc_id . '</a> &gt; ' . Vocabulary::action_modifytopic . '</div>');
				echo('<div class="blank" align="left"><span class="text_large"><img src="/img/ico_important.gif" align="absmiddle" class="home" />对不起，你刚才提交的信息里有些错误</span>');
				echo('<table cellpadding="0" cellspacing="0" border="0" class="form">');
				echo('<form action="/topic/update/' . $Topic->tpc_id . '.vx" method="post" id="form_topic_update">');
				if ($rt['tpc_title_error'] > 0) {
					echo('<tr><td width="100" align="right">标题</td><td width="400" align="left"><div class="error"><input type="text" class="sll" name="tpc_title" value="' . make_single_return($rt['tpc_title_value']) . '" /><br /><img src="/img/sico_error.gif" align="absmiddle" />&nbsp;' . $rt['tpc_title_error_msg'][$rt['tpc_title_error']] . '</div></td></tr>');
				} else {
					echo('<tr><td width="100" align="right">标题</td><td width="400" align="left"><input type="text" class="sll" name="tpc_title" value="' . make_single_return($rt['tpc_title_value']) . '" /></td></tr>');
				}
				if ($rt['tpc_description_error'] > 0) {
					echo('<tr><td width="100" align="right" valign="top">主题简介</td><td width="400" align="left"><div class="error"><textarea rows="5" class="ml" name="tpc_description">' . make_multi_return($rt['tpc_description_value']) . '</textarea><br /><img src="/ico/sico_error.gif" align="absmiddle" />&nbsp;' . $rt['tpc_description_error_msg'][$rt['tpc_description_error']] . '</div></td></tr>');
				} else {
					echo('<tr><td width="100" align="right" valign="top">主题简介</td><td width="400" align="left"><textarea rows="5" class="ml" name="tpc_description">' . make_multi_return($rt['tpc_description_value']) . '</textarea></td></tr>');
				}
				if ($rt['tpc_content_error'] > 0) {
					echo('<tr><td width="100" align="right" valign="top">主题内容</td><td width="400" align="left"><div class="error"><textarea rows="15" class="ml" name="tpc_content">' . make_multi_return($rt['tpc_content_value']) . '</textarea><br /><img src="/img/sico_error.gif" align="absmiddle" />&nbsp;' . $rt['tpc_content_error_msg'][$rt['tpc_content_error']] . '</div></td></tr>');
				} else {
					echo('<tr><td width="100" align="right" valign="top">主题内容</td><td width="400" align="left"><textarea rows="15" class="ml" name="tpc_content">' . make_multi_return($rt['tpc_content_value']) . '</textarea></td></tr>');
				}
				echo('<td width="500" colspan="3" valign="middle" align="right"><span class="tip">');
				_v_btn_f('立即修改', 'form_topic_update');
				echo('</span></td></tr>');
				echo('</form>');
				echo('</table>');
				_v_hr();
				echo('<span class="tip"><img src="/img/pico_left.gif" align="absmiddle" /><a href="/topic/view/' . $Topic->tpc_id . '.html">&nbsp;返回主题 / ' . make_plaintext($Topic->tpc_title) . '</a></span>');
				echo('</div>');
			}
		} else {
			echo('<div class="blank" align="left">');
			_v_ico_map();
			echo(' <a href="/">' . Vocabulary::site_name . '</a> &gt; <a href="/section/view/' . $Section->nod_id . '.html">' . $Section->nod_title . '</a> &gt; <a href="/board/view/' . $Node->nod_id . '.html">' . $Node->nod_title . '</a> &gt; <a href="/topic/view/' . $Topic->tpc_id . '.html">' . make_plaintext($Topic->tpc_title) . '</a> &gt; ' . Vocabulary::action_modifytopic . ' &gt; <strong>Access Denied</strong></div>');
			echo('<div class="blank" align="left"><span class="text_large"><img src="/img/ico_bomb.gif" align="absmiddle" class="home" />Access Denied</span><br />你在一个你不应该到达的地方，停止你的任何无意义的尝试吧</div>');
		}
		echo('</div>');
	}
	
	/* E module: Topic Update block */
	
	/* S module: Topic New block */
	
	public function vxTopicNew($options) {
		switch ($options['mode']) {
			case 'board':
				$Node = new Node($options['board_id'], $this->db);
				$Section = $Node->vxGetNodeInfo($Node->nod_sid);
				echo('<div id="main">');
				echo('<div class="blank" align="left">');
				_v_ico_map();
				echo(' <a href="/">' . Vocabulary::site_name . '</a> &gt; <a href="/section/view/' . $Section->nod_id . '.html">' . $Section->nod_title . '</a> &gt; <a href="/board/view/' . $Node->nod_id . '.html">' . $Node->nod_title . '</a> &gt; ' . Vocabulary::action_newtopic . '</div>');
				echo('<div class="blank" align="left"><span class="text_large"><img src="/img/ico_conf.gif" align="absmiddle" class="home" />' . Vocabulary::action_newtopic . '</span>');
				echo('<table cellpadding="0" cellspacing="0" border="0" class="form">');
				echo('<form action="/topic/create/' . $Node->nod_id . '.vx" method="post" id="form_topic_create">');
				echo('<tr><td width="100" align="right">标题</td><td width="400" align="left"><input onfocus="brightBox(this);" onblur="dimBox(this);" type="text" class="sll" name="tpc_title" /></td></tr>');
				echo('<tr><td width="100" align="right" valign="top">主题简介</td><td width="400" align="left"><textarea onfocus="brightBox(this);" onblur="dimBox(this);" rows="5" class="ml" name="tpc_description"></textarea></td></tr>');
				echo('<tr><td width="100" align="right" valign="top">主题内容</td><td width="400" align="left"><textarea onfocus="brightBox(this);" onblur="dimBox(this);" rows="15" class="ml" name="tpc_content"></textarea></td></tr>');
				echo('<td width="500" colspan="3" valign="middle" align="right">');
				_v_btn_f('立即创建', 'form_topic_create');
				echo('</td></tr>');
				echo('</form>');
				echo('</table>');
				_v_hr();
				echo('<span class="tip"><img src="/img/pico_left.gif" align="absmiddle" />&nbsp;<a href="/go/' . $Node->nod_name . '">返回讨论区 / ' . $Node->nod_title . '</a></span>');
				echo('</div>');
				echo('</div>');
				break;
			case 'section':
				$Section = new Node($options['section_id'], $this->db);
				echo('<div id="main">');
				echo('<div class="blank" align="left">');
				_v_ico_map();
				echo(' <a href="/">' . Vocabulary::site_name . '</a> &gt; <a href="/section/view/' . $Section->nod_id . '.html">' . $Section->nod_title . '</a> &gt; ' . Vocabulary::action_newtopic . '</div>');
				echo('<div class="blank" align="left"><span class="text_large"><img src="/img/ico_conf.gif" align="absmiddle" class="home" />' . Vocabulary::action_newtopic . '</span>');
				echo('<table cellpadding="0" cellspacing="0" border="0" class="form">');
				echo('<form action="/topic/create/' . $Section->nod_id . '.vx" method="post" id="form_topic_create">');
				echo('<tr><td width="100" align="right">标题</td><td width="400" align="left"><input type="text" class="sll" name="tpc_title" /></td></tr>');
				echo('<tr><td width="100" align="right">位于</td><td width="400" align="left"><select name="tpc_pid">');
				$Children = $Section->vxGetNodeChildren();
				$i = 0;
				while ($Node = mysql_fetch_object($Children)) {
					$i++;
					if ($i == 0) {
						echo('<option value="' . $Node->nod_id . '" selected="selected">' . $Node->nod_title . '</option>');
					} else {
						echo('<option value="' . $Node->nod_id . '">' . $Node->nod_title . '</option>');
					}
				}
				mysql_free_result($Children);
				echo('</select></td></tr>');
				echo('<tr><td width="100" align="right" valign="top">主题简介</td><td width="400" align="left"><textarea rows="5" class="ml" name="tpc_description"></textarea></td></tr>');
				echo('<tr><td width="100" align="right" valign="top">主题内容</td><td width="400" align="left"><textarea rows="15" class="ml" name="tpc_content"></textarea></td></tr>');
				echo('<td width="500" colspan="3" valign="middle" align="right">');
				_v_btn_f('立即创建', 'form_topic_create');
				echo('</td></tr>');
				echo('</form>');
				echo('</table>');
				_v_hr();
				echo('<span class="tip"><img src="/img/pico_left.gif" align="absmiddle" />&nbsp;<a href="/go/' . $Section->nod_name . '">返回区域 / ' . $Section->nod_title . '</a></span>');
				echo('</div>');
				echo('</div>');
				break;
		}
	}
	
	/* E module: Topic New block */
	
	/* S module: Topic Create block */
	
	public function vxTopicCreate($rt) {
		
		if ($rt['mode'] == 'board') {
			$Node = new Node($rt['board_id'], $this->db);
			$Section = $Node->vxGetNodeInfo($Node->nod_pid, $this->db);
		} else {
			if ($rt['tpc_pid_error'] == 0) {
				$Node = new Node($rt['tpc_pid_value'], $this->db);
			}
			$Section = new Node($rt['section_id'], $this->db);
		}
		
		if ($rt['errors'] == 0) {
		
			global $Topic;

			$Node->vxUpdateTopics();
			$usr_money_a = $this->User->vxParseMoney(abs($rt['exp_amount']));
			echo('<div id="main">');

			echo('<div class="blank" align="left">');
			_v_ico_map();
			echo(' <a href="/">' . Vocabulary::site_name . '</a> &gt; <a href="/section/view/' . $Section->nod_id . '.html">' . $Section->nod_title . '</a> &gt; <a href="/board/view/' . $Node->nod_id . '.html">' . $Node->nod_title . '</a> &gt; ' . Vocabulary::action_newtopic . '</div>');
			echo('<div class="blank" align="left"><span class="text_large"><img src="/img/ico_smile.gif" align="absmiddle" class="home" />新主题成功创建</span><br />新主题 [ <a href="/topic/view/' . $Topic->tpc_id . '.html">' . make_plaintext($Topic->tpc_title) . '</a> ] 成功创建，<strong>创建该长度为 ' . $rt['tpc_content_length'] . ' 个字符的主题花费了你的' . $usr_money_a['str'] . '</strong>，将在 3 秒内自动转向到你刚才创建的主题<br /><br /><img src="/img/pico_right.gif" align="absmiddle" />&nbsp;<a href="/topic/view/' . $Topic->tpc_id . '.html">立刻转到新主题 / ' . make_plaintext($Topic->tpc_title) . '</a><br /><br />');
			echo('<img src="/img/pico_right.gif" align="absmiddle" />&nbsp;<a href="/go/' . $Node->nod_name . '">转到新主题所在讨论区 / ' . make_plaintext($Node->nod_title) . '</a><br /><br />');
			echo('<img src="/img/pico_right.gif" align="absmiddle" />&nbsp;<a href="/go/' . $Section->nod_name . '">转到新主题所在区域 / ' . make_plaintext($Section->nod_title) . '</a><br /><br />');
			echo('<span class="tip">讨论区 ' . make_plaintext($Node->nod_title) . ' 中现在有 ' . ($Node->nod_topics + 1) . ' 篇主题，感谢你的贡献！</span>');
			echo('</div>');
			echo('</div>');
		
		} else {
		
			echo('<div id="main">');
			
			if ($rt['tpc_pid_error'] == 0) {
				echo('<div class="blank" align="left">');
				_v_ico_map();
				echo(' <a href="/">' . Vocabulary::site_name . '</a> &gt; <a href="/section/view/' . $Section->nod_id . '.html">' . $Section->nod_title . '</a> &gt; <a href="/board/view/' . $Node->nod_id . '.html">' . $Node->nod_title . '</a> &gt; ' . Vocabulary::action_newtopic . '</div>');
			} else {
				echo('<div class="blank" align="left">');
				_v_ico_map();
				echo(' <a href="/">' . Vocabulary::site_name . '</a> &gt; <a href="/section/view/' . $Section->nod_id . '.html">' . $Section->nod_title . '</a> &gt; ' . Vocabulary::action_newtopic . '</div>');
			}

			echo('<div class="blank" align="left"><span class="text_large"><img src="/img/ico_important.gif" align="absmiddle" class="home" />对不起，你刚才提交的信息里有些错误</span>');
			echo('<table cellpadding="0" cellspacing="0" border="0" class="form">');
			
			if ($rt['mode'] == 'board') {
				echo('<form action="/topic/create/' . $Node->nod_id . '.vx" method="post" id="form_topic_create">');
			} else {
				echo('<form action="/topic/create/' . $Section->nod_id . '.vx" method="post" id="form_topic_create">');
			}
			
			if ($rt['tpc_title_error'] > 0) {
				echo('<tr><td width="100" align="right">标题</td><td width="400" align="left"><div class="error"><input type="text" class="sll" name="tpc_title" value="' . make_single_return($rt['tpc_title_value']) . '" /><br /><img src="/img/sico_error.gif" align="absmiddle" />&nbsp;' . $rt['tpc_title_error_msg'][$rt['tpc_title_error']] . '</div></td></tr>');
			} else {
				echo('<tr><td width="100" align="right">标题</td><td width="400" align="left"><input type="text" class="sll" name="tpc_title" value="' . make_single_return($rt['tpc_title_value']) . '" /></td></tr>');
			}
			
			if ($rt['mode'] == 'section') {
				if ($rt['tpc_pid_error'] > 0) {
					echo('<tr><td width="100" align="right">位于</td><td width="400" align="left"><div class="error"><select name="tpc_pid">');
				} else {
					echo('<tr><td width="100" align="right">位于</td><td width="400" align="left"><select name="tpc_pid">');
				}
				
				$Children = $Section->vxGetNodeChildren();
				$i = 0;
				while ($O = mysql_fetch_object($Children)) {
					$i++;
					if ($rt['tpc_pid_error'] > 0) {
						if ($i == 1) {
							echo('<option value="' . $O->nod_id . '" selected="selected">' . $O->nod_title . '</option>');
						} else {
							echo('<option value="' . $O->nod_id . '">' . $O->nod_title . '</option>');
						}
					} else {
						if ($O->nod_id == $rt['tpc_pid_value']) {
							echo('<option value="' . $O->nod_id . '" selected="selected">' . $O->nod_title . '</option>');
						} else {
							echo('<option value="' . $O->nod_id . '">' . $O->nod_title . '</option>');
						}
					}
					$O = null;
				}
				
				if ($rt['tpc_pid_error'] > 0) {
					echo ('</select><br /><img src="/img/sico_error.gif" align="absmiddle" />&nbsp;' . $rt['tpc_pid_error_msg'][$rt['tpc_pid_error']] . '</div></td></tr>');
				} else {
					echo ('</select></td></tr>');
				}
			}
			
			if ($rt['tpc_description_error'] > 0) {
				echo('<tr><td width="100" align="right" valign="top">主题简介</td><td width="400" align="left"><div class="error"><textarea rows="5" class="ml" name="tpc_description">' . make_multi_return($rt['tpc_description_value']) . '</textarea><br /><img src="/ico/sico_error.gif" align="absmiddle" />&nbsp;' . $rt['tpc_description_error_msg'][$rt['tpc_description_error']] . '</div></td></tr>');
			} else {
				echo('<tr><td width="100" align="right" valign="top">主题简介</td><td width="400" align="left"><textarea rows="5" class="ml" name="tpc_description">' . make_multi_return($rt['tpc_description_value']) . '</textarea></td></tr>');
			}
			if ($rt['tpc_content_error'] > 0) {
				echo('<tr><td width="100" align="right" valign="top">主题内容</td><td width="400" align="left"><div class="error"><textarea rows="15" class="ml" name="tpc_content">' . make_multi_return($rt['tpc_content_value']) . '</textarea><br /><img src="/img/sico_error.gif" align="absmiddle" />&nbsp;' . $rt['tpc_content_error_msg'][$rt['tpc_content_error']] . '</div></td></tr>');
			} else {
				echo('<tr><td width="100" align="right" valign="top">主题内容</td><td width="400" align="left"><textarea rows="15" class="ml" name="tpc_content">' . make_multi_return($rt['tpc_content_value']) . '</textarea></td></tr>');
			}
			echo('<td width="500" colspan="3" valign="middle" align="right"><span class="tip">');
			_v_btn_f('立即创建', 'form_topic_create');
			echo('</span></td></tr>');
			echo('</form>');
			echo('</table>');
			_v_hr();
			if ($rt['mode'] == 'board') {
				echo('<span class="tip"><img src="/img/pico_left.gif" align="absmiddle" />&nbsp;<a href="/go/' . $Node->nod_name . '">返回讨论区 / ' . $Node->nod_title . '</a></span>');
			} else {
				echo('<span class="tip"><img src="/img/pico_left.gif" align="absmiddle" />&nbsp;<a href="/go/' . $Section->nod_name . '">返回区域 / ' . $Section->nod_title . '</a></span>');
			}
			echo('</div>');
			echo('</div>');

		}
	}
	
	/* E module: Topic Create block */
	
	/* S module: Post Create block */
	
	public function vxPostCreate($rt) {
		$Topic = new Topic($rt['topic_id'], $this->db);
		$Node = new Node($Topic->tpc_pid, $this->db);
		$Section = $Node->vxGetNodeInfo($Node->nod_sid);
		echo('<div id="main">');
		echo('<div class="blank" align="left">');
		_v_ico_map();
		echo(' <a href="/">' . Vocabulary::site_name . '</a> &gt; <a href="/section/view/' . $Section->nod_id . '.html" target="_self">' . $Section->nod_title . '</a> &gt; <a href="/board/view/' . $Node->nod_id . '.html">' . $Node->nod_title . '</a> &gt; <a href="/topic/view/' . $Topic->tpc_id . '.html">' . make_plaintext($Topic->tpc_title) . '</a> &gt; ' . Vocabulary::action_replytopic . '</div>');
		if ($rt['errors'] > 0) {
			echo('<div class="blank" align="left"><span class="text_large"><img src="/img/ico_important.gif" align="absmiddle" class="home" />' . Vocabulary::msg_submitwrong . '</span>');
			if ($rt['autistic']) {
				echo('<div class="notify">你正在回复的主题位于自闭模式的讨论区中，你只能回复自闭模式的讨论区中你自己创建的主题。</div>');
			}
			echo('<table cellpadding="0" cellspacing="0" border="0" class="form">');
			echo('<form action="/post/create/' . $Topic->tpc_id . '.vx" method="post" id="form_post_create">');
			if ($rt['pst_title_error'] > 0) {
				echo('<tr><td width="100" align="right">回复标题</td><td width="400" align="left"><div class="error"><input type="text" class="sll" name="pst_title" value="' . make_single_return($rt['pst_title_value']) . '" /><br /><img src="/img/sico_error.gif" align="absmiddle" />&nbsp;' . $rt['pst_title_error_msg'][$rt['pst_title_error']] . '</div></td></tr>');
			} else {
				echo('<tr><td width="100" align="right">回复标题</td><td width="400" align="left"><input type="text" class="sll" name="pst_title" value="' . make_single_return($rt['pst_title_value']) . '" /></td></tr>');
			}
			if ($rt['pst_content_error'] > 0) {
				echo('<tr><td width="100" align="right" valign="top">回复内容</td><td width="400" align="left"><div class="error"><textarea rows="15" class="ml" name="pst_content">' . $rt['pst_content_value'] . '</textarea><br /><img src="/img/sico_error.gif" align="absmiddle" />&nbsp;' . make_multi_return($rt['pst_content_error_msg'][$rt['pst_content_error']]) . '</div></td></tr>');
			} else {
				echo('<tr><td width="100" align="right" valign="top">回复内容</td><td width="400" align="left"><textarea rows="15" class="ml" name="pst_content">' . make_multi_return($rt['pst_content_value']) .'</textarea></td></tr>');
			}
			echo('<td width="500" colspan="3" valign="middle" align="right"><span class="tip">');
			_v_btn_f('立即回复', 'form_post_create');
			echo('</span></td></tr>');
			echo('</form>');
			echo('</table>');
			_v_hr();
			echo('<span class="tip"><img src="/img/pico_left.gif" align="absmiddle" />&nbsp;<a href="/topic/view/' . $Topic->tpc_id . '.html">返回主题 / ' . make_plaintext($Topic->tpc_title) . '</a></span>');
			echo('</div>');
		} else {
			$usr_money_a = $this->User->vxParseMoney(abs($rt['exp_amount']));
			$Topic->vxTouch();
			$Topic->vxUpdatePosts();
			echo('<div class="blank"><span class="text_large"><img src="/img/ico_smile.gif" align="absmiddle" class="home" />主题回复成功</span><br />你已经成功回复了一篇主题，<strong>回复该主题花费了' . $usr_money_a['str'] . '</strong>，将在 3 秒内自动返回到主题<br /><br /><img src="/img/pico_right.gif" align="absmiddle" />&nbsp;<a href="/topic/view/' . $Topic->tpc_id . '/' . $rt['p_cur'] . '.html" target="_self">转到刚才回复的主题 / ' . make_plaintext($Topic->tpc_title) . '</a><br /><br />');
			echo('<img src="/img/pico_right.gif" align="absmiddle" />&nbsp;<a href="/go/' . $Node->nod_name . '">转到主题所在讨论区 / ' . make_plaintext($Node->nod_title) . '</a><br /><br />');
			echo('<img src="/img/pico_right.gif" align="absmiddle" />&nbsp;<a href="/go/' . $Section->nod_name . '">转到主题所在区域 / ' . make_plaintext($Section->nod_title) . '</a><br /><br />');
			echo('<span class="tip">主题 [ <a href="/topic/view/' . $Topic->tpc_id . '/' . $rt['p_cur'] . '.html" class="t">' . make_plaintext($Topic->tpc_title) . '</a> ] 现在有 ' . ($Topic->tpc_posts + 1) . ' 篇回复，感谢你的参与！</span>');
			echo('</div>');
		}
		echo('</div>');
	}
	
	/* E module: Post Create block */
	
	/* S module: Topic Top block */
	
	public function vxTopicTop() {
		echo('<div id="main">');
		echo('<div class="blank">');
		_v_ico_map();
		echo(' <a href="/">' . Vocabulary::site_name . '</a> &gt; ' . Vocabulary::term_toptopic . '</div>');
		echo('<div class="blank" align="left"><span class="text_large"><img src="/img/ico_top.gif" align="absmiddle" align="" class="ico" />' . Vocabulary::term_toptopic . '</span></div>');
		echo('<table width="100%" border="0" cellpadding="0" cellspacing="2" class="board">');
		echo('<tr><td width="50%" align="left" valign="top" class="container"><table width="100%" cellpadding="0" cellspacing="0" border="0" class="drawer"><tr><td height="18" class="orange">最多回复主题 Top 50</td></tr>');
		$sql = "SELECT tpc_id, tpc_pid, tpc_uid, tpc_title, tpc_hits, tpc_posts FROM babel_topic WHERE tpc_flag IN (0, 2) ORDER BY tpc_posts DESC LIMIT 50";
		$rs = mysql_query($sql, $this->db);
		$i = 0;
		while ($Topic = mysql_fetch_object($rs)) {
			$i++;
			$css_font_size = $this->vxGetItemSize($Topic->tpc_posts);
			if ($Topic->tpc_posts > 3) {
				$css_color = rand_color();
			} else {
				$css_color = rand_gray(2, 4);
			}
			if ($Topic->tpc_uid == $this->User->usr_id) {
				$img_star = 'star_active.png';
			} else {
				$img_star = 'star_inactive.png';
			}
			if (($i % 2) == 0) {
				echo('<tr><td class="even" height="20"><img src="/img/' . $img_star . '" align="absmiddle" />&nbsp;<a href="/topic/view/' . $Topic->tpc_id . '.html" target="_self" style="font-size: ' . $css_font_size . 'px; color: ' . $css_color . ';" class="var">' . make_plaintext($Topic->tpc_title) . '</a>&nbsp;<small class="fade">(' . $Topic->tpc_posts . ')</small><small class="grey">+' . $Topic->tpc_hits . '</small>&nbsp;<a href="/board/view/' . $Topic->tpc_pid . '.html" target="_self" class="img"><img src="/img/arrow.gif" border="0" align="absmiddle" /></a></td></tr>');
			} else {
				echo('<tr><td class="odd" height="20"><img src="/img/' . $img_star . '" align="absmiddle" />&nbsp;<a href="/topic/view/' . $Topic->tpc_id . '.html" target="_self" style="font-size: ' . $css_font_size . 'px; color: ' . $css_color . ';" class="var">' . make_plaintext($Topic->tpc_title) . '</a>&nbsp;<small class="fade">(' . $Topic->tpc_posts . ')</small><small class="grey">+' . $Topic->tpc_hits . '</small>&nbsp;<a href="/board/view/' . $Topic->tpc_pid . '.html" target="_self" class="img"><img src="/img/arrow.gif" border="0" align="absmiddle" /></a></td></tr>');
			}
		}
		mysql_free_result($rs);
		
		echo('</table></td><td width="50%" align="left" valign="top" class="container"><table width="100%" cellpadding="0" cellspacing="0" border="0" class="drawer"><tr><td width="50%" height="18" class="blue">最多点击主题 Top 50</td></tr>');
		$sql = "SELECT tpc_id, tpc_pid, tpc_uid, tpc_title, tpc_hits, tpc_posts FROM babel_topic WHERE tpc_flag = 0 ORDER BY tpc_hits DESC LIMIT 50";
		$rs = mysql_query($sql, $this->db);
		$i = 0;
		while ($Topic = mysql_fetch_object($rs)) {
			$i++;
			$css_font_size = $this->vxGetItemSize($Topic->tpc_posts);
			if ($Topic->tpc_posts > 3) {
				$css_color = rand_color();
			} else {
				$css_color = rand_gray(2, 4);
			}
			if ($Topic->tpc_uid == $this->User->usr_id) {
				$img_star = 'star_active.png';
			} else {
				$img_star = 'star_inactive.png';
			}
			if (($i % 2) == 0) {
				echo('<tr><td class="even" height="20"><img src="/img/' . $img_star . '" align="absmiddle" />&nbsp;<a href="/topic/view/' . $Topic->tpc_id . '.html" target="_self" style="font-size: ' . $css_font_size . 'px; color: ' . $css_color . ';" class="var">' . make_plaintext($Topic->tpc_title) . '</a>&nbsp;');
				if ($Topic->tpc_posts > 0) {
					echo('<small class="fade">(' . $Topic->tpc_posts . ')</small>');
				}
				echo('<small class="grey">+' . $Topic->tpc_hits . '</small>&nbsp;<a href="/board/view/' . $Topic->tpc_pid . '.html" target="_self" class="img"><img src="/img/arrow.gif" border="0" align="absmiddle" /></a></td></tr>');
			} else {
				echo('<tr><td class="odd" height="20"><img src="/img/' . $img_star . '" align="absmiddle" />&nbsp;<a href="/topic/view/' . $Topic->tpc_id . '.html" target="_self" style="font-size: ' . $css_font_size . 'px; color: ' . $css_color . ';" class="var">' . make_plaintext($Topic->tpc_title) . '</a>&nbsp;');
				if ($Topic->tpc_posts > 0) {
					echo('<small class="fade">(' . $Topic->tpc_posts . ')</small>');
				}
				echo('<small class="grey">+' . $Topic->tpc_hits . '</small>&nbsp;<a href="/board/view/' . $Topic->tpc_pid . '.html" target="_self" class="img"><img src="/img/arrow.gif" border="0" align="absmiddle" /></a></td></tr>');
			}
		}
		mysql_free_result($rs);
		echo('</table></td></tr>');
		echo('</table>');
		echo('</div>');
	}
	
	/* E module: Topic Top block */
	
	/* S module: Topic View block */
	
	public function vxTopicView($topic_id) {
		$Topic = new Topic($topic_id, $this->db, 1, 1);
		$Node = new Node($Topic->tpc_pid, $this->db);
		$Section = $Node->vxGetNodeInfo($Node->nod_sid);
		if ($this->User->vxIsLogin()) {
			$sql = "SELECT fav_id FROM babel_favorite WHERE fav_uid = {$this->User->usr_id} AND fav_type = 0 AND fav_res = {$Topic->tpc_id}";
			$rs = mysql_query($sql, $this->db);
			if (mysql_num_rows($rs) == 1) {
				$Fav = mysql_result($rs, 0, 0);
			} else {
				$Fav = 0;
			}
			mysql_free_result($rs);
		} else {
			$Fav = 0;
		}
		echo('<div id="main">');
		echo('<div class="blank">');
		_v_ico_map();
		echo(' <a href="/">' . Vocabulary::site_name . '</a> &gt; <a href="/go/' . $Section->nod_name . '" target="_self">' . $Section->nod_title . '</a> &gt; ');
		if (isset($_SESSION['babel_page_node_' . $Node->nod_id])) {
			echo('<a href="/board/view/' . $Node->nod_id . '/' . $_SESSION['babel_page_node_' . $Node->nod_id] . '.html">' . make_plaintext($Node->nod_title) . '</a>');	
		} else {
			echo('<a href="/go/' . $Node->nod_name . '">' . make_plaintext($Node->nod_title) . '</a>');
		}
		echo(' &gt; ' . make_plaintext($Topic->tpc_title) . '</div>');
		
		echo('<div class="blank"><table cellpadding="0" cellspacing="0" border="0">');
		echo('<tr><td valign="top" align="center"><a name="imgPortrait"></a>');
		if ($Topic->usr_portrait == '') {
			echo('<a href="/u/' . urlencode($Topic->usr_nick) . '" class="var"><img src="/img/p_' . $Topic->usr_gender . '.gif" style="margin-bottom: 5px;" class="portrait" /></a><br /><a href="/u/' . urlencode($Topic->usr_nick) . '" class="t">' . $Topic->usr_nick . '</a>');
		} else {
			echo('<a href="/u/' . urlencode($Topic->usr_nick) . '" class="var"><img src="/img/p/' . $Topic->usr_portrait . '.' . BABEL_PORTRAIT_EXT . '" style="margin-bottom: 5px;" class="portrait" /></a><br /><a href="/u/' . urlencode($Topic->usr_nick) . '" class="t">' . $Topic->usr_nick . '</a>');
		}
		echo('<br /><div style="padding-top: 3px;"><a href="/geo/' . $Topic->usr_geo . '" class="o">' . $this->Geo->map['name'][$Topic->usr_geo] . '</a></div>');
		$sql = "SELECT COUNT(tpc_id) FROM babel_topic WHERE tpc_uid = {$Topic->usr_id}";
		$rs = mysql_query($sql, $this->db);
		if ($this->tpc_count > 0) {
			$usr_share = (mysql_result($rs, 0, 0) / $this->tpc_count) * 100;
		} else {
			$usr_share = 0;
		}
		mysql_free_result($rs);
		printf("<small class=\"grey\"><br /><br /><a href=\"/topic/archive/user/{$Topic->usr_nick}\">%.3f%%</a></small>", $usr_share);
		if ($this->User->vxIsLogin()) {
			if ($Topic->usr_id != $this->User->usr_id) {
				echo("<br /><br /><button class=\"mini\" onclick=\"sendMessage({$Topic->usr_id})\"><img src=\"/img/icons/silk/email_go.png\" border=\"0\" /></button>");
			}
		}
		$sql = "SELECT onl_nick, onl_created, onl_lastmoved FROM babel_online WHERE onl_nick = '{$Topic->usr_nick}' ORDER BY onl_lastmoved DESC LIMIT 1";
		$rs = mysql_query($sql, $this->db);
		if ($_online = mysql_fetch_array($rs)) {
			echo('<br /><small class="lime">online now</small>');
			unset($_online);
		} else {
			echo('<br /><small class="na">disconnected</small>');
		}
		mysql_free_result($rs);
		echo('</td><td valign="top" align="right" class="text">');
		if ($this->User->vxIsLogin()) {
			echo('<a href="#replyForm" onclick="jumpReply();">' . $this->lang->reply() . '</a>');
		} else {
			echo('<a href="/login//topic/view/' . $Topic->tpc_id . '.html">' . $this->lang->login_and_reply() . '</a>');	
		}
		echo(' | ');
		if (strlen($Topic->tpc_description) > 0) {
			echo('<a href="#" onclick="switchDisplay(' . "'" . 'tpcBrief' . "'" . ');">' . $this->lang->switch_description() . '</a> | ');
		}
		echo('<a href="#reply">' . $this->lang->jump_to_replies() . '</a>');
		if ($Topic->tpc_posts > 0) {
			echo('<small class="fade">(' . $Topic->tpc_posts . ')</small>');
		}
		echo('<div class="brief" id="tpcBrief">' . $Topic->tpc_description . '</div><table cellpadding="0" cellspacing="0" border="0"><tr><td width="40" height="30" class="lt"></td><td height="30" class="ct"></td><td width="40" height="30" class="rt"></td></tr><tr><td width="40" class="lm" valign="top"><img src="/img/td_arrow.gif" /></td><td class="origin" valign="top">');
		if ($Fav > 0) {
			echo('<img src="/img/icons/silk/star.png" align="absmiddle" />&nbsp;');
		}
		echo('<h1 class="ititle">' . make_plaintext($Topic->tpc_title) . '</h1> <span class="tip_i">... by ' . $Topic->usr_nick . ' ... ' . make_descriptive_time($Topic->tpc_created) . ' ... ' . $this->lang->hits($Topic->tpc_hits) . ' </span>');
		if ($this->User->usr_id == 1) {
			echo('&nbsp;<a href="#;" class="var" onclick="if (confirm(' . "'确认擦除？'" . ')) {location.href=' . "'/topic/erase/{$Topic->tpc_id}.vx';" . '}"><img src="/img/icons/silk/delete.png" align="absmiddle" border="0" /></a>');
		} else {
			if (($Topic->tpc_posts == 0) && ($Topic->tpc_uid == $this->User->usr_id) && ((time() - $Topic->tpc_created) < (86400 * 31))) {
				echo('&nbsp;<a href="#;" class="var" onclick="if (confirm(' . "'确认擦除？'" . ')) {location.href=' . "'/topic/erase/{$Topic->tpc_id}.vx';" . '}"><img src="/img/icons/silk/delete.png" align="absmiddle" border="0" /></a>');
			}
		}
		$ico_topic_modify = '&nbsp;<a href="/topic/modify/' . $Topic->tpc_id . '.vx" class="var"><img src="/img/icons/silk/page_white_edit.png" align="absmiddle" border="0" /></a>';
		if ($Topic->tpc_uid == $this->User->usr_id && $this->User->usr_id != 1) {
			if ((time() - $Topic->tpc_created) < 86400) {
				if ($Topic->tpc_posts < 3) {
					echo $ico_topic_modify;
				}
			}
		} else {
			if ($this->User->usr_id == 1) {
				echo $ico_topic_modify;
			}
		}
		if ($this->User->usr_id == 1) {
			echo('&nbsp;<a href="/topic/move/' . $Topic->tpc_id . '.vx" class="var"><img src="/img/icons/silk/arrow_out.png" align="absmiddle" border="0" /></a>');
		}
		echo('</span><br /><br />' . $Topic->tpc_content);
		
		echo('</span></td><td width="40" class="rm"></td></tr><tr><td width="40" height="20" class="lb"></td><td height="20" class="cb"></td><td width="40" height="20" class="rb"></td></tr></table></td></tr>');
		echo('</table>');
		
		_v_hr();
		
		echo('<div style="padding: 5px 2px 2px 2px;" align="right">');

		/* S: left and right */
		
		$sql = "SELECT tpc_id, tpc_title FROM babel_topic WHERE tpc_created < {$Topic->tpc_created} AND tpc_uid = {$Topic->tpc_uid} ORDER BY tpc_created DESC LIMIT 1";
		$rs = mysql_query($sql, $this->db);
		if (mysql_num_rows($rs) == 1) {
			$Left = mysql_fetch_object($rs);
			
		} else {
			$Left = false;
		}
		mysql_free_result($rs);
		
		$sql = "SELECT tpc_id, tpc_title FROM babel_topic WHERE tpc_created > {$Topic->tpc_created} AND tpc_uid = {$Topic->tpc_uid} ORDER BY tpc_created ASC LIMIT 1";
		$rs = mysql_query($sql, $this->db);
		if (mysql_num_rows($rs) == 1) {
			$Right = mysql_fetch_object($rs);
		} else {
			$Right = false;
		}
		mysql_free_result($rs);
		
		if ($Left && $Right) {
			echo ('<a href="/topic/view/' . $Left->tpc_id . '.html" class="tg"><span class="tip_i"><img src="/img/pico_left.gif" align="absmiddle" border="0" /> </span>' . make_plaintext($Left->tpc_title) . '&nbsp;</a><span class="tip_i">&nbsp;&nbsp;|&nbsp;&nbsp;</span>');
			echo ('<a href="/topic/view/' . $Right->tpc_id . '.html" class="tg">&nbsp;' . make_plaintext($Right->tpc_title) . '<span class="tip_i"> <img src="/img/pico_right.gif" align="absmiddle" border="0" /></span></a>');
		} else {
			if ($Left) {
				echo ('<a href="/topic/view/' . $Left->tpc_id . '.html" class="tg"><span class="tip_i"><img src="/img/pico_left.gif" align="absmiddle" border="0" /> </span>' . make_plaintext($Left->tpc_title) . '&nbsp;</a>');
			}
			if ($Right) {
				echo ('<a href="/topic/view/' . $Right->tpc_id . '.html" class="tg">&nbsp;' . make_plaintext($Right->tpc_title) . '<span class="tip_i"> <img src="/img/pico_right.gif" align="absmiddle" border="0" /></span></a>');
			}
		}
		
		/* E: left and right */
		
		_v_hr();
		
		/* S: new */
		
		echo('<a href="/topic/new/' . $Topic->tpc_pid . '.vx" class="h">在本讨论区创建新主题</a>');
		
		/* E: new */
		
		/* S: add to favorite */

		if ($this->User->vxIsLogin()) {
			if ($Fav > 0) {
				echo('<div id="tpcFav" style="display: inline;"><a onclick="removeFavoriteTopic(' . $Fav . ')" href="#;" class="h">X&nbsp;我不再喜欢这篇主题</a></div>');
			} else {
				echo('<div id="tpcFav" style="display: inline;"><a onclick="addFavoriteTopic(' . $Topic->tpc_id . ')" href="#;" class="h">:) 我喜欢这篇主题</a></div>');
			}
		}
		
		/* E: add to favorite */
		echo('<span class="tip_i">');
		if ($Topic->tpc_favs > 0) {
			echo(' ... <img src="' . CDN_UI . 'img/icons/silk/group_add.png" align="absmiddle"/> <a href="/who/fav/topic/' . $Topic->tpc_id . '" class="t">&nbsp;' . $Topic->tpc_favs . '&nbsp;</a>');
		}
		echo(' ... <a href="http://' . BABEL_DNS_FEED . '/feed/topic/' . $Topic->tpc_id . '.rss" target="_blank"><img src="' . CDN_UI . 'img/icons/silk/feed.png" align="absmiddle" border="0" alt="RSS 2.0 Feed" /></a>');
		echo('</span>');
		echo('</div>');
		echo('</div>');

		echo('<div class="blank">');
		echo('<a name="reply" class="img"><img src="/img/spacer.gif" width="1" height="1" style="display: none;" /></a>');
		$p = array();
		$p['base'] = '/topic/view/' . $topic_id . '/';
		$p['ext'] = '.html';
		$sql = "SELECT COUNT(pst_id) FROM babel_post WHERE pst_tid = {$topic_id}";
		$rs = mysql_query($sql, $this->db);
		$p['items'] = mysql_result($rs, 0, 0);
		$Topic->tpc_reply_count = $p['items'];
		mysql_free_result($rs);
		$i = 0;
		if ($p['items'] > 0) {
			$p['size'] = BABEL_TPC_PAGE;
			$p['span'] = BABEL_PG_SPAN;
			if (($p['items'] % $p['size']) == 0) {
				$p['total'] = $p['items'] / $p['size'];
			} else {
				$p['total'] = floor($p['items'] / $p['size']) + 1;
			}
			if (isset($_GET['p'])) {
				$p['cur'] = intval($_GET['p']);
			} else {
				$p['cur'] = 1;
			}
			if ($p['cur'] < 1) {
				$p['cur'] = 1;
			}
			if ($p['cur'] > $p['total']) {
				$p['cur'] = $p['total'];
			}
			if (($p['cur'] - $p['span']) >= 1) {
				$p['start'] = $p['cur'] - $p['span'];
			} else {
				$p['start'] = 1;
			}
			if (($p['cur'] + $p['span']) <= $p['total']) {
				$p['end'] = $p['cur'] + $p['span'];
			} else {
				$p['end'] = $p['total'];
			}
			$p['sql'] = ($p['cur'] - 1) * $p['size'];
			$rs = $Topic->vxGetAllReply($p);
		}
		if (!isset($p['cur'])) {
			$p['cur'] = 1;
		}
		$_SESSION['babel_page_topic'] = $p['cur'];
		if ($Topic->tpc_reply_count > 0) {
			echo('<div id="vxReplyTop">本主题共有 ' . $Topic->tpc_posts . ' 条回复 | <a href="#top">回到顶部</a> | ');
			if ($this->User->vxIsLogin()) {
				echo('<a href="#replyForm" onclick="jumpReply();">回复主题</a>');
			} else {
				echo('<a href="/login//topic/view/' . $Topic->tpc_id . '.html">登录后回复主题</a>');	
			}
			if ($p['total'] > 1) {
				echo('<br /><br />');
				$this->vxDrawPages($p);
			}
			echo('</div>');
			$i = 0;
			while ($Reply = mysql_fetch_object($rs)) {
				$i++;
				$j = ($p['cur'] - 1) * 60 + $i;
				if ($Reply->usr_portrait == '') {
					$img_usr_portrait = CDN_P . 'p_' . $Reply->usr_gender . '_s.gif';
				} else {
					$img_usr_portrait = CDN_P . 'p/' . $Reply->usr_portrait . '_s.' . BABEL_PORTRAIT_EXT;
				}
				if ($this->User->usr_id == 1) {
					$ico_erase = '&nbsp;<img src="/img/icons/silk/delete.png" align="absmiddle" onclick="if (confirm(' . "'确认擦除？'" . ')) {location.href=' . "'/post/erase/{$Reply->pst_id}.vx';" . '}" border="0" />';
				} else {
					$ico_erase = '';
				}
				if ($this->User->usr_id == 1 || ($this->User->usr_id == $Reply->usr_id && $j == $p['items'])) {
					$ico_modify = '&nbsp;<a href="/post/modify/' . $Reply->pst_id . '.vx" class="var"><img src="/img/icons/silk/page_white_edit.png" align="absmiddle" border="0" /></a>';
				} else {
					$ico_modify = '';
				}
				if (substr($Reply->pst_title, 0, 4) == 'Re: ' | trim($Reply->pst_title) == '') {
					if ($Reply->usr_id == $Topic->tpc_uid) {
						$txt_title = $j . ' 楼 <strong class="red">**</strong> <a href="/u/' . urlencode($Reply->usr_nick) . '" name="p' . $Reply->pst_id . '">' . $Reply->usr_nick . '</a> <span class="tip_i"> <a href="/geo/' . $Reply->usr_geo . '" class="silver">' . $this->Geo->map['name'][$Reply->usr_geo] . '</a> </span> @ ' . make_descriptive_time($Reply->pst_created);
					} else {
						$txt_title = $j . ' 楼 <a href="/u/' . urlencode($Reply->usr_nick) . '" name="p' . $Reply->pst_id . '">' . $Reply->usr_nick . '</a> <span class="tip_i"> <a href="/geo/' . $Reply->usr_geo . '" class="silver">' . $this->Geo->map['name'][$Reply->usr_geo] . '</a> </span> @ ' . make_descriptive_time($Reply->pst_created);
					}
				} else {
					if ($Reply->usr_id == $Topic->tpc_uid) {
						$txt_title = $j . ' 楼 <strong class="red">**</strong> <a href="/u/' . urlencode($Reply->usr_nick) . '" name="p' . $Reply->pst_id . '">' . $Reply->usr_nick . '</a> <span class="tip_i"> <a href="/geo/' . $Reply->usr_geo . '" class="silver">' . $this->Geo->map['name'][$Reply->usr_geo] . '</a> </span> @ ' . make_descriptive_time($Reply->pst_created) . '说: ' . $Reply->pst_title;
					} else {
						$txt_title = $j . ' 楼 <a href="/u/' . urlencode($Reply->usr_nick) . '" name="p' . $Reply->pst_id . '">' . $Reply->usr_nick . '</a> <span class="tip_i"> <a href="/geo/' . $Reply->usr_geo . '" class="silver">' . $this->Geo->map['name'][$Reply->usr_geo] . '</a> </span> @ ' . make_descriptive_time($Reply->pst_created) . '说: ' . $Reply->pst_title;
					}
				}
				
				$txt_title .= $ico_erase . $ico_modify;
				
				if (($i % 2) == 0) {
					echo ('<div class="light_even"><span style="color: ' . rand_color() . ';"><img src="' . $img_usr_portrait . '" align="absmiddle" style="border-left: 2px solid ' . rand_color() . '; padding: 0px 5px 0px 5px;" />');
					if ($Reply->usr_id == $Topic->tpc_uid) {
						echo ($txt_title . '</span><br /><br />' . format_ubb($Reply->pst_content));
					} else {
						echo ($txt_title . '</span><br /><br />' . format_ubb($Reply->pst_content));
					}
					echo ('</div>');
				} else {
					echo ('<div class="light_odd"><span style="color: ' . rand_color() . ';"><img src="' . $img_usr_portrait . '" align="absmiddle" style="border-left: 2px solid ' . rand_color() . '; padding: 0px 5px 0px 5px;" />');
					if ($Reply->usr_id == $Topic->tpc_uid) {
						echo ($txt_title . '</span><br /><br />' . format_ubb($Reply->pst_content));
					} else {
						echo ($txt_title . '</span><br /><br />' . format_ubb($Reply->pst_content));
					}
					echo ('</div>');
				}
			}
			if ($p['total'] > 1) {
				$this->vxDrawPages($p);
				echo('<br /><br />');
			}
			if ($this->Validator->vxIsAutisticNode($Node->nod_id, $this->cs)) {
				if ($this->User->usr_id == $Topic->tpc_uid) {
					echo('<div id="vxReplyTip"><a name="replyForm" class="img"><img src="/img/spacer.gif" width="1" height="1" style="display: none;" /></a><span class="tip_i">这是一个自闭模式的讨论区，你可以且只能回复你自己创建的主题。</span></div>');
				} else {
					echo('<div id="vxReplyTip"><a name="replyForm" class="img"><img src="/img/spacer.gif" width="1" height="1" style="display: none;" /></a><span class="tip_i">这是一个自闭模式的讨论区，你可能无法参与所有的主题。</span></div>');
				}
			} else {
				echo('<div id="vxReplyTip"><a name="replyForm" class="img"><img src="/img/spacer.gif" width="1" height="1" style="display: none;" /></a><span class="tip_i">看完之后有话想说？那就帮楼主加盖一层吧！</span></div>');
			}
		} else {
			if ($this->Validator->vxIsAutisticNode($Node->nod_id, $this->cs)) {
				if ($this->User->usr_id == $Topic->tpc_uid) {
					echo('<div id="vxReplyTip"><a name="replyForm" class="img"><img src="/img/spacer.gif" width="1" height="1" style="display: none;" /></a><span class="tip_i">这是一个自闭模式的讨论区，你可以且只能回复你自己创建的主题。</span></div>');
				} else {
					echo('<div id="vxReplyTip"><a name="replyForm" class="img"><img src="/img/spacer.gif" width="1" height="1" style="display: none;" /></a><span class="tip_i">这是一个自闭模式的讨论区，你可能无法参与所有的主题。</span></div>');
				}
			} else {
				echo('<div id="vxReplyTip"><a name="replyForm" class="img"><img src="/img/spacer.gif" width="1" height="1" style="display: none;" /></a><span class="tip_i">目前这个主题还没有回复，或许你可以帮楼主加盖一层？</span></div>');
			}
		}
		$i++;
		// if (($i % 2) == 0) { $_tmp = 'light_even'; } else { $_tmp = 'light_odd'; }
		$_tmp = 'light_odd';
		if ($this->User->vxIsLogin()) {
			if ($this->User->usr_portrait == '') {
				$img_usr_portrait = '/img/p_' . $this->User->usr_gender . '_s.gif';
			} else {
				$img_usr_portrait = '/img/p/' . $this->User->usr_portrait . '_s.' . BABEL_PORTRAIT_EXT;
			}
			if ($this->Validator->vxIsAutisticNode($Node->nod_id, $this->cs)) {
				if ($this->User->usr_id == $Topic->tpc_uid) {
					echo('<div class="' . $_tmp . '"><form action="/post/create/' . $Topic->tpc_id . '.vx" method="post" id="form_topic_reply"><span style="color: ' . rand_color() . ';"><img src="' . $img_usr_portrait . '" align="absmiddle" style="border-left: 2px solid ' . rand_color(0, 1) . '; padding: 0px 5px 0px 5px;" />现在继续回复道：<input type="text" class="sll" name="pst_title" value="Re: ' . make_single_return($Topic->tpc_title, 0) . '" /><br /><br /><textarea name="pst_content" rows="10" class="quick" id="taQuick"></textarea><input type="hidden" name="p_cur" value="' . $p['cur'] . '" /><div align="left" style="margin: 10px 0px 0px 0px; padding-left: 390px;">');
					_v_btn_f('立即回复', 'form_topic_reply');
					echo('</div></span></form></div>');
				} else {
					echo('<div class="' . $_tmp . '">你不能回复自闭模式讨论区中别人创建的主题。</div>');
				}
			} else {
				echo('<div class="' . $_tmp . '"><form action="/post/create/' . $Topic->tpc_id . '.vx" method="post" id="form_topic_reply"><span style="color: ' . rand_color() . ';"><img src="' . $img_usr_portrait . '" align="absmiddle" style="border-left: 2px solid ' . rand_color(0, 1) . '; padding: 0px 5px 0px 5px;" />现在回复楼主道：<input type="text" class="sll" name="pst_title" value="Re: ' . make_single_return($Topic->tpc_title, 0) . '" /><br /><br /><textarea name="pst_content" rows="10" class="quick" id="taQuick"></textarea><input type="hidden" name="p_cur" value="' . $p['cur'] . '" /><div align="left" style="margin: 10px 0px 0px 0px; padding-left: 390px;">');
				_v_btn_f('立即回复', 'form_topic_reply');
				echo('</div></span></form></div>');
			}
		} else {
			_v_hr();
			echo('<div class="light_odd" align="left"><span class="tip">');
			echo('<img src="/img/icons/silk/vcard.png" align="absmiddle" /> ');
			echo(' 在回复之前你需要先进行登录</span>');
			echo('<table cellpadding="0" cellspacing="0" border="0" class="form">');
			echo('<form action="/login.vx" method="post" id="Login">');
			echo('<input type="hidden" name="return" value="/topic/view/' . $Topic->tpc_id . '.html" />');
			echo('<tr><td width="200" align="right">电子邮件或昵称</td><td width="200" align="left"><input type="text" maxlength="100" class="sl" name="usr" tabindex="1" /></td><td width="150" rowspan="2" valign="middle" align="right"><input type="image" src="/img/graphite/login.gif" alt="' . Vocabulary::action_login . '" tabindex="3" /></td></tr><tr><td width="200" align="right">密码</td><td align="left"><input type="password" maxlength="32" class="sl" name="usr_password" tabindex="2" /></td></tr></form></table></div>');
		}
		echo('<div class="light_odd" style="margin-bottom: 5px; " align="left">');
		echo('<a href="#top">回到顶部</a> | ');
		if (isset($_SESSION['babel_page_node_' . $Node->nod_id])) {
			echo('<a href="/board/view/' . $Node->nod_id . '/' . $_SESSION['babel_page_node_' . $Node->nod_id] . '.html">' . make_plaintext($Node->nod_title) . '</a>');
		} else {
			$_SESSION['babel_page_node_' . $Node->nod_id] = 1;
			echo('<a href="/go/' . $Node->nod_name . '">' . make_plaintext($Node->nod_title) . '</a>');
		}
		echo(' | <a href="/">回到首页</a>');
		if ($this->User->vxIsLogin()) {
			echo(' | <a href="/user/modify.vx">修改信息与设置</a>');
		} else {
			echo(' | <a href="/signup.html">注册</a> | <a href="/passwd.vx">忘记密码</a>');
		}
		echo('</div>');
		
		if (isset($_SESSION['babel_hot'])) {
			echo('<span class="tip_i"><img src="/img/icons/silk/award_star_gold_1.png" align="absmiddle" /> 当前热门主题&nbsp;&nbsp;<a href="/topic/view/' . $_SESSION['babel_hot']['id'] . '.html" class="t">' . make_plaintext($_SESSION['babel_hot']['title']) . '</a> ... ' . $_SESSION['babel_hot']['posts'] . ' 篇回复</span>');
		}
		echo('</div>');
	}
	
	/* E module: Topic View block */
	
	/* S module: Topic Move block */
	
	public function vxTopicMove($topic_id) {
		$Topic = new Topic($topic_id, $this->db, 1, 1);
		$Node = new Node($Topic->tpc_pid, $this->db);
		$Section = $Node->vxGetNodeInfo($Node->nod_sid);
	
		echo('<div id="main">');
		
		echo('<div class="blank">');
		_v_ico_map();
		echo(' <a href="/">' . Vocabulary::site_name . '</a> &gt; <a href="/go/' . $Section->nod_name . '" target="_self">' . $Section->nod_title . '</a> &gt; <a href="/go/' . $Node->nod_name . '" target="_self">' . $Node->nod_title . '</a> &gt; <a href="/topic/view/' . $Topic->tpc_id . '.html">' . make_plaintext($Topic->tpc_title) . '</a> &gt; 移动主题</div>');
		
		echo('<div class="blank" align="left">');
		
		echo('<img src="/img/gt.gif" align="absmiddle" /> 你现在正在将主题 <a href="/topic/view/' . $Topic->tpc_id . '.html" class="t">' . make_plaintext($Topic->tpc_title) . '</a> 移动到 ...');
		
		echo('<hr size="1" color="#DDD" style="color: #DDD; background-color: #DDD; height: 1px; border: 0;" />');
		
		$sql = "SELECT nod_id, nod_title, nod_name FROM babel_node WHERE nod_level = 1 ORDER BY nod_weight DESC";
		$rs = mysql_query($sql, $this->db);
		while ($S = mysql_fetch_object($rs)) {
			echo('<h1><img src="/img/s/' . $S->nod_name . '.gif" align="absmiddle" />&nbsp;&nbsp;' . make_plaintext($S->nod_title) . '</h1>');
			$sql = "SELECT nod_id, nod_title, nod_name FROM babel_node WHERE nod_pid = {$S->nod_id} ORDER BY nod_topics DESC";
			$rs2 = mysql_query($sql, $this->db) or die(mysql_error($this->db));
			while ($N = mysql_fetch_object($rs2)) {
				$css_color = rand_color();
				echo('<a href="/topic/move/' . $Topic->tpc_id . '/to/' . $N->nod_id . '" class="var" style="color: ' . $css_color . ';">' . $N->nod_title . '</a> &nbsp;');
				$N = null;
			}
			$S = null;
			mysql_free_result($rs2);
		}
		mysql_free_result($rs);
		
		echo('</div>');
		
		echo('</div>');
	}
	
	/* E module: Topic Move block */

	/* S module: Expense View block */
	
	public function vxExpenseView() {
		$p = array();
		$p['base'] = '/expense/view/';
		$p['ext'] = '.vx';
		$sql = "SELECT COUNT(exp_id) FROM babel_expense WHERE exp_uid = {$this->User->usr_id}";
		$rs = mysql_query($sql, $this->db);
		$p['items'] = mysql_result($rs, 0, 0);
		mysql_free_result($rs);
		
		echo('<div id="main">');
		echo('<div class="blank" align="left">');
		_v_ico_map();
		echo(' <a href="/">' . Vocabulary::site_name . '</a> &gt; ' . Vocabulary::action_viewexpense . '</div>');
		echo('<div class="blank" align="left">');
		echo('<span class="text_large"><img src="/img/ico_expense.gif" align="absmiddle" class="home" />' . Vocabulary::action_viewexpense);
		/* S: truncate */
		if ($p['items'] > 0) {
			echo('<input type="image" style="margin-left: 10px;" onclick="truncateExpense()" src="/img/tico_truncate.gif" align="absmiddle" />');
		}
		/* E: truncate */
		echo('</span>');
		echo('<br />目前你口袋里有' . $this->User->usr_money_a['str']);
		echo('</div>');
		echo('<table width="100%" border="0" cellpadding="0" cellspacing="2" class="board">');
		$p['size'] = BABEL_USR_EXPENSE_PAGE;
		$p['span'] = BABEL_PG_SPAN;
		if (($p['items'] % $p['size']) == 0) {
			$p['total'] = $p['items'] / $p['size'];
		} else {
			$p['total'] = floor($p['items'] / $p['size']) + 1;
		}
		if (isset($_GET['p'])) {
			$p['cur'] = intval($_GET['p']);
		} else {
			$p['cur'] = 1;
		}
		if ($p['cur'] < 1) {
			$p['cur'] = 1;
		}
		if ($p['cur'] > $p['total']) {
			$p['cur'] = $p['total'];
		}
		if (($p['cur'] - $p['span']) >= 1) {
			$p['start'] = $p['cur'] - $p['span'];
		} else {
			$p['start'] = 1;
		}
		if (($p['cur'] + $p['span']) <= $p['total']) {
			$p['end'] = $p['cur'] + $p['span'];
		} else {
			$p['end'] = $p['total'];
		}
		$p['sql'] = ($p['cur'] - 1) * $p['size'];
		if ($p['total'] > 1) {
			echo('<tr><td align="left" class="hf" colspan="4">');
			$this->vxDrawPages($p);
			echo('</td></tr>');
		}
		$sql = "SELECT exp_id, exp_amount, exp_type, exp_memo, exp_created FROM babel_expense WHERE exp_uid = {$this->User->usr_id} ORDER BY exp_created DESC LIMIT {$p['sql']},{$p['size']}";
		$rs = mysql_query($sql, $this->db);
		while ($Expense = mysql_fetch_object($rs)) {
			echo('<tr>');
			if ($Expense->exp_amount > 0) {
				echo('<td width="24" height="24" align="center" valign="middle" class="star"><img src="/img/star_active.png" /></td>');
			} else {
				echo('<td width="24" height="24" align="center" valign="middle" class="star"><img src="/img/star_inactive.png" /></td>');
			}
			echo('<td height="24" align="left" id="tdExpense' . $Expense->exp_id . 'T" onmouseover="changeBlockStyle(' . "'" . 'tdExpense' . $Expense->exp_id . "T', '#FFFFCC'" . ');changeBlockStyle(' . "'" . 'tdExpense' . $Expense->exp_id . "N', '#FFFFCC'" . ');changeBlockStyle(' . "'" . 'tdExpense' . $Expense->exp_id . "L', '#FFFFCC'" . ');" onmouseout="changeBlockStyle(' . "'" . 'tdExpense' . $Expense->exp_id . "T', '#FFFFFF'" . ');changeBlockStyle(' . "'" . 'tdExpense' . $Expense->exp_id . "N', '#FFFFFF'" . ');changeBlockStyle(' . "'" . 'tdExpense' . $Expense->exp_id . "L', '#FFFFFF'" . ');">' . $this->User->usr_expense_type_msg[$Expense->exp_type] . '&nbsp;<span class="text_property">');
			switch ($Expense->exp_type) {
				default:
					echo($Expense->exp_memo);
					break;
				case 8:
					echo('收件人：<strong>' . $Expense->exp_memo . '</strong>');
					break;
			}
			echo('</span></td>');
			echo('<td width="120" height="24" align="left" id="tdExpense' . $Expense->exp_id . 'N" onmouseover="changeBlockStyle(' . "'" . 'tdExpense' . $Expense->exp_id . "T', '#FFFFCC'" . ');changeBlockStyle(' . "'" . 'tdExpense' . $Expense->exp_id . "N', '#FFFFCC'" . ');changeBlockStyle(' . "'" . 'tdExpense' . $Expense->exp_id . "L', '#FFFFCC'" . ');" onmouseout="changeBlockStyle(' . "'" . 'tdExpense' . $Expense->exp_id . "T', '#FFFFFF'" . ');changeBlockStyle(' . "'" . 'tdExpense' . $Expense->exp_id . "N', '#FFFFFF'" . ');changeBlockStyle(' . "'" . 'tdExpense' . $Expense->exp_id . "L', '#FFFFFF'" . ');">');
			if ($Expense->exp_amount > 0) {
				echo('<small class="green">');
			} else {
				echo('<small class="red">');
			}
			printf("%.2f</small></td>", $Expense->exp_amount);
			echo('<td width="120" height="24" align="left" id="tdExpense' . $Expense->exp_id . 'L" onmouseover="changeBlockStyle(' . "'" . 'tdExpense' . $Expense->exp_id . "T', '#FFFFCC'" . ');changeBlockStyle(' . "'" . 'tdExpense' . $Expense->exp_id . "N', '#FFFFCC'" . ');changeBlockStyle(' . "'" . 'tdExpense' . $Expense->exp_id . "L', '#FFFFCC'" . ');" onmouseout="changeBlockStyle(' . "'" . 'tdExpense' . $Expense->exp_id . "T', '#FFFFFF'" . ');changeBlockStyle(' . "'" . 'tdExpense' . $Expense->exp_id . "N', '#FFFFFF'" . ');changeBlockStyle(' . "'" . 'tdExpense' . $Expense->exp_id . "L', '#FFFFFF'" . ');"><small class="time">' . make_descriptive_time($Expense->exp_created) . '</small></td>');
			echo('</tr>');
		}
		mysql_free_result($rs);
		if ($p['total'] > 1) {
			echo('<tr><td align="left" class="hf" colspan="4">');
			$this->vxDrawPages($p);
			echo('</td></tr>');
		}
		echo('</table>');
		echo('</div>');
		echo('<div class="_hh"></div>');
		echo('</div>');
	}
	
	/* E module: Expense View block */
	
	/* S module: Online View block */
	
	public function vxOnlineView() {
		$p = array();
		$p['base'] = '/online/view/';
		$p['ext'] = '.vx';
		$sql = "SELECT COUNT(onl_hash) FROM babel_online";
		$rs = mysql_query($sql, $this->db);
		$p['items'] = mysql_result($rs, 0, 0);
		mysql_free_result($rs);
		
		$p['size'] = 36;
		$p['span'] = BABEL_PG_SPAN;
		if (($p['items'] % $p['size']) == 0) {
			$p['total'] = $p['items'] / $p['size'];
		} else {
			$p['total'] = floor($p['items'] / $p['size']) + 1;
		}
		if (isset($_GET['p'])) {
			$p['cur'] = intval($_GET['p']);
		} else {
			$p['cur'] = 1;
		}
		if ($p['cur'] < 1) {
			$p['cur'] = 1;
		}
		if ($p['cur'] > $p['total']) {
			$p['cur'] = $p['total'];
		}
		if (($p['cur'] - $p['span']) >= 1) {
			$p['start'] = $p['cur'] - $p['span'];
		} else {
			$p['start'] = 1;
		}
		if (($p['cur'] + $p['span']) <= $p['total']) {
			$p['end'] = $p['cur'] + $p['span'];
		} else {
			$p['end'] = $p['total'];
		}
		$p['sql'] = ($p['cur'] - 1) * $p['size'];
		echo('<div id="main">');
		echo('<div class="blank" align="left">');
		_v_ico_map();
		echo(' <a href="/">' . Vocabulary::site_name . '</a> &gt; ' . Vocabulary::action_viewonline . '</div>');
		echo('<div class="blank" align="left">');
		echo('<span class="text_large"><img src="/img/ico_board.gif" align="absmiddle" class="home" />' . Vocabulary::action_viewonline . '</span>');
		echo('<br />目前共有 ' . $this->online_count . ' 人或者机器在线，其中注册会员 ' . $this->online_count_reg . ' 个，游客 ' . $this->online_count_anon . ' 个。');
		_v_hr();
		echo('<img src="/img/icons/silk/user_go.png" align="absmiddle" class="map" /> 当前在线的注册会员&nbsp;&nbsp;');
		$sql = "SELECT DISTINCT onl_nick FROM babel_online WHERE onl_nick != '' ORDER BY onl_nick ASC";
		$rs = mysql_query($sql, $this->db);
		while ($Member = mysql_fetch_object($rs)) {
			$css_color = rand_color();
			echo(' <a href="/u/' . urlencode($Member->onl_nick) . '" style="color: ' . $css_color . '" class="var">' . make_plaintext($Member->onl_nick) . '</a> ');
			$Member = null;
		}
		mysql_free_result($rs);
		
		if ($p['total'] > 1) {
			_v_hr();
			$this->vxDrawPages($p);
		}
		
		_v_hr();
		
		echo('<table width="100%" border="0" cellpadding="0" cellspacing="0">');
		$sql = 'SELECT onl_nick, onl_ip, onl_ua, onl_uri, onl_ref, onl_created, onl_lastmoved FROM babel_online ORDER BY onl_lastmoved DESC LIMIT ' . $p['sql'] . ',' . $p['size'];
		$rs = mysql_query($sql, $this->db);
		$i = 0;
		while ($Online = mysql_fetch_object($rs)) {
			$i++;
			if ($i == 1) {
				echo('<tr>');
			}
			if (($i > 1) && ($i % 3 == 1)) {
				echo('<tr>');
			}
			echo('<td width="33%"><div class="user_gray_border">');
			if ($Online->onl_nick == '') {
				echo('<span class="tip_i">匿名访客或机器人 - ' . make_descriptive_time($Online->onl_lastmoved) . ' - <small class="fade">' . $Online->onl_ip . '</small></span>');
				_v_hr();
				if (mb_strlen($Online->onl_ua, 'UTF-8') > 30) {
					$Online->onl_ua = mb_substr($Online->onl_ua, 0, 30) . ' ...';
				}
				echo('<span class="tip_i"><img src="/img/icons/silk/bullet_white.png" align="absmiddle" /> <small>' . make_plaintext($Online->onl_ua) . '</small></span>');
				_v_hr();
				if (mb_strlen($Online->onl_uri, 'UTF-8') > 30) {
					$Online->onl_uri_display = mb_substr($Online->onl_ua, 0, 30) . ' ...';
				} else {
					$Online->onl_uri_display = $Online->onl_uri;
				}
				echo('<img src="/img/icons/silk/bullet_green.png" align="absmiddle" /> <span class="tip_i"><small>' . make_plaintext($Online->onl_uri_display) . '</small></span>');
				if ($Online->onl_ref != '') {
					if (strlen($Online->onl_ref) >= 40) {
						$ref = substr($Online->onl_ref, 0, 40) . '...';
					} else {
						$ref = $Online->onl_ref;
					}
					_v_hr();
					echo('<img src="/img/icons/silk/bullet_yellow.png" align="absmiddle" /> <span class="tip_i"><small><a href="' . make_plaintext($Online->onl_ref) . '" class="var">' . make_plaintext($ref) . '</a></small></small>');
				}
			} else {
				echo('<img src="/img/icons/silk/user.png" align="absmiddle" /> <a href="/u/' . urlencode($Online->onl_nick) . '" class="t">' . make_plaintext($Online->onl_nick) . '</a> <span class="tip_i"> - ' . make_descriptive_time($Online->onl_lastmoved) . ' - </span> <small class="lime">' . make_masked_ip($Online->onl_ip) . '</small>');
				_v_hr();
				if (mb_strlen($Online->onl_ua, 'UTF-8') > 30) {
					$Online->onl_ua = mb_substr($Online->onl_ua, 0, 30) . ' ...';
				}
				echo('<span class="tip"><img src="/img/icons/silk/computer.png" align="absmiddle" /> <small>' . make_plaintext($Online->onl_ua) . '</small></span>');
				_v_hr();
				if (mb_strlen($Online->onl_uri, 'UTF-8') > 30) {
					$Online->onl_uri_display = mb_substr($Online->onl_ua, 0, 30) . ' ...';
				} else {
					$Online->onl_uri_display = $Online->onl_uri;
				}
				echo('<img src="/img/icons/silk/user_go.png" align="absmiddle" /> <small><a href="' . make_plaintext($Online->onl_uri) . '">' . make_plaintext($Online->onl_uri_display) . '</a></small>');
				if ($Online->onl_ref != '') {
					if (strlen($Online->onl_ref) >= 40) {
						$ref = substr($Online->onl_ref, 0, 40) . ' ...';
					} else {
						$ref = $Online->onl_ref;
					}
					_v_hr();
					echo('<img src="/img/icons/silk/bullet_yellow.png" align="absmiddle" /> <span class="tip_i"><small><a href="' . make_plaintext($Online->onl_ref) . '" class="var">' . make_plaintext($ref) . '</a></small></small>');
				}
			}
			
			echo('</div></td>');
			if (($i > 1) && (($i % 3) == 0)) {
				echo('</tr>');
			}
		}
		mysql_free_result($rs);
		
		echo('</table>');
		
		if ($p['total'] > 1) {
			_v_hr();
			$this->vxDrawPages($p);
		}
		echo('</div>');
		echo('</div>');
	}
	
	/* E module: Online View block */
	
	/* S module: Who Join block */
	
	public function vxWhoJoin() {
		$p = array();
		$p['base'] = '/who/join/';
		$p['ext'] = '.html';
		$p['items'] = $this->usr_count;
		
		$p['size'] = 36;
		$p['span'] = BABEL_PG_SPAN;
		if (($p['items'] % $p['size']) == 0) {
			$p['total'] = $p['items'] / $p['size'];
		} else {
			$p['total'] = floor($p['items'] / $p['size']) + 1;
		}
		if (isset($_GET['p'])) {
			$p['cur'] = intval($_GET['p']);
		} else {
			$p['cur'] = 1;
		}
		if ($p['cur'] < 1) {
			$p['cur'] = 1;
		}
		if ($p['cur'] > $p['total']) {
			$p['cur'] = $p['total'];
		}
		if (($p['cur'] - $p['span']) >= 1) {
			$p['start'] = $p['cur'] - $p['span'];
		} else {
			$p['start'] = 1;
		}
		if (($p['cur'] + $p['span']) <= $p['total']) {
			$p['end'] = $p['cur'] + $p['span'];
		} else {
			$p['end'] = $p['total'];
		}
		$p['sql'] = ($p['cur'] - 1) * $p['size'];
		
		echo('<div id="main">');
		echo('<div class="blank" align="left">');
		_v_ico_map();
		echo(' <a href="/">' . Vocabulary::site_name . '</a> &gt; ' . Vocabulary::term_member . '列表');
		
		_v_hr();
		
		if ($p['total'] > 1) {
			$this->vxDrawPages($p);
			_v_hr();
		}
		
		echo('<table width="100%" border="0" cellpadding="0" cellspacing="0">');
		$sql = 'SELECT usr_id, usr_nick, usr_gender, usr_geo, usr_brief, usr_portrait, usr_created, usr_logins FROM babel_user ORDER BY usr_created DESC LIMIT ' . $p['sql'] . ',' . $p['size'];
		$rs = mysql_query($sql, $this->db);
		$i = 0;
		while ($Member = mysql_fetch_object($rs)) {
			$i++;
			if ($i == 1) {
				echo('<tr>');
			}
			if (($i > 1) && ($i % 3 == 1)) {
				echo('<tr>');
			}
			echo('<td width="33%">');
			if ($Member->usr_logins > 31) {
				echo('<div class="user_graphite_border">');
			} else {
				echo('<div class="user_gray_border">');
			}
			if ($Member->usr_portrait != '') {
				$img_p = '<img src="/img/p/' . $Member->usr_portrait . '_n.jpg" align="absmiddle" class="portrait" />';
			} else {
				if ($Member->usr_gender == 1) {
					$img_p = '<img src="/img/icons/silk/male.png" align="absmiddle" />';
				} elseif ($Member->usr_gender == 2) {
					$img_p = '<img src="/img/icons/silk/female.png" align="absmiddle" />';
				} elseif ($Member->usr_gender == 0) {
					$img_p = '<img src="/img/icons/silk/user_gray.png" align="absmiddle" />';
				} elseif ($Member->usr_gender == 9) {
					$img_p = '<img src="/img/icons/silk/user_green.png" align="absmiddle" />';
				} elseif ($Member->usr_gender == 5) {
					$img_p = '<img src="/img/icons/silk/user_suit.png" align="absmiddle" />';
				} elseif ($Member->usr_gender == 6) {
					$img_p = '<img src="/img/icons/silk/user_female.png" align="absmiddle" />';
				} else {
					$img_p = '<img src="/img/icons/silk/user.png" align="absmiddle" />';
				}
			}
			echo($img_p . ' <a href="/u/' . urlencode($Member->usr_nick) . '" class="t">' . make_plaintext($Member->usr_nick) . '</a>');
			if ($Member->usr_geo != 'earth') {
				echo(' <a href="/geo/' . urlencode($Member->usr_geo) . '" class="o">' . $this->Geo->map['name'][$Member->usr_geo] . '</a>');
			}
			if ($Member->usr_brief != '') {
				_v_hr();
				echo('<span class="tip">' . make_plaintext($Member->usr_brief) . '</span>');
			}
			_v_hr();
			echo('<span class="tip"><img src="/img/icons/silk/bullet_green.png" align="absmiddle" /> ');
			if ($Member->usr_created > (time() - 86400)) {
				echo(make_descriptive_time($Member->usr_created));
			} else {
				if ($Member->usr_logins > 31) {
					echo('<small>' . date('Y-n-j G:i:s', $Member->usr_created) . '</small>');
					echo('<small class="lime"> - ' . $Member->usr_logins . ' logins</small>');
				} else {
					echo('<span class="tip_i"><small>' . date('Y-n-j G:i:s', $Member->usr_created) . '</small></span>');
				}
			}
			echo('</span>');
			echo('</div></td>');
			if (($i > 1) && (($i % 3) == 0)) {
				echo('</tr>');
			}
		}
		mysql_free_result($rs);
		
		echo('</table>');
		
		if ($p['total'] > 1) {
			_v_hr();
			$this->vxDrawPages($p);
		}
		_v_hr();
		echo('<span class="tip_i"><img src="/img/icons/silk/information.png" align="absmiddle" /> 共 ' . $this->usr_count . ' 名注册会员</small>');
		echo('</div>');
		echo('</div>');
		
	}
	
	/* E module: Who Join block */
	
	public function vxMobile() {
		echo('<div id="main">');
		echo('<div class="blank" align="left">');
		_v_ico_map();
		echo(' <a href="/">' . Vocabulary::site_name . '</a> &gt; ' . Vocabulary::action_mobile_search . '</div>');
		if (isset($_GET['no'])) {
			$no = trim($_GET['no']);
			if (strlen($no) == 11) {
				$no_7 = mysql_real_escape_string(substr($no, 0, 7));
				$sql = "SELECT mob_no, mob_area, mob_subarea FROM babel_mobile_data WHERE mob_no = {$no_7}";
				$rs = mysql_query($sql);
				if (mysql_num_rows($rs) == 1) {
					
					$N = mysql_fetch_object($rs);
					echo('<div class="blank"><span class="mob"><img src="/img/gt.gif" align="absmiddle" alt="&gt;" />&nbsp;手机号码 <span class="mobile">' . $no . '</span> 的所在地：' . $N->mob_area);
					if ($N->mob_subarea != '') {
						echo(' / ' . $N->mob_subarea);
					}
					echo('</span></div>');
				} else {
					echo('<div class="blank"><span class="mob"><img src="/img/pico_web.gif" align="absmiddle" class="portrait" />&nbsp;手机号码 <span class="mobile">' . $no . '</span> 的所在地未知。</span></div>');
				}
				mysql_free_result($rs);
			}
		}
		echo('<div class="blank" align="left"><span class="text_large"><img src="/img/ico_search.gif" class="home" align="absmiddle" />' . Vocabulary::action_mobile_search . '</span><form action="http://www.v2ex.com/search_mobile.php" method="get"><input type="text" name="q" id="k_search_q" onmouseover="this.focus()" class="search" /><span class="tip"></span><br /><br /><input type="image" src="/img/graphite/search.gif" /></form></div>');
		echo('<div class="blank"><img src="/img/pico_tuser.gif" align="absmiddle" class="portrait" />&nbsp;数据来源 <a href="http://www.imobile.com.cn/" target="_blank">手机之家</a></div>');
		echo('</div>');
	}
	
	public function vxMan() {
		echo('<div id="main">');
		echo('<div class="blank" align="left">');
		_v_ico_map();
		echo(' <a href="/">' . Vocabulary::site_name . '</a> &gt; ' . Vocabulary::action_man_search . '</div>');
		if (isset($_GET['q'])) {
			$_q = urldecode(strtolower(substr($_SERVER['REQUEST_URI'], 5, (strlen($_SERVER['REQUEST_URI']) - 5))));
			$_q_h = 'search_man_' . md5($_q);
		} else {
			$_q = '';
		}
		echo('<div class="blank" align="left"><span class="text_large"><img src="/img/ico_freebsd.gif" class="home" align="absmiddle" />' . Vocabulary::action_man_search . '</span><form action="http://www.v2ex.com/search_man.php" method="get"><input type="text" name="q" id="k_search_q" onmouseover="this.focus()" class="search" value="' . make_single_return($_q) . '" /><span class="tip"></span><br /><br /><input type="image" src="/img/graphite/search.gif" /></form></div>');
		
		echo('<table width="100%" border="0" cellpadding="0" cellspacing="2" class="board">');
		if ($_q != '') {
			$time_start = microtime_float();
			if ($hits_c = $this->cl->load($_q_h)) {
				$hits_c = unserialize($hits_c);
				$_count = unserialize($this->cl->load('count_' . $_q_h));
				$time_end = microtime_float();
				$time_elapsed = $time_end - $time_start;
				if ($_count > 0) {
					$i = 0;
					echo('<tr><td colspan="2" class="hf" height="18" style="border-bottom: 1px solid #CCC;">找到 ' . $_count . ' 篇匹配的参考文档，以下是最相关的前 ' . ($_count > 30 ? 30 : $_count) . ' 篇，');
					printf('耗时 %.3f 秒。', $time_elapsed);
					echo('</td></tr>');
					echo('<tr><td valign="top">');
					echo('<table border="0" cellpadding="0" cellspacing="2">');
					foreach ($hits_c as $hit_c) {
						$i++;
						if ($i == 1) {
							echo('<tr><td colspan="2" height="10"></td></tr>');
						}
						echo('<tr><td width="24" height="18" valign="top" align="center" class="star"><a href="http://' . BABEL_DNS_NAME . '/man/' . $hit_c['set_name'] . '/" target="_blank" class="var"><img src="/img/man/' . $hit_c['set_name'] . '.gif" alt="' . $hit_c['set_title'] . '" border="0" /></a></td>');
						echo('<td height="18" class="star"><a href="http://' . BABEL_DNS_NAME . $hit_c['url'] . '" class="blue" target="_blank">' . htmlspecialchars_decode($hit_c['title']) . '</a><small> - <a href="http://' . BABEL_DNS_NAME . '/man/' . $hit_c['set_name'] . '/" class="t" target="_blank">' . $hit_c['set_title'] . '</a> - <span class="tip_i">');
						printf("%.3f%%", $hit_c['score'] * 10);
						echo('</span></small></td></tr>');
						echo('<tr><td width="24"></td><td class="hf"><span class="excerpt">');
						echo(trim_br(make_excerpt_man(wordwrap(htmlspecialchars_decode($hit_c['contents']), 76, '<br />', 1), $_q, '<span class="text_matched">\1</span>')));
						echo('</span></td></tr>');
						echo('<tr><td width="24"></td><td valign="top"><span class="tip"><span class="green">');
						echo(BABEL_DNS_NAME .  $hit_c['url']);
						echo(' - ' . date('Y年n月j日', $hit_c['mtime']));
						echo('</span></span></td></tr>');
						echo('<tr><td colspan="2" height="10"></td></tr>');
					}
					echo('</table></td>');
					echo('<td width=" class="hf" valign="top" align="right">');
					// some promotions here
					echo('</td></tr>');
				} else {
					printf("<tr><td colspan=\"2\" class=\"hf\">没有找到任何匹配的参考文档，本次操作耗时 %.3f 秒。</td></tr>", $time_elapsed);
				}
			} else {
				try {
					$index = new Zend_Search_Lucene(BABEL_PREFIX . '/data/lucene/man');
					$hits = $index->find($_q);
					$_count = count($hits);
					$time_end = microtime_float();
					$time_elapsed = $time_end - $time_start;
				} catch (Zend_Search_Lucene_Exception $e) {
					printf("<tr><td colspan=\"2\" class=\"hf\">没有找到任何匹配的参考文档，建议你检查你搜索时候所用的语法，本次操作耗时 %.3f 秒。</td></tr>", $time_elapsed);
					$_count = 0;
				}
				
				if ($_count > 0) {
					$hits_c = array();
					echo('<tr><td colspan="2" class="hf" height="18" style="border-bottom: 1px solid #CCC;">找到 ' . $_count . ' 篇匹配的参考文档，以下是最相关的前 ' . ($_count > 30 ? 30 : $_count) . ' 篇，');
					printf('耗时 %.3f 秒。', $time_elapsed);
					echo('</td></tr>');
					$i = 0;
					echo('<tr><td valign="top">');
					echo('<table border="0" cellpadding="0" cellspacing="2">');
					foreach ($hits as $hit) {
						$doc = $hit->getDocument();
						$hit_c = array();
						$hit_c['url'] = $doc->getFieldValue('url');
						$hit_c['title'] = $doc->getFieldValue('title');
						$hit_c['contents'] = $doc->getFieldValue('contents');
						$hit_c['set_name'] = $doc->getFieldValue('set_name');
						$hit_c['set_title'] = $doc->getFieldValue('set_title');
						$hit_c['mtime'] = $doc->getFieldValue('mtime');
						$hit_c['score'] = $hit->score;
						$hits_c[] = $hit_c;
						$i++;
						if ($i > 30) {
							break;
						}
						if ($i == 1) {
							echo('<tr><td colspan="2" height="10"></td></tr>');
						}
						echo('<tr><td width="24" height="18" valign="top" align="center" class="star"><a href="http://' . BABEL_DNS_NAME . '/man/' . $hit_c['set_name'] . '/" target="_blank" class="var"><img src="/img/man/' . $hit_c['set_name'] . '.gif" alt="' . $hit_c['set_title'] . '" border="0" /></a></td>');
						echo('<td height="18" class="star"><a href="http://' . BABEL_DNS_NAME . $hit_c['url'] . '" class="blue" target="_blank">' . htmlspecialchars_decode($hit_c['title']) . '</a><small> - <a href="http://' . BABEL_DNS_NAME . '/man/' . $hit_c['set_name'] . '/" class="t" target="_blank">' . $hit_c['set_title'] . '</a> - <span class="tip_i">');
						printf("%.3f%%", $hit_c['score'] * 10);
						echo('</span></small></td>');
						echo('</tr>');
						echo('<tr><td width="24"></td><td class="hf"><span class="excerpt">');
						echo(trim_br(make_excerpt_man(wordwrap(htmlspecialchars_decode($hit_c['contents']), 76, '<br />', 1), $_q, '<span class="text_matched">\1</span>')));
						echo('</span></td>');
						echo('</tr>');
						echo('<tr><td width="24"></td><td valign="top"><span class="tip"><span class="green">');
						echo(BABEL_DNS_NAME .  $hit_c['url']);
						echo(' - ' . date('Y年n月j日', $hit_c['mtime']));
						echo('</span></span></td></tr>');
						echo('<tr><td colspan="2" height="10"></td></tr>');
					}
					echo('</table></td>');
					echo('<td width=" class="hf" valign="top" align="right">');
					// some promotions here
					echo('</td></tr>');
					$this->cl->save(serialize($hits_c), $_q_h);
					$this->cl->save(serialize($_count), 'count_' . $_q_h);
				} else {
					$hits_c = array();
					$_count = 0;
					$this->cl->save(serialize($hits_c), $_q_h);
					$this->cl->save(serialize($_count), 'count_' . $_q_h);
					if (@!$e) {
						printf("<tr><td colspan=\"2\" class=\"hf\">没有找到任何匹配的参考文档，本次操作耗时 %.3f 秒。</td></tr>", $time_elapsed);
					}
				}
			}
			
			echo('<tr><td class="hf" colspan="2" height="18" style="border-top: 1px solid #CCC;"><img src="/img/pico_tuser.gif" align="absmiddle" class="portrait" />&nbsp;目前索引有 <span class="tip_i">');
			if ($sets = $this->cl->load('sets_search_man')) {
				$sets = unserialize($sets);
				foreach ($sets as $key => $value) {
					$css_color = rand_color();
					echo(' ... <a href="http://' . BABEL_DNS_NAME . '/man/' . $key . '/" target="_blank" style="color: ' . $css_color . '" class="var">' . $value[$key] . '</a>');
				}
			} else {
				$sets = array();
				$xml = simplexml_load_file(BABEL_PREFIX . '/res/man.xml');
				foreach ($xml->sets->set as $o) {
					$css_color = rand_color();
					$set = array();
					$set[strval($o['name'])] = strval($o['title']);
					$sets[strval($o['name'])] = $set;
					echo(' ... <a href="http://' . BABEL_DNS_NAME . '/man/' . $o['name'] . '/" target="_blank" style="color: ' . $css_color . '" class="var">' . $o['title'] . '</a>');
				}
				$this->cl->save(serialize($sets), 'sets_search_man');
			}
			echo('</span></td></tr>');
			echo('</table>');
		} else {
			echo('<tr><td class="hf" height="18"><img src="/img/pico_tuser.gif" align="absmiddle" class="portrait" />&nbsp;目前索引有 <span class="tip_i">');
			if ($sets = $this->cl->load('sets_search_man')) {
				$sets = unserialize($sets);
				foreach ($sets as $key => $data) {
					$css_color = rand_color();
					echo(' ... <a href="http://' . BABEL_DNS_NAME . '/man/' . $key . '/" target="_blank" style="color: ' . $css_color . '" class="var">' . $value . '</a>');
				}
			} else {
				$sets = array();
				$xml = simplexml_load_file(BABEL_PREFIX . '/res/man.xml');
				foreach ($xml->sets->set as $o) {
					$css_color = rand_color();
					$set = array();
					$set[strval($o['name'])] = strval($o['title']);
					$sets[] = $set;
					echo(' ... <a href="http://' . BABEL_DNS_NAME . '/man/' . $o['name'] . '/" target="_blank" style="color: ' . $css_color . '" class="var">' . $o['title'] . '</a>');
				}
				$this->cl->save('sets_search_man', serialize($sets));
			}
			echo('</span></td></tr>');
			echo('</table>');
		}
		
		echo('</div>');
	}
	
	public function vxZen($options) {
		$User =& $options['target'];
		
		/* S: Unfinished Projects */
		if ($this->User->usr_id == $User->usr_id) {
			$sql = "SELECT zpr_id, zpr_uid, zpr_private, zpr_title, zpr_created, zpr_lastupdated, zpr_lasttouched, zpr_completed FROM babel_zen_project WHERE zpr_progress = 0 AND zpr_uid = {$User->usr_id} ORDER BY zpr_created ASC";
		} else {
			$sql = "SELECT zpr_id, zpr_uid, zpr_private, zpr_title, zpr_created, zpr_lastupdated, zpr_lasttouched, zpr_completed FROM babel_zen_project WHERE zpr_progress = 0 AND zpr_uid = {$User->usr_id} AND zpr_private = 0 ORDER BY zpr_created ASC";
		}
		
		$rs = mysql_query($sql, $this->db);
		echo('<div id="main">');
		echo('<div class="blank" align="left">');
		_v_ico_map();
		echo(' <a href="/">' . Vocabulary::site_name . '</a> &gt; <a href="/u/' . urlencode($User->usr_nick) . '">' . $User->usr_nick . '</a> &gt; ' . Vocabulary::term_zen . ' <span class="tip_i"><small>alpha</small></span></div>');
		echo('<div class="blank"><span class="text_large"><a style="color: ' . rand_color() . ';" href="/u/' . urlencode($User->usr_nick) . '" class="var">' . $User->usr_nick . '</a> / 进行中的项目</span>');
		
		if ($_SESSION['babel_ua']['GECKO_DETECTED'] || $_SESSION['babel_ua']['KHTML_DETECTED'] || $_SESSION['babel_ua']['OPERA_DETECTED']) {
			$hack_width = 'width="100%" ';
		} else {
			$hack_width = 'width="99%" ';
		}
		
		echo('<table '. $hack_width . 'cellpadding="0" cellspacing="0" border="0" class="zen">');
		
		while ($Project = mysql_fetch_object($rs)) {
			echo('<tr><td class="zen_project">');
			echo('<a name="p' . $Project->zpr_id . '"></a>');
			echo('<span class="zen_project"><img src="' . CDN_IMG . 'gt.gif" align="absmiddle" />&nbsp;&nbsp;' . make_plaintext($Project->zpr_title) . '</span><span class="tip_i"> ... 创建于 ' . make_descriptive_time($Project->zpr_created));
			if ($Project->zpr_uid == $this->User->usr_id) {
				echo(' ... <a href="#;" onclick="if (confirm(' . "'确认删除项目及其下面的所有任务？\\n\\n" . addslashes(make_single_return(make_plaintext($Project->zpr_title))) . "'" . ')) { location.href = ' . "'" . '/erase/zen/project/' . $Project->zpr_id . '.vx' . "'" . ';}" class="zen_rm">X del</a>');
				if ($Project->zpr_private == 1) {
					$permission = '* private';
				} else {
					$permission = '@ public';
				}
				echo(' <a href="/change/zen/project/permission/' . $Project->zpr_id . '.vx" class="zen_pr">' . $permission . '</a>');	
			}
			if ($Project->zpr_uid == $this->User->usr_id) {
				if ($Project->zpr_private == 1) {
					echo (' ... 这个项目只有你自己可以看到');
				} else {
					echo (' ... 这个项目人人可见');
				}
			}
			echo('</span></td></tr>');
			$sql = "SELECT zta_id, zta_uid, zta_title, zta_progress, zta_created, zta_lastupdated, zta_completed FROM babel_zen_task WHERE zta_pid = {$Project->zpr_id} ORDER BY zta_progress ASC, zta_created ASC";
			$tasks = mysql_query($sql, $this->db);
			$i = 0;
			$j = 0;
			while ($Task = mysql_fetch_object($tasks)) {
				if ($Task->zta_progress == 0) {
					$i++;
					echo('<tr><td class="zen_task_todo">');
					if ($Project->zpr_uid == $this->User->usr_id) {
						echo('<input onchange="ZENDoneTask(' . $Task->zta_id . ');" type="checkbox" />');
					} else {
						echo('<input disabled="disabled" type="checkbox" />');
					}
					echo('&nbsp;' . make_plaintext($Task->zta_title));
					if ($Task->zta_uid == $this->User->usr_id) {
						echo('<span class="tip_i"> ... <a href="#;" onclick="if (confirm(' . "'确认删除任务？\\n\\n" . addslashes(make_single_return(make_plaintext($Task->zta_title))) . "'" . ')) { location.href = ' . "'" . '/erase/zen/task/' . $Task->zta_id . '.vx' . "'" . ';}" class="zen_rm">X del</a></span>');
					}
					echo('</td></tr>');
				} else {
					$j++;
					if (($j == 1) && ($Project->zpr_uid == $this->User->usr_id)) {
						$this->vxZENProjectForm($Project);
					}
					echo('<tr><td class="zen_task_done"><img src="' . CDN_IMG . 'check_green.gif" align="absmiddle" alt="done" />&nbsp;&nbsp;' . make_plaintext($Task->zta_title));
					if ($Task->zta_uid == $this->User->usr_id) {
						echo('<span class="tip_i"> ... <a href="#;" onclick="if (confirm(' . "'确认删除任务？\\n\\n" . addslashes(make_single_return(make_plaintext($Task->zta_title))) . "'" . ')) { location.href = ' . "'" . '/erase/zen/task/' . $Task->zta_id . '.vx' . "'" . ';}" class="zen_rm">X del</a> <a href="/undone/zen/task/' . $Task->zta_id . '.vx" class="zen_undone">- undone</a></span>');
					}
					echo('</td></tr>');
				}
			}
			if (($i == 0 && $j == 0) && ($Project->zpr_uid == $this->User->usr_id)) {
				$this->vxZENProjectForm($Project);
			}
			
			if (($i > 0 && $j == 0) && ($Project->zpr_uid == $this->User->usr_id)) {
				$this->vxZENProjectForm($Project);
			}
			mysql_free_result($tasks);
		}
		
		mysql_free_result($rs);
		echo('</table>');
		if ($this->User->usr_id == $User->usr_id && $this->User->vxIsLogin()) {
			echo('<form class="zen" action="/recv/zen/project.vx" method="post">');
			echo('创建新项目 <input type="text" class="sll" name="zpr_title" maxlength="80" /> <input type="submit" class="zen_btn" value="创建" />');
			echo('</form>');
		}
		if (!$this->User->vxIsLogin()) {
			echo('<span class="tip">ZEN 是帮助你管理时间的一个小工具，如果你就是 <a href="/u/' . urlencode($User->usr_nick) . '" class="t">' . make_plaintext($User->usr_nick) . '</a>，你可以在 [ <a href="/login.vx" class="t">登录</a> ] 之后管理自己的时间</span>');
		} else {
			if (isset($_SESSION['babel_zen_message'])) {
				if ($_SESSION['babel_zen_message'] != '') {
					echo('<span class="tip_i">' . $_SESSION['babel_zen_message'] . '</span>');
					$_SESSION['babel_zen_message'] = '';
				} else {
				}
			} else {
				$_SESSION['babel_zen_message'] = '';
			}
		}
		echo('</div>');
		/* E: Unfinished Projects */
		
		/* S: Finished Projects */
		if ($this->User->usr_id == $User->usr_id) {
			$sql = "SELECT zpr_id, zpr_uid, zpr_private, zpr_title, zpr_created, zpr_lastupdated, zpr_lasttouched, zpr_completed FROM babel_zen_project WHERE zpr_progress = 1 AND zpr_uid = {$User->usr_id} ORDER BY zpr_completed ASC";
		} else {
			$sql = "SELECT zpr_id, zpr_uid, zpr_private, zpr_title, zpr_created, zpr_lastupdated, zpr_lasttouched, zpr_completed FROM babel_zen_project WHERE zpr_progress = 1 AND zpr_uid = {$User->usr_id} AND zpr_private = 0 ORDER BY zpr_completed ASC";
		}
		$rs = mysql_query($sql, $this->db);
		
		echo('<div class="blank"><span class="text_large"><a style="color: ' . rand_color() . ';" href="/u/' . urlencode($User->usr_nick) . '" class="var">' . $User->usr_nick . '</a> / 完成了的项目</span>');
		
		echo('<table '. $hack_width . 'cellpadding="0" cellspacing="0" border="0" class="zen">');
		
		while ($Project = mysql_fetch_object($rs)) {
			echo('<tr><td class="zen_project">');
			echo('<a name="p' . $Project->zpr_id . '"></a>');
			echo('<span class="zen_project"><img src="' . CDN_IMG . 'gt.gif" align="absmiddle" />&nbsp;&nbsp;' . make_plaintext($Project->zpr_title) . '</span><span class="tip_i"> ... ');
			if ((time() - $Project->zpr_completed) < 100) {
				echo('刚刚完成');
			} else {
				echo('完成于 ' . make_descriptive_time($Project->zpr_completed));
			}
			if ($Project->zpr_uid == $this->User->usr_id) {
				echo(' ... <a href="#;" onclick="if (confirm(' . "'确认删除项目及其下面的所有任务？\\n\\n" . addslashes(make_single_return(make_plaintext($Project->zpr_title))) . "'" . ')) { location.href = ' . "'" . '/erase/zen/project/' . $Project->zpr_id . '.vx' . "'" . ';}" class="zen_rm">X del</a>');
				if ($Project->zpr_private == 1) {
					$permission = '* private';
				} else {
					$permission = '@ public';
				}
				echo(' <a href="/change/zen/project/permission/' . $Project->zpr_id . '.vx" class="zen_pr">' . $permission . '</a>');	
			}
			if ($Project->zpr_uid == $this->User->usr_id) {
				if ($Project->zpr_private == 1) {
					echo (' ... 这个项目只有你自己可以看到');
				} else {
					echo (' ... 这个项目人人可见');
				}
			}
			echo('</span></td></tr>');
			$sql = "SELECT zta_id, zta_uid, zta_title, zta_progress, zta_created, zta_lastupdated, zta_completed FROM babel_zen_task WHERE zta_pid = {$Project->zpr_id} ORDER BY zta_completed ASC";
			$tasks = mysql_query($sql, $this->db);
			$i = 0;
			$j = 0;
			while ($Task = mysql_fetch_object($tasks)) {
				if ($Task->zta_progress == 0) {
					$sql = "UPDATE babel_zen_project SET zpr_progress = 0 WHERE zpr_id = {$Task->zta_pid} LIMIT 1";
					mysql_unbuffered_query($sql, $this->db);
					echo('<script type="text/javascript">location.href="/zen/{$User->usr_nick}";</script>');
				}
				echo('<tr><td class="zen_task_done"><img src="' . CDN_IMG . 'check_green.gif" align="absmiddle" alt="done" />&nbsp;&nbsp;' . make_plaintext($Task->zta_title));
				if ($this->User->usr_id == $Project->zpr_uid) {
					echo('<span class="tip_i"> ... <a href="/undone/zen/task/' . $Task->zta_id . '.vx" class="zen_undone">- undone</a></span>');
				}
				echo('</td></tr>');
			}
			mysql_free_result($tasks);
		}
		
		echo('</table>');
		
		$count_projects_done = mysql_num_rows($rs);
		if ($count_projects_done > 0) {
			echo('<span class="tip_i">恭喜，' . $User->usr_nick . ' 已经完成了 ' . $count_projects_done . ' 个项目！</span>');
		} else {
			echo('<span class="tip_i">还没有任何已经完成了的项目，何不今天就试试用 ZEN 来管理你的时间？</span>');
		}
		
		mysql_free_result($rs);
		
		echo('</div>');
		/* E: Finished Projects */

		echo('<div class="blank"><img src="' . CDN_UI . 'img/icons/silk/clock.png" alt="ZEN" align="absmiddle" /> 关于 ZEN <span class="tip_i"><small>alpha</small></span><br /><br /><span class="tip">ZEN 是一个帮助你管理时间的小工具。我们的愿望是，通过合理地使用 ZEN，你将可以有更多的时间用于一些更有意义的事情。<br /><br />使用 ZEN 非常简单，感觉就像是在一张白纸上写上要做的事情，然后再一项一项地划掉。<br /><br />目前 ZEN 正处于 alpha 测试阶段，并不是十分地稳定，并不是每一个功能都足够完善，不过请放心，我们每天都在改进它！</span></div>');
		echo('</div>');
	}
	
	public function vxUserGraphic($options) {
		_v_m_s();
		if (!$options['mode']) {
			echo('<div class="blank" align="left">');
			_v_ico_map();
			echo(' <a href="/">' . Vocabulary::site_name . '</a> &gt; ' . Vocabulary::term_user_graphic . ' <span class="tip_i"><small>alpha</small></span></div>');
			echo('<div class="blank"><h1 class="ititle">' . Vocabulary::term_user_empty . '</h1></div>');
		} else {
			$_u = $options['target'];
			$_u->usr_nick_plain = make_plaintext($_u->usr_nick);
			echo('<div class="blank" align="left">');
			_v_ico_map();
			echo(' <a href="/">' . Vocabulary::site_name . '</a> &gt; <a href="/u/' . urlencode($_u->usr_nick_plain) . '">' . $_u->usr_nick_plain . '</a> &gt; ' . Vocabulary::term_user_graphic . ' <span class="tip_i"><small>alpha</small></span></div>');
			_v_b_l_s();
			if ($_u->usr_id == $this->User->usr_id) {
				echo(_v_h1_i('我的最新图片'));
			} else {
				echo(_v_h1_i('<a href="/u/' . urlencode($_u->usr_nick) . '">' . $_u->usr_nick_plain . '</a> 的最新图片'));
			}
			_v_hr();
			_v_d_e();
		}
		_v_d_e();
	}
	
	public function vxGeoHome($geo) {
		$Geo = new Geo($geo, $this->Geo->map);
		$geo_real = mysql_real_escape_string($Geo->geo->geo, $this->db);
		$geo_md5 = md5($geo);
		$geos_all_children = $Geo->vxGetRecursiveChildrenArray('', true);
		$geos_all_children_sql = implode(',', $geos_all_children);
		echo('<div id="main">');
		echo('<div class="blank" align="left">');
		_v_ico_map();
		echo(' <a href="/">' . Vocabulary::site_name . '</a> &gt; ' . Vocabulary::term_region . ' &gt; ' . $Geo->geo->name->cn . ' <span class="tip_i"><small>portal</small></span></div>');
		echo('<div class="blank" align="left">');
		if ($this->User->vxIsLogin() && $this->User->usr_geo == $Geo->geo->geo) {
			echo('<span class="text_large">我在' . $Geo->geo->name->cn . '</span><span class="tip_i"> ... <a href="/user/move.vx" class="t">修改我的所在地</a></span>');
			_v_hr();
		}
		
		if ($geo_route = $this->cs->get('babel_geo_route_' . $geo_md5)) {
			$geo_route = unserialize($geo_route);
		} else {
			$geo_route = $Geo->vxGetRoute($geo);
			$this->cs->save(serialize($geo_route), 'babel_geo_route_' . $geo_md5);
		}
		echo('<div class="geo_home_middle" style="margin-bottom: 5px;" ><img src="/img/gt.gif" align="absmiddle" /> ');
		$i = 0;
		foreach ($geo_route as $g => $g_name) {
			$i++;
			if ($i == 1) {
				if ($g == $geo) {
					echo($g_name);
				} else {
					echo('<a href="/geo/' . $g . '" class="o">' . $g_name . '</a>');
				}
			} else {
				if ($g == $geo) {
					echo(' &gt; ' . $g_name);
				} else {
					echo(' &gt; <a href="/geo/' . $g . '" class="o">' . $g_name . '</a>');
				}
			}
		}
		if ($Geo->geo->description->cn != '') {
			echo('<div class="geo_home_desc">');
			echo $Geo->geo->description->cn;
			_v_d_e();
		}
		_v_d_e();
		
		if ($_SESSION['babel_ua']['GECKO_DETECTED'] || $_SESSION['babel_ua']['KHTML_DETECTED'] || $_SESSION['babel_ua']['OPERA_DETECTED']) {
			$hack_width = 'width="100%" ';
		} else {
			$hack_width = 'width="99%" ';
		}
		echo('<table cellpadding="0" cellspacing="0" border="0" ' . $hack_width . '>');
		echo('<tr>');
		echo('<td width="60%" align="left" valign="top" style="border-right: 1px solid #EEE; border-top: 1px solid #EEE; border-bottom: 1px solid #EEE;">');
		echo('<div class="geo_home_bar_top"><img src="/img/icons/silk/award_star_gold_2.png" align="absmiddle" /> 同区域最新活跃主题<span class="tip_i"> ... <img src="/img/icons/silk/feed.png" align="absmiddle" alt="RSS" /> <a href="/feed/geo/' . $Geo->geo->geo . '">RSS 种子输出</a></span></div>');
		echo('<div class="geo_home_content">');
		if ($Topics = $this->cs->get('babel_geo_topics_latest_' . $geo_md5)) {
			$Topics = unserialize($Topics);
		} else {
			$sql = "SELECT usr_id, usr_nick, usr_portrait, usr_gender, tpc_id, tpc_uid, tpc_title, tpc_content, tpc_hits FROM babel_topic, babel_user WHERE tpc_uid = usr_id AND usr_id IN (SELECT usr_id FROM babel_user WHERE usr_geo IN ({$geos_all_children_sql})) AND tpc_flag IN (0, 2) ORDER BY tpc_lasttouched DESC LIMIT 25";
			$rs = mysql_query($sql, $this->db);
			$Topics = array();
			while ($Topic = mysql_fetch_object($rs)) {
				$Topics[$Topic->tpc_id] = $Topic;
			}
			$this->cs->save(serialize($Topics), 'babel_geo_topics_latest_' . $geo_md5);
		}
		$i = 0;
		$authors = array();
		foreach ($Topics as $Topic) {
			$i++;
			if (!array_key_exists($Topic->usr_id, $authors)) {
				$authors[$Topic->usr_id] = rand_color();
			}
			if ($i > 1) {
				if (($i % 2) == 0) {
					$css_class = 'geo_home_entry_even';
				} else {
					$css_class = 'geo_home_entry_odd';
				}
			} else {
				$css_class = 'geo_home_entry_odd';
			}
			$img_p = $Topic->usr_portrait ? CDN_IMG . 'p/' . $Topic->usr_portrait . '_n.jpg' : CDN_IMG . 'p_' . $Topic->usr_gender . '_n.gif';
			echo('<div class="' . $css_class . '"><img src="' . $img_p . '" class="portrait" align="absmiddle" border="0" /> <a href="/topic/view/' . $Topic->tpc_id . '.html">' . make_plaintext($Topic->tpc_title) . '</a> <span class="tip_i">...</span> <a href="/u/' . urlencode($Topic->usr_nick) . '" style="color: ' . $authors[$Topic->usr_id] . '" class="var">' . make_plaintext($Topic->usr_nick) . '</a></div>');
		}
		_v_d_e();
		echo('</td>');
		echo('<td width="40%" align="left" valign="top" style="border-top: 1px solid #EEE; border-bottom: 1px solid #EEE;">');
		echo('<div class="geo_home_bar_top"><img src="/img/icons/silk/award_star_gold_1.png" align="absmiddle" /> 本月同区域最热主题</div>');
		echo('<div class="geo_home_content">');
		if ($Topics = $this->cs->get('babel_geo_topics_hot_' . $geo_md5)) {
			$Topics = unserialize($Topics);
		} else {
			$now = getdate(time());
			$start = mktime(0, 0, 0, $now['mon'], 1, $now['year']);
			$sql = "SELECT tpc_id, tpc_title, tpc_hits, tpc_posts FROM babel_topic WHERE tpc_uid IN (SELECT usr_id FROM babel_user WHERE usr_geo IN ({$geos_all_children_sql})) AND tpc_created > {$start} AND tpc_flag IN (0, 2) ORDER BY tpc_hits DESC, tpc_lasttouched DESC LIMIT 10";
			$rs = mysql_query($sql, $this->db);
			$Topics = array();
			while ($Topic = mysql_fetch_object($rs)) {
				$Topics[$Topic->tpc_id] = $Topic;
			}
			$this->cs->save(serialize($Topics), 'babel_geo_topics_hot_' . $geo_md5);
		}
		$i = 0;
		foreach ($Topics as $Topic) {
			$i++;
			if ($i > 1) {
				if (($i % 2) == 0) {
					$css_class = 'geo_home_entry_even';
				} else {
					$css_class = 'geo_home_entry_odd';
				}
			} else {
				$css_class = 'geo_home_entry_odd';
			}
			echo('<div class="' . $css_class . '"><small class="fade">' . $i . '.</small> ' . '<a href="/topic/view/' . $Topic->tpc_id . '.html">' . make_plaintext($Topic->tpc_title) . '</a> <span class="tip_i"><small> ... ' . $Topic->tpc_hits . '/' . $Topic->tpc_posts . '</small></span>');
			_v_d_e();
		}
		echo('</div>');
		_v_hr();
		if ($geo_count = $this->cs->get('babel_geo_settle_count_' . $geo_md5)) {
			$geo_count = intval($geo_count);
		} else {
			$sql = "SELECT COUNT(*) FROM babel_user WHERE usr_geo IN ({$geos_all_children_sql})";
			$rs = mysql_query($sql, $this->db);
			$geo_count = intval(mysql_result($rs, 0, 0));
			mysql_free_result($rs);
			$this->cs->save(strval($geo_count), 'babel_geo_settle_count_' . $geo_md5);
		}
		echo('<div class="geo_home_bar"><img src="/img/icons/silk/group.png" align="absmiddle" /> 在' . $Geo->geo->name->cn . '的会员<span class="tip_i"> ... 共 ' . $geo_count . ' 人</span></div>');
		$sql = "SELECT usr_id, usr_gender, usr_portrait, usr_nick FROM babel_user WHERE usr_geo IN ({$geos_all_children_sql}) ORDER BY usr_created DESC LIMIT 10";
		$rs = mysql_query($sql, $this->db);
		if (mysql_num_rows($rs) > 0) {
			echo('<div class="geo_home_content"><div class="geo_home_entry_odd" align="left">');
		}
		while ($User = mysql_fetch_object($rs)) {
			$img_p = $User->usr_portrait ? CDN_IMG . 'p/' . $User->usr_portrait . '_n.jpg' : CDN_IMG . 'p_' . $User->usr_gender . '_n.gif';
			echo('<a href="/u/' . urlencode($User->usr_nick) . '"><img src="' . $img_p . '" alt="' . make_single_return($User->usr_nick) . '" class="psmall" align="absmiddle" border="0" /></a> ');
		}
		if ($geo_count > 10) {
			echo('<span class="tip_i"> ... <small><a href="/who/settle/' . $Geo->geo->geo . '" class="o">more</a> &gt;</small></span></div>');
		} else {
			if (mysql_num_rows($rs) > 0) {
				_v_d_e();
			}
		}
		mysql_free_result($rs);
		_v_d_e();
		if ($this->User->vxIsLogin()) {
			if ($friends = $this->cs->get('babel_user_friends_geo_' . $geo_md5 . '_' . $this->User->usr_id)) {
				$friends = unserialize($friends);
			} else {
				$sql = "SELECT usr_id, usr_gender, usr_portrait, usr_nick FROM babel_user WHERE usr_geo IN ({$geos_all_children_sql}) AND usr_id IN (SELECT frd_fid FROM babel_friend WHERE frd_uid = {$this->User->usr_id}) ORDER BY usr_created DESC";
				$rs = mysql_query($sql, $this->db);
				$friends = array();
				while ($Friend = mysql_fetch_object($rs)) {
					$friends[$Friend->usr_id] = $Friend;
				}
				mysql_free_result($rs);
				$this->cs->save(serialize($friends), 'babel_user_friends_geo_' . $geo_md5 . '_' . $this->User->usr_id);
			}
			$friends_count = count($friends);
			_v_hr();
			echo('<div class="geo_home_bar"><img src="/img/icons/silk/heart.png" align="absmiddle" /> 我在' . $Geo->geo->name->cn . '的好友<span class="tip_i"> ... 共 ' . $friends_count . ' 人</span></div>');
			echo('<div class="geo_home_content">');
			$i = 0;
			$gap = 13;
			foreach ($friends as $Friend) {
				$i++;
				if ($i == 1) {
					echo('<div class="geo_home_entry_odd" align="left">');
				}
				if (($i > $gap) && ((($i - 1) % $gap) == 0)) {
					if (((($i - 1) / $gap) % 2) == 0) {
						echo('<div class="geo_home_entry_odd" align="left">');
					} else {
						echo('<div class="geo_home_entry_even" align="left">');
					}
				}
				$img_p = $Friend->usr_portrait ? CDN_IMG . 'p/' . $Friend->usr_portrait . '_n.jpg' : CDN_IMG . 'p_' . $Friend->usr_gender . '_n.gif';
				
				echo('<a href="/u/' . urlencode($Friend->usr_nick) . '"><img src="' . $img_p . '" alt="' . make_single_return($Friend->usr_nick) . '" class="psmall" align="absmiddle" border="0" /></a> ');
				if (($i > ($gap - 1)) && (($i % $gap) == 0)) {
					echo '</div>';
				}
			}
			_v_d_e();
		}
		_v_d_e();
		echo('</td>');
		echo('</tr>');
		echo('</table>');
		
		echo('<div class="geo_home_middle" style="margin-bottom: 5px;" ><img src="/img/gt.gif" align="absmiddle" /> ');
		$i = 0;
		foreach ($geo_route as $g => $g_name) {
			$i++;
			if ($i == 1) {
				if ($g == $geo) {
					echo($g_name);
				} else {
					echo('<a href="/geo/' . $g . '" class="o">' . $g_name . '</a>');
				}
			} else {
				if ($g == $geo) {
					echo(' &gt; ' . $g_name);
				} else {
					echo(' &gt; <a href="/geo/' . $g . '" class="o">' . $g_name . '</a>');
				}
			}
		}
		_v_d_e();
		
		/* To be implemented: Geo related sites
		_v_hr();
		echo('<div class="geo_home_bar"><img src="/img/gt.gif" align="absmiddle" /> ' . $Geo->geo->name->cn . '的相关网站</div>');
		*/
		
		/* Start: geos_children */
		if ($geos_children = $this->cl->load('babel_geo_children_' . $geo)) {
			$geos_children = unserialize($geos_children);
		} else {
			$geos_children = $this->Geo->vxGetChildrenArray($geo);
			$this->cl->save(serialize($geos_children), 'babel_geo_children_' . $geo);
		}
		if (count($geos_children) > 0) {
			$len_total = 0;
			foreach ($geos_children as $elem) {
				$len_total = $len_total + mb_strlen($elem, 'UTF-8');
			}
			$len_avg = floor($len_total / count($geos_children));
			$br = calc_geo_break($len_avg);
			_v_hr();
			if ($o = $this->cl->load('babel_geo_children_' . $geo . '_o')) {
				echo $o;
			} else {
				$o = '';
				$o .= '<div class="geo_home_bar"><img src="/img/icons/silk/world_go.png" align="absmiddle" /> 下属于' . $Geo->geo->name->cn . '的区域</div>';
				$o .= '<div class="geo_home_content">';
				$o .= '<blockquote>';
				$i = 0;
				foreach ($geos_children as $g => $g_name) {
					$i++;
					$css_color = rand_color();
					$o .= '<a href="/geo/' . $g . '" class="var" style="color: ' . $css_color . ';">' . $g_name . '</a>&nbsp; ';
					if ($i % $br == 0) {
						$o .= '<br />';
					}
				}
				$o .= '</blockquote>';
				$o .= '</div>';
				echo $o;
				$this->cl->save($o, 'babel_geo_children_' . $geo . '_o');
			}
		}
		/* End: array geos_children */
		
		/* Start: array geos_parallel */
		if ($geos_parallel = $this->cl->load('babel_geo_parallel_' . $geo)) {
			$geos_parallel = unserialize($geos_parallel);
		} else {
			$geos_parallel = $this->Geo->vxGetParallelArray($geo);
			$this->cl->save(serialize($geos_parallel), 'babel_geo_parallel_' . $geo);
		}
		if (count($geos_parallel) > 0) {
			$len_total = 0;
			foreach ($geos_parallel as $elem) {
				$len_total = $len_total + mb_strlen($elem, 'UTF-8');
			}
			$len_avg = floor($len_total / count($geos_parallel));
			$br = calc_geo_break($len_avg);
			_v_hr();
			if ($o = $this->cl->load('babel_geo_parallel_' . $geo . '_o')) {
				echo $o;
			} else {
				$o = '';
				$o .= '<div class="geo_home_bar"><img src="/img/icons/silk/world_link.png" align="absmiddle" /> 与' . $Geo->geo->name->cn . '平行的区域</div>';
				$o .= '<div class="geo_home_content">';
				$o .= '<blockquote>';
				$i = 0;
				foreach ($geos_parallel as $g => $g_name) {
					$i++;
					$css_color = rand_color();
					$o .= '<a href="/geo/' . $g . '" class="var" style="color: ' . $css_color . ';">' . $g_name . '</a>&nbsp; ';
					if ($i % $br == 0) {
						$o .= '<br />';
					}
				}
				$o .= '</blockquote>';
				$o .= '</div>';
				echo $o;
				$this->cl->save($o, 'babel_geo_parallel_' . $geo . '_o');
			}
		}
		/* End: array geos_parallel */
		_v_hr();
		echo('<div class="geo_home_middle"><small>地名列表是按照字母顺序排列的</small></div>');
		_v_d_e();
		echo('</div>');
	}
	
	public function vxZen2($options) {
		$_u = $options['target'];
		$z2 = new Zen_API($this->db, $_u, $this->Validator, false);
		echo('<script type="text/javascript" src="' . CDN_UI . 'js/jquery.js"> </script>');
		echo('<script type="text/javascript" src="/js/babel_zen2.js"> </script>');
		echo('<div id="main">');
		/* S: You're here */
		echo('<div class="blank">');
		_v_ico_map();
		echo(' <a href="/">' . Vocabulary::site_name . '</a> &gt; <a href="/u/' . urlencode($_u->usr_nick) . '">' . make_plaintext($_u->usr_nick) . '</a> &gt; ZEN <span class="tip_i"><small>2.0 alpha</small></span>');
		echo('</div>');
		/* E: You're here */
		echo('<div class="blank">');
		echo('<div id="zen2_top"><div id="zen2_top_left"><img src="/img/icons/silk/clock.png" align="absmiddle" /> ZEN / ' . $_u->usr_nick . ' &nbsp;<span class="zen2_date">' . date('M j', time()) . '</span></div>');
		echo('<div id="zen2_top_right"><span class="zen2_cur">概览</span>&nbsp;|&nbsp;<a href="">项目管理</a>&nbsp;|&nbsp;<a href="">随机展示</a>&nbsp;|&nbsp;<a href="">帮助与指南</a></div></div>');
		_v_hr();
		echo('<div class="zen2_projects"><div class="zen2_menu">进行中的项目</div>');
		echo('<div class="zen2_blank" id="zen2_projects_active">');
		echo $z2->vxLoadProjectsActive();
		echo('</div>');
		echo('</div>');
		_v_hr();
		echo('<div class="zen2_projects"><div class="zen2_menu">完成了的项目</div>');
		echo('<div class="zen2_blank" id="zen2_projects_done">');
		echo $z2->vxLoadProjectsDone();
		echo('</div>');
		echo('</div>');
		_v_hr();
		echo('<img src="/img/icons/silk/information.png" align="absmiddle" /> ' . Zen::vxTip($this->cl));
		echo('</div>');
		echo('</div>');
	}
	
	public function vxProjectView($Project) {
		$Project = new Project($Project->zpr_id, $this->db);
		echo('<script type="text/javascript" src="/js/babel_zen2.js"> </script>');
		echo('<div id="main">');
		/* S: You're here */
		echo('<div class="blank">');
		_v_ico_map();
		echo(' <a href="/">' . Vocabulary::site_name . '</a> &gt; <a href="/u/' . urlencode($Project->usr_nick) . '">' . $Project->usr_nick_plain . '</a> &gt; <a href="/zen2/' . urlencode($Project->usr_nick) . '">ZEN</a> &gt; ' . $Project->zpr_title_plain . ' <span class="tip_i"><small>2.0 alpha</small></span>');
		echo('</div>');
		/* E: You're here */
		echo('<div class="blank">');
		/* S: Z2 top */
		echo('<div id="zen2_top"><div id="zen2_top_left"><img src="/img/icons/silk/clock.png" align="absmiddle" /> ZEN / ' . $Project->usr_nick_plain . ' &nbsp;<span class="zen2_date">' . date('M j', time()) . '</span></div>');
		echo('<div id="zen2_top_right"><a href="/zen2/' . urlencode($Project->usr_nick) . '">概览</a>&nbsp;|&nbsp;<span class="zen2_cur">项目管理</span>&nbsp;|&nbsp;<a href="">随机展示</a>&nbsp;|&nbsp;<a href="">帮助与指南</a></div></div>');
		_v_hr();
		/* E: Z2 top */
		/* S: Project title */
		echo('<div class="zen2_projects"><div class="zen2_menu"><span class="text_large">' . $Project->zpr_title_plain . '</span></div>');
		/* E: Project title */
		
		echo('<div class="zen2_blank">');
		/* S: tasks */
		echo('<div class="zen2_board">');
		echo('<img src="' . CDN_UI . 'img/icons/silk/flag_red.png" align="absmiddle" /> 任务 <span class="tip_i"><small>tasks</small></span>');
		echo(' <div class="zen2_project_right" id="project_task_toolbar"> &nbsp; <img src="' . CDN_UI . 'img/icons/silk/add.png" align="absmiddle" />&nbsp;<a href="#;" class="t" onclick="z2SwitchProjectTaskToolbar(' . $Project->zpr_id . ');">添加新任务</a></div>');
		$sql = "SELECT zta_id, zta_title FROM babel_zen_task WHERE zta_pid = {$Project->zpr_id} AND zta_progress = 0 ORDER BY zta_created ASC";
		$rs = mysql_query($sql);
		$i = 0;
		while ($_task = mysql_fetch_array($rs)) {
			$i++;
			if ($i == 1) {
				_v_hr();
			}
			echo('<div id="zta_' . $_task['zta_id'] . '" class="zen2_entry_task">');
			if ($Project->zpr_uid == $this->User->usr_id) {
				echo('<input class="cbox" type="checkbox" align="absmiddle" />');
			} else {
				echo('<input class="cbox" type="checkbox" align="absmiddle" disabled="disabled" />');
			}
			echo(' ' . make_plaintext($_task['zta_title']) . '</div>');
		}
		mysql_free_result($rs);
		$sql = "SELECT zta_id, zta_title FROM babel_zen_task WHERE zta_pid = {$Project->zpr_id} AND zta_progress = 1 ORDER BY zta_completed DESC";
		$rs = mysql_query($sql);
		$i = 0;
		while ($_task = mysql_fetch_array($rs)) {
			$i++;
			if ($i == 1) {
				_v_hr();
			}
			echo('<div id="zta_' . $_task['zta_id'] . '" class="zen2_entry_task_done">');
			echo('<img src="/img/check_green.gif" align="absmiddle" />');
			echo(' ' . make_plaintext($_task['zta_title']) . '</div>');
		}
		mysql_free_result($rs);
		echo('</div>');
		/* E: tasks */
		/* S: notes */
		echo('<div class="zen2_board">');
		echo('<img src="' . CDN_UI . 'img/icons/silk/note.png" align="absmiddle" /> 笔记 <span class="tip_i"><small>notes</small></span>');
		echo(' <div class="zen2_project_right" id="project_note_toolbar"> &nbsp; <img src="/img/icons/silk/add.png" align="absmiddle" />&nbsp;<a href="#;" class="t">添加新笔记</a></div>');
		_v_hr();
		echo('<div class="zen2_entry_note"><h1>Hello World</h1>Hello this is a test.</div>');
		echo('<div class="zen2_entry_note"><h1>Hello World</h1>Hello this is a test.</div>');
		echo('</div>');
		/* E: notes */
		/* S: dbs */
		echo('<div class="zen2_board">');
		echo('<img src="/img/icons/silk/database.png" align="absmiddle" /> 数据库 <span class="tip_i"><small>databases</small></span>');
		echo(' <div class="zen2_project_right" id="project_db_toolbar"> &nbsp; <img src="/img/icons/silk/add.png" align="absmiddle" />&nbsp;<a href="#;" class="t">添加新数据库</a></div>');
		_v_hr();
		echo('</div>');
		/* E: dbs */
		/* S: links */
		echo('<div class="zen2_board">');
		echo('<img src="/img/icons/silk/link.png" align="absmiddle" /> 链接 <span class="tip_i"><small>links</small></span>');
		echo(' <div class="zen2_project_right" id="project_link_toolbar"> &nbsp; <img src="/img/icons/silk/add.png" align="absmiddle" />&nbsp;<a href="#;" class="t">添加新链接</a></div>');
		_v_hr();
		echo('</div>');
		/* E: links */
		echo('<div class="conclude">本项目创建于 <small>' . date('Y-n-j G:i:s', $Project->zpr_created) . '</small> | 最后修改于 <small>' . date('Y-n-j G:i:s', $Project->zpr_lastupdated) . '</small></div>');
		echo('</div>');
		_v_d_e();
		echo('</div>');
	}
	
	public function vxMozillaSidebar() {
		echo('<div id="single">');
		echo('<div class="blank">');
		echo('<a href="http://www.flickr.com/"><img src="' . CDN_UI . 'img/favicons/flickr.png" align="absmiddle" border="0" alt="Flickr" /></a> ');
		echo('<a href="http://www.yahoo.com/"><img src="' . CDN_UI . 'img/favicons/yahoo.png" align="absmiddle" border="0" alt="Yahoo!" /></a> ');
		echo('<a href="http://del.icio.us/"><img src="' . CDN_UI . 'img/favicons/delicious.png" align="absmiddle" border="0" alt="del.icio.us" /></a> ');
		echo('<a href="http://www.google.com/"><img src="' . CDN_UI . 'img/favicons/google/google.png" align="absmiddle" border="0" alt="Google" /></a> ');
		echo('<a href="http://www.deviantart.com/"><img src="' . CDN_UI . 'img/favicons/da.png" align="absmiddle" border="0" alt="DeviantART" /></a> ');
		echo('<a href="http://www.interfacelift.com/"><img src="' . CDN_UI . 'img/favicons/ifl.png" align="absmiddle" border="0" alt="InterfaceLIFT" /></a> ');
		echo('<a href="http://www.osnews.com/"><img src="' . CDN_UI . 'img/favicons/osnews.png" align="absmiddle" border="0" alt="OSNews" /></a> ');
		echo('<a href="http://www.slashdot.com/"><img src="' . CDN_UI . 'img/favicons/slashdot.png" align="absmiddle" border="0" alt="Slashdot" /></a> ');
		echo('<a href="http://www.thinkvitamin.com/"><img src="' . CDN_UI . 'img/favicons/vitamin.png" align="absmiddle" border="0" alt="Think Vitamin" /></a> ');
		echo('<a href="http://www.youtube.com/"><img src="' . CDN_UI . 'img/favicons/youtube.png" align="absmiddle" border="0" alt="YouTube" /></a> ');
		echo('<a href="http://www.mac.com/"><img src="' . CDN_UI . 'img/favicons/dotmac.png" align="absmiddle" border="0" alt=".Mac" /></a> ');
		_v_hr();
		echo('<a href="http://www.netvibes.com/"><img src="' . CDN_UI . 'img/favicons/netvibes.png" align="absmiddle" border="0" alt="NetVibes" /></a> ');
		echo('<a href="http://www.pageflakes.com/"><img src="' . CDN_UI . 'img/favicons/pageflakes.png" align="absmiddle" border="0" alt="Pageflakes" /></a> ');
		_v_hr(); // Google Web Services
		echo('<a href="http://www.google.com/"><img src="' . CDN_UI . 'img/favicons/google/google.png" align="absmiddle" border="0" alt="Google" /></a> ');
		echo('<small><strong>Google</strong></small> | <a href="http://reader.google.com/"><img src="' . CDN_UI . 'img/favicons/google/reader.png" align="absmiddle" border="0" alt="Google Reader" /></a> <a href="http://docs.google.com/"><img src="' . CDN_UI . 'img/favicons/google/docs.png" align="absmiddle" border="0" alt="Google Docs & Spreadsheets" /></a> <a href="http://www.blogger.com/"><img src="' . CDN_UI . 'img/favicons/google/blogger.png" align="absmiddle" border="0" alt="Blogger" /></a> <a href="http://groups.google.com/"><img src="' . CDN_UI . 'img/favicons/google/groups.png" align="absmiddle" border="0" alt="Google Groups" /></a> <a href="http://www.gmail.com/"><img src="' . CDN_UI . 'img/favicons/google/gmail.png" align="absmiddle" border="0" alt="Gmail" /></a> <a href="http://calendar.google.com/"><img src="' . CDN_UI . 'img/favicons/google/calendar.png" align="absmiddle" border="0" alt="Google Calendar" /></a>');
		_v_hr();
		echo('<a href="/"><img src="' . CDN_UI . 'img/favicons/v2ex.png" align="absmiddle" border="0" /></a> ');
		echo('<small><strong>' . Vocabulary::site_name . '</strong> | </small><span class="tip_i"><img src="' . CDN_UI . 'img/icons/silk/table_multiple.png" align="absmiddle" /> <small>' . $this->tpc_count . ' &nbsp; <img src="' . CDN_UI . 'img/icons/silk/comments.png" align="absmiddle" /> ' . $this->pst_count . ' &nbsp; <img src="' . CDN_UI . 'img/icons/silk/group.png" align="absmiddle" /> ' . $this->usr_count . '</small></span>');
		_v_hr();
		$sql = "SELECT tpc_id, tpc_title, tpc_posts, usr_id, usr_nick, usr_gender, usr_portrait FROM babel_topic, babel_user WHERE tpc_flag IN (0, 2) AND tpc_uid = usr_id ORDER BY tpc_lasttouched DESC LIMIT 20";
		$rs = mysql_query($sql);
		$i = 0;
		while ($Topic = mysql_fetch_object($rs)) {
			$i++;
			$css_class = ($i % 2 == 0) ? 'entry_even' : 'entry_odd';
			if ($Topic->tpc_posts > 10) {
				$css_color = rand_color();
			} else {
				$css_color = rand_gray();
			}
			$img_p = $Topic->usr_portrait ? CDN_IMG . 'p/' . $Topic->usr_portrait . '_n.jpg' : CDN_IMG . 'p_' . $Topic->usr_gender . '_n.gif';
			echo('<div class="' . $css_class . '"><img src="' . $img_p . '" class="portrait" align="absmiddle" /> <a href="/topic/view/' . $Topic->tpc_id . '.html" class="var" style="color: ' . $css_color . '">' . make_plaintext($Topic->tpc_title) . '</a></div>');
		}
		_v_hr();
		echo('<a href="http://www.kijiji.cn/"><img src="' . CDN_UI . 'img/favicons/kijiji.png" align="absmiddle" border="0" alt="Kijiji" /></a> ');
		echo('<a href="http://www.douban.com/"><img src="' . CDN_UI . 'img/favicons/douban.png" align="absmiddle" border="0" alt="豆瓣" /></a> ');
		echo('<a href="http://www.yupoo.com/"><img src="' . CDN_UI . 'img/favicons/yupoo.png" align="absmiddle" border="0" alt="Yupoo" /></a> ');
		echo('<a href="http://www.zhuaxia.com/"><img src="' . CDN_UI . 'img/favicons/zhuaxia.png" align="absmiddle" border="0" alt="抓虾" /></a> ');
		echo('<a href="http://www.blogbus.com/"><img src="' . CDN_UI . 'img/favicons/blogbus.png" align="absmiddle" border="0" alt="BlogBus" /></a> ');
		echo('<a href="http://www.verycd.com/"><img src="' . CDN_UI . 'img/favicons/verycd.png" align="absmiddle" border="0" alt="VeryCD" /></a> ');
		echo('<a href="http://www.wealink.com/"><img src="' . CDN_UI . 'img/favicons/wealink.png" align="absmiddle" border="0" alt="We@Link" /></a> ');
		echo('<a href="http://www.tudou.com/"><img src="' . CDN_UI . 'img/favicons/tudou.png" align="absmiddle" border="0" alt="土豆" /></a> ');
		echo('<a href="http://www.yodao.com/"><img src="' . CDN_UI . 'img/favicons/yodao.png" align="absmiddle" border="0" alt="有道" /></a> ');
		echo('<a href="http://www.kooxoo.com/"><img src="' . CDN_UI . 'img/favicons/kooxoo.png" align="absmiddle" border="0" alt="酷讯" /></a> ');
		echo('<a href="http://www.feedsky.com/"><img src="' . CDN_UI . 'img/favicons/feedsky.png" align="absmiddle" border="0" alt="FeedSky" /></a> ');
		_v_hr();
		echo('<a href="http://www.getfirefox.com/"><img src="' . CDN_UI . 'img/favicons/firefox.png" align="absmiddle" border="0" alt="Firefox" /></a> ');
		echo('<a href="http://www.skype.com/"><img src="' . CDN_UI . 'img/favicons/skype.png" align="absmiddle" border="0" alt="Skype" /></a> ');
		echo('<a href="http://www.opera.com/"><img src="' . CDN_UI . 'img/favicons/opera.png" align="absmiddle" border="0" alt="Opera" /></a> ');
		echo('<a href="http://www.sourceforge.net/"><img src="' . CDN_UI . 'img/favicons/sf.png" align="absmiddle" border="0" alt="SourceForge" /></a> ');
		echo('<a href="http://dev.mysql.com/"><img src="' . CDN_UI . 'img/favicons/mysql.png" align="absmiddle" border="0" alt="MySQL Developer Zone" /></a> ');
		echo('<a href="http://www.php.net/"><img src="' . CDN_UI . 'img/favicons/php.png" align="absmiddle" border="0" alt="PHP" /></a> ');
		echo('</div>');
		echo('</div>');
	}

	public function vxTopWealth() {
		echo('<div id="single">');
		echo('<div class="blank">');
		echo('<img src="' . CDN_UI . 'img/icons/silk/coins_add.png" align="absmiddle" /> 社区财富排行');
		_v_hr();
		if ($o = $this->cs->get('babel_top_wealth')) {
			echo $o;
		} else {
			ob_start();
			$sql = "SELECT usr_id, usr_nick, usr_gender, usr_portrait, usr_money FROM babel_user WHERE usr_sw_top_wealth = 1 ORDER BY usr_money DESC LIMIT 10";
			$rs = mysql_query($sql);
			$i = 0;
			$full = 0;
			while ($Richer = mysql_fetch_object($rs)) {
				$i++;
				if ($i == 1) { $full = $Richer->usr_money; $percentage = 1; } else { $percentage = $Richer->usr_money / $full; }
				$css_class = ($i % 2 == 0) ? 'entry_even' : 'entry_odd';
				$img_p = $Richer->usr_portrait ? CDN_IMG . 'p/' . $Richer->usr_portrait . '_n.jpg' : CDN_IMG . 'p_' . $Richer->usr_gender . '_n.gif';
				
				echo('<div class="' . $css_class . '">');
				echo('<table width="100%" cellpadding="0" cellspacing="0" border="0">');
				echo('<tr>');
				echo('<td width="100" align="left">');
				echo('<a href="/u/' . urlencode($Richer->usr_nick) . '" target="_parent"><img src="' . $img_p . '" align="absmiddle" class="portrait" border="0" alt="' . make_single_return(make_plaintext($Richer->usr_nick)) . '" /></a> ');
				echo('<a href="/u/' . urlencode($Richer->usr_nick) . '" target="_parent" class="var" style="color: ' . rand_color() . '">' . make_plaintext($Richer->usr_nick) . '</a>');
				echo('</td>');
				echo('<td width="auto">');
				$width = strval(intval($percentage * 300)) . 'px';
				echo('<div style="float: left; padding: 1px; width: ' . $width . '; border: 1px solid #9C3; -moz-border-radius: 2px;">');
				echo('<div style="height: 15px; background-image: url(' . "'/img/progress.png'" . ');"> </div></div>');
				$_MONEY = $this->User->vxParseMoney($Richer->usr_money);
				echo('<div style="">&nbsp;&nbsp;<small>');
				if ($_MONEY['g'] > 0) {
					echo(vsprintf('%d', $_MONEY['g']) . '<img src="/img/coin_g.png" align="absmiddle" /> ');
				}
				if ($_MONEY['s'] > 0) {
					echo(vsprintf('%d', $_MONEY['s']) . '<img src="/img/coin_s.png" align="absmiddle" /> ');
				}
				if ($_MONEY['c'] > 1) {
					echo(vsprintf('%d', $_MONEY['c']) . '<img src="/img/coin_c.png" align="absmiddle" />');
				}
				echo('</small></div>');
				echo('</td>');
				echo('</tr>');
				echo('</table>');
				echo('</div>');
			}
			$o = ob_get_contents();
			ob_end_clean();
			$this->cs->save($o, 'babel_top_wealth');
			echo $o;
		}
		_v_hr();
		echo('</div>');
		echo('</div>');
	}
	
	/* E public modules */
	
	/* S private modules */
	
	/* S module: Home Section block */
	
	private function vxZENProjectForm($Project) {
		echo('<tr><td class="zen_task_new"><div id="pf_' . $Project->zpr_id . '"><img src="' . CDN_IMG . 'plus_green.gif" align="absmiddle" alt="+" /> <a href="#;" class="t" onclick="ZENSwitchProjectForm(' . $Project->zpr_id . ');">添加新任务</a></div></td></tr>');
	}
	
	private function vxHomeSection($section_id, $items = 18) {
		$sql = "SELECT nod_id FROM babel_node WHERE nod_sid = {$section_id}";
		$rs = mysql_query($sql, $this->db);
		$board_count = mysql_num_rows($rs);
		$board_ids = '';
		$i = 0;
		while ($Board = mysql_fetch_object($rs)) {
			$i++;
			if ($i == $board_count) {
				$board_ids = $board_ids . $Board->nod_id;
			} else {
				$board_ids = $board_ids . $Board->nod_id . ', ';
			}
		}
		mysql_free_result($rs);
		$which = rand(1, 2);
		if ($which == 1) {
			$action = '/topic/view/';
			$suffix = '.html';
			$sql = "SELECT tpc_id AS itm_id, tpc_title AS itm_title, tpc_created AS itm_time, tpc_posts AS itm_items FROM babel_topic WHERE tpc_pid IN ({$board_ids}) AND tpc_flag IN (0, 2) ORDER BY rand() LIMIT {$items}";
		} else {
			$action = '/go/';
			$suffix = '';
			$sql = "SELECT nod_name AS itm_id, nod_title AS itm_title, nod_lastupdated AS itm_time, nod_topics AS itm_items FROM babel_node WHERE nod_sid = {$section_id} ORDER BY rand()";
		}
		$rs = mysql_query($sql, $this->db);
		$i = 0;
		$o = '';
		while ($Item = mysql_fetch_object($rs)) {
			if ((time() - $Item->itm_time) < 86400) {
				$img_star = '<img src="' . CDN_UI . 'img/bunny.gif" align="absmiddle" />&nbsp;';
			} else {
				$img_star = '';
			}
			$i++;
			if ($Item->itm_items > 3) {
				$css_color = ' color: ' . rand_color();
			} else {
				$css_color = ' color: ' . rand_gray(2, 4);
			}
			$css_font_size = $this->vxGetItemSize($Item->itm_items);
			$o .= '<span class="tip_i">';
			if ($i != 1) {
				$o .= '&nbsp; &nbsp;';
			}
			$o .= $img_star . '<a href="' . $action . $Item->itm_id . $suffix . '" class="var" style="font-size: ' . $css_font_size . 'px; ' . $css_color . ';">' . make_plaintext($Item->itm_title);
			$o .= '</a></span>';
		}
		mysql_free_result($rs);
		
		return $o;
	}
	
	/* E module: Home Section block */
	
	/* S module: Home Section block Remix */
	
	private function vxHomeSectionRemix($node_id, $node_level = 1, $items = 3) {
		if ($node_level < 2) {
			$sql = "SELECT nod_id FROM babel_node WHERE nod_sid = {$node_id}";
			$rs = mysql_query($sql, $this->db);
			$board_count = mysql_num_rows($rs);
			$board_ids = '';
			$i = 0;
			while ($Board = mysql_fetch_object($rs)) {
				$i++;
				if ($i == $board_count) {
					$board_ids = $board_ids . $Board->nod_id;
				} else {
					$board_ids = $board_ids . $Board->nod_id . ', ';
				}
			}
			mysql_free_result($rs);
			$items = rand($items - 1, $items * 2);
			$sql = "SELECT usr_id, usr_nick, usr_gender, usr_portrait, nod_id, nod_name, nod_title, tpc_id, tpc_title, tpc_description, tpc_content, tpc_hits, tpc_posts, tpc_created, tpc_lasttouched FROM babel_topic, babel_user, babel_node WHERE nod_id = tpc_pid AND usr_id = tpc_uid AND tpc_posts > 1 AND tpc_hits > 10 AND tpc_pid IN ({$board_ids}) AND tpc_flag IN (0) ORDER BY tpc_lasttouched DESC LIMIT {$items}";
		} else {
			$board_ids = $node_id;
			$items = 15;
			$sql = "SELECT usr_id, usr_nick, usr_gender, usr_portrait, nod_id, nod_name, nod_title, tpc_id, tpc_title, tpc_description, tpc_content, tpc_hits, tpc_posts, tpc_created, tpc_lasttouched FROM babel_topic, babel_user, babel_node WHERE nod_id = tpc_pid AND usr_id = tpc_uid AND tpc_pid IN ({$board_ids}) AND tpc_flag IN (0) ORDER BY tpc_lasttouched DESC LIMIT {$items}";
		}
		
		
		$rs = mysql_query($sql, $this->db);
		$i = 0;
		$o = '';
		while ($Topic = mysql_fetch_object($rs)) {
			$i++;
			$css_color = rand_color();
			$o = $o . '<dl class="home_topic">';
			$img_p = $Topic->usr_portrait ? CDN_IMG . 'p/' . $Topic->usr_portrait . '_s.jpg' : CDN_IMG . 'p_' . $Topic->usr_gender . '_s.gif';
			$o .= '<dt style="margin-bottom: 2px;">&nbsp;';
			$o .= '<a href="/u/' . urlencode($Topic->usr_nick) . '" class="var"><img src="' . $img_p . '" align="absmiddle" class="portrait" border="0" /></a>&nbsp;&nbsp;';
			$o .= '<a href="/topic/view/' . $Topic->tpc_id . '.html" class="var" style="color: ' . $css_color . '; font-size: 18px;">';
			$o .= make_plaintext($Topic->tpc_title);
			$url = 'http://' . BABEL_DNS_NAME . '/topic/view/' . $Topic->tpc_id . '.html';
			$o .= '</a><span class="tip_h"> ... ' . make_descriptive_time($Topic->tpc_lasttouched) . '，' . $Topic->tpc_posts . ' 篇回复，' . $Topic->tpc_hits . ' 次点击</span></dt><dd>';
			if (preg_match('/\[media/i', $Topic->tpc_content)) {
				$o .= '本主题含有多媒体影音内容，请 <a href="/topic/view/' . $Topic->tpc_id . '.html" class="t">点击这里阅读全文</a> ...';
			} else {
				$o .= make_excerpt_home($Topic);
			}
			if ($node_level < 2) {
				$o .= '<span class="tip_i" style="display: block; clear: left; margin-top: 10px; padding-top: 5px; padding-bottom: 5px; border-top: 1px solid #E0E0E0; font-size: 12px; font-size: 12px;">... <a href="/topic/view/' . $Topic->tpc_id . '.html#reply" class="t">' . $Topic->tpc_posts . ' 篇回复</a> | <a href="/topic/view/' . $Topic->tpc_id . '.html#replyForm" class="t">添加回复</a> | 阅读讨论区 <a href="/remix/' . $Topic->nod_name . '" class="t">' . $Topic->nod_title . '</a> | <a href="/u/' . urlencode($Topic->usr_nick) . '" class="t">' . $Topic->usr_nick . '</a> 的个人空间';
			} else {
				$o .= '<span class="tip_i" style="display: block; clear: left; margin-top: 10px; padding-top: 5px; padding-bottom: 5px; border-top: 1px solid #E0E0E0; font-size: 12px; font-size: 12px;">... <a href="/topic/view/' . $Topic->tpc_id . '.html#reply" class="t">' . $Topic->tpc_posts . ' 篇回复</a> | <a href="/topic/view/' . $Topic->tpc_id . '.html#replyForm" class="t">添加回复</a> | <a href="/u/' . urlencode($Topic->usr_nick) . '" class="t">' . $Topic->usr_nick . '</a> 的个人空间';
			}
			$o .= ' | ';
			
			$title = urlencode($Topic->tpc_title);
			$o .= '<a href="http://del.icio.us/post?url=' . $url . '&title=' . $title . '" class="var" target="_blank"><img src="/img/prom/delicious.png" border="0" align="absmiddle" alt="收藏到 del.icio.us" /></a> | ';
			$o .= '<a href="http://reddit.com/submit?url=' . $url . '&title=' . $title . '" class="var" target="_blank"><img src="/img/prom/reddit.png" border="0" align="absmiddle" alt="收藏到 reddit" /></a> | ';
			$o .= '<a href="http://technorati.com/cosmos/search.html?url=' . $url . '" class="var" target="_blank"><img src="/img/prom/technorati.png" border="0" align="absmiddle" alt="在 Technorati 中搜索本主题" /></a> | ';
			$o .= '<a href="http://ma.gnolia.com/bookmarklet/add?url=' . $url . '&title=' . $title . '" class="var" target="_blank"><img src="/img/prom/magnoliacom.png" border="0" align="absmiddle" alt="收藏到 Ma.gonolia" /></a> | ';
			$o .= '<a href="http://blogmarks.net/my/new.php?mini=1&truc=3&title=' . $title . '&url=' . $url . '" class="var" target="_blank"><img src="/img/prom/blogmarks.png" border="0" align="absmiddle" alt="收藏到 BlogMarks" /></a> | ';
			$o .= '<a href="http://www.furl.net/storeIt.jsp?t=' . $title . '&u=' . $url . '" class="var" target="_blank"><img src="/img/prom/furl.png" border="0" align="absmiddle" alt="收藏到 LookSmart FURL" /></a> | ';
			$o .= '<a href="http://www.spurl.net/spurl.php?v=3&title=' . $title . '&url=' . $url . '&blocked=" class="var" target="_blank"><img src="/img/prom/spurl.png" border="0" align="absmiddle" alt="收藏到 Spurl" /></a> | ';
			$o .= '<a href="http://simpy.com/simpy/LinkAdd.do?title=' . $title . '&href=' . $url . '&note=&_doneURI=http%3A%2F%2Fwww.simpy.com%2F&v=6&src=bookmarklet" class="var" target="_blank"><img src="/img/prom/simpy.png" border="0" align="absmiddle" alt="收藏到 simpy" /></a> | ';
			$o .= '<a href="http://tailrank.com/share/?title=' . $title . '&link_href=' . $url . '&text=" class="var" target="_blank"><img src="/img/prom/tailrank.png" border="0" align="absmiddle" alt="收藏到 Tailrank" /></a>';
			$o .= '</span></dd></dl>';
		}
		mysql_free_result($rs);
		
		return $o;
	}
	
	/* E module: Home Section block Remix */
	
	/* S module: Get Item Size logic */
	
	private function vxGetItemSize($posts) {
		if ($posts > 400) {
			return 19;
		} else {
			if ($posts > 200) {
				return 18;
			} else {
				if ($posts > 100) {
					return 17;
				} else {
					if ($posts >= 50) {
						return 16;
					} else {
						if ($posts >= 26) {
							return 15;
						} else {
							if ($posts >= 10) {
								return 14;
							} else {
								if ($posts >= 4) {
									return 13;
								} else {
									return 12;
								}
							}
						}
					}
				}
			}
		}
	}
	
	/* E module: Get Item Size logic */
	
	/* S module: Get Menu Size logic */
	
	private function vxGetMenuSize($posts) {
		if ($posts > 200) {
			return 17;
		} else {
			if ($posts >= 100) {
				return 16;
			} else {
				if ($posts >= 50) {
					return 15;
				} else {
					if ($posts >= 20) {
						return 14;
					} else {
						if ($posts >= 4) {
							return 13;
						} else {
							return 12;
						}
					}
				}
			}
		}
	}
	
	/* E module: Get Menu Size logic */
	
	/* S module: Draw Pages logic */
	
	private function vxDrawPages($p) {
		if ($p['start'] != 1) {
			echo('<a href="' . $p['base'] . '1' . $p['ext'] . '" class="p_edge">1</a>');
		}
		for ($i = $p['start']; $i <= $p['end']; $i++) {
			if ($p['cur'] == $i) {
				echo('<strong class="p_cur">' . $i . '</strong>');
			} else {
				echo('<a href="' . $p['base'] . $i . $p['ext'] . '" class="p">' . $i . '</a>');
			}
		}
		if ($p['end'] != $p['total']) {
			echo('<a href="' . $p['base'] . $p['total'] . $p['ext'] . '" class="p_edge">' . $p['total'] . '</a>');
		}
		echo('<strong class="p_info">' . $p['items'] . ' ITEMS / ' . $p['size'] . ' PER PAGE</strong>');
	}
	
	/* E module: Draw Pages logic */
	
	/* E private modules */
}

/* E Page class */
?>
