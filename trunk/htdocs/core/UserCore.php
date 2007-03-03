<?php
/* Project Babel
*  Author: Livid Torvalds
*  File: /htdocs/core/UserCore.php
*  Usage: User Class
*  Format: 1 tab indent(4 spaces), LF, UTF-8, no-BOM
*
*  Subversion Keywords:
*
*  $Id$
*  $LastChangedDate$
*  $LastChangedRevision$
*  $LastChangedBy$
*  $URL$
*/

if (@V2EX_BABEL != 1) {
	die('<strong>Project Babel</strong><br /><br />Made by <a href="http://www.v2ex.com/">V2EX</a> | software for internet');
}

/* S User class */

class User {
	public $db;
	public $bf;

	public $usr_id;
	public $usr_gid;
	public $usr_email;
	public $usr_email_notify;
	public $usr_geo;
	public $usr_password;
	public $usr_nick;
	public $usr_full;
	public $usr_addr;
	public $usr_telephone;
	public $usr_identity;
	public $usr_gender;
	public $usr_brief;
	public $usr_portrait;
	public $usr_money;
	public $usr_width;
	public $usr_hits;
	public $usr_logins;
	public $usr_created;
	public $usr_sw_shell;
	public $usr_sw_notify_reply;
	public $usr_sw_notify_reply_all;
	public $usr_lastlogin;
	public $usr_money_a;
	public $usr_gender_a;
	public $usr_expense_type_msg;
	
	public function __construct($usr_email, $usr_password, $db, $session = true) {
		$this->usr_id = 0;
		$this->usr_gid = 0;
		$this->usr_email = '';
		$this->usr_email_notify = '';
		$this->usr_geo = 'earth';
		$this->usr_password = '';
		$this->usr_nick = '';
		$this->usr_full = '';
		$this->usr_addr = '';
		$this->usr_telephone = '';
		$this->usr_identity = '';
		$this->usr_gender = 0;
		$this->usr_brief = '';
		$this->usr_portrait = '';
		$this->usr_money = 0;
		$this->usr_width = 1024;
		$this->usr_hits = 0;
		$this->usr_logins = 0;
		$this->usr_created = 0;
		$this->usr_sw_shell = 0;
		$this->usr_sw_notify_reply = 0;
		$this->usr_sw_notify_reply_all = 0;
		$this->usr_lastlogin = 0;
		$this->usr_lastlogin_ua = '';
		$this->usr_money_a = array();
		$this->usr_gender_a = array(0 => '未知', 1 => '男性', 2 => '女性', 5 => '女性改（变）为男性', 6 => '男性改（变）为女性', 9 => '未说明');
		$this->usr_gender_a_fun = array(0 => '不知道是男是女', 1 => '男', 2 => '女', 5 => '出生的时候是个女孩子，后来成了一个男生', 6 => '出生的时候是个男生，后来变成了一个女孩子', 9 => '不想说自己是男是女');
		/* exp_type:
		0 => Mystery Payment
		1 => Signup Initial
		2 => Topic Create
		3 => Post Create
		4 => Gain From Replied Topic
		5 => Loopback
		999 => Mystery Income */
		$this->usr_expense_type_msg = array(0 => '神秘的支出', 1 => '注册得到启动资金', 2 => '创建新主题', 3 => '回复别人创建的主题', 4 => '主题被别人回复', 5 => '回复自己创建的主题', 6 => '修改主题', 7 => '主题利息收入', 8 => '发送社区短消息', 9 => '社区奖励', 100 => '百页斩', 999 => '神秘的收入', 1000 => '千页斩');
		
		$this->db = $db;
		
		$this->bf = new Crypt_Blowfish(BABEL_BLOWFISH_KEY);
		
		$e = 0;

		if (strlen($usr_email) > 0 && strlen($usr_password) > 0) {
			$sql = "SELECT usr_id, usr_gid, usr_email, usr_email_notify, usr_geo, usr_password, usr_nick, usr_full, usr_addr, usr_telephone, usr_identity, usr_gender, usr_brief, usr_portrait, usr_money, usr_width, usr_hits, usr_logins, usr_created, usr_sw_shell, usr_sw_notify_reply, usr_sw_notify_reply_all, usr_lastlogin, usr_lastlogin_ua FROM babel_user WHERE usr_email = '{$usr_email}' AND usr_password = '{$usr_password}'";
			$rs = mysql_query($sql, $this->db);
			if (mysql_num_rows($rs) == 1) {
				$O = mysql_fetch_object($rs);
				$this->usr_id = $O->usr_id;
				$this->usr_gid = $O->usr_gid;
				$this->usr_email = $O->usr_email;
				$this->usr_email_notify = $O->usr_email_notify;
				$this->usr_geo = $O->usr_geo;
				$this->usr_password = $O->usr_password;
				$this->usr_nick = $O->usr_nick;
				$this->usr_full = $O->usr_full;
				$this->usr_addr = $O->usr_addr;
				$this->usr_telephone = $O->usr_telephone;
				$this->usr_identity = $O->usr_identity;
				$this->usr_gender = $O->usr_gender;
				$this->usr_brief = $O->usr_brief;
				$this->usr_portrait = $O->usr_portrait;
				$this->usr_money = $O->usr_money;
				$this->usr_width = $O->usr_width;
				$this->usr_hits = $O->usr_hits;
				$this->usr_logins = $O->usr_logins;
				$this->usr_created = $O->usr_created;
				$this->usr_sw_shell = $O->usr_sw_shell;
				$this->usr_sw_notify_reply = $O->usr_sw_notify_reply;
				$this->usr_sw_notify_reply_all = $O->usr_sw_notify_reply_all;
				$this->usr_lastlogin = $O->usr_lastlogin;
				$this->usr_lastlogin_ua = $O->usr_lastlogin_ua;
				$this->usr_money_a = $this->vxParseMoney();
				if ($session) {
					$this->vxSessionStart();
				}
				$O = null;
			} else {
				$e++;
			}
		} else {
			if (isset($_COOKIE['babel_usr_email']) && isset($_COOKIE['babel_usr_password'])) {
				if (!strlen($_COOKIE['babel_usr_email']) > 0) {
					$e++;
				}
				if (!strlen($_COOKIE['babel_usr_password']) > 0) {
					$e++;
				}
			} else {
				$e++;
			}
		
			if ($e == 0) {
				if (get_magic_quotes_gpc()) {
					$real_usr_email = mysql_real_escape_string(stripslashes($_COOKIE['babel_usr_email']));
					$real_usr_password = mysql_real_escape_string($this->bf->decrypt(stripslashes($_COOKIE['babel_usr_password'])));
				} else {
					$real_usr_email = mysql_real_escape_string($_COOKIE['babel_usr_email']);
					$real_usr_password = mysql_real_escape_string($this->bf->decrypt($_COOKIE['babel_usr_password']));
				}
				$sql = "SELECT usr_id, usr_gid, usr_email, usr_email_notify, usr_geo, usr_password, usr_nick, usr_full, usr_addr, usr_telephone, usr_identity, usr_gender, usr_brief, usr_portrait, usr_money, usr_width, usr_hits, usr_logins, usr_created, usr_sw_shell, usr_sw_notify_reply, usr_sw_notify_reply_all, usr_lastlogin, usr_lastlogin_ua FROM babel_user WHERE usr_email = '" . $real_usr_email . "' AND usr_password = '" . $real_usr_password . "'";
				$rs = mysql_query($sql, $this->db);
				if (mysql_num_rows($rs) == 1) {
					$O = mysql_fetch_object($rs);
					$this->usr_id = $O->usr_id;
					$this->usr_gid = $O->usr_gid;
					$this->usr_email = $O->usr_email;
					$this->usr_email_notify = $O->usr_email_notify;
					$this->usr_geo = $O->usr_geo;
					$this->usr_password = $O->usr_password;
					$this->usr_nick = $O->usr_nick;
					$this->usr_full = $O->usr_full;
					$this->usr_addr = $O->usr_addr;
					$this->usr_telephone = $O->usr_telephone;
					$this->usr_identity = $O->usr_identity;
					$this->usr_gender = $O->usr_gender;
					$this->usr_brief = $O->usr_brief;
					$this->usr_portrait = $O->usr_portrait;
					$this->usr_money = $O->usr_money;
					$this->usr_width = $O->usr_width;
					$this->usr_hits = $O->usr_hits;
					$this->usr_logins = $O->usr_logins;
					$this->usr_created = $O->usr_created;
					$this->usr_sw_shell = $O->usr_sw_shell;
					$this->usr_sw_notify_reply = $O->usr_sw_notify_reply;
					$this->usr_sw_notify_reply_all = $O->usr_sw_notify_reply_all;
					$this->usr_lastlogin = $O->usr_lastlogin;
					$this->usr_lastlogin_ua = $O->usr_lastlogin_ua;
					$this->usr_money_a = $this->vxParseMoney();
					if ($session) {
						$this->vxSessionStart();
					}
					$O = null;
				} else {
					//
				}
				mysql_free_result($rs);
			}
		}
	}
	
	public function __destruct() {
	}
	
	public function vxSessionStart() {
		setcookie('babel_usr_email', $this->usr_email, time() + 2678400, '/', BABEL_DNS_DOMAIN);
		setcookie('babel_usr_password', $this->bf->encrypt($this->usr_password), time() + 2678400, '/', BABEL_DNS_DOMAIN);
		$_SESSION['babel_usr_email'] = $this->usr_email;
		$_SESSION['babel_usr_password'] = $this->usr_password;
	}
	
	public function vxLogout() {
		$this->usr_id = 0;
		$this->usr_gid = 0;
		$this->usr_email = '';
		$this->usr_email_notify = '';
		$this->usr_geo = 'earth';
		$this->usr_password = '';
		$this->usr_nick = '';
		$this->usr_full = '';
		$this->usr_addr = '';
		$this->usr_telephone = '';
		$this->usr_identity = '';
		$this->usr_gender = 0;
		$this->usr_brief = '';
		$this->usr_portrait = '';
		$this->usr_hits = 0;
		$this->usr_logins = 0;
		$this->usr_created = 0;
		$this->usr_sw_shell = 0;
		$this->usr_sw_notify_reply = 0;
		$this->usr_lastlogin = 0;
		$this->usr_money = 0;
		$this->usr_width = 1024;
		$this->usr_money_a = array();
		setcookie('babel_usr_email', '', 0, '/', BABEL_DNS_DOMAIN);
		setcookie('babel_usr_password', '', 0, '/', BABEL_DNS_DOMAIN);
		setcookie('babel_usr_email', '', 0, '/');
		setcookie('babel_usr_password', '', 0, '/');
	}
	
	public function vxIsLogin() {
		if ($this->usr_id != 0) {
			return true;
		} else {
			return false;
		}
	}
	
	public function vxGetUserInfo($user_id) {
		$sql = "SELECT usr_id, usr_gender, usr_nick, usr_brief, usr_email, usr_portrait, usr_created, usr_lastlogin FROM babel_user WHERE usr_id = {$user_id}";
		$rs = mysql_query($sql, $this->db);
		$User = mysql_fetch_object($rs);
		mysql_free_result($rs);
		return $User;
	}
	
	public function vxAddHits($user_id) {
		$sql = "SELECT COUNT(*) FROM babel_online WHERE onl_ip = '" . $_SERVER['REMOTE_ADDR'] . "'";
		$rs = mysql_query($sql);
		if (mysql_result($rs, 0, 0) < 3) {
			mysql_free_result($rs);
			$sql = "UPDATE babel_user SET usr_hits = (usr_hits + 1) WHERE usr_id = {$user_id} LIMIT 1";
			mysql_query($sql, $this->db);
			if (mysql_affected_rows($this->db) == 1) {
				return true;
			} else {
				return false;
			}
		} else {
			mysql_free_result($rs);
		}
	}
	
	public function vxUpdateLogin($user_id = 0) {
		if ($user_id == 0) {
			$user_id = $this->usr_id;
		}
		$_time = time();
		$_ua = mysql_real_escape_string($_SESSION['babel_ua']['ua']);
		$_sql = "UPDATE babel_user SET usr_lastlogin = {$_time}, usr_lastlogin_ua = '{$_ua}', usr_logins = usr_logins + 1 WHERE usr_id = {$user_id}";
		mysql_unbuffered_query($_sql);
	}
	
	public function vxParseMoney($money = '') {
		if ($money == '') {
			$money = $this->usr_money;
		}
		
		$usr_money_a = array();
		
		$usr_money_a['total'] = $money;
		
		/* now start parsing:
		g -> Gold
		s -> Silver
		c -> Copper */
		if ($money >= 10000) {
			$g = intval($money / 10000);
			$usr_money_a['g'] = $g;
			$r = $money - ($g * 10000);
			if ($r > 100) {
				$s = intval($r / 100);
				$usr_money_a['s'] = $s;
				$r = $r - ($s * 100);
				if ($r > 10) {
					$usr_money_a['c'] = substr($r, 0, 5);
				} else {
					$usr_money_a['c'] = substr($r, 0, 4);
				}
			} else {
				$usr_money_a['s'] = 0;
				if ($r > 10) {
					$usr_money_a['c'] = substr($r, 0, 5);
				} else {
					$usr_money_a['c'] = substr($r, 0, 4);
				}
			}
		} else {
			$usr_money_a['g'] = 0;
			if ($money >= 100) {
				$s = intval($money / 100);
				$usr_money_a['s'] = $s;
				$r = $money - ($s * 100);
				if ($r > 10) {
					$usr_money_a['c'] = substr($r, 0, 5);
				} else {
					$usr_money_a['c'] = substr($r, 0, 4);
				}
			} else {
				$usr_moeny_a['g'] = 0;
				$usr_money_a['s'] = 0;
				if ($money > 10) {
					$usr_money_a['c'] = substr($money, 0, 5);
				} else {
					$usr_money_a['c'] = substr($money, 0, 4);
				}
			}
		}
		
		/* translate it into a descriptive string */
		if ($usr_money_a['g'] > 0) {
			$g_str = ' ' . $usr_money_a['g'] . ' 金币';
		} else {
			$g_str = '';
		}
		
		if ($usr_money_a['s'] > 0) {
			$s_str = ' ' . $usr_money_a['s'] . ' 银币';
		} else {
			$s_str = '';
		}
		
		if ($usr_money_a['c'] > 0) {
			$c_str = ' ' . $usr_money_a['c'] . ' 铜币';
		} else {
			$c_str = '';
		}
		
		$usr_money_a['str'] = $g_str . $s_str . $c_str;
		if ($usr_money_a['total'] == 0) {
			$usr_money_a['str'] = '身无分文';
		}
		
		return $usr_money_a;
	}
	
	/* S expense modules */
	
	/* S module: Pay Logic */
	
	/* exp_type:
	0 => Mystery Payment
	1 => Signup Initial
	2 => Topic Create
	3 => Post Create
	4 => Gain From Replied Topic
	5 => Loopback
	9 => Community Bonus
	100 => Hundred Kills
	999 => Mystery Income 
	1000 => Thousand Kills
	*/
	
	public function vxPay($user_id, $amount, $type, $memo = '', $other_id = 0) {
		if ($amount != 0) {
			$sql = "SELECT usr_id, usr_money FROM babel_user WHERE usr_id = {$user_id}";
			$rs = mysql_query($sql, $this->db);
			$User = mysql_fetch_object($rs);
			mysql_free_result($rs);
			$usr_money = $User->usr_money + $amount;
			$sql = "UPDATE babel_user SET usr_money = {$usr_money} WHERE usr_id = {$user_id} LIMIT 1";
			mysql_query($sql, $this->db);
			if (mysql_affected_rows($this->db) == 1) {
				$sql = "INSERT INTO babel_expense(exp_uid, exp_amount, exp_type, exp_memo, exp_created) VALUES({$user_id}, {$amount}, {$type}, '{$memo}', " . time() . ")";
				mysql_query($sql, $this->db);
				if (mysql_affected_rows($this->db) == 1) {
					if ($type != 3) {
						return true;
					} else {
						$amount = abs($amount);
						$sql = "SELECT usr_id, usr_money FROM babel_user WHERE usr_id = {$other_id}";
						$rs = mysql_query($sql, $this->db);
						$User = mysql_fetch_object($rs);
						mysql_free_result($rs);
						$usr_money = $User->usr_money + $amount;
						$sql = "UPDATE babel_user SET usr_money = {$usr_money} WHERE usr_id = {$User->usr_id} LIMIT 1";
						mysql_query($sql, $this->db);
						if (mysql_affected_rows($this->db) == 1) {
							$sql = "INSERT INTO babel_expense(exp_uid, exp_amount, exp_type, exp_created) VALUES({$User->usr_id}, {$amount}, 4, " . time() . ")";
							mysql_query($sql, $this->db);
							if (mysql_affected_rows($this->db) == 1) {
								return true;
							} else {
								return false;
							}
						} else {
							return false;
						}
					}
				} else {
					return false;
				}
			} else {
				return false;
			}
		}
	}
	
	/* E expense modules */
}

/* E User class */
?>
