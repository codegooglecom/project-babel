<?php
/* Project Moscow
 *
 * Author: Livid Torvalds
 * File: /htdocs/core/MobileCore.php
 * Usage: V2EX Mobile Core Class
 * Format: 1 tab indent(4 spaces), LF, UTF-8, no-BOM
 *
 * Subversion Keywords:
 *
 * $Id$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
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
	require_once('core/Settings.php');
	
	/* 3rdparty PEAR cores */
	ini_set('include_path', BABEL_PREFIX . '/libs/pear' . PATH_SEPARATOR . ini_get('include_path'));
	require_once('Cache/Lite.php');
	require_once('Crypt/Blowfish.php');
	require_once('HTTP/Request.php');

	/* 3rdparty Zend Framework cores */
	ini_set('include_path', BABEL_PREFIX . '/libs/zf/' . ZEND_FRAMEWORK_VERSION . PATH_SEPARATOR . ini_get('include_path'));
	
	/* 3rdparty cores */
	require_once(BABEL_PREFIX . '/libs/magpierss/rss_fetch.inc');
	require_once(BABEL_PREFIX . '/libs/smarty/libs/Smarty.class.php');
	
	/* built-in cores */
	require_once('core/Vocabularies.php');
	require_once('core/Utilities.php');
	require_once('core/UserCore.php');
	require_once('core/NodeCore.php');
	require_once('core/TopicCore.php');
	require_once('core/ChannelCore.php');
	require_once('core/URLCore.php');
	require_once('core/ValidatorCore.php');
} else {
	die('<strong>Project Babel</strong><br /><br />Made by V2EX | software for internet');
}

/* S Mobile class */

class Mobile {
	public function __construct($sess_start = true) {
		check_env();
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
		if ($sess_start) {
			session_start();
		}
		$this->URL = new URL();
		$this->User = new User('', '', $this->db);
		$this->Validator = new Validator($this->db, $this->User);
		
		if (!isset($_SESSION['babel_ua'])) {
			$_SESSION['babel_ua'] = $this->Validator->vxGetUserAgent();
		}
		
		global $CACHE_LITE_OPTIONS_SHORT;
		$this->cs = new Cache_Lite($CACHE_LITE_OPTIONS_SHORT);
		global $CACHE_LITE_OPTIONS_LONG;
		$this->cl = new Cache_Lite($CACHE_LITE_OPTIONS_LONG);
	}

	public function __destruct() {
		if (@$this->db) {
			mysql_close($this->db);
		}
	}
	
	public function vxHome() {
		$this->vxHeader(Vocabulary::site_title_mobile);
		$this->vxBodyStart();
		$this->vxH1(false);
		echo('<div class="content"><small>');
		if ($this->User->vxIsLogin()) {
			echo('<a href="/u/' . urlencode($this->User->usr_nick) . '">' . $this->User->usr_nick . '</a> - ');
			echo('<a href="/logout.vx">登出</a>');
		} else {
			echo('<a href="/login.vx">登录</a>');
		}
		echo('</small></div>');
		echo('<div class="content">');
		echo('<ul>');
		$sql = 'SELECT COUNT(*) FROM babel_topic WHERE tpc_flag IN (0, 2)';
		$rs = mysql_query($sql);
		$_total = mysql_result($rs, 0, 0);
		mysql_free_result($rs);
		$_per = 10;
		if (($_total % $_per) == 0) {
			$_pages = $_total / $_per;
		} else {
			$_pages = floor($_total / $_per) + 1;
		}
		if (isset($_GET['p'])) {
			$_p = intval($_GET['p']);
			if ($_p < 1) {
				$_p = 1;
			}
			if ($_p > $_pages) {
				$_p = $_pages;
			}
		} else {
			$_p = 1;
		}
		if ($_p == 1) {
			$_p_first = true;
		} else {
			$_p_first = false;
		}
		if ($_p == $_pages) {
			$_p_last = true;
		} else {
			$_p_last = false;
		}
		$_SESSION['babel_page_home_mobile'] = $_p;
		$_p_start = ($_p - 1) * $_per; 
		$sql = "SELECT tpc_id, tpc_title, tpc_posts, tpc_hits FROM babel_topic WHERE tpc_flag IN (0, 2) ORDER BY tpc_lasttouched DESC LIMIT {$_p_start},{$_per}";
		$rs = mysql_query($sql);
		while ($Topic = mysql_fetch_object($rs)) {
			echo('<li><a href="/t/' . $Topic->tpc_id . '">' . make_plaintext($Topic->tpc_title) . '</a> <small>' . $Topic->tpc_hits . '/' . $Topic->tpc_posts . '</small></li>');
		}
		mysql_free_result($rs);
		echo('</ul>');
		echo('<small>');
		if ($_pages > 1) {
			if (!$_p_last) {
				echo('&nbsp;&nbsp;<a href="/' . ($_p + 1) . '">下一页</a>');
			}
			if (!$_p_first) {
				echo('&nbsp;&nbsp;<a href="/' . ($_p - 1) . '">上一页</a>');
			}
			echo('&nbsp;&nbsp;' . $_p . '/' . $_pages);
		}
		echo('</small>');
		echo('</div>');
		$this->vxBottom();
		$this->vxBodyEnd();
		$this->vxHTMLEnd();
	}
	
	public function vxLogin() {
		$rt = $this->Validator->vxLoginCheck();
		if (strtoupper($_SERVER['REQUEST_METHOD']) == 'GET') {
			$this->vxHeader(Vocabulary::action_login);
			$this->vxBodyStart();
			$this->vxH1();
			echo('<div class="content"><small><a href="/' . $_SESSION['babel_page_home_mobile'] . '">' . Vocabulary::site_name . '</a> &gt; ' . Vocabulary::action_login . '</small></div>');
			echo('<div class="form">');
			echo('<form action="/login.vx" method="post">');
			echo('用户: <input type="text" name="usr" class="textbox" /><br />');
			echo('密码: <input type="password" name="usr_password" class="textbox" /><br />');
			echo('<input type="submit" value="登 录" class="go" />');
			if (strlen($rt['return']) > 0) {
				echo('<input type="hidden" name="return" value="' . make_single_return($rt['return']) . '" />');
			}
			echo('</form>');
			echo('</div>');
			$this->vxBottom();
			$this->vxBodyEnd();
			$this->vxHTMLEnd();
		} else {
			if ($rt['target'] == 'ok') {
				$this->User = new User($rt['usr_email_value'], sha1($rt['usr_password_value']), $this->db);
				$this->User->vxUpdateLogin();
				/* start the session now */
				$this->User->vxSessionStart();
				if ($this->User->vxIsLogin()) {
					if (isset($rt['return'])) {
						if (mb_strlen($rt['return'], 'UTF-8') > 0) {
							$this->URL->vxToRedirect($rt['return']);
						}
					}
				}
			}
			$this->vxHeader(Vocabulary::action_login);
			$this->vxBodyStart();
			$this->vxH1();
			switch ($rt['target']) {
				default:
				case 'welcome':
					if (isset($_GET['r'])) {
						if (get_magic_quotes_gpc()) {
							$rt['return'] = make_single_safe(stripslashes($_GET['r']));
						} else {
							$rt['return'] = make_single_safe($_GET['r']);
						}
					} else {
						$rt['return'] = '';
					}
					echo('<div class="content"><small><a href="/' . $_SESSION['babel_page_home_mobile'] . '">' . Vocabulary::site_name . '</a> &gt; ' . Vocabulary::action_login . '</small></div>');
					echo('<div class="form">');
					echo('<form action="/login.vx" method="post">');
					echo('用户: <input type="text" name="usr" class="textbox" /><br />');
					echo('密码: <input type="password" name="usr_password" class="textbox" /><br />');
					echo('<input type="submit" value="登 录" class="go" />');
					if (strlen($rt['return']) > 0) {
						echo('<input type="hidden" name="return" value="' . make_single_return($rt['return']) . '" />');
					}
					echo('</form>');
					echo('</div>');
					break;
				case 'ok':
					echo('<div class="content"><small>');
					echo('<a href="/u/' . urlencode($this->User->usr_nick) . '">' . make_plaintext($this->User->usr_nick) . '</a> - ');
					echo('<a href="/logout.vx">登出</a>');
					echo('</small></div>');
					echo('<div class="content">欢迎回到 <a href="/' . $_SESSION['babel_page_home_mobile'] . '">' . Vocabulary::site_name . '</a>，你上次登录时间是在 ' . make_descriptive_time($this->User->usr_lastlogin) . '。</div>');
					break;
				case 'error':
					echo('<div class="content"><small><a href="/' . $_SESSION['babel_page_home_mobile'] . '">' . Vocabulary::site_name . '</a> &gt; ' . Vocabulary::action_login . '</small></div>');
					echo('<div class="form">');
					echo('<form action="/login.vx" method="post"><small class="error">密码或者用户名错误</small><br />');
					echo('用户: <input type="text" name="usr" class="textbox" /><br />');
					echo('密码: <input type="password" name="usr_password" class="textbox" /><br />');
					echo('<input type="submit" value="登 录" class="go" />');
					if (strlen($rt['return']) > 0) {
						echo('<input type="hidden" name="return" value="' . make_single_return($rt['return']) . '" />');
					}
					echo('</form>');
					echo('</div>');
					break;
			}
			$this->vxBottom();
			$this->vxBodyEnd();
			$this->vxHTMLEnd();
		}
	}
	
	public function vxLogout() {
		$this->User->vxLogout();
		$this->vxHeader(Vocabulary::action_login);
		$this->vxBodyStart();
		$this->vxH1();
		echo('<div class="content"><small><a href="/' . $_SESSION['babel_page_home_mobile'] . '">' . Vocabulary::site_name . '</a> &gt; ' . Vocabulary::action_logout . '</small></div>');
		echo('<div class="content">你已经从 ' . Vocabulary::site_name . ' 完全登出。<br /><br />感谢你访问 ' . Vocabulary::site_name . '，没有任何的个人信息留在你当前使用的设备上。<br /><br /><a href="/login.vx">重新登录</a></div>');
		$this->vxBottom();
		$this->vxBodyEnd();
		$this->vxHTMLEnd();
	}
	
	public function vxTopic() {
		if (isset($_GET['topic_id'])) {
			$topic_id = intval($_GET['topic_id']);
			$sql = "SELECT tpc_id, tpc_uid, tpc_pid, tpc_title, tpc_content, tpc_posts, tpc_created, usr_id, usr_nick FROM babel_topic, babel_user WHERE tpc_uid = usr_id AND tpc_id = {$topic_id} AND tpc_flag IN (0, 2)";
			$rs = mysql_query($sql);
			if ($Topic = mysql_fetch_object($rs)) {
				mysql_free_result($rs);
				mysql_unbuffered_query("UPDATE babel_topic SET tpc_hits = tpc_hits + 1 WHERE tpc_id = {$topic_id}");
				$Board = new Node($Topic->tpc_pid, $this->db);
				$Section = $Board->vxGetNodeInfo($Board->nod_pid);
				$this->vxHeader(make_plaintext($Topic->tpc_title));
				$this->vxBodyStart();
				$this->vxH1();
				echo('<div class="content"><small>');
				if ($this->User->vxIsLogin()) {
					echo('<a href="/u/' . urlencode($this->User->usr_nick) . '">' . $this->User->usr_nick . '</a> - ');
					echo('<a href="/logout.vx">登出</a>');
				} else {
					echo('<a href="/login.vx">登录</a>');
				}
				echo('</small></div>');
				echo('<div class="content"><small><a href="/' . $_SESSION['babel_page_home_mobile'] . '">' . Vocabulary::site_name . '</a> &gt; ' . make_plaintext($Section->nod_title) . ' &gt; ' . make_plaintext($Board->nod_title) . '</small></div>');
				echo('<h2>' . make_plaintext($Topic->tpc_title) . '</h2>');
				echo('<div class="author"><small class="author">by <a href="/u/' . urlencode(make_plaintext($Topic->usr_nick)) . '">' . make_plaintext($Topic->usr_nick) . '</a> at ' . date('Y-n-j H:i:s', $Topic->tpc_created) . '</small></div>');
				echo('<span class="text">' . format_ubb($Topic->tpc_content) . '</span>');
				$sql = "SELECT COUNT(pst_id) FROM babel_post WHERE pst_tid = {$topic_id}";
				$rs = mysql_query($sql);
				$_total = mysql_result($rs, 0, 0);
				mysql_free_result($rs);
				$_per = 10;
				if (($_total % $_per) == 0) {
					$_pages = $_total / $_per;
				} else {
					$_pages = floor($_total / $_per) + 1;
				}
				if (isset($_GET['p'])) {
					$_p = intval($_GET['p']);
					if ($_p < 1) {
						$_p = 1;
					}
					if ($_p > $_pages) {
						$_p = $_pages;
					}
				} else {
					$_p = 1;
				}
				if ($_p == 1) {
					$_p_first = true;
				} else {
					$_p_first = false;
				}
				if ($_p == $_pages) {
					$_p_last = true;
				} else {
					$_p_last = false;
				}
				$_SESSION['babel_page_topic_mobile'] = $_p;
				$_SESSION['babel_page_topic_last_mobile'] = $_pages;
				$_p_start = ($_p - 1) * $_per;
				$sql = "SELECT pst_id, pst_title, pst_content, pst_created, usr_id, usr_nick FROM babel_post, babel_user WHERE pst_tid = {$topic_id} AND pst_uid = usr_id ORDER BY pst_created ASC LIMIT {$_p_start}, {$_per}";
				$rs = mysql_query($sql);
				$i = 0;
				while ($Post = mysql_fetch_object($rs)) {
					$i++;
					$j = $_p_start + $i;
					echo('<div class="author"><small><strong>#' . $j . '</strong> - <a href="/u/' . urlencode(make_plaintext($Post->usr_nick)) . '">' . make_plaintext($Post->usr_nick) . '</a> at ' . date('Y-n-j H:i:s', $Post->pst_created) . ':</small></div><div class="content">' . format_ubb($Post->pst_content) . '</div>');
				}
				echo('<div class="content"><small>');
				if ($_pages > 1) {
					if (!$_p_last) {
						echo('&nbsp;&nbsp;<a href="/t/' . $topic_id . '/' . ($_p + 1) . '">下一页</a>');
					}
					if (!$_p_first) {
						echo('&nbsp;&nbsp;<a href="/t/' . $topic_id . '/' . ($_p - 1) . '">上一页</a>');
					}
					echo('&nbsp;&nbsp;' . $_p . '/' . $_pages . '&nbsp;&nbsp;共 ' . $_total . ' 篇回复');
				}
				echo('&nbsp;&nbsp;<a href="/' . $_SESSION['babel_page_home_mobile'] . '">返回</a>');
				echo('</small></div>');
				if ($this->User->vxIsLogin()) {
					if ($this->Validator->vxIsAutisticNode($Topic->tpc_pid, $this->cs)) {
						if ($this->User->usr_id == $Topic->tpc_uid) {
							echo('<div class="content"><form action="/post/create/mobile/' . $Topic->tpc_id . '.vx" method="post"><textarea name="reply" class="textbox" cols="30" rows="4" maxlength="1000"></textarea><br /><input type="submit" value="回 复" class="go" /><small> &nbsp; 回复不能超过 1000 字</small></form></div>');
						} else {
							echo('<div class="content"><small>你不能回复自闭模式讨论区中他人创建的主题</small></div>');
						}
					} else {
						echo('<div class="content"><form action="/post/create/mobile/' . $Topic->tpc_id . '.vx" method="post"><textarea name="reply" class="textbox" cols="30" rows="4" maxlength="1000"></textarea><br /><input type="submit" value="回 复" class="go" /><small> &nbsp; 回复不能超过 1000 字</small>');
						echo('</form></div>');
					}
				}
				$this->vxBottom();
				$this->vxBodyEnd();
				$this->vxHTMLEnd();
			} else {
				mysql_free_result($rs);
				$this->vxHome();
			}
		} else {
			$this->vxHome();
		}
	}
	
	public function vxPostCreate() {
		if ($this->User->vxIsLogin()) {
			if (isset($_GET['topic_id'])) {
				$topic_id = intval($_GET['topic_id']);
				if ($this->Validator->vxExistTopic($topic_id)) {
					$rt = $this->Validator->vxPostCreateMobileCheck($topic_id, $this->User);
					if ($rt['errors'] == 0) {
						$this->Validator->vxPostCreateInsert($rt['topic_id'], $this->User->usr_id, '', $rt['reply_value'], $rt['exp_amount']);
						$Topic = new Topic($rt['topic_id'], $this->db);
						$Topic->vxTouch();
						$Topic->vxUpdatePosts();
						$this->URL->vxToRedirect($this->URL->vxGetTopicViewMobile($topic_id, $_SESSION['babel_page_topic_last_mobile']));
					} else {
						$Topic = new Topic($rt['topic_id'], $this->db);
						$Node = new Node($Topic->tpc_pid, $this->db);
						$Section = $Node->vxGetNodeInfo($Node->nod_pid);
						$this->vxHeader(make_plaintext($Topic->tpc_title) . ' - 回复出错');
						$this->vxBodyStart();
						$this->vxH1();
						echo('<div class="content"><small><a href="/' . $_SESSION['babel_page_home_mobile'] . '">V2EX</a> &gt; ' . make_plaintext($Section->nod_title) . ' &gt; ' . make_plaintext($Node->nod_title) . ' &gt; <a href="/t/' . $Topic->tpc_id . '/' . $_SESSION['babel_page_topic_mobile'] . '">' . make_plaintext($Topic->tpc_title) . '</a> &gt; 回复出错</small></div>');
						echo('<div class="content"><form action="/post/create/mobile/' . $Topic->tpc_id . '.vx" method="post"><small class="error">' . $rt['reply_error_msg'][$rt['reply_error']] . '</small><br /><textarea name="reply" class="textbox" cols="30" rows="4" maxlength="1000">' . make_multi_return($rt['reply_value']) . '</textarea><br /><input type="submit" value="回 复" class="go" />&nbsp;&nbsp;<input type="button" value="取 消" class="go" onclick="location.href=' . "'/t/{$Topic->tpc_id}/{$_SESSION['babel_page_topic_mobile']}';" . '" /></form></div>');
						$this->vxBottom();
						$this->vxBodyEnd();
						$this->vxHTMLEnd();
					}
				} else {
					$this->URL->vxToRedirect($this->vxURL->vxGetHome());
				}
			} else {
				$this->URL->vxToRedirect($this->vxURL->vxGetHome());
			}
		} else {
			if (isset($_GET['topic_id'])) {
				$topic_id = intval($_GET['topic_id']);
				$this->URL->vxToRedirect($this->URL->vxGetLogin($this->URL->vxGetTopicViewMoblie($topic_id)));
			} else {
				$this->URL->vxToRedirect($this->URL->vxGetLogin($this->URL->vxGetHome()));
			}
		}
	}
	
	public function vxUser() {
		if (isset($_GET['user_nick'])) {
			$user_nick = make_single_safe($_GET['user_nick']);
			if (get_magic_quotes_gpc()) {
				$user_nick = stripslashes($user_nick);
			}
			$user_id = $this->Validator->vxExistUserNick($user_nick);
			if ($user_id) {
				$_u = $this->User->vxGetUserInfo($user_id);
			} else {
				$_u = false;
			}
		} else {
			$_u = false;
		}
		if ($_u) {
			$this->vxHeader(make_plaintext($_u->usr_nick));
		} else {
			$this->vxHeader('用户不存在');
		}
		$this->vxBodyStart();
		$this->vxH1();
		if ($_u) {
			echo('<div class="content"><small>');
			if ($this->User->vxIsLogin()) {
				echo('<a href="/u/' . urlencode($this->User->usr_nick) . '">' . $this->User->usr_nick . '</a> - ');
				echo('<a href="/logout.vx">登出</a>');
			} else {
				echo('<a href="/login.vx">登录</a>');
			}
			echo('</small></div>');
			echo('<div class="content"><small><a href="/">V2EX</a> &gt; ' . $_u->usr_nick . '</small></div>');
			$_u->usr_nick_plain = make_plaintext($_u->usr_nick);
			if ($_u->usr_portrait == '') {
				$_u->usr_portrait_img = '/img/p_' . $_u->usr_gender . '_s.gif';
			} else {
				$_u->usr_portrait_img = '/img/p/' . $_u->usr_portrait . '_s.' . BABEL_PORTRAIT_EXT;
			}
			echo('<div class="content">');
			echo('<img src="' . $_u->usr_portrait_img . '" class="p" align="left" style="margin-right: 10px;" />');
			echo($_u->usr_nick_plain);
			echo('，' . $this->User->usr_gender_a_fun[$_u->usr_gender]);
			echo('，' . date('Y 年 n 月 j 日', $_u->usr_created) . '来到 ' . Vocabulary::site_name);
			$_o = $this->Validator->vxExistOnline($_u->usr_nick);
			if ($_o) {
				echo('，当前在线，正在查看 <a href="' . $_o->onl_uri . '">' . $_o->onl_uri . '</a> ');
			} else {
				echo('，当前不在线');
			}
			echo('，上次登录时间 ' . date('Y 年 n 月 j 日 G:i:s', $_u->usr_lastlogin));
			echo('，总共登录 ' . $_u->usr_logins . ' 次');
			echo('。');
			echo('<div class="c"></div>');
			echo('</div>');
			if (trim($_u->usr_brief) != '') {
				echo('<div class="author" align="center">' . make_plaintext($_u->usr_brief) . '</div>');
			}
			$sql = "SELECT COUNT(tpc_id) FROM babel_topic WHERE tpc_uid = {$_u->usr_id} AND tpc_flag IN (0, 2)";
			$rs = mysql_query($sql);
			$_total = mysql_result($rs, 0, 0);
			mysql_free_result($rs);
			$_per = 10;
			if (($_total % $_per) == 0) {
				$_pages = $_total / $_per;
			} else {
				$_pages = floor($_total / $_per) + 1;
			}
			if (isset($_GET['p'])) {
				$_p = intval($_GET['p']);
				if ($_p < 1) {
					$_p = 1;
				}
				if ($_p > $_pages) {
					$_p = $_pages;
				}
			} else {
				$_p = 1;
			}
			if ($_p == 1) {
				$_p_first = true;
			} else {
				$_p_first = false;
			}
			if ($_p == $_pages) {
				$_p_last = true;
			} else {
				$_p_last = false;
			}
			$_SESSION['babel_page_user_topic_mobile'] = $_p;
			$_p_start = ($_p - 1) * $_per;
			$sql = "SELECT tpc_id, tpc_title, tpc_hits, tpc_posts FROM babel_topic WHERE tpc_uid = {$_u->usr_id} AND tpc_flag IN (0, 2) ORDER BY tpc_created DESC LIMIT {$_p_start}, {$_per}";
			$rs = mysql_query($sql, $this->db);
			if (mysql_num_rows($rs) > 0) {
				echo('<div class="content">');
				echo('<small>' . $_u->usr_nick . ' 共创建了 ' . $_total . ' 个主题 - ' . $_u->usr_nick . ' 的 <a href="/f/' . urlencode($_u->usr_nick) . '">朋友</a></small>');
				echo('<ul>');
				while ($Topic = mysql_fetch_object($rs)) {
					echo('<li><a href="/t/' . $Topic->tpc_id . '">' . make_plaintext($Topic->tpc_title) . '</a> <small>' . $Topic->tpc_hits . '/' . $Topic->tpc_posts . '</small></li>');
				}
				echo('</ul>');
				mysql_free_result($rs);
				echo('<small>');
				if ($_pages > 1) {
					if (!$_p_last) {
						echo('&nbsp;&nbsp;<a href="/u/' . urlencode($_u->usr_nick) . '/' . ($_p + 1) . '">下一页</a>');
					}
					if (!$_p_first) {
						echo('&nbsp;&nbsp;<a href="/u/' . urlencode($_u->usr_nick) . '/' . ($_p - 1) . '">上一页</a>');
					}
					echo('&nbsp;-&nbsp;' . $_p . '/' . $_pages);
				}
				echo('</small>');
				echo('</div>');
			} else {
				mysql_free_result($rs);
			}
		} else {
			echo('<div class="content"><small>');
			if ($this->User->vxIsLogin()) {
				echo('<a href="/u/' . urlencode($this->User->usr_nick) . '">' . $this->User->usr_nick . '</a> - ');
				echo('<a href="/logout.vx">登出</a>');
			} else {
				echo('<a href="/login.vx">登录</a>');
			}
			echo('</small></div>');
			echo('<div class="content">用户不存在</div>');
		}
		$this->vxBottom();
		$this->vxBodyEnd();
		$this->vxHTMLEnd();
	}
	
	public function vxFriend() {
		if (isset($_GET['user_nick'])) {
			$user_nick = make_single_safe($_GET['user_nick']);
			if (get_magic_quotes_gpc()) {
				$user_nick = stripslashes($user_nick);
			}
			$user_id = $this->Validator->vxExistUserNick($user_nick);
			if ($user_id) {
				$_u = $this->User->vxGetUserInfo($user_id);
			} else {
				$_u = false;
			}
		} else {
			$_u = false;
		}
		if ($_u) {
			$this->vxHeader(make_plaintext($_u->usr_nick) . ' 的朋友');
		} else {
			$this->vxHeader('用户不存在');
		}
		$this->vxBodyStart();
		$this->vxH1();
		echo('<div class="content"><small>');
		if ($this->User->vxIsLogin()) {
			echo('<a href="/u/' . urlencode($this->User->usr_nick) . '">' . $this->User->usr_nick . '</a> - ');
			echo('<a href="/logout.vx">登出</a>');
		} else {
			echo('<a href="/login.vx">登录</a>');
		}
		echo('</small></div>');
		if ($_u) {
			$sql = "SELECT COUNT(frd_id) FROM babel_friend WHERE frd_uid = {$_u->usr_id}";
			$rs = mysql_query($sql);
			$_total = mysql_result($rs, 0, 0);
			mysql_free_result($rs);
			$_per = 10;
			if (($_total % $_per) == 0) {
				$_pages = $_total / $_per;
			} else {
				$_pages = floor($_total / $_per) + 1;
			}
			if (isset($_GET['p'])) {
				$_p = intval($_GET['p']);
				if ($_p < 1) {
					$_p = 1;
				}
				if ($_p > $_pages) {
					$_p = $_pages;
				}
			} else {
				$_p = 1;
			}
			if ($_p == 1) {
				$_p_first = true;
			} else {
				$_p_first = false;
			}
			if ($_p == $_pages) {
				$_p_last = true;
			} else {
				$_p_last = false;
			}
			$_SESSION['babel_page_user_friend_mobile'] = $_p;
			$_p_start = ($_p - 1) * $_per;
			echo('<div class="content"><small><a href="/">V2EX</a> &gt; <a href="/u/' . urlencode($_u->usr_nick) . '">' . $_u->usr_nick . '</a> &gt; ' . $_total . ' 个朋友</small></div>');
			echo('<div class="content"><small><a href="/u/' . $_u->usr_nick . '" target="_blank">' . $_u->usr_nick . '</a> 共有 ' . $_total . ' 个朋友</small></div>');
			if ($o = $this->cl->get('babel_user_friend_mobile_' . $_u->usr_id . '_' . $_p)) {
				echo $o;
			} else {
				$_o = '';
				$sql = "SELECT usr_id, usr_gender, usr_nick, usr_portrait, usr_hits, frd_created FROM babel_user, babel_friend WHERE usr_id = frd_fid AND frd_uid = {$_u->usr_id} ORDER BY frd_created ASC LIMIT {$_p_start}, {$_per}";
				$rs = mysql_query($sql, $this->db);
				while ($Friend = mysql_fetch_array($rs)) {
					if ($Friend['usr_portrait'] == '') {
						$Friend['usr_portrait_img'] = '/img/p_' . $Friend['usr_gender'] . '_n.gif';
					} else {
						$Friend['usr_portrait_img'] = '/img/p/' . $Friend['usr_portrait'] . '_n.' . BABEL_PORTRAIT_EXT;
					}
					$_o .= '<div class="content"><small>';
					$sql = "SELECT tpc_id, tpc_title, tpc_lasttouched FROM babel_topic WHERE tpc_uid = {$Friend['usr_id']} ORDER BY tpc_created DESC LIMIT 1";
					$rs_topic = mysql_query($sql, $this->db);
					if ($Topic = mysql_fetch_object($rs_topic)) {
						$_o .= '<img src="' . $Friend['usr_portrait_img'] . '" align="absmiddle" class="p" /> <a href="/u/' . $Friend['usr_nick'] . '">' . $Friend['usr_nick'] . '</a> - <a href="/t/' . $Topic->tpc_id . '">' . make_plaintext($Topic->tpc_title) . '</a> - ' . make_desc_time($Topic->tpc_lasttouched) . ' ago</small>';
					} else {
						$_o .= '<img src="' . $Friend['usr_portrait_img'] . '" align="absmiddle" class="p" /> <a href="/u/' . $Friend['usr_nick'] . '">' . $Friend['usr_nick'] . '</a></small>';
					}
					unset($Topic);
					mysql_free_result($rs_topic);
					$_o .= '</div>';
				}
				mysql_free_result($rs);
				echo $_o;
				$this->cl->save($_o, 'babel_user_friend_mobile_' . $_u->usr_id . '_' . $_p);
			}
			if ($_pages > 1) {
				echo('<div class="content"><small>');
				if (!$_p_last) {
					echo('&nbsp;&nbsp;<a href="/f/' . urlencode($_u->usr_nick) . '/' . ($_p + 1) . '">下一页</a>');
				}
				if (!$_p_first) {
					echo('&nbsp;&nbsp;<a href="/f/' . urlencode($_u->usr_nick) . '/' . ($_p - 1) . '">上一页</a>');
				}
				echo('&nbsp;-&nbsp;' . $_p . '/' . $_pages);
				echo('</small></div>');
			}
		} else {
			echo('<div class="content">用户不存在</div>');
		}
		$this->vxBottom();
		$this->vxBodyEnd();
		$this->vxHTMLEnd();
	}
	
	public function vxIngPublic() {
		$this->vxHeader(Vocabulary::site_title_mobile);
		$this->vxBodyStart();
		$this->vxH1(true);
		echo('<div class="content"><small>');
		if ($this->User->vxIsLogin()) {
			echo('<a href="/u/' . urlencode($this->User->usr_nick) . '">' . $this->User->usr_nick . '</a> - ');
			echo('<a href="/logout.vx">登出</a>');
		} else {
			echo('<a href="/login.vx">登录</a>');
		}
		echo('</small></div>');
		echo('<div class="content"><small><a href="/">' . Vocabulary::site_name . '</a> &gt; ING</small></div>');
		echo('<div class="content">');
		echo('<ul>');
		echo('</ul>');
		echo('</div>');
		$this->vxBottom();
		$this->vxBodyEnd();
		$this->vxHTMLEnd();
		
	}
	
	public function vxHeader($title) {
		echo('<?xml version="1.0" encoding="utf-8"?>' . "\n");
		echo('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">' . "\n");
		echo('<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="zh-CN" lang="zh-CN">' . "\n");
		echo('<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" />' . "\n");
		echo('<meta name="generator" content="Bluefish 1.0.6"/>' . "\n");
		$this->vxTitle($title);
		$this->vxLink();
		echo('</head>');
	}
	
	public function vxTitle($title) {
		echo("<title>{$title}</title>\n");
	}
	
	public function vxLink() {
		echo('<link rel="stylesheet" type="text/css" href="/css/mobile/style.css" />');
	}
	
	public function vxBodyStart() {
		echo('<body>');
	}
	
	public function vxH1($link = true) {
		if (!isset($_SESSION['babel_page_home_mobile'])) {
			$_SESSION['babel_page_home_mobile'] = 1;
		}
		if ($link) {
			echo('<h1><a href="/' . $_SESSION['babel_page_home_mobile'] . '">' . Vocabulary::site_title_mobile . '</a></h1>');
		} else {
			echo('<h1>' . Vocabulary::site_title_mobile . '</h1>');
		}
	}
	
	public function vxBottom() {
		echo('<div><small>&copy; 2007 <a href="http://labs.v2ex.com/" target="_blank">V2EX Labs</a> | software for internet</small></div>');
	}
	
	public function vxBodyEnd() {
		echo('</body>');
	}
	
	public function vxHTMLEnd() {
		echo('</html>');
	}
}

/* E Mobile class */
?>
