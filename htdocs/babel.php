<?php
/* Project Babel
 *
 * Author: Livid Liu <v2ex.livid@mac.com>
 * File: /htdocs/babel.php
 * Usage: Loader for Web
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

DEFINE('V2EX_BABEL', 1);

$GOOGLE_AD_LEGAL = false;

require('core/V2EXCore.php');

if (isset($_GET['m'])) {
	$m = strtolower(trim($_GET['m']));
} else {
	$m = 'home';
}

define('__PAGE__', $m);

$p = &new Page();

$global_has_bottom = true;

switch ($m) {
	default:
	case 'home':
		if (strtolower($_SERVER['SERVER_NAME']) != BABEL_DNS_NAME && !BABEL_DEBUG) {
			header('Location: http://' . BABEL_DNS_NAME . '/');
			die('REDIRECTING ...');
		} else {
			if ($_SESSION['babel_ua']['DEVICE_LEVEL'] < 3 && $_SESSION['babel_ua']['DEVICE_LEVEL'] > 0) {
				$global_has_bottom = false;
				require_once('core/MobileCore.php');
				$p_m = &new Mobile(false);
				$p_m->vxHome();
				break;
			} else {
				if (isset($_GET['style'])) {
					switch ($_GET['style']) {
						case 'shuffle':
							$p->vxHomeBundle('shuffle');
							break;
						case 'remix':
							$p->vxHomeBundle('remix');
							break;
						default:
							$p->vxHomeBundle(BABEL_HOME_STYLE_DEFAULT);
							break;
					}
				} else {
					if (isset($_SESSION['babel_home_style'])) {
						if ($_SESSION['babel_home_style'] != '') {
							$p->vxHomeBundle($_SESSION['babel_home_style']);
						} else {
							$p->vxHomeBundle(BABEL_HOME_STYLE_DEFAULT);
						}
					} else {
						$p->vxHomeBundle(BABEL_HOME_STYLE_DEFAULT);
					}
				}
				break;
			}
		}
		
	case 'hot':
		$GOOGLE_AD_LEGAL = true;
		$p->vxHead($msgSiteTitle = Vocabulary::term_hottopic);
		$p->vxBodyStart();
		$p->vxTop();
		$p->vxContainer('hot');
		break;

	case 'topic_latest':
		$GOOGLE_AD_LEGAL = true;
		$p->vxHead($msgSiteTitle = Vocabulary::term_latesttopic);
		$p->vxBodyStart();
		$p->vxTop();
		$p->vxContainer('topic_latest');
		break;
		
	case 'topic_answered_latest':
		$GOOGLE_AD_LEGAL = true;
		$p->vxHead($msgSiteTitle = Vocabulary::term_latest_answered_topic);
		$p->vxBodyStart();
		$p->vxTop();
		$p->vxContainer('topic_answered_latest');
		break;
		
	case 'fav_latest':
		$GOOGLE_AD_LEGAL = true;
		$p->vxHead($msgSiteTitle = Vocabulary::term_latestfav);
		$p->vxBodyStart();
		$p->vxTop();
		$p->vxContainer('fav_latest');
		break;
		
	case 'search':
		$GOOGLE_AD_LEGAL = true;
		$p->vxSearchBundle();
		break;

	case 'user_logins':
		$GOOGLE_AD_LEGAL = true;
		$p->vxHead($msgSiteTitle = Vocabulary::term_userlogins);
		$p->vxBodyStart();
		$p->vxTop();
		$p->vxContainer('user_logins');
		break;
	
	case 'session_stats':
		$GOOGLE_AD_LEGAL = true;
		$p->vxHead($msgSiteTitle = Vocabulary::term_sessionstats);
		$p->vxBodyStart();
		$p->vxTop();
		$p->vxContainer('session_stats');
		break;

	case 'login':
		if (strtoupper($_SERVER['REQUEST_METHOD']) == 'POST') {
			$rt = $p->Validator->vxLoginCheck();
			$p->User = new User($rt['usr_email_value'], sha1($rt['usr_password_value']), $p->db);
			/* start the session now */
			$p->User->vxSessionStart();
			if ($p->User->vxIsLogin()) {
				if (isset($rt['return'])) {
					if (strlen($rt['return']) > 0) {
						$p->URL->vxToRedirect($rt['return']);
					}
				}
			}
		} else {
			if ($p->User->vxIsLogin()) {
				$rt = array('target' => 'me');
			} else {
				$rt = array('target' => 'welcome');
				if (isset($_GET['r'])) {
					$rt['return'] = $_GET['r'];
				} else {
					$rt['return'] = '';
				}
			}
		}
		if ($rt['target'] == 'me') {
			$p->URL->vxToRedirect($p->URL->vxGetUserOwnHome());
		} else {
			$p->vxHead($msgSiteTitle = Vocabulary::action_login);
			$p->vxBodyStart();
			$p->vxTop();
			$p->vxContainer('login', $options = $rt);
		}
		break;
		
	case 'passwd':
		$p->vxHead($msgSiteTitle = Vocabulary::action_passwd);
		$p->vxBodyStart();
		$p->vxTop();
		$options = array();
		if (isset($_GET['k'])) {
			$k = mysql_real_escape_string(trim($_GET['k']), $p->db);
			if (strlen($k) > 0) {
				$_oneday = time() - 86400;
				$sql = "SELECT pwd_id, pwd_uid, usr_id, usr_email, usr_password FROM babel_passwd, babel_user WHERE pwd_uid = usr_id AND pwd_hash = '{$k}' AND pwd_created > {$_oneday} ORDER BY pwd_created DESC LIMIT 1";
				$rs = mysql_query($sql);
				
				if ($O = mysql_fetch_object($rs)) {
					mysql_free_result($rs);
					$options['mode'] = 'key';
					$options['key'] = $k;
					$options['target'] = new User($O->usr_email, $O->usr_password, $p->db, false);
					$O = null;
				} else {
					mysql_free_result($rs);
					$options['mode'] = 'get';
				}
			} else {
				$options['mode'] = 'get';
			}
		} else {
			if (strtoupper($_SERVER['REQUEST_METHOD']) == 'GET') {
				$options['mode'] = 'get';
			} else {
				if (isset($_POST['key'])) {
					$k = mysql_real_escape_string(trim($_POST['key']), $p->db);
					if (strlen($k) > 0) {
						$_oneday = time() - 86400;
						$sql = "SELECT pwd_id, pwd_uid, usr_id, usr_email, usr_password FROM babel_passwd, babel_user WHERE pwd_hash = '{$k}' AND pwd_created > {$_oneday} AND pwd_uid = usr_id ORDER BY pwd_created DESC LIMIT 1";
						$rs = mysql_query($sql);
						
						if ($O = mysql_fetch_object($rs)) {
							mysql_free_result($rs);
							$options['mode'] = 'reset';
							$options['key'] = $k;
							$options['target'] = new User($O->usr_email, $O->usr_password, $p->db, false);
							$O = null;
							
							$options['rt'] = $p->Validator->vxUserPasswordUpdateCheck();
							
							if ($options['rt']['errors'] == 0) {
								$p->Validator->vxUserPasswordUpdateUpdate($options['target']->usr_id, sha1($options['rt']['usr_password_value']));
							}
						} else {
							mysql_free_result($rs);
							$options['mode'] = 'post';
						}
					} else {
						$options['mode'] = 'post';
					}
				} else {
					$options['mode'] = 'post';
				}
			}
		}
		$p->vxContainer('passwd', $options);
		break;

	case 'logout':
		$p->User->vxLogout();
		$p->vxHead($msgSiteTitle = Vocabulary::action_logout);
		$p->vxBodyStart();
		$p->vxTop();
		$p->vxContainer('logout');
		break;
	
	case 'signup':
		if ($p->User->vxIsLogin()) {
			$p->URL->vxToRedirect($p->URL->vxGetUserOwnHome());
		} else {
			$p->vxHead($msgSiteTitle = Vocabulary::action_signup);
			$p->vxBodyStart();
			$p->vxTop();
			$p->vxContainer('signup');
		}
		break;

	case 'status':
		$p->vxHead($msgSiteTitle = Vocabulary::term_status);
		$p->vxBodyStart();
		$p->vxTop();
		$p->vxContainer('status');
		break;
	
	case 'jobs_kijiji':
		$GOOGLE_AD_LEGAL = true;
		$p->vxHead($msgSiteTitle = Vocabulary::term_jobs_kijiji);
		$p->vxBodyStart();
		$p->vxTop();
		$p->vxContainer('jobs_kijiji');
		break;

	case 'community_guidelines':
		$GOOGLE_AD_LEGAL = true;
		$p->vxHead($msgSiteTitle = Vocabulary::term_community_guidelines);
		$p->vxBodyStart();
		$p->vxTop();
		$p->vxContainer('community_guidelines');
		break;
		
	case 'partners':
		$GOOGLE_AD_LEGAL = false;
		$p->vxHead($msgSiteTitle = Vocabulary::term_partners);
		$p->vxBodyStart();
		$p->vxTop();
		$p->vxContainer('partners');
		break;
	
	case 'new_features':
		$GOOGLE_AD_LEGAL = true;
		$p->vxHead($msgSiteTitle = Vocabulary::term_newfeatures);
		$p->vxBodyStart();
		$p->vxTop();
		$p->vxContainer('new_features');
		break;
		
	case 'timtowtdi':
		$GOOGLE_AD_LEGAL = true;
		$p->vxHead($msgSiteTitle = $p->lang->timtowtdi());
		$p->vxBodyStart();
		$p->vxTop();
		$p->vxContainer('timtowtdi');
		break;
	
	case 'rules':
		$GOOGLE_AD_LEGAL = true;
		$p->vxHead($msgSiteTitle = Vocabulary::term_rules);
		$p->vxBodyStart();
		$p->vxTop();
		$p->vxContainer('rules');
		break;

	case 'terms':
		$GOOGLE_AD_LEGAL = true;
		$p->vxHead($msgSiteTitle = Vocabulary::term_terms);
		$p->vxBodyStart();
		$p->vxTop();
		$p->vxContainer('terms');
		break;

	case 'privacy':
		$GOOGLE_AD_LEGAL = true;
		$p->vxHead($msgSiteTitle = Vocabulary::term_privacy);
		$p->vxBodyStart();
		$p->vxTop();
		$p->vxContainer('privacy');
		break;
		
	case 'policies':
		$GOOGLE_AD_LEGAL = true;
		$p->vxHead($msgSiteTitle = Vocabulary::term_policies);
		$p->vxBodyStart();
		$p->vxTop();
		$p->vxContainer('policies');
		break;
		
	case 'out_of_money':
		$GOOGLE_AD_LEGAL = true;
		$p->vxHead($msgSiteTitle = Vocabulary::term_out_of_money);
		$p->vxBodyStart();
		$p->vxTop();
		$p->vxContainer('out_of_money');
		break;
		
	case 'geo_home':
		$GOOGLE_AD_LEGAL = true;
		if (isset($_GET['geo'])) {
			$geo = strtolower(make_single_safe($_GET['geo']));
			if (function_exists('get_magic_quotes_gpc')) {
				if (get_magic_quotes_gpc()) {
					$geo = stripslashes($geo);
				}
			}
			if (!$p->Geo->vxIsExist($geo)) {
				$geo = 'earth';
			}
		} else {
			$geo = 'earth';
		}
		$p->vxHead($msgSiteTitle = $p->Geo->map['name'][$geo]);
		$p->vxBodyStart();
		$p->vxTop();
		$p->vxContainer('geo_home', $geo);
		break;

	case 'user_home':
		if ($_SESSION['babel_ua']['DEVICE_LEVEL'] < 3 && $_SESSION['babel_ua']['DEVICE_LEVEL'] > 0) {
			$global_has_bottom = false;
			require_once('core/MobileCore.php');
			$p_m = &new Mobile(false);
			$p_m->vxUser();
			break;
		} else {
			$options = array();
			if (isset($_GET['do'])) {
				$do = strtolower(trim($_GET['do']));
				if ($do == 'me') {
					if ($p->User->vxIsLogin()) {
						$options['mode'] = 'fixed';
						$options['target'] = $p->User;
					} else {
						$options['mode'] = 'random';
					}
				} else {
					if (isset($_GET['user_nick'])) {
						$user_nick = make_single_safe($_GET['user_nick']);
						if (get_magic_quotes_gpc()) {
							$user_nick_real = mysql_real_escape_string(stripslashes($user_nick), $p->db);
						} else {
							$user_nick_real = mysql_real_escape_string($user_nick, $p->db);
						}
						if (mb_strlen($user_nick_real, 'UTF-8') > 0) {
							$sql = "SELECT usr_id, usr_geo, usr_nick, usr_skype, usr_lastfm, usr_brief, usr_religion, usr_religion_permission, usr_gender, usr_portrait, usr_hits, usr_logins, usr_created, usr_lastlogin, usr_lastlogin_ua FROM babel_user WHERE usr_nick = '{$user_nick_real}'";
							$rs = mysql_query($sql, $p->db);
							if ($O = mysql_fetch_object($rs)) {
								$options['mode'] = 'fixed';
								$options['target'] = $O;
								$O = null;
							} else {
								$options['mode'] = 'random';
							}
							mysql_free_result($rs);
						} else {
							$options['mode'] = 'random';
						}
					} else {
						$options['mode'] = 'random';
					}
				}
			} else {
				if (isset($_GET['user_nick'])) {
					$user_nick = make_single_safe($_GET['user_nick']);
					if (function_exists('get_magic_quotes_gpc')) {
						if (get_magic_quotes_gpc()) {
							$user_nick_real = mysql_real_escape_string(stripslashes($user_nick), $p->db);
						} else {
							$user_nick_real = mysql_real_escape_string($user_nick, $p->db);
						}
					} else {
						$user_nick_real = mysql_real_escape_string($user_nick, $p->db);
					}
					if (mb_strlen($user_nick_real, 'UTF-8') > 0) {
						$sql = "SELECT usr_id, usr_geo, usr_nick, usr_skype, usr_lastfm, usr_brief, usr_religion, usr_religion_permission, usr_gender, usr_portrait, usr_hits, usr_logins, usr_created, usr_lastlogin, usr_lastlogin_ua FROM babel_user WHERE usr_nick = '{$user_nick_real}'";
						$rs = mysql_query($sql, $p->db);
						if ($O = mysql_fetch_object($rs)) {
							$options['mode'] = 'fixed';
							$options['target'] = $O;
							$O = null;
						} else {
							$options['mode'] = 'random';
						}
						mysql_free_result($rs);
					} else {
						$options['mode'] = 'random';
					}
				} else {
					$options['mode'] = 'random';
				}
			}
				
			if ($options['mode'] == 'random') {
				$sql = "SELECT usr_id, usr_geo, usr_nick, usr_skype, usr_lastfm, usr_brief, usr_religion, usr_religion_permission, usr_gender, usr_portrait, usr_hits, usr_logins, usr_created, usr_lastlogin, usr_lastlogin_ua FROM babel_user ORDER BY rand() LIMIT 1";
				$rs = mysql_query($sql, $p->db);
				$options['target'] = mysql_fetch_object($rs);
				mysql_free_result($rs);
				$p->vxHead($msgSiteTitle = Vocabulary::term_user_random, '', 'http://' . BABEL_DNS_NAME . '/feed/user/' . urlencode($options['target']->usr_nick));
			} else {
				$p->vxHead($msgSiteTitle = make_plaintext($options['target']->usr_nick), '', 'http://' . BABEL_DNS_NAME . '/feed/user/' . urlencode($options['target']->usr_nick));
			}
	
			$p->vxBodyStart();
			$p->vxTop($msgBanner = Vocabulary::site_banner, $keyword = $options['target']->usr_nick);
			$p->vxContainer('user_home', $options);
			break;
		}

	case 'user_create':
		if (strtoupper($_SERVER['REQUEST_METHOD']) != 'POST') {
			$p->vxHead($msgSiteTitle = Vocabulary::action_signup);
			$p->vxBodyStart();
			$p->vxTop();
			$p->vxContainer('signup');
			break;
		} else {
			$rt = $p->Validator->vxUserCreateCheck();
			if ($rt['errors'] == 0) {
				$O = $p->Validator->vxUserCreateInsert($rt['usr_nick_value'], $rt['usr_password_value'], $rt['usr_email_value'], $rt['usr_gender_value']);
				$p->User = new User($O->usr_email, $O->usr_password, $p->db);
				$p->User->vxPay($p->User->usr_id, BABEL_USR_INITIAL_MONEY, 1);
				$p->User->vxSessionStart();
			}
			$p->vxHead($msgSiteTitle = Vocabulary::action_signup);
			$p->vxBodyStart();
			$p->vxTop();
			$p->vxContainer('user_create', $options = $rt);
			break;
		}
	
	case 'user_modify':
		if ($p->User->vxIsLogin()) {
			$p->vxHead($msgSiteTitle = Vocabulary::action_modifyprofile);
			$p->vxBodyStart();
			$p->vxTop();
			$p->vxContainer('user_modify');
			break;
		} else {
			$p->URL->vxToRedirect($p->URL->vxGetLogin($p->URL->vxGetUserModify()));
			break;
		}
		
	case 'user_update':
		if ($p->User->vxIsLogin()) {
			if (strtoupper($_SERVER['REQUEST_METHOD']) != 'POST') {
				$p->URL->vxToRedirect($p->URL->vxGetLogin($p->URL->vxGetUserModify()));
			} else {
				$rt = $p->Validator->vxUserUpdateCheck();
				if ($rt['errors'] == 0) {
					$p->Validator->vxUserUpdateUpdate($rt['usr_full_value'], $rt['usr_nick_value'], $rt['usr_email_notify_value'], $rt['usr_brief_value'], $rt['usr_gender_value'], $rt['usr_religion_value'], $rt['usr_religion_permission_value'], $rt['usr_addr_value'], $rt['usr_telephone_value'], $rt['usr_skype_value'], $rt['usr_lastfm_value'], $rt['usr_identity_value'], $rt['usr_width_value'], $rt['usr_sw_shuffle_cloud_value'], $rt['usr_sw_right_friends_value'], $rt['usr_sw_top_wealth_value'], $rt['usr_sw_shell_value'], $rt['usr_sw_notify_reply_value'], $rt['usr_sw_notify_reply_all_value'], $rt['usr_password_value']);
					if ($rt['pswitch'] == 'b') {
						$p->User->vxLogout();
					}
				}
				$p->vxHead($msgSiteTitle = Vocabulary::action_modifyprofile);
				$p->vxBodyStart();
				$p->vxTop();
				$p->vxContainer('user_update', $options = $rt);
			}
			break;
		} else {
			$p->URL->vxToRedirect($p->URL->vxGetLogin($p->URL->vxGetUserModify()));
			break;
		}
	
	case 'user_move':
		if ($p->User->vxIsLogin()) {
			$p->vxHead($msgSiteTitle = Vocabulary::action_modifygeo);
			$p->vxBodyStart();
			$p->vxTop();
			$p->vxContainer('user_move');
			break;
		} else {
			$p->URL->vxToRedirect($p->URL->vxGetLogin($p->URL->vxGetUserMove()));
			break;
		}
	
	case 'topic_archive_user':
		$GOOGLE_AD_LEGAL = true;
		if (isset($_GET['user_nick'])) {
			$user_nick = mysql_real_escape_string(trim($_GET['user_nick']), $p->db);
			$sql = "SELECT usr_id, usr_nick, usr_gender, usr_portrait, usr_hits, usr_gender, usr_created FROM babel_user WHERE usr_nick = '{$user_nick}' LIMIT 1";
			$rs = mysql_query($sql, $p->db);
			if ($User = mysql_fetch_object($rs)) {
				mysql_free_result($rs);
				$p->vxHead($msgSiteTitle = make_plaintext($User->usr_nick) . ' 的所有主题');
				$p->vxBodyStart();
				$p->vxTop();
				$p->vxContainer('topic_archive_user', $options = $User);
			} else {
				mysql_free_result($rs);
				$p->vxHomeBundle(BABEL_HOME_STYLE_DEFAULT);
			}
			break;
		} else {
			$p->vxHomeBundle(BABEL_HOME_STYLE_DEFAULT);
			break;
		}
		
	case 'channel_view':
		$GOOGLE_AD_LEGAL = true;
		if (isset($_GET['channel_id'])) {
			$channel_id = intval($_GET['channel_id']);
			if ($p->Validator->vxExistChannel($channel_id)) {
				$Channel = new Channel($channel_id, $p->db);
				$p->vxHead($msgSiteTitle = make_plaintext($Channel->chl_title));
				$p->vxBodyStart();
				$p->vxTop();
				$p->vxContainer('channel_view', $options = $Channel);
				break;
			} else {
				$p->vxHomeBundle(BABEL_HOME_STYLE_DEFAULT);
				break;
			}
		} else {
			$p->vxHomeBundle(BABEL_HOME_STYLE_DEFAULT);
			break;
		}

	case 'board_view':
		$GOOGLE_AD_LEGAL = true;
		$permit = 1;
		if (isset($_GET['board_id'])) {
			$board_id = intval($_GET['board_id']);
			// check if user was accessing a restricted node:
			if (in_array($board_id, $p->restricted->nodes_restricted)) {
				if ($p->User->vxIsLogin()) {
					if (!in_array($p->User->usr_id, $p->restricted->users_permitted[$board_id])) {
						$permit = 0;
					}
				} else {
					$permit = 0;
				}
			}
			if ($permit) {
				$sql = "SELECT nod_id, nod_name, nod_title FROM babel_node WHERE nod_id = {$board_id} AND nod_level > 1";
				$rs = mysql_query($sql, $p->db);
				if (mysql_num_rows($rs) == 1) {
					$O = mysql_fetch_object($rs);
					mysql_free_result($rs);
					if (preg_match('/^([0-9]{6})$/', $O->nod_name)) {
						$nod_title = $O->nod_title . ' (' . $O->nod_name . ')';
					} else {
						$nod_title = make_plaintext($O->nod_title);
					}
					$p->vxHead($msgSiteTitle = $nod_title, '', 'http://' . BABEL_DNS_NAME . '/feed/board/' . $O->nod_name . '.rss');
					$p->vxBodyStart();
					$p->vxTop($msgBanner = Vocabulary::site_banner, $keyword = $O->nod_title);
					$p->vxContainer('board_view', $options = array('board_id' => $O->nod_id));
					break;
				} else {
					mysql_free_result($rs);
					$p->vxHomeBundle(BABEL_HOME_STYLE_DEFAULT);
					break;
				}
			} else {
				$sql = "SELECT nod_id, nod_name, nod_title, nod_sid FROM babel_node WHERE nod_id = {$board_id} AND nod_level > 1";
				$rs = mysql_query($sql, $p->db);
				if (mysql_num_rows($rs) == 1) {
					$Board = mysql_fetch_object($rs);
					mysql_free_result($rs);
					$p->vxBoardViewDeniedBundle($Board);
					break;
				} else {
					mysql_free_result($rs);
					$p->vxHomeBundle(BABEL_HOME_STYLE_DEFAULT);
					break;
				}
			}
		} else {
			$permit = 1;
			if (isset($_GET['board_name'])) {
				$board_name = strtolower(trim($_GET['board_name']));
				$sql = "SELECT nod_id, nod_sid, nod_name, nod_title, nod_level FROM babel_node WHERE nod_name = '{$board_name}' and nod_level > 0";
				$rs = mysql_query($sql, $p->db);
				if (mysql_num_rows($rs) == 1) {
					$Node = mysql_fetch_object($rs);
					mysql_free_result($rs);
					// check if user was accessing a restricted node:
					if (in_array($Node->nod_id, $p->restricted->nodes_restricted)) {
						if ($p->User->vxIsLogin()) {
							if (!in_array($p->User->usr_id, $p->restricted->users_permitted[$Node->nod_id])) {
								$permit = 0;
							}
						} else {
							$permit = 0;
						}
					}
					if ($permit) {
						if (preg_match('/^([0-9]{6})$/', $Node->nod_name)) {
							$nod_title = $Node->nod_title . ' (' . $Node->nod_name . ')';
						} else {
							$nod_title = make_plaintext($Node->nod_title);
						}
						$p->vxHead($msgSiteTitle = $nod_title, '', 'http://' . BABEL_DNS_NAME . '/feed/board/' . $Node->nod_name . '.rss');
						$p->vxBodyStart();
						$p->vxTop($msgBanner = Vocabulary::site_banner, $keyword = $Node->nod_title);
						switch ($Node->nod_level) {
							case 2:
							default:
								$p->vxContainer('board_view', $options = array('board_id' => $Node->nod_id));
								break;
							case 1:
								$p->vxContainer('section_view', $options = array('section_id' => $Node->nod_id));
								break;
						}
					} else {
						$p->vxBoardViewDeniedBundle($Node);
					}
					break;
				} else {
					mysql_free_result($rs);
					$p->vxHomeBundle(BABEL_HOME_STYLE_DEFAULT);
					break;
				}
			} else {
				$p->vxHomeBundle(BABEL_HOME_STYLE_DEFAULT);
				break;
			}
		}
		
	case 'who_fav_node':
		$GOOGLE_AD_LEGAL = true;
		if (isset($_GET['node_name'])) {
			$node_name = strtolower(trim($_GET['node_name']));
			$sql = "SELECT nod_id, nod_name, nod_title, nod_level FROM babel_node WHERE nod_name = '{$node_name}' and nod_level > 0";
			$rs = mysql_query($sql, $p->db);
			if ($Node = mysql_fetch_object($rs)) {
				mysql_free_result($rs);
				if ($Node->nod_level > 1) {
					$p->vxHead($msgSiteTitle = '谁收藏了 ' . $Node->nod_title . ' 讨论区');
				} else {
					$p->vxHead($msgSiteTitle = '谁收藏了 ' . $Node->nod_title . ' 区域');
				}
				$p->vxBodyStart();
				$p->vxTop();
				$p->vxContainer('who_fav_node', $options = array('node_id' => $Node->nod_id, 'node_level' => $Node->nod_level));
				$Node = null;
				break;
			} else {
				mysql_free_result($rs);
				$p->vxHomeBundle(BABEL_HOME_STYLE_DEFAULT);
				break;
			}
		} else {
			$p->vxHomeBundle(BABEL_HOME_STYLE_DEFAULT);
			break;		
		}
		
	case 'who_fav_topic':
		$GOOGLE_AD_LEGAL = true;
		if (isset($_GET['topic_id'])) {
			$topic_id = intval($_GET['topic_id']);
			if ($p->Validator->vxExistTopic($topic_id)) {
				$Topic = new Topic($topic_id, $p->db);
				$p->vxHead($msgSiteTitle = '谁收藏了主题 - ' . make_plaintext($Topic->tpc_title));
				$p->vxBodyStart();
				$p->vxTop();
				$p->vxContainer('who_fav_topic', $options = $Topic);
				$Topic = null;
				break;
			} else {
				mysql_free_result($rs);
				$p->vxHomeBundle(BABEL_HOME_STYLE_DEFAULT);
				break;
			}
		} else {
			$p->vxHomeBundle(BABEL_HOME_STYLE_DEFAULT);
			break;		
		}
		
	case 'who_settle_geo':
		$GOOGLE_AD_LEGAL = true;
		if (isset($_GET['geo'])) {
			$geo = strtolower(make_single_safe($_GET['geo']));
			if (!$p->Geo->vxIsExist($geo)) {
				$geo = 'earth';
			}
		} else {
			$geo = 'earth';
		}
		$p->vxHead($msgSiteTitle = '谁在' . $p->Geo->map['name'][$geo]);
		$p->vxBodyStart();
		$p->vxTop();
		$p->vxContainer('who_settle_geo', $options = $geo);
		break;
	
	case 'who_going_geo':
		$GOOGLE_AD_LEGAL = true;
		if (isset($_GET['geo'])) {
			$geo = strtolower(make_single_safe($_GET['geo']));
			if (!$p->Geo->vxIsExist($geo)) {
				$geo = 'earth';
			}
		} else {
			$geo = 'earth';
		}
		$p->vxHead($msgSiteTitle = '谁想去' . $p->Geo->map['name'][$geo]);
		$p->vxBodyStart();
		$p->vxTop();
		$p->vxContainer('who_going_geo', $options = $geo);
		break;
		
	case 'who_visited_geo':
		$GOOGLE_AD_LEGAL = true;
		if (isset($_GET['geo'])) {
			$geo = strtolower(make_single_safe($_GET['geo']));
			if (!$p->Geo->vxIsExist($geo)) {
				$geo = 'earth';
			}
		} else {
			$geo = 'earth';
		}
		$p->vxHead($msgSiteTitle = '谁去过' . $p->Geo->map['name'][$geo]);
		$p->vxBodyStart();
		$p->vxTop();
		$p->vxContainer('who_visited_geo', $options = $geo);
		break;	
	
	case 'who_connect_user':
		$GOOGLE_AD_LEGAL = true;
		if (isset($_GET['user_nick'])) {
			$user_nick = fetch_single($_GET['user_nick']);
			if ($user_id = $p->Validator->vxExistUserNick($user_nick)) {
				$p->vxHead($msgSiteTitle = '谁把 ' . make_plaintext($user_nick) . ' 加为好友？');
				$p->vxBodyStart();
				$p->vxTop();
				$p->vxContainer('who_connect_user', $options = $user_id);
				break;
			} else {
				$p->vxHomeBundle();
				break;
			}
		} else {
			$p->vxHomeBundle();
			break;
		}
		
	case 'topic_fresh':
		$GOOGLE_AD_LEGAL = true;
		$p->vxHead($msgSiteTitle = Vocabulary::action_freshtopic);
		$p->vxBodyStart();
		$p->vxTop();
		$p->vxContainer('topic_fresh');
		break;
		
	case 'topic_favorite':
		$GOOGLE_AD_LEGAL = true;
		if ($p->User->vxIsLogin()) {
			$p->vxHead($msgSiteTitle = Vocabulary::term_favorite);
			$p->vxBodyStart();
			$p->vxTop();
			$p->vxContainer('topic_favorite');
			break;
		} else {
			$p->URL->vxToRedirect($p->URL->vxGetLogin($p->URL->vxGetTopicFavorite()));
			break;
		}
	
	case 'topic_top':
		$GOOGLE_AD_LEGAL = true;
		$p->vxHead($msgSiteTitle = Vocabulary::term_toptopic);
		$p->vxBodyStart();
		$p->vxTop();
		$p->vxContainer('topic_top');
		break;
	
	case 'topic_new':
		if ($p->User->vxIsLogin()) {
			$exp = -(BABEL_TPC_PRICE);
			if ((abs($exp) * 1.2) > $p->User->usr_money) {
				$p->vxHead($msgSiteTitle = Vocabulary::term_out_of_money);
				$p->vxBodyStart();
				$p->vxTop();
				$p->vxContainer('out_of_money');
				break;
			} else {
				if (isset($_GET['board_id'])) {
					$board_id = intval($_GET['board_id']);
					// check if user had permission to post to a restricted node
					if (!check_node_permission($board_id, $p->User, $p->restricted)) {
						$Board = new Node($board_id, $p->db);
						$p->vxBoardViewDeniedBundle($Board);
						break;
					} else {
						if (strlen($board_id) > 0) {
							$sql = "SELECT nod_id, nod_level FROM babel_node WHERE nod_id = {$board_id}";
							$rs = mysql_query($sql, $p->db);
							if (mysql_num_rows($rs) == 1) {
								$O = mysql_fetch_object($rs);
								mysql_free_result($rs);
								$p->vxHead($msgSiteTitle = Vocabulary::action_newtopic);
								$p->vxBodyStart();
								$p->vxTop();
								if ($O->nod_level > 1) {
									$p->vxContainer('topic_new', $options = array('mode' => 'board', 'board_id' => $O->nod_id));
								} else {
									$p->vxContainer('topic_new', $options = array('mode' => 'section', 'section_id' => $O->nod_id));	
								}
								$O = null;
								break;
							} else {
								mysql_free_result($rs);
								$p->vxHomeBundle(BABEL_HOME_STYLE_DEFAULT);
								break;
							}
						} else {
							$p->vxHomeBundle(BABEL_HOME_STYLE_DEFAULT);
							break;
						}
					}
				} else {
					$p->vxHomeBundle(BABEL_HOME_STYLE_DEFAULT);
					break;
				}
			}
		} else {
			if (isset($_GET['board_id'])) {
				$board_id = intval(trim($_GET['board_id']));
				$p->URL->vxToRedirect($p->URL->vxGetLogin($p->URL->vxGetTopicNew($board_id)));
			} else {
				$p->vxHomeBundle(BABEL_HOME_STYLE_DEFAULT);
				break;
			}
		}

	case 'topic_create':
		if ($p->User->vxIsLogin()) {
			$exp = -(BABEL_TPC_PRICE);
			if ((abs($exp) * 1.2) > $p->User->usr_money) {
				$p->vxHead($msgSiteTitle = Vocabulary::term_out_of_money);
				$p->vxBodyStart();
				$p->vxTop();
				$p->vxContainer('out_of_money');
				break;
			} else {
				if (strtoupper($_SERVER['REQUEST_METHOD']) != 'POST') {
					if (isset($_GET['board_id'])) {
						$board_id = intval($_GET['board_id']);
						$sql = "SELECT nod_id, nod_level FROM babel_node WHERE nod_id = {$board_id} AND nod_level > 0";
						$rs = mysql_query($sql, $p->db);
						if (mysql_num_rows($rs) == 1) {
							$O = mysql_fetch_object($rs);
							mysql_free_result($rs);
							$p->vxHead($msgSiteTitle = Vocabulary::action_newtopic);
							$p->vxBodyStart();
							$p->vxTop();
							if ($O->nod_level > 1) {
								$p->vxContainer('topic_new', $options = array('mode' => 'board', 'board_id' => $O->nod_id));
							} else {
								$p->vxContainer('topic_new', $options = array('mode' => 'section', 'section_id' => $O->nod_id));	
							}
							$O = null;
							break;
						} else {
							mysql_free_result($rs);
							$p->vxHomeBundle(BABEL_HOME_STYLE_DEFAULT);
							break;
						}				
					} else {
						$p->vxHomeBundle(BABEL_HOME_STYLE_DEFAULT);
						break;
					}
				} else {
					if (isset($_GET['board_id'])) {
						$board_id = intval($_GET['board_id']);
						$sql = "SELECT nod_id, nod_level FROM babel_node WHERE nod_id = {$board_id} AND nod_level > 0";
						$rs = mysql_query($sql, $p->db);
						if (mysql_num_rows($rs) == 1) {
							$O = mysql_fetch_object($rs);
							mysql_free_result($rs);
							if ($O->nod_level > 1) {
								$rt = $p->Validator->vxTopicCreateCheck($options = array('mode' => 'board', 'board_id' => $O->nod_id), $p->User);
							} else {
								$rt = $p->Validator->vxTopicCreateCheck($options = array('mode' => 'section', 'section_id' => $O->nod_id), $p->User);
							}
							$O = null;
							if ($rt['out_of_money']) {
								$p->vxHead($msgSiteTitle = Vocabulary::term_out_of_money);
								$p->vxBodyStart();
								$p->vxTop();
								$p->vxContainer('out_of_money');
							} else {
								if ($rt['errors'] == 0) {
									$O = $p->Validator->vxTopicCreateInsert($rt['tpc_pid_value'], $p->User->usr_id, $rt['tpc_title_value'], $rt['tpc_description_value'], $rt['tpc_content_value'], $rt['exp_amount']);
									$sql = "SELECT tpc_id, tpc_pid, tpc_title, usr_id, usr_email, usr_nick, usr_sw_notify_reply FROM babel_topic, babel_user WHERE tpc_uid = usr_id AND tpc_uid = {$p->User->usr_id} ORDER BY tpc_created DESC LIMIT 1";
									$rs = mysql_query($sql, $p->db);
									$Topic = mysql_fetch_object($rs);
									mysql_free_result($rs);
									// $p->vxHead($msgSiteTitle = Vocabulary::action_newtopic, $return = '/topic/view/' . $Topic->tpc_id . '.html');
									$Node = new Node($Topic->tpc_pid, $p->db);
									$Node->vxUpdateTopics();
									$Node = null;
									$p->URL->vxToRedirect($p->URL->vxGetTopicView($Topic->tpc_id));
								} else {
									$p->vxHead($msgSiteTitle = Vocabulary::action_newtopic);
									$p->vxBodyStart();
									$p->vxTop();
									$p->vxContainer('topic_create', $options = $rt);
								}
							}
							break;
						} else {
							mysql_free_result($rs);
							$p->vxHomeBundle(BABEL_HOME_STYLE_DEFAULT);
							break;
						}
					} else {
						mysql_free_result($rs);
						$p->vxHomeBundle(BABEL_HOME_STYLE_DEFAULT);
						break;
					}
				}
			}
		} else {
			if (isset($_GET['board_id'])) {
				$board_id = intval($_GET['board_id']);
				$p->URL->vxToRedirect($p->URL->vxGetLogin($p->URL->vxGetTopicNew($board_id)));
				break;
			} else {
				$p->vxHomeBundle(BABEL_HOME_STYLE_DEFAULT);
				break;
			}
		}

	case 'topic_view':
		define('BABEL_AT', 'topic_view');
		$GOOGLE_AD_LEGAL = true;
		if (isset($_GET['topic_id'])) {
			$topic_id = intval(trim($_GET['topic_id']));
			if ($p->Validator->vxIsDangerousTopic($topic_id, $p->cs)) {
				$sql = "UPDATE babel_topic SET tpc_flag = 1 WHERE tpc_id = {$topic_id}";
				mysql_unbuffered_query($sql);
				$p->URL->vxToRedirect($p->URL->vxGetHome());
				die('');
			}
			$sql = "SELECT tpc_id, tpc_pid, tpc_title FROM babel_topic WHERE tpc_id = {$topic_id}";
			$rs = mysql_query($sql, $p->db);
			if (mysql_num_rows($rs) == 1) {
				$Topic = mysql_fetch_object($rs);
				mysql_free_result($rs);
				// check if user was accessing a restricted node
				if (!check_node_permission($Topic->tpc_pid, $p->User, $p->restricted)) {
					$Node = new Node($Topic->tpc_pid, $p->db);
					$p->vxBoardViewDeniedBundle($Node);
				} else {
					$p->vxHead($msgSiteTitle = make_plaintext($Topic->tpc_title), '', $feedURL = 'http://' . BABEL_DNS_FEED . '/feed/topic/' . $Topic->tpc_id . '.rss');
					$p->vxBodyStart();
					$p->vxTop($msgBanner = Vocabulary::site_banner, $keyword = make_single_return($Topic->tpc_title, 0));
					$p->vxContainer('topic_view', $options = array('topic_id' => $Topic->tpc_id));
				}
			} else {
				$p->vxHomeBundle(BABEL_HOME_STYLE_DEFAULT);
			}
			break;
		} else {
			$p->vxHomeBundle(BABEL_HOME_STYLE_DEFAULT);
			break;
		}
	
	case 'topic_move':
		$GOOGLE_AD_LEGAL = false;
		if (isset($_GET['topic_id'])) {
			$topic_id = intval(trim($_GET['topic_id']));
			if ($p->User->vxIsLogin() && $p->User->usr_id == 1) {
				$sql = "SELECT tpc_id, tpc_title FROM babel_topic WHERE tpc_id = {$topic_id}";
				$rs = mysql_query($sql, $p->db);
				if (mysql_num_rows($rs) == 1) {
					$Topic = mysql_fetch_object($rs);
					mysql_free_result($rs);
					$p->vxHead($msgSiteTitle = make_plaintext($Topic->tpc_title) . ' - 移动');
					$p->vxBodyStart();
					$p->vxTop($msgBanner = Vocabulary::site_banner, $keyword = $Topic->tpc_title);
					$p->vxContainer('topic_move', $options = array('topic_id' => $Topic->tpc_id));
				} else {
					$p->vxHomeBundle(BABEL_HOME_STYLE_DEFAULT);
				}
			} else {
				die(); // do I really need a beautiful screen of death?
			}
			break;
		} else {
			$p->vxHomeBundle(BABEL_HOME_STYLE_DEFAULT);
			break;
		}
	
	case 'section_view':
		$GOOGLE_AD_LEGAL = true;
		if (isset($_GET['section_id'])) {
			$section_id = intval(trim($_GET['section_id']));
			$sql = "SELECT nod_id, nod_title FROM babel_node WHERE nod_id = {$section_id} AND nod_level = 1";
			$rs = mysql_query($sql, $p->db);
			if (mysql_num_rows($rs) == 1) {
				$Section = mysql_fetch_object($rs);
				mysql_free_result($rs);
				$p->vxHead($msgSiteTitle = $Section->nod_title);
				$p->vxBodyStart();
				$p->vxTop($msgBanner = Vocabulary::site_banner, $keyword = $Section->nod_title);
				$p->vxContainer('section_view', $options = array('section_id' => $section_id));
				break;
			} else {
				mysql_free_result($rs);
				$p->vxHomeBundle(BABEL_HOME_STYLE_DEFAULT);
				break;
			}
		} else {
			$section_id = rand(2, 5);
			$sql = "SELECT nod_id, nod_title FROM babel_node WHERE nod_id = {$section_id} AND nod_level = 1";
			$rs = mysql_query($sql, $p->db);
			if (mysql_num_rows($rs) == 1) {
				$Section = mysql_fetch_object($rs);
				mysql_free_result($rs);
				$p->vxHead($msgSiteTitle = ' | ' . $Section->nod_title);
				$p->vxBodyStart();
				$p->vxTop($msgBanner = Vocabulary::site_banner, $keyword = $Section->nod_title);
				$p->vxContainer('section_view', $options = array('section_id' => $Section->nod_id));
				break;
			} else {
				mysql_free_result($rs);
				$p->vxHomeBundle(BABEL_HOME_STYLE_DEFAULT);
				break;
			}
		}
	
	case 'post_modify':
		/*
		 *
		 * If you're looking for privileges stuff, they're inside V2EXCore.php
		 *
		 */
		if ($p->User->vxIsLogin()) {
			if (isset($_GET['post_id'])) {
				$post_id = intval($_GET['post_id']);
				if ($p->Validator->vxExistPost($post_id)) {
					$Post = new Post($post_id, $p->db);
					$Topic = new Topic($Post->pst_tid, $p->db, 0);
					$p->vxHead($msgSiteTitle = Vocabulary::action_modifypost . ' | ' . make_plaintext($Post->pst_title));
					$p->vxBodyStart();
					$p->vxTop();
					$p->vxContainer('post_modify', $options = $Post);
					break;
				} else {
					$p->HomeBundle(BABEL_HOME_STYLE_DEFAULT);
					break;
				}
			} else {
				$p->HomeBundle(BABEL_HOME_STYLE_DEFAULT);
				break;
			}
		} else {
			if (isset($_GET['post_id'])) {
				$post_id = intval($_GET['post_id']);
				$p->URL->vxToRedirect($p->URL->vxGetLogin($p->URL->vxGetPostModify($post_id)));
				break;
			} else {
				$p->vxHomeBundle(BABEL_HOME_STYLE_DEFAULT);
				break;
			}
		}
		
	case 'post_update':
		if ($p->User->vxIsLogin()) {
			if (strtoupper($_SERVER['REQUEST_METHOD']) != 'POST') {
				if (isset($_GET['post_id'])) {
					$post_id = intval($_GET['post_id']);
					if ($p->Validator->vxExistPost($post_id)) {
						$p->URL->vxToRedirect($p->URL->vxGetPostModify($post_id));
						break;
					} else {
						$p->vxHomeBundle(BABEL_HOME_STYLE_DEFAULT);
						break;
					}
				} else {
					$p->vxHomeBundle(BABEL_HOME_STYLE_DEFAULT);
					break;
				}
			} else {
				if (isset($_GET['post_id'])) {
					$post_id = intval($_GET['post_id']);
					if ($p->Validator->vxExistPost($post_id)) {
						$Post = new Post($post_id, $p->db);
						$rt = $p->Validator->vxPostUpdateCheck($Post, $p->User);
						if ($rt['errors'] == 0) {
							$Post = new Post($post_id, $p->db);
							$p->Validator->vxPostUpdateUpdate($rt['post_id'], $rt['pst_title_value'], $rt['pst_content_value']);
							if (!isset($_SESSION['babel_page_topic'])) {
								$_SESSION['babel_page_topic'] = 1;
							}
							$p->URL->vxToRedirect($p->URL->vxGetTopicView($Post->pst_tid, $_SESSION['babel_page_topic'], 'p' . $Post->pst_id));
						} else {
							$p->vxHead($msgSiteTitle = Vocabulary::action_modifypost . ' | ' . make_plaintext($rt['pst_title_value']));
							$p->vxBodyStart();
							$p->vxTop();
							$p->vxContainer('post_update', $options = $rt);	
						}
						break;
					} else {
						$p->vxHomeBundle(BABEL_HOME_STYLE_DEFAULT);
						break;
					}
				} else {
					$p->vxHomeBundle(BABEL_HOME_STYLE_DEFAULT);
					break;
				}
			}
		} else {
			if (isset($_GET['post_id'])) {
				$post_id = intval($_GET['post_id']);
				if ($p->Validator->vxExistTopic($post_id)) {
					URL::vxToRedirect(URL::vxGetLogin(URL::vxGetPostModify($post_id)));
					break;
				} else {
					$p->vxHomeBundle(BABEL_HOME_STYLE_DEFAULT);
					break;
				}
			} else {
				$p->vxHomeBundle(BABEL_HOME_STYLE_DEFAULT);
				break;
			}
		}
	
	case 'topic_modify':
		if ($p->User->vxIsLogin()) {
			$exp = -(BABEL_TPC_UPDATE_PRICE);
			if ((abs($exp) * 1.2) > $p->User->usr_money) {
				$p->vxHead($msgSiteTitle = Vocabulary::term_out_of_money);
				$p->vxBodyStart();
				$p->vxTop();
				$p->vxContainer('out_of_money');
				break;
			} else {
				if (isset($_GET['topic_id'])) {
					$topic_id = intval(trim($_GET['topic_id']));
					if (strlen($topic_id) > 0) {
						if ($p->Validator->vxExistTopic($topic_id)) {
							$Topic = new Topic($topic_id, $p->db, 0);
							$p->vxHead($msgSiteTitle = Vocabulary::action_modifytopic . ' | ' . make_plaintext($Topic->tpc_title));
							$p->vxBodyStart();
							$p->vxTop();
							$p->vxContainer('topic_modify', $options = $Topic);
							break;
						} else {
							$p->HomeBundle();
							break;
						}
					} else {
						$p->HomeBundle();
						break;
					}
				} else {
					$p->HomeBundle();
					break;
				}
			}
		} else {
			if (isset($_GET['topic_id'])) {
				$topic_id = intval(trim($_GET['topic_id']));
				$p->URL->vxToRedirect($p->URL->vxGetLogin($p->URL->vxGetTopicModify($topic_id)));
				break;
			} else {
				$p->vxHomeBundle(BABEL_HOME_STYLE_DEFAULT);
				break;
			}
		}
		
	case 'topic_update':
		if ($p->User->vxIsLogin()) {
			if (strtoupper($_SERVER['REQUEST_METHOD']) != 'POST') {
				if (isset($_GET['topic_id'])) {
					$topic_id = intval($_GET['topic_id']);
					if ($p->Validator->vxExistTopic($topic_id)) {
						$p->URL->vxToRedirect($p->URL->vxGetTopicModify($topic_id));
						break;
					} else {
						$p->vxHomeBundle(BABEL_HOME_STYLE_DEFAULT);
						break;
					}
				} else {
					$p->vxHomeBundle(BABEL_HOME_STYLE_DEFAULT);
					break;
				}
			} else {
				if (isset($_GET['topic_id'])) {
					$topic_id = intval($_GET['topic_id']);
					if ($p->Validator->vxExistTopic($topic_id)) {
						$rt = $p->Validator->vxTopicUpdateCheck($topic_id, $p->User);
						if ($rt['out_of_money']) {
							$p->vxHead($msgSiteTitle = Vocabulary::term_out_of_money);
							$p->vxBodyStart();
							$p->vxTop();
							$p->vxContainer('out_of_money');
						} else {
							if ($rt['errors'] == 0) {
								// $p->vxHead($msgSiteTitle = Vocabulary::action_modifytopic . ' | ' . make_plaintext($rt['tpc_title_value']), $return = '/topic/view/' . $rt['topic_id'] . '.html');
								$Topic = new Topic($rt['topic_id'], $p->db);
								if ($p->User->usr_id == $Topic->tpc_uid) {
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
								if ($p->User->usr_id == 1) {
									$rt['permit'] = 1;
								}
								if ($rt['permit'] == 1) {
									$p->Validator->vxTopicUpdateUpdate($rt['topic_id'], $rt['tpc_title_value'], $rt['tpc_description_value'], $rt['tpc_content_value'], $rt['exp_amount']);
									$Topic->vxTouch();
									$Topic = null;
									$p->URL->vxToRedirect($p->URL->vxGetTopicView($rt['topic_id']));
								} else {
									$p->vxHead($msgSiteTitle = Vocabulary::action_modifytopic . ' | ' . make_plaintext($rt['tpc_title_value']));
									$p->vxBodyStart();
									$p->vxTop();
									$p->vxContainer('topic_update', $options = $rt);
								}
							} else {
								$p->vxHead($msgSiteTitle = Vocabulary::action_modifytopic . ' | ' . make_plaintext($rt['tpc_title_value']));
								$p->vxBodyStart();
								$p->vxTop();
								$p->vxContainer('topic_update', $options = $rt);
							}
						}
						break;
					} else {
						$p->vxHomeBundle(BABEL_HOME_STYLE_DEFAULT);
						break;
					}
				} else {
					$p->vxHomeBundle(BABEL_HOME_STYLE_DEFAULT);
					break;
				}
			}
		} else {
			if (isset($_GET['topic_id'])) {
				$topic_id = intval(trim($_GET['topic_id']));
				if ($p->Validator->vxExistTopic($topic_id)) {
					$p->URL->vxToRedirect($p->URL->vxGetLogin($p->URL->vxGetTopicModify($topic_id)));
					break;
				} else {
					$p->vxHomeBundle(BABEL_HOME_STYLE_DEFAULT);
					break;
				}
			} else {
				$p->vxHomeBundle(BABEL_HOME_STYLE_DEFAULT);
				break;
			}
		}
		
	case 'topic_erase':
		if ($p->User->vxIsLogin()) {
			if (isset($_GET['topic_id'])) {
				$topic_id = intval($_GET['topic_id']);
				if ($p->Validator->vxExistTopic($topic_id)) {
					if ($p->User->usr_id == 1) {
						$Topic = new Topic($topic_id, $p->db);
						$Topic->vxEraseTopic($topic_id);
						$Topic->vxUpdateTopics($Topic->tpc_pid);
						$p->URL->vxToRedirect($p->URL->vxGetBoardView($Topic->tpc_pid));
						break;
					} else {
						$Topic = new Topic($topic_id, $p->db);
						if (($Topic->tpc_uid == $p->User->usr_id) && ($Topic->tpc_posts == 0) && ((time() - $Topic->tpc_created) < (86400 * 31))) {
							$Topic->vxEraseTopic($topic_id);
							$Topic->vxUpdateTopics($Topic->tpc_pid);
							$p->URL->vxToRedirect($p->URL->vxGetBoardView($Topic->tpc_pid));
						} else {
							$p->vxTopicEraseDeniedBundle($Topic);
						}
						break;
					}
				} else {
					$p->vxHomeBundle(BABEL_HOME_STYLE_DEFAULT);
					break;
				}
			} else {
				$p->vxHomeBundle(BABEL_HOME_STYLE_DEFAULT);
				break;
			}
		} else {
			if (isset($_GET['topic_id'])) {
				$post_id = intval($_GET['topic_id']);
				if ($p->Validator->vxExistTopic($topic_id)) {
					$p->URL->vxToRedirect($p->URL->vxGetLogin($p->URL->vxGetTopicErase($topic_id)));
					break;
				} else {
					$p->vxHomeBundle(BABEL_HOME_STYLE_DEFAULT);
					break;
				}
			} else {
				$p->vxHomeBundle(BABEL_HOME_STYLE_DEFAULT);
				break;
			}
		}
		
	case 'post_erase':
		if ($p->User->vxIsLogin()) {
			if (isset($_GET['post_id'])) {
				$post_id = intval($_GET['post_id']);
				if ($p->Validator->vxExistPost($post_id)) {
					if ($p->User->usr_id == 1) {
						$Post = new Post($post_id, $p->db);
						$Post->vxErasePost($post_id);
						$Post->vxUpdatePosts($Post->pst_tid);
						$Topic = new Topic($Post->pst_tid, $p->db);
						$Topic->vxUpdateFollowers();
						$Topic = null;
						$p->URL->vxToRedirect($p->URL->vxGetTopicView($Post->pst_tid));
						break;
					} else {
						$p->vxDeniedBundle();
						break;
					}
				} else {
					$p->vxHomeBundle(BABEL_HOME_STYLE_DEFAULT);
					break;
				}
			} else {
				$p->vxHomeBundle(BABEL_HOME_STYLE_DEFAULT);
				break;
			}
		} else {
			if (isset($_GET['post_id'])) {
				$post_id = intval($_GET['post_id']);
				if ($p->Validator->vxExistPost($post_id)) {
					$p->URL->vxToRedirect($p->URL->vxGetLogin($p->URL->vxGetPostErase($post_id)));
					break;
				} else {
					$p->vxHomeBundle(BABEL_HOME_STYLE_DEFAULT);
					break;
				}
			} else {
				$p->vxHomeBundle(BABEL_HOME_STYLE_DEFAULT);
				break;
			}
		}

	case 'post_create':
		if ($p->User->vxIsLogin()) {
			if (strtoupper($_SERVER['REQUEST_METHOD']) != 'POST') {
				$p->vxHomeBundle(BABEL_HOME_STYLE_DEFAULT);
				break;
			} else {
				$topic_id = intval($_GET['topic_id']);
				if ($p->Validator->vxExistTopic($topic_id)) {
					
					$rt = $p->Validator->vxPostCreateCheck($topic_id, $p->User);
					$rt['autistic'] = false;
					
					$Topic = new Topic($rt['topic_id'], $p->db);
					
					if ($p->Validator->vxIsAutisticNode($Topic->tpc_pid, $p->cs)) {
						if ($p->User->usr_id != $Topic->tpc_uid) {
							$rt['errors']++;
							$rt['autistic'] = true;
						}
					}
					if ($rt['out_of_money']) {
						$p->vxHead($msgSiteTitle = Vocabulary::term_out_of_money);
						$p->vxBodyStart();
						$p->vxTop();
						$p->vxContainer('out_of_money');
					} else {
						if ($rt['errors'] == 0) {
							$p->Validator->vxPostCreateInsert($rt['topic_id'], $p->User->usr_id, $rt['pst_title_value'], $rt['pst_content_value'], $rt['exp_amount']);
							if (!isset($_SESSION['babel_page_topic'])) {
								$_SESSION['babel_page_topic'] = 1;
							}
							// $p->vxHead($msgSiteTitle = Vocabulary::action_replytopic, $return = '/topic/view/' . $topic_id . '/' . $_SESSION['babel_page_topic'] . '.html');
							$Topic->vxTouch();
							$Topic->vxUpdatePosts();
							
							$_pst_content_plain = htmlspecialchars_decode(strip_tags(format_ubb($rt['pst_content_value'])));
							
							// Start: Send a mail to topic owner

							if ($Topic->usr_sw_notify_reply == 1 && $Topic->tpc_uid != $p->User->usr_id) {
								$mail = array();
								$mail['subject'] = '[' . Vocabulary::site_name . ' 新回复] - ' . make_plaintext($Topic->tpc_title);
								$mail['body'] = "{$Topic->usr_nick}，你好！\n\n你发表在 " . Vocabulary::site_name . " 的讨论主题 [ {$Topic->tpc_title} ] 被会员 {$p->User->usr_nick} 在北京时间 " . date('Y-n-j G:i:s', time()) . " 回复了，因此我们发送此邮件给你。\n\n-----------------------------------------------\n" . $_pst_content_plain . "\n-----------------------------------------------\n\n你可以点击下面的地址查看这篇新回复。\n\nhttp://" . BABEL_DNS_NAME . $p->URL->vxGetTopicView($rt['topic_id'], $_SESSION['babel_page_topic']) . "\n\n如果你不想再收到此类邮件，你可以在个人设置中关闭 [ 邮件通知自己的主题的新回复 ] 功能。" . BABEL_AM_SIGNATURE;
								if ($Topic->usr_email_notify != '') {
									$_receiver = $Topic->usr_email_notify;
								} else {
									$_receiver = $Topic->usr_email;
								}
								$am = new Airmail($_receiver, $mail['subject'], $mail['body'], $p->db);
								$am->vxSend();
								$am = null;
								if (BABEL_DEBUG) {
									if (isset($_SESSION['babel_debug_log'])) {
										$_SESSION['babel_debug_log'][time()] = 'babel - mail sent to: ' . $_receiver;
									} else {
										$_SESSION['babel_debug_log'] = array();
										$_SESSION['babel_debug_log'][time()] = 'babel - mail sent to: ' . $_receiver;
									}
								}
							}
							
							// Over: Send a mail to topic owner
							
							// Start: Update topic followers
							
							$_followers = $Topic->vxUpdateFollowers();
							if (count($_followers) > 0) {						
								$sql = 'SELECT usr_id, usr_nick, usr_email, usr_email_notify, usr_sw_notify_reply_all FROM babel_user WHERE usr_id IN (' . implode(',', array_keys($_followers)) . ')';
								$rs = mysql_query($sql, $p->db);
								
								while ($_follower = mysql_fetch_object($rs)) {
									if ($_follower->usr_id != $Topic->tpc_uid && $_follower->usr_sw_notify_reply_all == 1 && $_follower->usr_id != $p->User->usr_id) {
										if ($_follower->usr_email_notify != '') {
											$_receiver = $_follower->usr_email_notify;
										} else {
											$_receiver = $_follower->usr_email;
										}
										$mail = array();
										$mail['subject'] = '[' . Vocabulary::site_name . ' 新回复] - ' . make_plaintext($Topic->tpc_title);
										$mail['body'] = "{$_follower->usr_nick}，你好！\n\n你在 " . Vocabulary::site_name . " 参与过的讨论主题 [ {$Topic->tpc_title} ] 被会员 {$p->User->usr_nick} 在北京时间 " . date('Y-n-j G:i:s', time()) . " 回复了，因此我们发送此邮件给你。\n\n-----------------------------------------------\n" . $_pst_content_plain . "\n-----------------------------------------------\n\n你可以点击下面的地址查看这篇新回复。\n\nhttp://" . BABEL_DNS_NAME . $p->URL->vxGetTopicView($rt['topic_id'], $_SESSION['babel_page_topic']) . "\n\n如果你不想再收到此类邮件，你可以在个人设置中关闭 [ 邮件通知我参与过的主题的新回复 ] 功能。" . BABEL_AM_SIGNATURE;
										$am = new Airmail($_receiver, $mail['subject'], $mail['body'], $p->db);
										$am->vxSend();
										$am = null;
										if (BABEL_DEBUG) {
											if (isset($_SESSION['babel_debug_log'])) {
												$_SESSION['babel_debug_log'][time()] = 'babel - mail sent to: ' . $_receiver;
											} else {
												$_SESSION['babel_debug_log'] = array();
												$_SESSION['babel_debug_log'][time()] = 'babel - mail sent to: ' . $_receiver;
											}
										}
									}
								}
								mysql_free_result($rs);							
							}
							// Over: Update topic followers
							$p->URL->vxToRedirect($p->URL->vxGetTopicView($rt['topic_id'], $_SESSION['babel_page_topic'], 'replyForm'));
						} else {
							$p->vxHead($msgSiteTitle = Vocabulary::action_replytopic);
							$p->vxBodyStart();
							$p->vxTop();
							$p->vxContainer('post_create', $options = $rt);
						}
					}
					$Topic = null;
					break;
				} else {
					$p->vxHomeBundle(BABEL_HOME_STYLE_DEFAULT);
					break;
				}
			}
		} else {
			if (isset($_GET['topic_id'])) {
				$topic_id = intval(trim($_GET['topic_id']));
				if ($p->Validator->vxExistTopic($topic_id)) {
					$p->URL->vxToRedirect($p->URL->vxGetLogin($p->URL->vxGetTopicView($topic_id)));
				} else {
					$p->vxHomeBundle(BABEL_HOME_STYLE_DEFAULT);
				}
				break;
			} else {
				$p->vxHomeBundle(BABEL_HOME_STYLE_DEFAULT);
				break;
			}
		}

	case 'expense_view':
		if ($p->User->vxIsLogin()) {
			$p->vxHead($msgSiteTitle = Vocabulary::action_viewexpense);
			$p->vxBodyStart();
			$p->vxTop();
			$p->vxContainer('expense_view');
			break;
		} else {
			$p->URL->vxToRedirect($p->URL->vxGetLogin($p->URL->vxGetExpenseView()));
			break;
		}
		
	case 'online_view':
		if ($p->User->vxIsLogin()) {
			$p->vxHead($msgSiteTitle = Vocabulary::action_viewonline);
			$p->vxBodyStart();
			$p->vxTop();
			$p->vxContainer('online_view');
			break;
		} else {
			$p->URL->vxToRedirect($p->URL->vxGetLogin($p->URL->vxGetOnlineView()));
			break;
		}
		
	case 'who_join':
		$p->vxHead($msgSiteTitle = Vocabulary::term_member . '列表');
		$p->vxBodyStart();
		$p->vxTop();
		$p->vxContainer('who_join');
		break;
		
	case 'mobile':
		$GOOGLE_AD_LEGAL = true;
		$p->vxHead($msgSiteTitle = Vocabulary::action_mobile_search);
		$p->vxBodyStart();
		$p->vxTop();
		$p->vxContainer('mobile');
		break;
		
	case 'man':
		$GOOGLE_AD_LEGAL = true;
		if (isset($_GET['q'])) {
			$_q = urldecode(substr($_SERVER['REQUEST_URI'], 5, (strlen($_SERVER['REQUEST_URI']) - 5)));
			$p->vxHead($msgSiteTitle = $_q);
		} else {
			$p->vxHead($msgSiteTitle = Vocabulary::action_man_search);
		}
		$p->vxBodyStart();
		$p->vxTop();
		$p->vxContainer('man');
		break;
	
	case 'site':
		$GOOGLE_AD_LEGAL = true;
		if (isset($_GET['site'])) {
			$_site = strtolower(make_single_safe($_GET['site']));
			if (preg_match('/^([a-zA-Z0-9]+)$/i', $_site)) {
				$dn = BABEL_PREFIX . '/sites/' . $_site;
				if (file_exists($dn)) {
					if (is_dir($dn)) {
						$Site = new Site($_site);
					} else {
						$p->vxHomeBundle(BABEL_HOME_STYLE_DEFAULT);
					}
				} else {
					$p->vxHomeBundle(BABEL_HOME_STYLE_DEFAULT);
				}
			} else {
				$p->vxHomeBundle(BABEL_HOME_STYLE_DEFAULT);
			}
		} else {
			$p->vxHomeBundle(BABEL_HOME_STYLE_DEFAULT);
		}
		break;
	
	case 'zen':
		$GOOGLE_AD_LEGAL = true;
		
		if (isset($_GET['user_nick'])) {
			$user_nick = mysql_real_escape_string(make_single_safe($_GET['user_nick']), $p->db);
			if (strlen($user_nick) > 0) {
				$sql = "SELECT usr_id, usr_nick, usr_brief, usr_gender, usr_portrait, usr_hits, usr_created FROM babel_user WHERE usr_nick = '{$user_nick}'";
				$rs = mysql_query($sql, $p->db);
				if ($O = mysql_fetch_object($rs)) {
					$options['mode'] = 'fixed';
					$options['target'] = $O;
					$O = null;
				} else {
					if ($p->User->vxIsLogin()) {
						$options['mode'] = 'self';
					} else {
						$options['mode'] = 'random';
					}
				}
				mysql_free_result($rs);
			} else {
				if ($p->User->vxIsLogin()) {
					$options['mode'] = 'self';
				} else {
					$options['mode'] = 'random';
				}
			}
		} else {
			if ($p->User->vxIsLogin()) {
				$options['mode'] = 'self';
			} else {
				$options['mode'] = 'random';
			}
		}
			
		if ($options['mode'] == 'random') {
			$sql = "SELECT usr_id, usr_nick, usr_brief, usr_gender, usr_portrait, usr_hits, usr_created FROM babel_user ORDER BY rand() LIMIT 1";
			$rs = mysql_query($sql, $p->db);
			$options['target'] = mysql_fetch_object($rs);
			mysql_free_result($rs);	
		}
		
		if ($options['mode'] == 'self') {
			$sql = "SELECT usr_id, usr_nick, usr_brief, usr_gender, usr_portrait, usr_hits, usr_created FROM babel_user WHERE usr_id = {$p->User->usr_id}";
			$rs = mysql_query($sql, $p->db);
			$options['target'] = mysql_fetch_object($rs);
			mysql_free_result($rs);	
		}
		
		$p->vxHead($msgSiteTitle = make_plaintext($options['target']->usr_nick) . ' - ' . Vocabulary::term_zen);
		$p->vxBodyStart();
		$p->vxTop();
		$p->vxContainer('zen', $options);
		break;
	
	case 'zen2':
		$GOOGLE_AD_LEGAL = false;
		
		if (isset($_GET['user_nick'])) {
			$user_nick = mysql_real_escape_string(make_single_safe($_GET['user_nick']), $p->db);
			if (strlen($user_nick) > 0) {
				$sql = "SELECT usr_id, usr_nick, usr_brief, usr_gender, usr_portrait, usr_hits, usr_created FROM babel_user WHERE usr_nick = '{$user_nick}'";
				$rs = mysql_query($sql, $p->db);
				if ($O = mysql_fetch_object($rs)) {
					$options['mode'] = 'fixed';
					$options['target'] = $O;
					$O = null;
				} else {
					if ($p->User->vxIsLogin()) {
						$options['mode'] = 'self';
					} else {
						$options['mode'] = 'random';
					}
				}
				mysql_free_result($rs);
			} else {
				if ($p->User->vxIsLogin()) {
					$options['mode'] = 'self';
				} else {
					$options['mode'] = 'random';
				}
			}
		} else {
			if ($p->User->vxIsLogin()) {
				$options['mode'] = 'self';
			} else {
				$options['mode'] = 'random';
			}
		}
			
		if ($options['mode'] == 'random') {
			$sql = "SELECT usr_id, usr_nick, usr_brief, usr_gender, usr_portrait, usr_hits, usr_created FROM babel_user ORDER BY rand() LIMIT 1";
			$rs = mysql_query($sql, $p->db);
			$options['target'] = mysql_fetch_object($rs);
			mysql_free_result($rs);	
		}
		
		if ($options['mode'] == 'self') {
			$sql = "SELECT usr_id, usr_nick, usr_brief, usr_gender, usr_portrait, usr_hits, usr_created FROM babel_user WHERE usr_id = {$p->User->usr_id}";
			$rs = mysql_query($sql, $p->db);
			$options['target'] = mysql_fetch_object($rs);
			mysql_free_result($rs);	
		}
		
		$p->vxHead($msgSiteTitle = make_plaintext($options['target']->usr_nick) . ' - ' . Vocabulary::term_zen);
		$p->vxBodyStart();
		$p->vxTop();
		$p->vxContainer('zen2', $options);
		break;
		
	case 'pix':
		if (isset($_GET['user_nick'])) {
			$user_nick = mysql_real_escape_string(make_single_safe($_GET['user_nick']), $p->db);
			if (strlen($user_nick) > 0) {
				$sql = "SELECT usr_id, usr_nick, usr_brief, usr_gender, usr_portrait, usr_hits, usr_created FROM babel_user WHERE usr_nick = '{$user_nick}'";
				$rs = mysql_query($sql, $p->db);
				if ($O = mysql_fetch_object($rs)) {
					$options['mode'] = 'ok';
					$options['target'] = $O;
					$O = null;
				} else {
					$options['mode'] = false;
				}
				mysql_free_result($rs);
			} else {
				$options['mode'] = false;
			}
		} else {
			$options['mode'] = false;
		}
		if (!$options['mode']) {
			$p->vxHead($msgSiteTitle = Vocabulary::term_user_empty);
		} else {
			$p->vxHead($msgSiteTitle = make_plaintext($options['target']->usr_nick) . ' - ' . Vocabulary::term_pix);
		}
		$p->vxBodyStart();
		$p->vxTop();
		$p->vxContainer('pix', $options);
		break;
		
	case 'project_view':
		if (isset($_GET['project_id'])) {
			$project_id = intval($_GET['project_id']);
			$sql = "SELECT zpr_id, zpr_title, zpr_private FROM babel_zen_project WHERE zpr_id = {$project_id}";
			$rs = mysql_query($sql, $p->db);
			if (mysql_num_rows($rs) == 1) {
				$Project = mysql_fetch_object($rs);
				mysql_free_result($rs);
				$p->vxHead($msgSiteTitle = make_plaintext($Project->zpr_title));
				$p->vxBodyStart();
				$p->vxTop($msgBanner = Vocabulary::site_banner, $keyword = make_single_return($Project->zpr_title, 0));
				$p->vxContainer('project_view', $options = $Project);
			} else {
				$p->vxHomeBundle(BABEL_HOME_STYLE_DEFAULT);
			}
			break;
		} else {
			$p->vxHomeBundle(BABEL_HOME_STYLE_DEFAULT);
			break;
		}
		
	case 'sidebar':
		$global_has_bottom = false;
		$p->vxHeadMini('侧栏', 90, '/sidebar.html');
		$p->vxBodyStart();
		$p->vxMozillaSidebar();
		break;
		
	case 'top_wealth':
		$global_has_bottom = false;
		$p->vxHeadMini('社区财富排行');
		$p->vxBodyStart();
		$p->vxTopWealth();
		break;
		
	case 'ing_public':
		$p->vxHead($msgSiteTitle = '大家在做什么', '', 'http://' . BABEL_DNS_NAME . '/feed/ing');
		$p->vxBodyStart();
		$p->vxTop();
		$p->vxContainer('ing_public');
		break;
		
	case 'ing_personal':
		$public = false;
		if (isset($_GET['u'])) {
			$u = make_single_safe($_GET['u']);
		} else {
			$u = false;
			$public = true;
		}
		if ($u) {
			if (get_magic_quotes_gpc()) {
				$u = mysql_real_escape_string(stripslashes($u));
			} else {
				$u = mysql_real_escape_string($u);
			}
			$User = $p->User->vxGetUserInfoByNick($u);
			if (!$User) {
				$public = true;
			}
		}
		if ($public) {
			$p->vxHead($msgSiteTitle = '大家在做什么');
		} else {
			$p->vxHead($msgSiteTitle = $User->usr_nick_plain . ' 在做什么', '', 'http://' . BABEL_DNS_NAME . '/feed/ing/' . $User->usr_nick_url);
		}
		$p->vxBodyStart();
		$p->vxTop();
		if ($public) {
			$p->vxContainer('ing_public');
		} else {
			$p->vxContainer('ing_personal', $User);
		}
		break;
		
	case 'ing_friends':
		$public = false;
		if (isset($_GET['u'])) {
			$u = make_single_safe($_GET['u']);
		} else {
			$u = false;
			$public = true;
		}
		if ($u) {
			if (get_magic_quotes_gpc()) {
				$u = mysql_real_escape_string(stripslashes($u));
			} else {
				$u = mysql_real_escape_string($u);
			}
			$User = $p->User->vxGetUserInfoByNick($u);
			if (!$User) {
				$public = true;
			}
		}
		if ($public) {
			$p->vxHead($msgSiteTitle = '大家在做什么');
		} else {
			$p->vxHead($msgSiteTitle = make_plaintext($User->usr_nick) . ' 和朋友们在做什么', '', 'http://' . BABEL_DNS_NAME . '/feed/ing/friends/' . $User->usr_nick_url);
		}
		$p->vxBodyStart();
		$p->vxTop();
		if ($public) {
			$p->vxContainer('ing_public');
		} else {
			$p->vxContainer('ing_friends', $User);
		}
		break;
		
	case 'ojs_ing_personal':
		$global_has_bottom = false;
		$p->vxHeadMini('JavaScript 输出我的 ING 中的最新活动');
		$p->vxBodyStart();
		$p->vxOutputJavaScriptIngPersonal();
		break;
		
	case 'dry':
		$options = array();
		$options['mode'] = false;
		if (isset($_GET['user_nick'])) {
			$user_nick = mysql_real_escape_string(make_single_safe($_GET['user_nick']), $p->db);
			if (strlen($user_nick) > 0) {
				$sql = "SELECT usr_id, usr_nick, usr_brief, usr_gender, usr_portrait, usr_hits, usr_created FROM babel_user WHERE usr_nick = '{$user_nick}'";
				$rs = mysql_query($sql, $p->db);
				if ($O = mysql_fetch_object($rs)) {
					$options['mode'] = 'fixed';
					$O->usr_nick_plain = make_plaintext($O->usr_nick);
					$O->usr_nick_url = urlencode($O->usr_nick);
					$options['target'] = $O;
					$O = null;
				} else {
					if ($p->User->vxIsLogin()) {
						$options['mode'] = 'self';
					}
				}
				mysql_free_result($rs);
			} else {
				if ($p->User->vxIsLogin()) {
					$options['mode'] = 'self';
				}
			}
		} else {
			if ($p->User->vxIsLogin()) {
				$options['mode'] = 'self';
			}
		}
			
		if ($options['mode'] == 'self') {
			$sql = "SELECT usr_id, usr_nick, usr_brief, usr_gender, usr_portrait, usr_hits, usr_created FROM babel_user WHERE usr_id = {$p->User->usr_id}";
			$rs = mysql_query($sql, $p->db);
			$O = mysql_fetch_object($rs);
			$O->usr_nick_plain = make_plaintext($O->usr_nick);
			$O->usr_nick_url = urlencode($O->usr_nick);
			$options['target'] = $O;
			$O = null;
			mysql_free_result($rs);	
		}
		
		if ($options['mode']) {
			$p->vxHead($msgSiteTitle = make_plaintext($options['target']->usr_nick) . ' - ' . Vocabulary::term_dry);
			$p->vxBodyStart();
			$p->vxTop();
			$p->vxContainer('dry', $options);
		} else {
			$p->vxHomeBundle();
		}
		break;
		
	case 'dry_new':
		if ($p->User->vxIsLogin()) {
			$p->vxHead($msgSiteTitle = Vocabulary::action_dry_new);
			$p->vxBodyStart();
			$p->vxTop();
			$p->vxContainer('dry_new');
		} else {
			$p->URL->vxToRedirect($p->URL->vxGetLogin($p->URL->vxGetDryNew()));
		}
		break;
		
	case 'dry_create':
		if ($p->User->vxIsLogin()) {
			$p->vxHead($msgSiteTitle = Vocabulary::action_dry_new);
			$p->vxBodyStart();
			$p->vxTop();
			$p->vxContainer('dry_create');
		} else {
			$p->URL->vxToRedirect($p->URL->vxGetLogin($p->URL->vxGetDryNew()));
		}
		break;
		
	case 'add':
		$hot = false;
		if (isset($_GET['u'])) {
			$u = make_single_safe($_GET['u']);
		} else {
			$u = false;
			$hot = true;
		}
		if ($u) {
			if (function_exists('get_magic_quotes_gpc')) {
				if (get_magic_quotes_gpc()) {
					$u = mysql_real_escape_string(stripslashes($u));
				} else {
					$u = mysql_real_escape_string($u);
				}
			} else {
				$u = mysql_real_escape_string($u);
			}
			if (strtolower($u) == 'own') {
				if ($p->User->vxIsLogin()) {
					$User = $p->User->vxGetUserInfoByNick($p->User->usr_nick);
				} else {
					$User = false;
					$hot = true;
				}
			} else {
				$User = $p->User->vxGetUserInfoByNick($u);
			}
			if (!$User) {
				$hot = true;
			}
		}
		if ($hot) {
			$p->vxHead($msgSiteTitle = '热门收藏');
		} else {
			$p->vxHead($msgSiteTitle = $User->usr_nick_plain . ' - ADD', '', 'http://' . BABEL_DNS_NAME . '/feed/add/' . $User->usr_nick_url);
		}
		$p->vxBodyStart();
		$p->vxTop();
		if ($hot) {
			$p->vxContainer('add_hot');
		} else {
			$p->vxContainer('add', $User);
		}
		break;
		
	case 'add_hot':
		$p->vxHead($msgSiteTitle = '热门收藏');
		$p->vxBodyStart();
		$p->vxTop();
		$p->vxContainer('add_hot');
		break;
		
	case 'add_buttons':
		$p->vxHead($msgSiteTitle = '安装浏览器按钮');
		$p->vxBodyStart();
		$p->vxTop();
		$p->vxContainer('add_buttons');
		break;
		
	case 'add_sync':
		$sync = array();
		$sync['status'] = 'default';
		if (!$p->User->vxIsLogin()) {
			die($p->URL->vxToRedirect($p->URL->vxGetLogin($p->URL->vxGetAddSync())));
			break;
		} else {
			$p->vxHead($msgSiteTitle = '同步');
			$p->vxBodyStart();
			$p->vxTop();
			$p->vxContainer('add_sync', $sync);
			break;
		}
		
	case 'add_sync_start':
		if (!$p->User->vxIsLogin()) {
			die($p->URL->vxToRedirect($p->URL->vxGetLogin($p->URL->vxGetAddSync())));
			break;
		} else {
			$sync = array();
			$sync['status'] = 'default';
			if (isset($_POST['d_u']) && isset($_POST['d_p'])) {
				$del_user = fetch_single($_POST['d_u']);
				$del_pass = fetch_single($_POST['d_p']);
				if ($del_user != '' && $del_pass != '') {
					require_once('Zend/Service/Delicious.php');
					$del = new Zend_Service_Delicious($del_user, $del_pass);
					try {
						$posts = $del->getAllPosts();
					} catch (Zend_Service_Delicious_Exception $e) {
						$posts = false;
						$sync['status'] = 'error';
					}
					if ($posts) {
						var_dump($posts);
						$result = Add::vxSync($p->User, $p->db, $posts);
						var_dump($result);
					}
				} else {
					$sync['status'] = 'default';
				}
			} else {
				$sync['status'] = 'default';
			}
			$p->vxHead($msgSiteTitle = '同步');
			$p->vxBodyStart();
			$p->vxTop();
			$p->vxContainer('add_sync', $sync);
			break;
		}
		
	case 'add_add':
		if (!$p->User->vxIsLogin()) {
			die($p->URL->vxToRedirect($p->URL->vxGetLogin($p->URL->vxGetAddAdd())));
			break;
		} else {
			$p->vxHead($msgSiteTitle = '添加新收藏');
			$p->vxBodyStart();
			$p->vxTop();
			$p->vxContainer('add_add');
			break;
		}
		
	case 'add_save':
		if (!$p->User->vxIsLogin()) {
			die($p->URL->vxToRedirect($p->URL->vxGetLogin($p->URL->vxGetAddAdd())));
			break;
		} else {
			$p->vxHead($msgSiteTitle = '添加新收藏');
			$p->vxBodyStart();
			$p->vxTop();
			$p->vxContainer('add_save');
			break;
		}
		
	case 'blog_admin':
		if (!$p->User->vxIsLogin()) {
			die($p->URL->vxToRedirect($p->URL->vxGetLogin($p->URL->vxGetBlogAdmin())));
			break;
		} else {
			$p->vxHead($msgSiteTitle = '我的博客网志');
			$p->vxBodyStart();
			$p->vxTop();
			$p->vxContainer('blog_admin');
			break;
		}
		
	case 'blog_create':
		if (!$p->User->vxIsLogin()) {
			die($p->URL->vxToRedirect($p->URL->vxGetLogin($p->URL->vxGetBlogCreate())));
			break;
		} else {
			$p->vxHead($msgSiteTitle = '创建新的博客网站');
			$p->vxBodyStart();
			$p->vxTop();
			$p->vxContainer('blog_create');
			break;
		}
		
	case 'blog_create_save':
		if (!$p->User->vxIsLogin()) {
			die($p->URL->vxToRedirect($p->URL->vxGetLogin($p->URL->vxGetBlogCreate())));
			break;
		} else {
			$rt = $p->Validator->vxBlogCreateCheck($p->User->usr_money);
			if ($rt['errors'] == 0) {
				$p->Validator->vxBlogCreateInsert($p->User->usr_id, $rt['blg_name_value'], $rt['blg_title_value'], $rt['blg_description_value']);
				$sql = "SELECT blg_id FROM babel_weblog WHERE blg_uid = {$p->User->usr_id} ORDER BY blg_created DESC LIMIT 1";
				$rs = mysql_query($sql);
				$weblog_id = mysql_result($rs, 0, 0);
				mysql_free_result($rs);
				Weblog::vxBuild($p->User->usr_id, $weblog_id);
				die($p->URL->vxToRedirect($p->URL->vxGetBlogAdmin()));
				break;
			} else {
				$p->vxHead($msgSiteTitle = '创建新的博客网站');
				$p->vxBodyStart();
				$p->vxTop();
				$p->vxContainer('blog_create_save', $rt);
				break;
			}
		}
		
	case 'blog_portrait':
		if (!$p->User->vxIsLogin()) {
			if (isset($_GET['weblog_id'])) {
				$weblog_id = intval($_GET['weblog_id']);
				die($p->URL->vxToRedirect($p->URL->vxGetLogin($p->URL->vxGetBlogPortrait($weblog_id))));
				break;
			} else {
				die($p->URL->vxToRedirect($p->URL->vxGetLogin($p->URL->vxGetBlogAdmin())));
				break;
			}
		} else {
			if (isset($_GET['weblog_id'])) {
				$weblog_id = intval($_GET['weblog_id']);
				$Weblog = new Weblog($weblog_id);
				if ($Weblog->weblog) {
					if ($Weblog->blg_uid == $p->User->usr_id) {
						$p->vxHead($msgSiteTitle = '修改博客网站图标');
						$p->vxBodyStart();
						$p->vxTop();
						$p->vxContainer('blog_portrait', $Weblog);
						break;
					} else {
						$_SESSION['babel_message_weblog'] = '你没有权力对这个博客网站进行操作';
						die($p->URL->vxToRedirect($p->URL->vxGetBlogAdmin()));
						break;
					}
				} else {
					die($p->URL->vxToRedirect($p->URL->vxGetBlogAdmin()));
					break;
				}
			} else {
				die($p->URL->vxToRedirect($p->URL->vxGetBlogAdmin()));
				break;
			}
		}
		
	case 'blog_config':
		if ($p->User->vxIsLogin()) {
			if (isset($_GET['weblog_id'])) {
				$weblog_id = intval($_GET['weblog_id']);
				$Weblog = new Weblog($weblog_id);
				if ($Weblog->weblog) {
					if ($Weblog->blg_uid == $p->User->usr_id) {
						$p->vxHead($msgSiteTitle = '设置博客网站');
						$p->vxBodyStart();
						$p->vxTop();
						$p->vxContainer('blog_config', $Weblog);
						break;
					} else {
						$_SESSION['babel_message_weblog'] = '你没有权力对这个博客网站进行操作';
						die($p->URL->vxToRedirect($p->URL->vxGetBlogAdmin()));
						break;
					}
				} else {
					$_SESSION['babel_message_weblog'] = '指定的博客网站没有找到';
					die($p->URL->vxToRedirect($p->URL->vxGetBlogAdmin()));
					break;
				}
			} else {
				die($p->URL->vxToRedirect($p->URL->vxGetBlogAdmin()));
				break;
			}
		} else {
			if (isset($_GET['weblog_id'])) {
				$weblog_id = intval($_GET['weblog_id']);
				die($p->URL->vxToRedirect($p->URL->vxGetLogin($p->URL->vxGetBlogConfig($weblog_id))));
				break;
			} else {
				die($p->URL->vxToRedirect($p->URL->vxGetLogin($p->URL->vxGetBlogAdmin())));
				break;
			}
		}
		
	case 'blog_config_save':
		if (!$p->User->vxIsLogin()) {
			if (isset($_GET['weblog_id'])) {
				$weblog_id = intval($_GET['weblog_id']);
				die($p->URL->vxToRedirect($p->URL->vxGetLogin($p->URL->vxGetBlogConfig($weblog_id))));
			} else {
				die($p->URL->vxToRedirect($p->URL->vxGetLogin($p->URL->vxGetBlogAdmin())));
			}
			break;
		} else {
			if (isset($_GET['weblog_id'])) {
				$weblog_id = intval($_GET['weblog_id']);
				if (Weblog::vxMatchPermission($p->User->usr_id, $weblog_id)) {
					$rt = $p->Validator->vxBlogConfigCheck($p->User->usr_money, $weblog_id);
					if ($rt['errors'] == 0) {
						$p->Validator->vxBlogConfigUpdate($weblog_id, $rt['blg_title_value'], $rt['blg_description_value'], $rt['blg_mode_value'], $rt['blg_comment_permission_value']);
						die($p->URL->vxToRedirect($p->URL->vxGetBlogAdmin()));
						break;
					} else {
						$p->vxHead($msgSiteTitle = '设置博客网站');
						$p->vxBodyStart();
						$p->vxTop();
						$p->vxContainer('blog_config_save', $rt);
						break;
					}
				} else {
					$_SESSION['babel_message_weblog'] = '你没有权力对这个博客网站进行操作';
					die($p->URL->vxToRedirect($p->URL->vxGetBlogAdmin()));
				}
			} else {
				die($p->URL->vxToRedirect($p->URL->vxGetLogin($p->URL->vxGetBlogAdmin())));
			}
			break;
		}
		
	case 'blog_compose':
		if (!$p->User->vxIsLogin()) {
			if (isset($_GET['weblog_id'])) {
				$weblog_id = intval($_GET['weblog_id']);
				die($p->URL->vxToRedirect($p->URL->vxGetLogin($p->URL->vxGetBlogCompose($weblog_id))));
			} else {
				die($p->URL->vxToRedirect($p->URL->vxGetLogin($p->URL->vxGetBlogAdmin())));
			}
			break;
		} else {
			if (isset($_GET['weblog_id'])) {
				$weblog_id = intval($_GET['weblog_id']);
				$Weblog = new Weblog($weblog_id);
				if ($Weblog->weblog) {
					if ($Weblog->blg_uid == $p->User->usr_id) {
						$p->vxHead($msgSiteTitle = '撰写新文章');
						$p->vxBodyStart();
						$p->vxTop();
						$p->vxContainer('blog_compose', $Weblog);
						break;
					} else {
						$_SESSION['babel_message_weblog'] = '你没有权力对这个博客网站进行操作';
						die($p->URL->vxToRedirect($p->URL->vxGetBlogAdmin()));
						break;
					}
				} else {
					$_SESSION['babel_message_weblog'] = '指定的博客网站没有找到';
					die($p->URL->vxToRedirect($p->URL->vxGetBlogAdmin()));
					break;
				}
			} else {
				die($p->URL->vxToRedirect($p->URL->vxGetBlogAdmin()));
				break;
			}
		}
		
	case 'blog_compose_save':
		if (!$p->User->vxIsLogin()) {
			if (isset($_GET['weblog_id'])) {
				$weblog_id = intval($_GET['weblog_id']);
				die($p->URL->vxToRedirect($p->URL->vxGetLogin($p->URL->vxGetBlogCompose($weblog_id))));
			} else {
				$_SESSION['babel_message_weblog'] = '指定的博客网站没有找到';
				die($p->URL->vxToRedirect($p->URL->vxGetLogin($p->URL->vxGetBlogAdmin())));
			}
			break;
		} else {
			if (isset($_GET['weblog_id'])) {
				$weblog_id = intval($_GET['weblog_id']);
				$Weblog = new Weblog($weblog_id);
				if ($Weblog->weblog) {
					if ($Weblog->blg_uid == $p->User->usr_id) {
						$rt = $p->Validator->vxBlogComposeCheck();
						if ($rt['errors'] == 0) {
							$p->Validator->vxBlogComposeInsert($p->User->usr_id, $Weblog->blg_id, $rt['bge_title_value'], $rt['bge_body_value'], $rt['bge_mode_value'], $rt['bge_comment_permission_value'], $rt['bge_status_value']);
							$Weblog->vxUpdateEntries();
							$sql = "SELECT bge_id FROM babel_weblog_entry WHERE bge_uid = {$p->User->usr_id} ORDER BY bge_created DESC LIMIT 1";
							$rs = mysql_query($sql);
							$entry_id = mysql_result($rs, 0, 0);
							mysql_free_result($rs);
							Weblog::vxBuild($p->User->usr_id, $Weblog->blg_id);
							die($p->URL->vxToRedirect($p->URL->vxGetBlogList($Weblog->blg_id)));
							break;
						} else {
							$rt['Weblog'] = $Weblog;
							$p->vxHead($msgSiteTitle = '撰写新文章');
							$p->vxBodyStart();
							$p->vxTop();
							$p->vxContainer('blog_compose_save', $rt);
							break;
						}
						break;
					} else {
						$_SESSION['babel_message_weblog'] = '你没有权力对这个博客网站进行操作';
						die($p->URL->vxToRedirect($p->URL->vxGetBlogAdmin()));
						break;
					}
				} else {
					$_SESSION['babel_message_weblog'] = '指定的博客网站没有找到';
					die($p->URL->vxToRedirect($p->URL->vxGetBlogAdmin()));
					break;
				}
			} else {
				$_SESSION['babel_message_weblog'] = '指定的博客网站没有找到';
				die($p->URL->vxToRedirect($p->URL->vxGetBlogAdmin()));
				break;
			}
		}
	
	case 'blog_list':
		if ($p->User->vxIsLogin()) {
			if (isset($_GET['weblog_id'])) {
				$weblog_id = intval($_GET['weblog_id']);
				$Weblog = new Weblog($weblog_id);
				if ($Weblog->weblog) {
					if ($Weblog->blg_uid == $p->User->usr_id) {
						$p->vxHead($msgSiteTitle = '管理文章');
						$p->vxBodyStart();
						$p->vxTop();
						$p->vxContainer('blog_list', $Weblog);
						break;
					} else {
						$_SESSION['babel_message_weblog'] = '你没有权力对这个博客网站进行操作';
						die($p->URL->vxToRedirect($p->URL->vxGetBlogAdmin()));
						break;
					}
				} else {
					$_SESSION['babel_message_weblog'] = '指定的博客网站没有找到';
					die($p->URL->vxToRedirect($p->URL->vxGetBlogAdmin()));
					break;
				}
			} else {
				die($p->URL->vxToRedirect($p->URL->vxGetBlogAdmin()));
				break;
			}
		} else {
			if (isset($_GET['weblog_id'])) {
				$weblog_id = intval($_GET['weblog_id']);
				die($p->URL->vxToRedirect($p->URL->vxGetLogin($p->URL->vxGetBlogList($weblog_id))));
				break;
			} else {
				die($p->URL->vxToRedirect($p->URL->vxGetLogin($p->URL->vxGetBlogAdmin())));
				break;
			}
		}
		
	case 'blog_edit':
		if ($p->User->vxIsLogin()) {
			if (isset($_GET['entry_id'])) {
				$entry_id = intval($_GET['entry_id']);
				$Entry = new Entry($entry_id);
				if ($Entry->entry) {
					if ($Entry->bge_uid == $p->User->usr_id) {
						$p->vxHead($msgSiteTitle = '编辑文章');
						$p->vxBodyStart();
						$p->vxTop();
						$p->vxContainer('blog_edit', $Entry);
						break;
					} else {
						$_SESSION['babel_message_weblog'] = '你没有权力对这个博客网站的文章进行操作';
						die($p->URL->vxToRedirect($p->URL->vxGetBlogAdmin()));
						break;
					}
				} else {
					$_SESSION['babel_message_weblog'] = '指定的文章没有找到';
					die($p->URL->vxToRedirect($p->URL->vxGetBlogAdmin()));
					break;
				}
			} else {
				die($p->URL->vxToRedirect($p->URL->vxGetBlogAdmin()));
				break;
			}
		} else {
			if (isset($_GET['entry_id'])) {
				$entry_id = intval($_GET['entry_id']);
				die($p->URL->vxToRedirect($p->URL->vxGetLogin($p->URL->vxGetBlogEdit($entry_id))));
				break;
			} else {
				die($p->URL->vxToRedirect($p->URL->vxGetLogin($p->URL->vxGetBlogAdmin())));
				break;
			}
		}
}

if ($global_has_bottom) {
	$p->vxBottom();
}
$p->vxBodyEnd();
?>
