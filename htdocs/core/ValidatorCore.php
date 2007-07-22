<?php
/* Project Babel
*  Author: Livid Torvalds
*  File: /htdocs/core/ValidatorCore.php
*  Usage: Validator Class
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

/* S Validator class */

class Validator {
	public $db;
	
	public $User;
	
	public function __construct($db, $User) {
		$this->db =& $db;
		$this->User =& $User;
	}
	
	public function vxExistNode($node_id) {
		$sql = "SELECT nod_id FROM babel_node WHERE nod_id = {$node_id}";
		$rs = mysql_query($sql, $this->db);
		if (mysql_num_rows($rs) == 1) {
			mysql_free_result($rs);
			return true;
		} else {
			mysql_free_result($rs);
			return false;
		}
	}
	
	public function vxExistBoardName($node_name) {
		$node_name = mysql_real_escape_string(trim($node_name));
		
		if ($node_name != '') {
			$sql = "SELECT nod_id, nod_pid, nod_title, nod_name, nod_level, nod_header, nod_footer FROM babel_node WHERE nod_name = '{$node_name}' AND nod_level = 2";
			$rs = mysql_query($sql);
			if (mysql_num_rows($rs) == 1) {
				$Node = mysql_fetch_object($rs);
				mysql_free_result($rs);
				$sql = "SELECT nod_id, nod_title, nod_name FROM babel_node WHERE nod_id = {$Node->nod_pid}";
				$rs = mysql_query($sql);
				$Section = mysql_fetch_object($rs);
				mysql_free_result($rs);
				$Node->sect_id = $Section->nod_id;
				$Node->sect_name = $Section->nod_name;
				$Node->sect_title = $Section->nod_title;
				$Section = null;
				return $Node;
			} else {
				mysql_free_result($rs);
				return false;
			}
		} else {
			return false;
		}
	}
	
	public function vxIsDangerousTopic($topic_id, $c) {
		if ($d = $c->get('dangerous_topics')) {
			$d = unserialize($d);
		} else {
			$xml = simplexml_load_file(BABEL_PREFIX . '/res/dangerous.xml');
			$d = array();
			foreach ($xml->topics->topic as $topic) {
				$d[] = intval($topic);
			}
			$c->save(serialize($d), 'dangerous_topics');
		}
		if (in_array($topic_id, $d)) {
			return true;
		} else {
			return false;
		}
	}
	
	public function vxIsAutisticNode($node_id, $c) {
		if ($d = $c->get('autistic_nodes')) {
			$d = unserialize($d);
		} else {
			$xml = simplexml_load_file(BABEL_PREFIX . '/res/autistic.xml');
			$d = array();
			foreach ($xml->nodes->node as $node) {
				$d[] = intval($node);
			}
			$c->save(serialize($d), 'autistic_nodes');
		}
		if (in_array($node_id, $d)) {
			return true;
		} else {
			return false;
		}
	}
	
	public function vxExistTopic($topic_id) {
		$sql = "SELECT tpc_id FROM babel_topic WHERE tpc_id = {$topic_id}";
		$rs = mysql_query($sql, $this->db);
		if (mysql_num_rows($rs) == 1) {
			mysql_free_result($rs);
			return true;
		} else {
			mysql_free_result($rs);
			return false;
		}
	}
	
	public function vxExistPost($post_id) {
		$sql = "SELECT pst_id FROM babel_post WHERE pst_id = {$post_id}";
		$rs = mysql_query($sql, $this->db);
		if (mysql_num_rows($rs) == 1) {
			mysql_free_result($rs);
			return true;
		} else {
			mysql_free_result($rs);
			return false;
		}
	}
	
	public function vxExistUser($user_id) {
		$sql = "SELECT usr_id FROM babel_user WHERE usr_id = {$user_id}";
		$rs = mysql_query($sql, $this->db);
		if (mysql_num_rows($rs) == 1) {
			mysql_free_result($rs);
			return true;
		} else {
			mysql_free_result($rs);
			return false;
		}
	}
	
	public function vxExistUserNick($user_nick) {
		$user_nick = mysql_real_escape_string($user_nick, $this->db);
		$sql = "SELECT usr_id FROM babel_user WHERE usr_nick = '{$user_nick}'";
		$rs = mysql_query($sql, $this->db);
		if (mysql_num_rows($rs) == 1) {
			$_r = mysql_result($rs, 0, 0);
			mysql_free_result($rs);
			return $_r;
		} else {
			mysql_free_result($rs);
			return false;
		}
	}
	
	public function vxExistOnline($user_nick) {
		$user_nick = mysql_real_escape_string($user_nick, $this->db);
		$sql = "SELECT onl_uri, onl_nick, onl_ip, onl_created, onl_lastmoved FROM babel_online WHERE onl_nick = '{$user_nick}' ORDER BY onl_lastmoved DESC LIMIT 1";
		$rs = mysql_query($sql, $this->db);
		if (mysql_num_rows($rs) == 1) {
			$_r = mysql_fetch_object($rs);
			mysql_free_result($rs);
			return $_r;
		} else {
			mysql_free_result($rs);
			return false;
		}
	}
	
	public function vxExistChannel($channel_id) {
		$sql = "SELECT chl_id FROM babel_channel WHERE chl_id = {$channel_id}";
		$rs = mysql_query($sql, $this->db);
		if (mysql_num_rows($rs) == 1) {
			mysql_free_result($rs);
			return true;
		} else {
			mysql_free_result($rs);
			return false;
		}
	}
	
	public function vxExistMessage($message_id) {
		$sql = "SELECT msg_id FROM babel_message WHERE msg_id = {$message_id}";
		$rs = mysql_query($sql, $this->db);
		if (mysql_num_rows($rs) == 1) {
			mysql_free_result($rs);
			return true;
		} else {
			mysql_free_result($rs);
			return false;
		}
	}
	
	/* S module: Message Create Check logic */
	
	public function vxMessageCreateCheck() {
		$rt = array();
		
		$rt['errors'] = 0;
		
		$rt['msg_receivers_value'] = '';
		/* receivers: raw */
		$rt['msg_receivers_a'] = array();
		/* receivers: validated */
		$rt['msg_receivers_v'] = array();
		/* receivers: validated names */
		$rt['msg_receivers_n'] = array();
		/* msg_receivers_error:
		0 => no error
		1 => empty
		2 => not exist
		999 => unspecific */
		$rt['msg_receivers_error'] = 0;
		$rt['msg_receivers_error_msg'] = array(1 => '你忘记写收件人了', 2 => '你写的一位或多位收件人不存在');
		
		if (isset($_POST['msg_receivers'])) {
			$rt['msg_receivers_value'] = make_single_safe($_POST['msg_receivers']);
			if (strlen($rt['msg_receivers_value']) > 0) {
				$rt['msg_receivers_a'] = explode(',', $rt['msg_receivers_value']);
				foreach ($rt['msg_receivers_a'] as $msg_receiver) {
					$msg_receiver = trim($msg_receiver);
					$sql = "SELECT usr_id, usr_nick FROM babel_user WHERE usr_nick = '{$msg_receiver}'";
					$rs = mysql_query($sql, $this->db);
					if (mysql_num_rows($rs) == 1) {
						$User = mysql_fetch_object($rs);
						mysql_free_result($rs);
						if ($User->usr_id != $this->User->usr_id) {
							if (!in_array($User->usr_id, $rt['msg_receivers_v'])) {
								$rt['msg_receivers_v'][] = $User->usr_id;
								$rt['msg_receivers_n'][] = $User->usr_nick;
							}
						}
					} else {
						mysql_free_result($rs);
						$rt['msg_receivers_error'] = 2;
						$rt['errors']++;
						break;
					}
				}
				if ($rt['msg_receivers_error'] == 0) {
					if (count($rt['msg_receivers_v']) == 0) {
						$rt['msg_receivers_value'] = '';
						$rt['msg_receivers_error'] = 1;
						$rt['errors']++;
					} else {
						$rt['msg_receivers_value'] = implode(',', $rt['msg_receivers_n']);
					}
				}
			} else {
				$rt['msg_receivers_error'] = 1;
				$rt['errors']++;
			}
		} else {
			$rt['msg_receivers_error'] = 1;
			$rt['errors']++;
		}
		
		$rt['msg_body_value'] = '';
		$rt['msg_body_error'] = 0;
		$rt['msg_body_error_msg'] = array(1 => '你忘记写消息内容了', 2 => '你写的消息内容超出长度限制了');
		
		if (isset($_POST['msg_body'])) {
			$rt['msg_body_value'] = make_multi_safe($_POST['msg_body']);
			$rt['msg_body_length'] = mb_strlen($rt['msg_body_value'], 'UTF-8');
			if ($rt['msg_body_length'] > 0) {
				if ($rt['msg_body_length'] > 200) {
					$rt['msg_body_error'] = 2;
					$rt['errors']++;
				}
			} else {
				$rt['msg_body_error'] = 1;
				$rt['errors']++;
			}
		} else {
			$rt['msg_body_error'] = 1;
			$rt['errors']++;
		}
		
		return $rt;
	}
	
	/* E module: Message Create Check logic */
	
	/* S module: Message Create Insert logic */
	
	public function vxMessageCreateInsert($sender_id, $receiver_id, $msg_body, $exp_memo, $expense_amount = BABEL_MSG_PRICE) {
		$t = time();
		if (get_magic_quotes_gpc()) {
			$msg_body = mysql_real_escape_string(stripslashes($msg_body));
		} else {
			$msg_body = mysql_real_escape_string($msg_body);
		}
		$sql = "INSERT INTO babel_message(msg_sid, msg_rid, msg_body, msg_created, msg_sent) VALUES({$sender_id}, {$receiver_id}, '{$msg_body}', {$t}, {$t})";
		mysql_query($sql, $this->db);
		if (mysql_affected_rows($this->db) == 1) {
			return $this->User->vxPay($this->User->usr_id, -$expense_amount, 8, $exp_memo);
		} else {
			die(mysql_error());
			return false;
		}
	}
	
	/* E module: Message Create Insert logic */
	
	/* S module: Login Check logic */
	
	public function vxLoginCheck() {
		$rt = array();
		
		$rt['target'] = 'welcome';
		$rt['return'] = '';
		
		$rt['errors'] = 0;
		
		$rt['usr_value'] = '';
		$rt['usr_email_value'] = '';
		/* usr_error:
		0 => no error
		1 => empty
		999 => unspecific */
		$rt['usr_error'] = 0;
		$rt['usr_error_msg'] = array(1 => '你忘记填写名字了');
		
		$rt['usr_password_value'] = '';
		/* usr_password_error:
		0 => no error
		1 => empty
		2 => mismatch
		999 => unspecific */
		$rt['usr_password_error'] = 0;
		$rt['usr_password_error_msg'] = array(1 => '你忘记填写密码了', 2 => '名字或者密码有错误');

		if (isset($_POST['return'])) {
			if (get_magic_quotes_gpc()) {
				$rt['return'] = trim(stripslashes($_POST['return']));
			} else {
				$rt['return'] = trim($_POST['return']);
			}
		}
		
		if (isset($_POST['usr'])) {
			if (get_magic_quotes_gpc()) {
				$rt['usr_value'] = strtolower(make_single_safe(stripslashes($_POST['usr'])));
			} else {
				$rt['usr_value'] = strtolower(make_single_safe($_POST['usr']));
			}
			if (strlen($rt['usr_value']) == 0) {
				$rt['usr_error'] = 1;
				$rt['errors']++;
			}
		} else {
			$rt['usr_error'] = 1;
			$rt['errors']++;
		}
		
		if ($rt['errors'] > 0) {
			$rt['target'] = 'error';
			return $rt;
		}
		
		if (isset($_POST['usr_password'])) {
			if (get_magic_quotes_gpc()) {
				$rt['usr_password_value'] = make_single_safe(stripslashes($_POST['usr_password']));
			} else {
				$rt['usr_password_value'] = make_single_safe($_POST['usr_password']);
			}
			if (strlen($rt['usr_password_value']) == 0) {
				$rt['usr_password_error'] = 1;
				$rt['errors']++;
			}
		} else {
			$rt['usr_password_error'] = 1;
			$rt['errors']++;
		}
		
		if ($rt['errors'] > 0) {
			$rt['target'] = 'error';
			return $rt;
		}
		
		$sql = "SELECT usr_id FROM babel_user WHERE usr_email = '" . mysql_real_escape_string($rt['usr_value']) . "' AND usr_password = '" . mysql_real_escape_string(sha1($rt['usr_password_value'])) . "'";
		$rs = mysql_query($sql, $this->db);
		if (mysql_num_rows($rs) == 1) {
			mysql_free_result($rs);
			$rt['usr_email_value'] = $rt['usr_value'];
			$rt['target'] = 'ok';
		} else {
			mysql_free_result($rs);
			$sql = "SELECT usr_id, usr_email FROM babel_user WHERE usr_nick = '" . mysql_real_escape_string($rt['usr_value']) . "' AND usr_password = '" . mysql_real_escape_string(sha1($rt['usr_password_value'])) . "'";
			$rs = mysql_query($sql, $this->db);
			if (mysql_num_rows($rs) == 1) {		
				$O = mysql_fetch_object($rs);
				$rt['usr_email_value'] = $O->usr_email;
				$rt['target'] = 'ok';
				$O = null;
			} else {
				$rt['target'] = 'error';
				$rt['usr_password_error'] = 2;
				$rt['errors']++;
			}
			mysql_free_result($rs);
		}
		
		return $rt;
	}
	
	/* E module: Login Check logic */
	
	/* S module: URL Classified logic */
	
	public function vxGetURLHost($url) {
		$o = array();
		
		$o['type'] = 'web';
		$o['url'] = strtolower($url);

		if (preg_match('/flickr\.com/', $url)) {
			$o['type'] = 'flickr';
			return $o;
		}
		
		if (preg_match('/feedburner\.com/', $url)) {
			$o['type'] = 'feedburner';
			return $o;
		}
		
		if (preg_match('/buzznet\.com/', $url)) {
			$o['type'] = 'buzznet';
			return $o;
		}
		
		
		if (preg_match('/technorati\.com/', $url)) {
			$o['type'] = 'technorati';
			return $o;
		}
		
		if (preg_match('/douban\.com/', $url)) {
			$o['type'] = 'douban';
			return $o;
		}
		
		if (preg_match('/mac\.com/', $url)) {
			$o['type'] = 'mac';
			return $o;
		}
		
		if (preg_match('/spaces\.msn\.com/', $url)) {
			$o['type'] = 'spaces';
			return $o;
		}
		
		if (preg_match('/spaces\.live\.com/', $url)) {
			$o['type'] = 'spaces';
			return $o;
		}
		
		if (preg_match('/live\.com/', $url)) {
			$o['type'] = 'spaces';
			return $o;
		}
		
		if (preg_match('/blinklist\.com/', $url)) {
			$o['type'] = 'blinklist';
			return $o;
		}
		
		if (preg_match('/bulaoge\.com/', $url)) {
			$o['type'] = 'bulaoge';
			return $o;
		}
		
		if (preg_match('/box\.net/', $url)) {
			$o['type'] = 'box';
			return $o;
		}
		
		if (preg_match('/deviantart\.com/', $url)) {
			$o['type'] = 'deviantart';
			return $o;
		}
		
		if (preg_match('/(google\.com)|(googlepages\.com)|(gfans\.org)/', $url)) {
			$o['type'] = 'google';
			return $o;
		}
		
		if (preg_match('/(blogspot\.com)/', $url)) {
			$o['type'] = 'blogspot';
			return $o;
		}
		
		
		if (preg_match('/del\.icio\.us/', $url)) {
			$o['type'] = 'delicious';
			return $o;
		}
		
		if (preg_match('/livid\.cn/', $url)) {
			$o['type'] = 'livid';
			return $o;
		}
		
		if (preg_match('/v2ex/', $url)) {
			$o['type'] = 'v2ex';
			return $o;
		}
		
		return $o;
	}
	
	/* E module: URL Classified logic */
	
	/* S module: User Agent Check logic */
	
	public function vxGetUserAgent($ua = '') {
		if ($ua == '') {
			if (isset($_SERVER['HTTP_USER_AGENT'])) {
				$ua = $_SERVER['HTTP_USER_AGENT'];
			} else {
				if (isset($_SERVER['HTTP_VIA'])) {
					if (preg_match('/(infoX)|(Nokia WAP Gateway)|(WAP)/i', $_SERVER['HTTP_VIA'], $z)) {
						$o['ua'] = $_SERVER['HTTP_VIA'];
						$o['name'] = 'WAP Gateway';
						$o['platform'] = 'Handheld';
						$o['version'] = '0';
						$o['DEVICE_LEVEL'] = 2;
						return $o;
					}
				}
			}
		}
		
		$o = array();
		
		$o['ua'] = $ua;
		$o['platform'] = '';
		$o['name'] = '';
		$o['version'] = '';
		$o['PSP_DETECTED'] = 0;
		$o['MSIE_DETECTED'] = 0;
		$o['GECKO_DETECTED'] = 0;
		$o['KHTML_DETECTED'] = 0;
		$o['OPERA_DETECTED'] = 0;
		$o['LEGACY_ENCODING'] = 0;
		/* DEVICE_LEVEL
		0 => bot
		1 => plaintext
		2 => handheld (limited display and processor)
		3 => pc (full capable)
		4 => fetcher (just file access)
		5 => tv (various features supported)
		*/
		$o['DEVICE_LEVEL'] = 0;
		
		/* PSP Internet Browser 
		 * Example: Mozilla/4.0 (PSP (PlayStation Portable); 2.00) */
		if (preg_match('/Mozilla\/4\.0 \(PSP \(PlayStation Portable\); ([2-9]?\.[0-9]*)\)/', $ua, $z)) {
			$o['platform'] = 'PSP';
			$o['name'] = 'PSP Internet Browser';
			$o['version'] = $z[1];
			$o['PSP_DETECTED'] = 1;
			$o['DEVICE_LEVEL'] = 2;
			return $o;
		}
		
		/* PalmOne Blazer */
		if (preg_match('/Blazer\/([1-9]+\.[0-9a-zA-Z]*)/', $ua, $z) && preg_match('/Palm/', $ua)) {
			$o['platform'] = 'PalmOS';
			$o['name'] = 'Blazer';
			$o['version'] = $z[1];
			$o['DEVICE_LEVEL'] = 2;
			return $o;
		}
		
		/* Xiino
		 * Example: Xiino/3.4E [en] (v.5.4.8; 153x130; c16/d) */
		if (preg_match('/Xiino\/([0-9a-zA-Z\.]*)/', $ua, $z)) {
			$o['platform'] = 'PalmOS';
			$o['name'] = 'Xiino';
			$o['version'] = $z[1];
			$o['LEGACY_ENCODING'] = 1;
			$o['DEVICE_LEVEL'] = 2;
			return $o;
		}
		
		/* Nokia 9300 Opera
		 * Example: Nokia9300/5.50 Series80/2.0 Profile/MIDP-2.0 Configuration/CLDC-1.1 */
		if (preg_match('/Nokia9300\/([0-9]+)\.([0-9]+) Series80\/([0-9]+)\.([0-9]+) Profile\/MIDP-([0-9]+)\.([0-9]+) Configuration\/CLDC-([0-9]+)\.([0-9]+)/', $ua, $z)) {
			$o['platform'] = 'Nokia9300';
			$o['name'] = 'Opera';
			$o['version'] = '6.0';
			$o['DEVICE_LEVEL'] = 2;
			$o['OPERA_DETECTED'] = 1;
			return $o;
		}
		
		/* PocketLink
		 * Example: Mozilla/5.0 (compatible; PalmOS) PLink 2.56c */
		if (preg_match('/Mozilla\/5\.0 \(compatible; PalmOS\) PLink ([0-9a-zA-Z\.]*)/', $ua, $z)) {
			$o['platform'] = 'PalmOS';
			$o['name'] = 'PocketLink';
			$o['version'] = $z[1];
			$o['LEGACY_ENCODING'] = 1;
			$o['DEVICE_LEVEL'] = 2;
			return $o;
		}
		
		/* Opera (Identify as Opera)
		 * Example: Opera/8.5 (Macintosh; PPC Mac OS X; U; zh-cn)
		 * Example: Opera/8.50 (Windows NT 5.0; U; en) */
		if (preg_match('/Opera\/([0-9]+\.[0-9]+) \(([a-zA-Z0-9\.\- ]*); ([a-zA-Z0-9\.\- ]*); ([a-zA-Z0-9\.\-\; ]*)\)/', $ua, $z)) {
			if (preg_match('/(Linux|Mac OS X)/', $ua, $y)) {
				$o['platform'] = $y[1];
			} else {
				$o['platform'] = $z[2];
			}
			$o['name'] = 'Opera';
			$o['version'] = $z[1];
			$o['DEVICE_LEVEL'] = 3;
			$o['OPERA_DETECTED'] = 1;
			return $o;
		}

		/* Opera (Identify as MSIE 6.0)
		 * Example: Mozilla/4.0 (compatible; MSIE 6.0; X11; Linux i686; en) Opera 8.5
		 * Example: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0; en) Opera 8.50 */
		if (preg_match('/Mozilla\/4\.0 \(compatible; MSIE ([0-9\.]*); ([a-zA-Z0-9\-\.;_ ]*); ([a-zA-Z0-9\-\.;_ ]*)\) Opera ([0-9\.]*)/', $ua, $z)) {
			if (preg_match('/(Linux|Mac OS X)/', $z[2], $y)) {
				$o['platform'] = $y[1];
			} else {
				$o['platform'] = $z[2];
			}
			$o['name'] = 'Opera';
			$o['version'] = $z[4];
			$o['DEVICE_LEVEL'] = 3;
			$o['OPERA_DETECTED'] = 1;
			return $o;
		}

		/* Opera (Identify as Mozilla/5.0)
		 * Example: Mozilla/5.0 (X11; Linux i686; U; en) Opera 8.5 
		 * Example: Mozilla/5.0 (Windows NT 5.0; U; en) Opera 8.50 */
		if (preg_match('/Mozilla\/5\.0 \(([a-zA-Z0-9\-\. ]*); ([a-zA-Z0-9\-\. ]*); ([a-zA-Z0-9\-\.; ]*)\) Opera ([0-9\.]*)/', $ua, $z)) {
			if (preg_match('/Windows ([a-zA-Z0-9\.\- ]*)/', $z[1], $y)) {
				$o['platform'] = $y[0];
			} else {
				if (preg_match('/(Linux|Mac OS X)/', $z[2], $y)) {
					$o['platform'] = $y[1];
				} else {
					$o['platform'] = $z[2];
				}
			}
			$o['name'] = 'Opera';
			$o['version'] = $z[4];
			$o['DEVICE_LEVEL'] = 3;
			$o['OPERA_DETECTED'] = 1;
			return $o;
		}
	
		/* Apple Safari 2.x 
		 * Example: Mozilla/5.0 (Macintosh; U; PPC Mac OS X; zh-cn) AppleWebKit/412.7 (KHTML, like Gecko) Safari/412.5 */
		if (preg_match('/Mozilla\/5\.0 \(Macintosh; U;([a-zA-Z0-9\s]+); [a-z\-]+\) AppleWebKit\/([0-9]+\.[0-9]+) \(KHTML, like Gecko\) Safari\/([0-9]+\.[0-9]+)/', $ua, $z)) {
			$o['platform'] = 'Mac OS X';
			$o['name'] = 'Safari';
			$o['version'] = $z[2];
			$o['DEVICE_LEVEL'] = 3;
			$o['KHTML_DETECTED'] = 1;
			return $o;
		}
		
		/* Apple Safari 3.x on Mac OS X
		 * Example: Mozilla/5.0 (Macintosh; U; Intel Mac OS X; en) AppleWebKit/522.10.1 (KHTML, like Gecko) Version/3.0 Safari/522.11 */
		if (preg_match('/Mozilla\/5\.0 \(Macintosh; U;([a-zA-Z0-9\s]+); [a-z\-]+\) AppleWebKit\/([0-9]+\.[0-9]+\.[0-9]+) \(KHTML, like Gecko\) Version\/([0-9]+\.[0-9]+) Safari\/([0-9]+\.[0-9]+)/', $ua, $z)) {
			$o['platform'] = 'Mac OS X';
			$o['name'] = 'Safari';
			$o['version'] = $z[3];
			$o['DEVICE_LEVEL'] = 3;
			$o['KHTML_DETECTED'] = 1;
			return $o;
		}
		
		/* Apple Safari 3.x on Windows
		 * Example: Mozilla/5.0 (Windows; U; Windows NT 5.1; zh) AppleWebKit/522.11.3 (KHTML, like Gecko) Version/3.0 Safari/522.11.3 */
		if (preg_match('/Mozilla\/5\.0 \(Windows; U;([a-zA-Z0-9\.\s]+); [a-z\-]+\) AppleWebKit\/([0-9]+\.[0-9]+\.[0-9]+) \(KHTML, like Gecko\) Version\/([0-9]+\.[0-9]+) Safari\/([0-9]+\.[0-9]+)/', $ua, $z)) {
			$o['platform'] = 'Windows';
			$o['name'] = 'Safari';
			$o['version'] = $z[3];
			$o['DEVICE_LEVEL'] = 3;
			$o['KHTML_DETECTED'] = 1;
			return $o;
		}
		
		/* Apple WebKit 
		 * Example: Mozilla/5.0 (Macintosh; U; PPC Mac OS X; en) AppleWebKit/420+ (KHTML, like Gecko) Safari/417.9.2 */
		if (preg_match('/Mozilla\/5\.0 \(Macintosh; U;([a-zA-Z0-9\s]+); [a-z\-]+\) AppleWebKit\/([0-9\+\.]+) \(KHTML, like Gecko\) Safari\/([0-9]+\.[0-9]+)/', $ua, $z)) {
			$o['platform'] = 'Mac OS X';
			$o['name'] = 'WebKit';
			$o['version'] = $z[2];
			$o['DEVICE_LEVEL'] = 3;
			$o['KHTML_DETECTED'] = 1;
			return $o;
		}
		
		/* Shiira (WebKit based)
		 * Example: Mozilla/5.0 (Macintosh; U; PPC Mac OS X; en) AppleWebKit/419 (KHTML, like Gecko) Shiira/2.0 b2 Safari/125 */
		if (preg_match('/Mozilla\/5\.0 \(Macintosh; U;([a-zA-Z0-9\s]+); [a-z\-]+\) AppleWebKit\/([0-9\+\.]+) \(KHTML, like Gecko\) Shiira\/([0-9]+\.[0-9]+) ([b0-9]+) Safari\/([0-9]+)/', $ua, $z)) {
			$o['platform'] = 'Mac OS X';
			$o['name'] = 'Shiira';
			if (isset($z[5])) {
				$o['version'] = $z[3] . ' ' . $z[4];
			} else {
				$o['version'] = $z[3];
			}
			$o['DEVICE_LEVEL'] = 3;
			$o['KHTML_DETECTED'] = 1;
			return $o;
		}
	
		/* KDE Konqueror
		 * Example: Mozilla/5.0 (compatible; Konqueror/3.4; Linux) KHTML/3.4.2 (like Gecko) (Debian package 4:3.4.2-4) */
		if (preg_match('/Mozilla\/5\.0 \(compatible; Konqueror\/([0-9\.]*); ([a-zA-Z]*)\) KHTML\/([0-9\.]*)/', $ua, $z)) {
			$o['platform'] = $z[2];
			$o['name'] = 'Konqueror';
			$o['version'] = $z[1];
			$o['DEVICE_LEVEL'] = 3;
			$o['KHTML_DETECTED'] = 1;
			return $o;
		}

		/* iCab
		 * Example: Mozilla/5.0 (compatible; iCab 3.0.1; Macintosh; U; PPC Mac OS X)*/
		if (preg_match('/Mozilla\/5\.0 \(compatible; iCab ([0-9\.]+); Macintosh; U; PPC Mac OS X\)/', $ua, $z)) {
			$o['platform'] = 'Macintosh';
			$o['name'] = 'iCab';
			$o['version'] = $z[1];
			$o['DEVICE_LEVEL'] = 3;
			return $o;
		}
	
		/* Microsoft Internet Explorer 
		 * Example: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.1.4322; .NET CLR 2.0.50215) */
		if (preg_match('/Mozilla\/4\.0 \([a-z]+; MSIE ([0-9]+\.[0-9]+); ([a-zA-Z0-9\.\- ]+)/', $ua, $z)) {
			$o['platform'] = $z[2];
			$o['name'] = 'Internet Explorer';
			$o['version'] = $z[1];
			$o['DEVICE_LEVEL'] = 3;
			$o['MSIE_DETECTED'] = 1;
			return $o;
		}

		/* Chimera
		 * Example: Chimera/2.0alpha */
		if (preg_match('/^Chimera\/([0-9a-zA-Z\.]*)/', $ua, $z)) {
			$o['platform'] = 'Unix';
			$o['name'] = 'Chimera';
			$o['version'] = $z[1];
			$o['DEVICE_LEVEL'] = 3;
			return $o;
		}

		/* Mozilla Camino | Firefox | Firebird | Thunderbird | SeaMonkey | Sunbird | Epiphany
		 * Camino Example: Mozilla/5.0 (Macintosh; U; PPC Mac OS X Mach-O; en-US; rv:1.8b4) Gecko/20050914 Camino/1.0a1
		 * Firefox Example: Mozilla/5.0 (Macintosh; U; PPC Mac OS X Mach-O; en-US; rv:1.8b4) Gecko/20050908 Firefox/1.4 
		 * Firefox Example: Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.7.12) Gecko/20050922 Firefox/1.0.7 (Debian package 1.0.7-1) */
		if (preg_match('/Mozilla\/5\.0 \(([a-zA-Z0-9]+); U; ([0-9a-zA-Z\.\- ]+); [a-zA-Z\- ]*; rv:([0-9a-z\.]+)\) Gecko\/([0-9]+) (Camino|Firefox|Firebird|SeaMonkey|Thunderbird|Sunbird|Epiphany)\/([0-9]+\.[0-9a-zA-Z\.]*)/', $ua, $z)) {
			if ($z[1] == 'Windows' | preg_match('/X11/', $z[1])) {
				$o['platform'] = $z[2];
				if (preg_match('/(Linux)/', $o['platform'], $y)) {
					$o['platform'] = $y[1];
				}
			} else {
				$o['platform'] = $z[1];
			}
			$o['name'] = $z[5];
			$o['version'] = $z[6];
			$o['DEVICE_LEVEL'] = 3;
			$o['GECKO_DETECTED'] = 1;
			return $o;
		}

		/* Mozilla Suite
		 * Example: Mozilla/5.0 (Macintosh; U; PPC Mac OS X Mach-O; en-US; rv:1.7.12) Gecko/20050915 */
		if (preg_match('/Mozilla\/5\.0 \(([a-zA-Z0-9]+); U; ([0-9a-zA-Z\.\- ]*); [a-zA-Z\- ]*; rv:([0-9a-z\.]+)\) Gecko\/([0-9]+)/', $ua, $z)) {
			if ($z[1] == 'Windows' | preg_match('/X11/', $z[1])) {
				$o['platform'] = $z[2];
				if (preg_match('/(Linux)/', $o['platform'], $y)) {
					$o['platform'] = $y[1];
				}
			} else {
				$o['platform'] = $z[1];
			}
			$o['name'] = 'Mozilla';
			$o['version'] = $z[3];
			$o['DEVICE_LEVEL'] = 3;
			$o['GECKO_DETECTED'] = 1;
			return $o;
		}

		/* Unknown Vendor Unknown Browser */
		if ($o['name'] == '') {
			$o['platform'] = 'Unknown Platform';
			$o['name'] = 'Unknown Browser';
			$o['version'] = 'Unknown Version';
			$o['DEVICE_LEVEL'] = 0;
			return $o;
		}
	}
	
	/* E module: User Agent Check logic */
	
	/* S module: User Create Check logic */
	
	public function vxUserCreateCheck() {
		$rt = array();
		
		$rt['errors'] = 0;
		
		$rt['usr_email_value'] = '';
		/* usr_email_error:
		0 => no error
		1 => empty
		2 => overflow (100 sbs)
		3 => mismatch
		4 => conflict
		999 => unspeicific */
		$rt['usr_email_error'] = 0;
		$rt['usr_email_error_msg'] = array(1 => '你忘记填写电子邮件地址了', 2 => '你的电子邮件地址太长了', 3 => '你的电子邮件地址看起来有问题', 4 => '这个电子邮件地址已经注册过了');
		
		$rt['usr_nick_value'] = '';
		/* usr_nick_error:
		0 => no error
		1 => empty
		2 => overflow (20 mbs)
		3 => invalid characters
		4 => conflict
		999 => unspecific */
		$rt['usr_nick_error'] = 0;
		$rt['usr_nick_error_msg'] = array(1 => '你忘记填写昵称了', 2 => '你的昵称太长了，精简一下吧', 3 => '你的昵称中包含了不被允许的字符', 4 => '你填写的这个昵称被别人用了');
		
		$rt['usr_password_value'] = '';
		$rt['usr_confirm_value'] = '';
		/* usr_password_error:
		0 => no error
		1 => empty
		2 => overflow (32 sbs)
		3 => invalid characters
		4 => not identical
		999 => unspecific */
		$rt['usr_password_error'] = 0;
		$rt['usr_password_error_msg'] = array(1 => '你忘记填写密码了', 2 => '你的这个密码太长了，缩减一下吧', 3 => '你填写的密码中包含了不被允许的字符', 4 => '你所填写的两个密码不匹配');
		/* usr_confirm_error:
		0 => no error
		1 => empty
		2 => overflow (32 sbs)
		3 => invalid characters(should not reach here in final rendering)
		4 => not identical
		999 => unspecific */
		$rt['usr_confirm_error'] = 0;
		$rt['usr_confirm_error_msg'] = array(1 => '你忘记填写密码确认了', 2 => '你的这个密码确认太长了，缩减一下吧', 3 => '你填写的密码中包含了不被允许的字符', 4 => '你所填写的两个密码不匹配');
		
		$rt['c_value'] = 0;
		$rt['c_error'] = 0;
		$rt['c_error_msg'] = array(1 => '你忘记填写确认码了', 4 => '你填写的确认码是错的');
		
		/* check: c */
		if (isset($_POST['c'])) {
			$rt['c_value'] = strtolower(trim($_POST['c']));
			if (strlen($rt['c_value']) > 0) {
				if ($rt['c_value'] != strtolower($_SESSION['c'])) {
					$rt['c_error'] = 4;
					$rt['errors']++;
				}
			} else {
				$rt['c_error'] = 1;
				$rt['errors']++;
			}
		} else {
			$rt['c_error'] = 1;
			$rt['errors']++;
			
		}
		
		/* check: usr_email */
		
		if (isset($_POST['usr_email'])) {
			$rt['usr_email_value'] = strtolower(make_single_safe($_POST['usr_email']));
			if (strlen($rt['usr_email_value']) == 0) {
				$rt['usr_email_error'] = 1;
				$rt['errors']++;
			} else {
				if (strlen($rt['usr_email_value']) > 100) {
					$rt['usr_email_error'] = 2;
					$rt['errors']++;
				} else {
					if (!is_valid_email($rt['usr_email_value'])) {
						$rt['usr_email_error'] = 3;
						$rt['errors']++;
					}
				}
			}
		} else {
			$rt['usr_email_error'] = 1;
			$rt['errors']++;
		}
		
		
		if ($rt['usr_email_error'] == 0) {
			$sql = "SELECT usr_email FROM babel_user WHERE usr_email = '" . $rt['usr_email_value'] . "'";
			$rs = mysql_query($sql, $this->db);
			if (mysql_num_rows($rs) > 0) {
				$rt['usr_email_error'] = 4;
				$rt['errors']++;
			}
			mysql_free_result($rs);
		}
		
		/* check: usr_nick */
		
		if (isset($_POST['usr_nick'])) {
			$rt['usr_nick_value'] = make_single_safe($_POST['usr_nick']);
			if (strlen($rt['usr_nick_value']) == 0) {
				$rt['usr_nick_error'] = 1;
				$rt['errors']++;
			} else {
				if (mb_strlen($rt['usr_nick_value']) > 20) {
					$rt['usr_nick_error'] = 2;
					$rt['errors']++;
				} else {
					if (!is_valid_nick($rt['usr_nick_value'])) {
						$rt['usr_nick_error'] = 3;
						$rt['errors']++;
					}
				}
			}
		} else {
			$rt['usr_nick_error'] = 1;
			$rt['errors']++;
		}
		
		if ($rt['usr_nick_error'] == 0) {
			$sql = "SELECT usr_nick FROM babel_user WHERE usr_nick = '" . $rt['usr_nick_value'] . "'";
			$rs = mysql_query($sql, $this->db);
			if (mysql_num_rows($rs) > 0) {
				$rt['usr_nick_error'] = 4;
				$rt['errors']++;
			}
			mysql_free_result($rs);
		}
		
		/* check: usr_gender */
		if (isset($_POST['usr_gender'])) {
			$rt['usr_gender_value'] = intval($_POST['usr_gender']);
			if (!in_array($rt['usr_gender_value'], array(0,1,2,5,6,9))) {
				$rt['usr_gender_value'] = 9;
			}
		} else {
			$rt['usr_gender_value'] = 9;
		}
		
		/* check: usr_password and usr_confirm */
		
		if (isset($_POST['usr_password'])) {
			$rt['usr_password_value'] = $_POST['usr_password'];
			if (strlen($rt['usr_password_value']) == 0) {
				$rt['usr_password_error'] = 1;
				$rt['errors']++;
			}
		} else {
			$rt['usr_password_error'] = 1;
			$rt['errors']++;
		}
		
		if (isset($_POST['usr_confirm'])) {
			$rt['usr_confirm_value'] = $_POST['usr_confirm'];
			if (strlen($rt['usr_confirm_value']) == 0) {
				$rt['usr_confirm_error'] = 1;
				$rt['errors']++;
			}
		} else {
			$rt['usr_confirm_error'] = 1;
			$rt['errors']++;
		}
		
		if (($rt['usr_password_error'] == 0) && ($rt['usr_confirm_error'] == 0)) {
			if (strlen($rt['usr_password_value']) > 32) {
				$rt['usr_password_error'] = 2;
				$rt['errors']++;
			}
			if (strlen($rt['usr_confirm_value']) > 32) {
				$rt['usr_confirm_error'] = 2;
				$rt['errors']++;
			}
		}
		
		if (($rt['usr_password_error'] == 0) && ($rt['usr_confirm_error'] == 0)) {
			if ($rt['usr_password_value'] != $rt['usr_confirm_value']) {
				$rt['usr_confirm_error'] = 4;
				$rt['errors']++;
			}
		}
		
		return $rt;
	}
	
	/* E module: User Create Check logic */
	
	/* S module: User Create Insert logic */
	
	public function vxUserCreateInsert($usr_nick, $usr_password, $usr_email, $usr_gender) {
		if (get_magic_quotes_gpc()) {
			$usr_nick = stripslashes($usr_nick);
			$usr_nick = mysql_real_escape_string($usr_nick);
			
			$usr_password = stripslashes($usr_password);
			$usr_password = mysql_real_escape_string($usr_password);
		} else {
			$usr_nick = mysql_real_escape_string($usr_nick);
			$usr_password = mysql_real_escape_string($usr_password);
		}
		$usr_password_encrypted = sha1($usr_password);
		$usr_created = time();
		
		/* insert new user */
		$sql = "INSERT INTO babel_user(usr_nick, usr_password, usr_email, usr_gender, usr_logins, usr_created, usr_lastupdated, usr_lastlogin) VALUES('{$usr_nick}', '{$usr_password_encrypted}', '{$usr_email}', {$usr_gender}, 1,  {$usr_created}, {$usr_created}, {$usr_created})";
		mysql_query($sql, $this->db);
		
		$sql = "SELECT usr_id, usr_nick, usr_password, usr_email, usr_money FROM babel_user WHERE usr_email = '{$usr_email}'";
		$User = mysql_fetch_object(mysql_query($sql, $this->db));

		$grp_created = time();
		$sql = "INSERT INTO babel_group(grp_oid, grp_nick, grp_created, grp_lastupdated) VALUES({$User->usr_id}, '{$User->usr_nick}', {$grp_created}, {$grp_created})";
		mysql_query($sql, $this->db);
		
		$sql = "SELECT grp_id, grp_nick FROM babel_group WHERE grp_nick = '{$User->usr_nick}'";
		$Group = mysql_fetch_object(mysql_query($sql, $this->db));
		
		$sql = "UPDATE babel_user SET usr_gid = {$Group->grp_id} WHERE usr_id = {$User->usr_id} LIMIT 1";
		mysql_query($sql, $this->db);
		
		return $User;
	}
	
	/* E module: User Create Insert logic */
	
	/* S module: User Password Update Check logic */
	
	public function vxUserPasswordUpdateCheck() {
		$rt = array();
		
		$rt['errors'] = 0;
		
		$rt['pswitch'] = 'a';
		
		$rt['usr_password_value'] = '';
		$rt['usr_confirm_value'] = '';
		/* usr_password_error:
		0 => no error
		1 => empty
		2 => overflow (32 sbs)
		3 => invalid characters
		4 => not identical
		5 => modify empty
		999 => unspecific */
		$rt['usr_password_error'] = 0;
		$rt['usr_password_touched'] = 0;
		$rt['usr_password_error_msg'] = array(1 => '你忘记填写密码了', 2 => '你的这个密码太长了，缩减一下吧', 3 => '你填写的密码中包含了不被允许的字符', 4 => '你所填写的两个密码不匹配', 5 => '修改密码时需将新密码输入两遍');
		/* usr_confirm_error:
		0 => no error
		1 => empty
		2 => overflow (32 sbs)
		3 => invalid characters(should not reach here in final rendering)
		4 => not identical
		5 => modify empty
		999 => unspecific */
		$rt['usr_confirm_error'] = 0;
		$rt['usr_confirm_touched'] = 0;
		$rt['usr_confirm_error_msg'] = array(1 => '你忘记填写密码确认了', 2 => '你的这个密码确认太长了，缩减一下吧', 3 => '你填写的密码中包含了不被允许的字符', 4 => '你所填写的两个密码不匹配', 5 => '修改密码时需将新密码输入两遍');

		/* S check: usr_password and usr_confirm */
		
		if (isset($_POST['usr_password'])) {
			$rt['usr_password_value'] = $_POST['usr_password'];
			if (strlen($rt['usr_password_value']) == 0) {
				$rt['usr_password_touched'] = 0;
				$rt['usr_password_error'] = 1;
				$rt['errors']++;
			} else {
				$rt['usr_password_touched'] = 1;
			}
		} else {
			$rt['usr_password_touched'] = 0;
			$rt['usr_password_error'] = 1;
			$rt['errors']++;
		}
		
		if (isset($_POST['usr_confirm'])) {
			$rt['usr_confirm_value'] = $_POST['usr_confirm'];
			if (strlen($rt['usr_confirm_value']) == 0) {
				$rt['usr_confirm_touched'] = 0;
				$rt['usr_confirm_error'] = 1;
				$rt['errors']++;
			} else {
				$rt['usr_confirm_touched'] = 1;
			}
		} else {
			$rt['usr_confirm_touched'] = 0;
			$rt['usr_confirm_error'] = 1;
			$rt['errors']++;
		}
		
		if (($rt['usr_password_touched'] == 0) && ($rt['usr_confirm_touched'] == 0)) {
			$rt['pswitch'] = 'a'; /* both blank */
		}
		
		if (($rt['usr_password_touched'] == 1) && ($rt['usr_confirm_touched'] == 1)) {
			$rt['pswitch'] = 'b'; /* both touched */
		}
		
		if (($rt['usr_password_touched'] == 1) && ($rt['usr_confirm_touched'] == 0)) {
			$rt['pswitch'] = 'c'; /* first touched */
		}
			
		if (($rt['usr_password_touched'] == 0) && ($rt['usr_confirm_touched'] == 1)) {
			$rt['pswitch'] = 'd'; /* second touched */
		}
		
		switch ($rt['pswitch']) {
			default:
			case 'a':
				/* nothing will happen */
				break;
			case 'b':
				/* a lot check here */
				if (strlen($rt['usr_password_value']) > 32) {
					$rt['usr_password_error'] = 2;
					$rt['errors']++;
				}
			
				if (strlen($rt['usr_confirm_value']) > 32) {
					$rt['usr_confirm_error'] = 2;
					$rt['errors']++;
				}
				
				if (($rt['usr_password_error'] == 0) && ($rt['usr_confirm_error'] == 0)) {
					if ($rt['usr_password_value'] != $rt['usr_confirm_value']) {
						$rt['usr_confirm_error'] = 4;
						$rt['errors']++;
					}
				}
				break;
			case 'c':
				$rt['usr_confirm_error'] = 5;
				$rt['errors']++;
				break;
			case 'd':
				$rt['usr_password_error'] = 5;
				$rt['errors']++;
				break;
		}
		
		return $rt;
	}
	
	/* E module: User Password Update Check logic */
	
	/* S module: User Password Update Update logic */
	
	public function vxUserPasswordUpdateUpdate($usr_id, $usr_password) {
		$sql = "DELETE FROM babel_passwd WHERE pwd_uid = {$usr_id}";
		mysql_query($sql, $this->db);
		
		$sql = "UPDATE babel_user SET usr_password = '{$usr_password}' WHERE usr_id = {$usr_id} LIMIT 1";
		mysql_query($sql, $this->db);
		
		if (mysql_affected_rows($this->db) == 1) {	
			return true;
		} else {
			return true;
		}
	}
	
	/* E module: User Password Update Update logic */
	
	/* S module: User Update Check logic */
	
	public function vxUserUpdateCheck() {
		$rt = array();
		
		$rt['errors'] = 0;
		
		$rt['usr_nick_value'] = '';
		/* usr_nick_error:
		0 => no error
		1 => empty
		2 => overflow (20 mbs)
		3 => invalid characters
		4 => conflict
		999 => unspecific */
		$rt['usr_nick_error'] = 0;
		$rt['usr_nick_error_msg'] = array(1 => '你忘记填写昵称了', 2 => '你的昵称太长了，精简一下吧', 3 => '你填写的昵称中包含了不被允许的字符', 4 => '你填写的这个昵称被别人用了');
		
		$rt['usr_email_notify_value'] = '';
		/* usr_email_notify_error:
		0 => no error
		2 => overflow (100 mbs)
		3 => invalid format
		999 => unspecific */
		$rt['usr_email_notify_error'] = 0;
		$rt['usr_email_notify_error_msg'] = array(2 => '邮箱地址不能超过 100 个字符', 3 => '邮箱地址不正确');

		$rt['usr_full_value'] = '';
		/* usr_full_error:
		0 => no error
		1 => empty
		2 => overflow (30 mbs)
		999 => unspecific */
		$rt['usr_full_error'] = 0;
		$rt['usr_full_error_msg'] = array(2 => '你的真实姓名长度超过了系统限制');
		
		$rt['usr_brief_value'] = '';
		/* usr_brief_error:
		0 => no error
		2 => overflow (100 mbs)
		*/
		$rt['usr_brief_error'] = 0;
		$rt['usr_brief_error_msg'] = array(2 => '你的自我简介太长了，精简一下吧');
		
		$rt['usr_gender_value'] = 9;
		
		$rt['usr_addr_value'] = '';
		/* usr_addr_error:
		0 => no error
		2 => overflow (100 mbs)
		*/
		$rt['usr_addr_error'] = 0;
		$rt['usr_addr_error_msg'] = array(2 => '你的家庭住址长度超过了系统限制');
		
		$rt['usr_telephone_value'] = '';
		/* usr_telephone_error:
		0 => no error
		2 => overflow (40 mbs)
		*/
		$rt['usr_telephone_error'] = 0;
		$rt['usr_telephone_error_msg'] = array(2 => '你的电话号码长度超过了系统限制');
		
		$rt['usr_skype_value'] = '';
		/* usr_skype_error:
		0 => no error
		2 => overflow (40 mbs)
		*/
		$rt['usr_skype_error'] = 0;
		$rt['usr_skype_error_msg'] = array(2 => '你的 Skype 帐号长度超过了系统限制');
		
		$rt['usr_lastfm_value'] = '';
		/* usr_lastfm_error:
		0 => no error
		2 => overflow (40 mbs)
		*/
		$rt['usr_lastfm_error'] = 0;
		$rt['usr_lastfm_error_msg'] = array(2 => '你的 Last.fm 帐号长度超过了系统限制');
		
		$rt['usr_identity_value'] = '';
		/* usr_identity_error:
		0 => no error
		3 => invalid
		*/
		$rt['usr_identity_error'] = 0;
		$rt['usr_identity_error_msg'] = array(3 => '身份证号码无效');
		
		$rt['pswitch'] = 'a';
		
		$rt['usr_password_value'] = '';
		$rt['usr_confirm_value'] = '';
		/* usr_password_error:
		0 => no error
		1 => empty
		2 => overflow (32 sbs)
		3 => invalid characters
		4 => not identical
		5 => modify empty
		999 => unspecific */
		$rt['usr_password_error'] = 0;
		$rt['usr_password_touched'] = 0;
		$rt['usr_password_error_msg'] = array(1 => '你忘记填写密码了', 2 => '你的这个密码太长了，缩减一下吧', 3 => '你填写的密码中包含了不被允许的字符', 4 => '你所填写的两个密码不匹配', 5 => '修改密码时需将新密码输入两遍');
		/* usr_confirm_error:
		0 => no error
		1 => empty
		2 => overflow (32 sbs)
		3 => invalid characters(should not reach here in final rendering)
		4 => not identical
		5 => modify empty
		999 => unspecific */
		$rt['usr_confirm_error'] = 0;
		$rt['usr_confirm_touched'] = 0;
		$rt['usr_confirm_error_msg'] = array(1 => '你忘记填写密码确认了', 2 => '你的这个密码确认太长了，缩减一下吧', 3 => '你填写的密码中包含了不被允许的字符', 4 => '你所填写的两个密码不匹配', 5 => '修改密码时需将新密码输入两遍');
		
		/* S check: usr_width */
		
		$rt['usr_width_value'] = 0;
		$x = simplexml_load_file(BABEL_PREFIX . '/res/valid_width.xml');
		$w = $x->xpath('/array/width');
		$ws = array();
		while(list( , $width) = each($w)) {
			$ws[] = strval($width);
		}
		$rt['usr_width_array'] = $ws;
		if (isset($_POST['usr_width'])) {
			$rt['usr_width_value'] = intval($_POST['usr_width']);
			if (!in_array($rt['usr_width_value'], $ws)) {
				$rt['usr_width_value'] = 800;
			}
		} else {
			$rt['usr_width_value'] = 800;
		}
		
		/* E check: usr_width */
		
		/* S check: usr_nick */
		
		if (isset($_POST['usr_nick'])) {
			$rt['usr_nick_value'] = make_single_safe($_POST['usr_nick']);
			if (strlen($rt['usr_nick_value']) == 0) {
				$rt['usr_nick_error'] = 1;
				$rt['errors']++;
			} else {
				if (mb_strlen($rt['usr_nick_value'], 'UTF-8') > 20) {
					$rt['usr_nick_error'] = 2;
					$rt['errors']++;
				} else {
					if (!is_valid_nick($rt['usr_nick_value'])) {
						$rt['usr_nick_error'] = 3;
						$rt['errors']++;
					}
				}
			}
		} else {
			$rt['usr_nick_error'] = 1;
			$rt['errors']++;
		}
		
		if ($rt['usr_nick_error'] == 0) {
			$sql = "SELECT usr_nick FROM babel_user WHERE usr_nick = '" . $rt['usr_nick_value'] . "' AND usr_id != " . $this->User->usr_id;
			$rs = mysql_query($sql, $this->db);
			if (mysql_num_rows($rs) > 0) {
				$rt['usr_nick_error'] = 4;
				$rt['errors']++;
			}
			mysql_free_result($rs);
		}
		
		/* E check: usr_nick */
		
		/* S check: usr_email_notify */
		
		if (isset($_POST['usr_email_notify'])) {
			$rt['usr_email_notify_value'] = make_single_safe($_POST['usr_email_notify']);
			if (mb_strlen($rt['usr_email_notify_value'], 'UTF-8') > 100) {
				$rt['usr_email_notify_error'] = 2;
				$rt['errors']++;
			} else {
				if (mb_strlen($rt['usr_email_notify_value'], 'UTF-8') > 0) {
					if (!is_valid_email($rt['usr_email_notify_value'])) {
						$rt['usr_email_notify_error'] = 3;
						$rt['errors']++;
					}
				}
			}
		}
		
		/* E check: usr_email_notify */
		
		/* S check: usr_full */
		
		if (isset($_POST['usr_full'])) {
			$rt['usr_full_value'] = make_single_safe($_POST['usr_full']);
			if (mb_strlen($rt['usr_full_value'], 'UTF-8') > 30) {
				$rt['usr_full_error'] = 2;
				$rt['errors']++;
			}
		}
		
		/* E check: usr_full */
		
		/* S check: usr_gender */
		
		if (isset($_POST['usr_gender'])) {
			$rt['usr_gender_value'] = intval($_POST['usr_gender']);
			if (!in_array($rt['usr_gender_value'], array(0,1,2,5,6,9))) {
				$rt['usr_gender_value'] = 9;
			}
		} else {
			$rt['usr_gender_value'] = 9;
		}
		
		/* E check: usr_gender */
		
		/* S check: usr_addr */
		
		if (isset($_POST['usr_addr'])) {
			$rt['usr_addr_value'] = make_single_safe($_POST['usr_addr']);
			if (mb_strlen($rt['usr_addr_value'], 'UTF-8') > 100) {
				$rt['usr_addr_error'] = 2;
				$rt['errors']++;
			}
		}
		
		/* E check: usr_addr */
		
		/* S check: usr_telephone */
		
		if (isset($_POST['usr_telephone'])) {
			$rt['usr_telephone_value'] = make_single_safe($_POST['usr_telephone']);
			if (mb_strlen($rt['usr_telephone_value'], 'UTF-8') > 40) {
				$rt['usr_telephone_error'] = 2;
				$rt['errors']++;
			}
		}
		
		/* E check: usr_telephone */
		
		/* S check: usr_skype */
		
		if (isset($_POST['usr_skype'])) {
			$rt['usr_skype_value'] = make_single_safe($_POST['usr_skype']);
			if (mb_strlen($rt['usr_skype_value'], 'UTF-8') > 40) {
				$rt['usr_skype_error'] = 2;
				$rt['errors']++;
			}
		}
		
		/* E check: usr_skype */
		
		/* S check: usr_lastfm */
		
		if (isset($_POST['usr_lastfm'])) {
			$rt['usr_lastfm_value'] = make_single_safe($_POST['usr_lastfm']);
			if (mb_strlen($rt['usr_lastfm_value'], 'UTF-8') > 40) {
				$rt['usr_lastfm_error'] = 2;
				$rt['errors']++;
			}
		}
		
		/* E check: usr_lastfm */
		
		/* S check: usr_identity */
		
		if (isset($_POST['usr_identity'])) {
			$rt['usr_identity_value'] = make_single_safe($_POST['usr_identity']);
			if (mb_strlen($rt['usr_identity_value'], 'UTF-8') > 0) {
				if (in_array(mb_strlen($rt['usr_identity_value'], 'UTF-8'), array(15, 18))) {
					if (!preg_match('/[a-zA-Z0-9]+/', $rt['usr_identity_value'])) {
						$rt['usr_identity_error'] = 3;
						$rt['errors']++;
					}
				}
			}
		}
		
		/* E check: usr_identity */
		
		/* S check: usr_brief */
		
		if (isset($_POST['usr_brief'])) {
			$rt['usr_brief_value'] = make_single_safe($_POST['usr_brief']);
			if (mb_strlen($rt['usr_brief_value'], 'UTF-8') > 0) {
				if (mb_strlen($rt['usr_brief_value'], 'UTF-8') > 100) {
					$rt['usr_brief_error'] = 2;
					$rt['errors']++;
				}
			}
		}
		
		/* E check: usr_brief */
		
		/* S check: usr_religion */
		/* default: Unknown */
		
		$_religions = read_xml_religions();
		
		if (isset($_POST['usr_religion'])) {
			$rt['usr_religion_value'] = make_single_safe($_POST['usr_religion']);
			if (!in_array($rt['usr_religion_value'], $_religions)) {
				$rt['usr_religion_value'] = 'Unknown';
			}
		} else {
			$rt['usr_religion_value'] = 'Unknown';
		}
		
		/* E check: usr_religion */
		
		/* S check: usr_religion_permission */
		/* default: 0 */
		/* options:
		   0 => secret (default)
		   1 => public
		   2 => public to the same
		 */

		if (isset($_POST['usr_religion_permission'])) {
			$rt['usr_religion_permission_value'] = intval($_POST['usr_religion_permission']);
			if (!in_array($rt['usr_religion_permission_value'], array(0, 1, 2))) {
				$rt['usr_religion_permission_value'] = 0;
			}
		} else {
			$rt['usr_religion_permission_value'] = 0;
		}
		
		/* E check: usr_religion_permission */
		
		/* S check: usr_sw_shuffle_cloud */
		/* default: 1 */
		
		if (isset($_POST['usr_sw_shuffle_cloud'])) {
			if (strtolower($_POST['usr_sw_shuffle_cloud']) == 'on') {
				$rt['usr_sw_shuffle_cloud_value'] = 1;
			} else {
				$rt['usr_sw_shuffle_cloud_value'] = 0;
			}
		} else {
			$rt['usr_sw_shuffle_cloud_value'] = 0;
		}
		
		/* E check: usr_sw_shuffle_cloud */
		
		/* S check: usr_sw_right_friends */
		/* default: 0 */
		
		if (isset($_POST['usr_sw_right_friends'])) {
			if (strtolower($_POST['usr_sw_right_friends']) == 'on') {
				$rt['usr_sw_right_friends_value'] = 1;
			} else {
				$rt['usr_sw_right_friends_value'] = 0;
			}
		} else {
			$rt['usr_sw_right_friends_value'] = 0;
		}
		
		/* E check: usr_sw_right_friends */
		
		/* S check: usr_sw_top_wealth */
		/* default: 0 */
		
		if (isset($_POST['usr_sw_top_wealth'])) {
			if (strtolower($_POST['usr_sw_top_wealth']) == 'on') {
				$rt['usr_sw_top_wealth_value'] = 1;
			} else {
				$rt['usr_sw_top_wealth_value'] = 0;
			}
		} else {
			$rt['usr_sw_top_wealth_value'] = 0;
		}
		
		/* E check: usr_sw_top_wealth */
		
		/* S check: usr_sw_shell */
		
		if (isset($_POST['usr_sw_shell'])) {
			if (strtolower($_POST['usr_sw_shell']) == 'on') {
				$rt['usr_sw_shell_value'] = 1;
			} else {
				$rt['usr_sw_shell_value'] = 0;
			}
		} else {
			$rt['usr_sw_shell_value'] = 0;
		}
		
		/* E check: usr_sw_shell */
		
		/* S check: usr_sw_notify_reply */
		
		if (isset($_POST['usr_sw_notify_reply'])) {
			if (strtolower($_POST['usr_sw_notify_reply']) == 'on') {
				$rt['usr_sw_notify_reply_value'] = 1;
			} else {
				$rt['usr_sw_notify_reply_value'] = 0;
			}
		} else {
			$rt['usr_sw_notify_reply_value'] = 0;
		}
		
		/* E check: usr_sw_notify_reply */
		
		/* S check: usr_sw_notify_reply_all */
		
		if (isset($_POST['usr_sw_notify_reply_all'])) {
			if (strtolower($_POST['usr_sw_notify_reply_all']) == 'on') {
				$rt['usr_sw_notify_reply_all_value'] = 1;
			} else {
				$rt['usr_sw_notify_reply_all_value'] = 0;
			}
		} else {
			$rt['usr_sw_notify_reply_all_value'] = 0;
		}
		
		/* E check: usr_sw_notify_reply_all */
		
		/* S check: usr_password and usr_confirm */
		
		if (isset($_POST['usr_password_new'])) {
			$rt['usr_password_value'] = $_POST['usr_password_new'];
			if (strlen($rt['usr_password_value']) == 0) {
				$rt['usr_password_touched'] = 0;
			} else {
				$rt['usr_password_touched'] = 1;
			}
		} else {
			$rt['usr_password_touched'] = 0;
		}
		
		if (isset($_POST['usr_confirm_new'])) {
			$rt['usr_confirm_value'] = $_POST['usr_confirm_new'];
			if (strlen($rt['usr_confirm_value']) == 0) {
				$rt['usr_confirm_touched'] = 0;
			} else {
				$rt['usr_confirm_touched'] = 1;
			}
		} else {
			$rt['usr_confirm_touched'] = 0;
		}
		
		if (($rt['usr_password_touched'] == 0) && ($rt['usr_confirm_touched'] == 0)) {
			$rt['pswitch'] = 'a';
		}
		
		if (($rt['usr_password_touched'] == 1) && ($rt['usr_confirm_touched'] == 1)) {
			$rt['pswitch'] = 'b';
		}
		
		if (($rt['usr_password_touched'] == 1) && ($rt['usr_confirm_touched'] == 0)) {
			$rt['pswitch'] = 'c';
		}
			
		if (($rt['usr_password_touched'] == 0) && ($rt['usr_confirm_touched'] == 1)) {
			$rt['pswitch'] = 'd';
		}
		
		switch ($rt['pswitch']) {
			default:
			case 'a':
				/* nothing will happen */
				break;
			case 'b':
				/* a lot check here */
				if (strlen($rt['usr_password_value']) > 32) {
					$rt['usr_password_error'] = 2;
					$rt['errors']++;
				}
			
				if (strlen($rt['usr_confirm_value']) > 32) {
					$rt['usr_confirm_error'] = 2;
					$rt['errors']++;
				}
				
				if (($rt['usr_password_error'] == 0) && ($rt['usr_confirm_error'] == 0)) {
					if ($rt['usr_password_value'] != $rt['usr_confirm_value']) {
						$rt['usr_confirm_error'] = 4;
						$rt['errors']++;
					}
				}
				break;
			case 'c':
				$rt['usr_confirm_error'] = 5;
				$rt['errors']++;
				break;
			case 'd':
				$rt['usr_password_error'] = 5;
				$rt['errors']++;
				break;
		}
		
		return $rt;
	}
	
	/* E module: User Update Check logic */
	
	/* S module: User Update Update logic */
	
	public function vxUserUpdateUpdate($usr_full, $usr_nick, $usr_email_notify, $usr_brief, $usr_gender, $usr_religion, $usr_religion_permission, $usr_addr, $usr_telephone, $usr_skype, $usr_lastfm, $usr_identity, $usr_width = 1024, $usr_sw_shuffle_cloud = 1, $usr_sw_right_friends = 0, $usr_sw_top_wealth = 0, $usr_sw_shell = 0, $usr_sw_notify_reply = 0, $usr_sw_notify_reply_all = 0, $usr_password = '') {
		$usr_id = $this->User->usr_id;
		
		if (get_magic_quotes_gpc()) {
			$usr_nick = stripslashes($usr_nick);
			$usr_nick = mysql_real_escape_string($usr_nick);
			
			if (strlen($usr_password) > 0) {
				$usr_password = stripslashes($usr_password);
				$usr_password = mysql_real_escape_string($usr_password);
			}
			
			$usr_email_notify = stripslashes($usr_email_notify);
			$usr_email_notify = mysql_real_escape_string($usr_email_notify);
			
			$usr_full = stripslashes($usr_full);
			$usr_full = mysql_real_escape_string($usr_full);
			
			$usr_brief = stripslashes($usr_brief);
			$usr_brief = mysql_real_escape_string($usr_brief);
			
			$usr_religion = stripslashes($usr_religion);
			$usr_religion = mysql_real_escape_string($usr_religion);
			
			$usr_addr = stripslashes($usr_addr);
			$usr_addr = mysql_real_escape_string($usr_addr);
			
			$usr_telephone = stripslashes($usr_telephone);
			$usr_telephone = mysql_real_escape_string($usr_telephone);
			
			$usr_skype = stripslashes($usr_skype);
			$usr_skype = mysql_real_escape_string($usr_skype);
			
			$usr_lastfm = stripslashes($usr_lastfm);
			$usr_lastfm = mysql_real_escape_string($usr_lastfm);
		} else {
			$usr_nick = mysql_real_escape_string($usr_nick);
			
			if (strlen($usr_password) > 0) {
				$usr_password = mysql_real_escape_string($usr_password);
			}
			
			$usr_email_notify = mysql_real_escape_string($usr_email_notify);
			
			$usr_full = mysql_real_escape_string($usr_full);
			
			$usr_brief = mysql_real_escape_string($usr_brief);
			
			$usr_religion = mysql_real_escape_string($usr_religion);
			
			$usr_addr = mysql_real_escape_string($usr_addr);
			
			$usr_telephone = mysql_real_escape_string($usr_telephone);
			
			$usr_skype = mysql_real_escape_string($usr_skype);
			
			$usr_lastfm = mysql_real_escape_string($usr_lastfm);
		}
		
		$usr_identity = mysql_real_escape_string($usr_identity);
		
		if (strlen($usr_password) > 0) {
			$usr_password = sha1($usr_password);
		}
		$usr_lastupdated = time();
		
		if (strlen($usr_password) > 0) {
			$sql = "UPDATE babel_user SET usr_full = '{$usr_full}', usr_nick = '{$usr_nick}', usr_email_notify = '{$usr_email_notify}', usr_brief = '{$usr_brief}', usr_gender = '{$usr_gender}', usr_religion = '{$usr_religion}', usr_religion_permission = {$usr_religion_permission}, usr_addr = '{$usr_addr}', usr_telephone = '{$usr_telephone}', usr_skype = '{$usr_skype}', usr_lastfm = '{$usr_lastfm}', usr_identity = '{$usr_identity}', usr_width = {$usr_width}, usr_sw_shuffle_cloud = {$usr_sw_shuffle_cloud}, usr_sw_right_friends = {$usr_sw_right_friends}, usr_sw_top_wealth = {$usr_sw_top_wealth}, usr_sw_shell = {$usr_sw_shell}, usr_sw_notify_reply = {$usr_sw_notify_reply}, usr_sw_notify_reply_all = {$usr_sw_notify_reply_all}, usr_password = '{$usr_password}', usr_lastupdated = {$usr_lastupdated} WHERE usr_id = {$usr_id} LIMIT 1";
		} else {
			$sql = "UPDATE babel_user SET usr_full = '{$usr_full}', usr_nick = '{$usr_nick}', usr_email_notify = '{$usr_email_notify}', usr_brief = '{$usr_brief}', usr_gender = '{$usr_gender}', usr_religion = '{$usr_religion}', usr_religion_permission = {$usr_religion_permission}, usr_addr = '{$usr_addr}', usr_telephone = '{$usr_telephone}', usr_skype = '{$usr_skype}', usr_lastfm = '{$usr_lastfm}', usr_identity = '{$usr_identity}', usr_width = {$usr_width}, usr_sw_shuffle_cloud = {$usr_sw_shuffle_cloud}, usr_sw_right_friends = {$usr_sw_right_friends}, usr_sw_top_wealth = {$usr_sw_top_wealth}, usr_sw_shell = {$usr_sw_shell}, usr_sw_notify_reply = {$usr_sw_notify_reply}, usr_sw_notify_reply_all = {$usr_sw_notify_reply_all}, usr_lastupdated = '{$usr_lastupdated}' WHERE usr_id = {$usr_id} LIMIT 1";
		}
		
		mysql_query($sql, $this->db);
		if (mysql_affected_rows($this->db)) {
			$sql = "UPDATE babel_group SET grp_nick = '{$usr_nick}', grp_lastupdated = {$usr_lastupdated} WHERE grp_oid = {$usr_id} LIMIT 1";
			mysql_query($sql, $this->db);
			if (mysql_affected_rows($this->db)) {
				return true;
			} else {
				return false;
			}
		} else {
			return true;
		}
	}
	
	/* E module: User Update Update logic */
	
	/* S module: Topic Create Check logic */
	
	public function vxTopicCreateCheck($options, $User) {
		$rt = array();
		
		$rt['out_of_money'] = false;
		
		switch ($options['mode']) {
			case 'board':
				$rt['mode'] = 'board';
				
				$board_id = $rt['board_id'] = $options['board_id'];
				
				$Node = new Node($rt['board_id'], $this->db);
				$Section = new Node($Node->nod_pid, $this->db);
				break;

			case 'section':
				$rt['mode'] = 'section';
				
				$section_id = $rt['section_id'] = $options['section_id'];
				
				$Section = new Node($rt['section_id'], $this->db);
				break;
		}
		
		$rt['exp_amount'] = 0;
		$rt['errors'] = 0;
		
		$rt['tpc_title_value'] = '';
		/* tpc_title_error:
		0 => no error
		1 => empty
		2 => overflow
		3 => invalid characters
		999 => unspecific */
		$rt['tpc_title_error'] = 0;
		$rt['tpc_title_error_msg'] = array(1 => '你忘记写标题了', 2 => '你的这个标题太长了', 3 => '你的标题中含有不被允许的字符');
		
		$rt['tpc_pid_value'] = 0;
		$rt['tpc_pid_error'] = 0;
		$rt['tpc_pid_error_msg'] = array(1 => '请选择一个讨论区');
		
		$rt['tpc_description_value'] = '';
		/* tpc_description_error:
		0 => no error
		2 => overflow
		999 => unspecific */
		$rt['tpc_description_error'] = 0;
		$rt['tpc_description_error_msg'] = array(2 => '你的这个描述太长了');
		
		$rt['tpc_content_value'] = '';
		/* tpc_content_error:
		0 => no error
		1 => empty
		2 => overflow
		999 => unspecific */
		$rt['tpc_content_length'] = 0;
		$rt['tpc_content_error'] = 0;
		$rt['tpc_content_error_msg'] = array(1 => '你忘记写内容了', 2 => '你的这篇主题的内容太长了');
		
		if (isset($_POST['tpc_title'])) {
			$rt['tpc_title_value'] = make_single_safe($_POST['tpc_title']);
			if (strlen($rt['tpc_title_value']) > 0) {
				if (mb_strlen($rt['tpc_title_value'], 'utf-8') > 80) {
					$rt['tpc_title_error'] = 2;
					$rt['errors']++;
				}
			} else {
				$rt['tpc_title_error'] = 1;
				$rt['errors']++;
			}
		} else {
			$rt['tpc_title_error'] = 1;
			$rt['errors']++;
		}
		
		
		if ($rt['mode'] == 'section') {
			if (isset($_POST['tpc_pid'])) {
				$rt['tpc_pid_value'] = intval($_POST['tpc_pid']);
				$sql = "SELECT nod_id FROM babel_node WHERE nod_pid = {$rt['section_id']} AND nod_id = {$rt['tpc_pid_value']}";
				$rs = mysql_query($sql, $this->db);
				if (mysql_num_rows($rs) != 1) {
					$rt['tpc_pid_error'] = 1;
					$rt['errors']++;
				}
				mysql_free_result($rs);
			} else {
				$rt['tpc_pid_error'] = 1;
				$rt['errors']++;
			}
		} else {
			$rt['tpc_pid_value'] = $rt['board_id'];
		}
		
		
		if (isset($_POST['tpc_description'])) {
			$rt['tpc_description_value'] = make_multi_safe($_POST['tpc_description']);
			if (strlen($rt['tpc_description_value']) > 1000) {
				$rt['tpc_description_error'] = 2;
				$rt['errors']++;
			}
		}
		
		if (isset($_POST['tpc_content'])) {
			$rt['tpc_content_value'] = make_multi_safe($_POST['tpc_content']);
			$rt['tpc_content_length'] = mb_strlen($rt['tpc_content_value'], 'UTF-8');
			if ($rt['tpc_content_length'] > 0) {
				if ($rt['tpc_content_length'] > BABEL_LIMIT_TOPIC_LENGTH) {
					$rt['tpc_content_error'] = 2;
					$rt['errors']++;
				}
			} else {
				$rt['tpc_content_error'] = 1;
				$rt['errors']++;
			}
		} else {
			$rt['tpc_content_error'] = 1;
			$rt['errors']++;
		}
		
		if ($rt['tpc_content_error'] == 0) {
			$tpc_content_length  = mb_strlen($rt['tpc_content_value'], 'utf-8');
			if ($tpc_content_length > 500) {
				$rt['exp_amount'] = -(intval(($tpc_content_length / 500) * (BABEL_TPC_PRICE)));
			} else {
				$rt['exp_amount'] = -(BABEL_TPC_PRICE);
			}
		} else {
			$rt['exp_amount'] = -(BABEL_TPC_PRICE);
		}
		
		if ((abs($rt['exp_amount']) * 1.2) > $User->usr_money) {
			$rt['errors']++;
			$rt['out_of_money'] = true; 
		}
		
		return $rt;
	}
	
	/* E module: Topic Create Check logic */
	
	/* S module: API Topic Create Check logic */
	
	public function vxAPITopicCreateCheck($tpc_title, $tpc_content, $tpc_description = '') {
		$rt = array();
		
		$rt['errors'] = 0;
		
		$rt['tpc_title_value'] = '';
		/* tpc_title_error:
		0 => no error
		1 => empty
		2 => overflow
		3 => invalid characters
		999 => unspecific */
		$rt['tpc_title_error'] = 0;
		$rt['tpc_title_error_msg'] = array(1 => '你忘记写标题了', 2 => '你的这个标题太长了', 3 => '你的标题中含有不被允许的字符');
		
		$rt['tpc_description_value'] = '';
		/* tpc_description_error:
		0 => no error
		2 => overflow
		999 => unspecific */
		$rt['tpc_description_error'] = 0;
		$rt['tpc_description_error_msg'] = array(2 => '你的这个描述太长了');
		
		$rt['tpc_content_value'] = '';
		/* tpc_content_error:
		0 => no error
		1 => empty
		2 => overflow
		999 => unspecific */
		$rt['tpc_content_length'] = 0;
		$rt['tpc_content_error'] = 0;
		$rt['tpc_content_error_msg'] = array(1 => '你忘记写内容了', 2 => '你的这篇主题的内容太长了');
		
		$rt['tpc_title_value'] = $tpc_title;
		if (strlen($rt['tpc_title_value']) > 0) {
			if (mb_strlen($rt['tpc_title_value'], 'utf-8') > 50) {
				$rt['tpc_title_error'] = 2;
				$rt['errors']++;
			}
		} else {
			$rt['tpc_title_error'] = 1;
			$rt['errors']++;
		}
	
		$rt['tpc_description_value'] = $tpc_description;
		if (strlen($rt['tpc_description_value']) > 1000) {
			$rt['tpc_description_error'] = 2;
			$rt['errors']++;
		}
	
		$rt['tpc_content_value'] = $tpc_content;
		$rt['tpc_content_length'] = mb_strlen($rt['tpc_content_value'], 'UTF-8');
		if ($rt['tpc_content_length'] > 0) {
			if ($rt['tpc_content_length'] > BABEL_LIMIT_TOPIC_LENGTH) {
				$rt['tpc_content_error'] = 2;
				$rt['errors']++;
			}
		} else {
			$rt['tpc_content_error'] = 1;
			$rt['errors']++;
		}
		
		return $rt;
	}
	
	/* E module: Topic Create API Check logic */

	/* S module: Topic Create Insert logic */
	
	public function vxTopicCreateInsert($board_id, $user_id, $tpc_title, $tpc_description, $tpc_content, $expense_amount) {
		if (get_magic_quotes_gpc()) {
			$tpc_title = stripslashes($tpc_title);
			$tpc_title = mysql_real_escape_string($tpc_title);
			
			$tpc_description = stripslashes($tpc_description);
			$tpc_description = mysql_real_escape_string($tpc_description);
			
			$tpc_content = stripslashes($tpc_content);
			$tpc_content = mysql_real_escape_string($tpc_content);
		} else {
			$tpc_title = mysql_real_escape_string($tpc_title);
			$tpc_description = mysql_real_escape_string($tpc_description);
			$tpc_content = mysql_real_escape_string($tpc_content);
		}
		$sql = "INSERT INTO babel_topic(tpc_pid, tpc_uid, tpc_title, tpc_description, tpc_content, tpc_created, tpc_lastupdated, tpc_lasttouched) VALUES({$board_id}, {$user_id}, '{$tpc_title}', '{$tpc_description}', '{$tpc_content}', " . time() . ", " . time() . ', ' . time() . ')';
		mysql_query($sql, $this->db);
		if (mysql_affected_rows($this->db) == 1) {
			$req = new HTTP_Request('http://' . BABEL_DNS_NAME . '/gen/feed/v2ex.rss');
			$req->sendRequest();
			return $this->User->vxPay($this->User->usr_id, $expense_amount, 2);
		} else {
			return false;
		}
	}
	
	/* E module: Topic Create Insert logic */
	
	/* S module: Topic Update Check logic */
	
	public function vxTopicUpdateCheck($topic_id, $User) {
		$rt = array();
		
		$rt['out_of_money'] = false;
		
		$rt['topic_id'] = $topic_id;
		$rt['exp_amount'] = 0;
		$rt['errors'] = 0;
		
		$rt['permit'] = 1;
		
		$rt['tpc_title_value'] = '';
		/* tpc_title_error:
		0 => no error
		1 => empty
		2 => overflow
		3 => invalid characters
		999 => unspecific */
		$rt['tpc_title_error'] = 0;
		$rt['tpc_title_error_msg'] = array(1 => '你忘记写标题了', 2 => '你的这个标题太长了', 3 => '你的标题中含有不被允许的字符');
		
		$rt['tpc_description_value'] = '';
		/* tpc_description_error:
		0 => no error
		2 => overflow
		999 => unspecific */
		$rt['tpc_description_error'] = 0;
		$rt['tpc_description_error_msg'] = array(2 => '你的这个描述太长了');
		
		$rt['tpc_content_value'] = '';
		/* tpc_content_error:
		0 => no error
		1 => empty
		2 => overflow
		999 => unspecific */
		$rt['tpc_content_length'] = 0;
		$rt['tpc_content_error'] = 0;
		$rt['tpc_content_error_msg'] = array(1 => '你忘记写内容了', 2 => '你的这篇主题的内容太长了');
		
		if (isset($_POST['tpc_title'])) {
			$rt['tpc_title_value'] = make_single_safe($_POST['tpc_title']);
			if (strlen($rt['tpc_title_value']) > 0) {
				if (mb_strlen($rt['tpc_title_value'], 'UTF-8') > 80) {
					$rt['tpc_title_error'] = 2;
					$rt['errors']++;
				}
			} else {
				$rt['tpc_title_error'] = 1;
				$rt['errors']++;
			}
		} else {
			$rt['tpc_title_error'] = 1;
			$rt['errors']++;
		}
		
		if (isset($_POST['tpc_description'])) {
			$rt['tpc_description_value'] = make_multi_safe($_POST['tpc_description']);
			if (strlen($rt['tpc_description_value']) > 1000) {
				$rt['tpc_description_error'] = 2;
				$rt['errors']++;
			}
		}
		
		if (isset($_POST['tpc_content'])) {
			$rt['tpc_content_value'] = make_multi_safe($_POST['tpc_content']);
			$rt['tpc_content_length'] = mb_strlen($rt['tpc_content_value'], 'UTF-8');
			if ($rt['tpc_content_length'] > 0) {
				if ($rt['tpc_content_length'] > BABEL_LIMIT_TOPIC_LENGTH) {
					$rt['tpc_content_error'] = 2;
					$rt['errors']++;
				}
			} else {
				$rt['tpc_content_error'] = 1;
				$rt['errors']++;
			}
		} else {
			$rt['tpc_content_error'] = 1;
			$rt['errors']++;
		}
		
		$rt['exp_amount'] = -(BABEL_TPC_UPDATE_PRICE);
		
		$Topic = new Topic($rt['topic_id'], $this->db);
		if ($this->User->usr_id != 1) {
			if ($Topic->tpc_uid != $this->User->usr_id) {
				$rt['permit'] = 0;
				$rt['errors']++;
			}
		}
		
		if ((abs($rt['exp_amount']) * 1.2) > $User->usr_money) {
			$rt['errors']++;
			$rt['out_of_money'] = true; 
		}
		
		return $rt;
	}
	
	/* E module: Topic Update Check logic */

	/* S module: Topic Update Update logic */
	
	public function vxTopicUpdateUpdate($tpc_id, $tpc_title, $tpc_description, $tpc_content, $expense_amount) {
		if (get_magic_quotes_gpc()) {
			$tpc_title = stripslashes($tpc_title);
			$tpc_title = mysql_real_escape_string($tpc_title);
			
			$tpc_description = stripslashes($tpc_description);
			$tpc_description = mysql_real_escape_string($tpc_description);
			
			$tpc_content = stripslashes($tpc_content);
			$tpc_content = mysql_real_escape_string($tpc_content);
		} else {
			$tpc_title = mysql_real_escape_string($tpc_title);
			$tpc_description = mysql_real_escape_string($tpc_description);
			$tpc_content = mysql_real_escape_string($tpc_content);
		}
		$sql = "UPDATE babel_topic SET tpc_title = '{$tpc_title}', tpc_description = '{$tpc_description}', tpc_content = '{$tpc_content}', tpc_lastupdated = " . time() . " WHERE tpc_id = {$tpc_id} LIMIT 1";
		mysql_query($sql, $this->db);
		if (mysql_affected_rows($this->db) == 1) {
			return $this->User->vxPay($this->User->usr_id, $expense_amount, 6);
		} else {
			return false;
		}
	}
	
	/* E module: Topic Update Update logic */
	
	/* S module: Post Create Check logic */
	
	public function vxPostCreateCheck($topic_id, $User) {
		$rt = array();
		
		$rt['out_of_money'] = false;
	
		$rt['topic_id'] = $topic_id;	
		$rt['exp_amount'] = 0;
		$rt['errors'] = 0;
		$rt['autistic'] = false;
		
		$rt['pst_title_value'] = '';
		/* pst_title_error:
		0 => no error
		1 => empty
		2 => overflow
		999 => unspecific */
		$rt['pst_title_error'] = 0;
		$rt['pst_title_error_msg'] = array(1 => '你忘记写标题了', 2 => '你写的标题太长了');
		
		$rt['pst_content_value'] = '';
		/* pst_content_error:
		0 => no error
		1 => empty
		2 => overflow
		999 => unspecific */
		$rt['pst_content_error'] = 0;
		$rt['pst_content_error_msg'] = array(1 => '你忘记写内容了', 2 => '你写的内容太长了');
		
		if (isset($_POST['pst_title'])) {
			$rt['pst_title_value'] = make_single_safe($_POST['pst_title']);
			if (strlen($rt['pst_title_value']) > 0) {
				if (mb_strlen($rt['pst_title_value'], 'UTF-8') > 80) {
					$rt['pst_title_error'] = 2;
					$rt['errors']++;
				}
			} else {
				$rt['pst_title_error'] = 1;
				$rt['errors']++;
			}
		} else {
			$rt['pst_title_error'] = 1;
			$rt['errors']++;
		}
		
		
		if (isset($_POST['pst_content'])) {
			$rt['pst_content_value'] = make_multi_safe($_POST['pst_content']);
			if (strlen($rt['pst_content_value']) > 0) {
				if (mb_strlen($rt['pst_content_value'], 'utf-8') > (10240)) {
					$rt['pst_content_error'] = 2;
					$rt['errors']++;
				}
			} else {
				$rt['pst_content_error'] = 1;
				$rt['errors']++;
			}
		} else {
			$rt['pst_content_error'] = 1;
			$rt['errors']++;
		}
		
		$sql = "SELECT tpc_uid FROM babel_topic WHERE tpc_id = {$topic_id}";
		$rs = mysql_query($sql, $this->db);
		$Topic = mysql_fetch_object($rs);
		mysql_free_result($rs);
		if ($Topic->tpc_uid != $this->User->usr_id) {
			$pst_price = BABEL_PST_PRICE;
		} else {
			$pst_price = BABEL_PST_SELF_PRICE;
		}
		
		if ($rt['pst_content_error'] == 0) {
			$pst_content_length  = mb_strlen($rt['pst_content_value'], 'utf-8');
			if ($pst_content_length > 200) {
				$rt['exp_amount'] = -(intval(($pst_content_length / 200) * $pst_price));
			} else {
				$rt['exp_amount'] = -($pst_price);
			}
		} else {
			$rt['exp_amount'] = -($pst_price);
		}
		
		if ((abs($rt['exp_amount']) * 1.2) > $User->usr_money) {
			$rt['errors']++;
			$rt['out_of_money'] = true;
		}
		
		if (isset($_POST['p_cur'])) {
			$rt['p_cur'] = intval($_POST['p_cur']);
		} else {
			$rt['p_cur'] = 1;
		}
		
		return $rt;
	}
	
	/* E module: Post Create Check logic */
	
	/* S module: Post Create Mobile Check logic */
	
	public function vxPostCreateMobileCheck($topic_id, $User) {
		$rt = array();
		
		$rt['out_of_money'] = false;
	
		$rt['topic_id'] = $topic_id;	
		$rt['exp_amount'] = 0;
		$rt['errors'] = 0;
		
		$rt['reply_value'] = '';
		/* reply_error:
		0 => no error
		1 => empty
		2 => overflow
		999 => unspecific */
		$rt['reply_error'] = 0;
		$rt['reply_error_msg'] = array(1 => '你忘记写回复内容了', 2 => '你写的回复内容太长了');
		
		if (isset($_POST['reply'])) {
			$rt['reply_value'] = make_multi_safe($_POST['reply']);
			if (mb_strlen($rt['reply_value'], 'utf-8') > 0) {
				if (mb_strlen($rt['reply_value'], 'utf-8') > (1024)) {
					$rt['reply_error'] = 2;
					$rt['errors']++;
				}
			} else {
				$rt['reply_error'] = 1;
				$rt['errors']++;
			}
		} else {
			$rt['reply_error'] = 1;
			$rt['errors']++;
		}
		
		$sql = "SELECT tpc_uid FROM babel_topic WHERE tpc_id = {$topic_id}";
		$rs = mysql_query($sql, $this->db);
		$Topic = mysql_fetch_object($rs);
		mysql_free_result($rs);
		if ($Topic->tpc_uid != $this->User->usr_id) {
			$pst_price = BABEL_PST_PRICE;
		} else {
			$pst_price = BABEL_PST_SELF_PRICE;
		}
		
		if ($rt['reply_error'] == 0) {
			$pst_content_length  = mb_strlen($rt['reply_value'], 'utf-8');
			if ($pst_content_length > 200) {
				$rt['exp_amount'] = -(intval(($pst_content_length / 200) * $pst_price));
			} else {
				$rt['exp_amount'] = -($pst_price);
			}
		} else {
			$rt['exp_amount'] = -($pst_price);
		}
		
		if ((abs($rt['exp_amount']) * 1.2) > $User->usr_money) {
			$rt['errors']++;
			$rt['out_of_money'] = true;
		}
		
		if (isset($_POST['p_cur'])) {
			$rt['p_cur'] = intval($_POST['p_cur']);
		} else {
			$rt['p_cur'] = 1;
		}
		
		return $rt;
	}
	
	/* E module: Post Create Check logic */
	
	/* S module: Post Create Insert logic */
	
	public function vxPostCreateInsert($topic_id, $user_id, $pst_title, $pst_content, $expense_amount) {
		if (get_magic_quotes_gpc()) {
			$pst_title = stripslashes($pst_title);
			$pst_title = mysql_real_escape_string($pst_title);
			
			$pst_content = stripslashes($pst_content);
			$pst_content = mysql_real_escape_string($pst_content);
		} else {
			$pst_title = mysql_real_escape_string($pst_title);
			$pst_content = mysql_real_escape_string($pst_content);
		}
		$sql = "INSERT INTO babel_post(pst_tid, pst_uid, pst_title, pst_content, pst_created, pst_lastupdated) VALUES({$topic_id}, {$user_id}, '{$pst_title}', '{$pst_content}', " . time() .", " . time() . ")";
		mysql_query($sql, $this->db);
		if (mysql_affected_rows($this->db) == 1) {
			$sql = "SELECT tpc_uid FROM babel_topic WHERE tpc_id = {$topic_id}";
			$rs = mysql_query($sql, $this->db);
			$Topic = mysql_fetch_object($rs);
			mysql_free_result($rs);
			$req = new HTTP_Request('http://' . BABEL_DNS_NAME . '/gen/feed/v2ex.rss');
			$req->sendRequest();
			if ($Topic->tpc_uid != $this->User->usr_id) {
				return $this->User->vxPay($this->User->usr_id, $expense_amount, 3, '', $Topic->tpc_uid);
			} else {
				return $this->User->vxPay($this->User->usr_id, $expense_amount, 5);
			}
		} else {
			return false;
		}
	}
	
	/* E module: Post Create Insert logic */
	
	/* S module: Post Update Check logic */
	
	public function vxPostUpdateCheck($Post, $User) {
		$rt = array();

		$rt['post_id'] = $Post->pst_id;
		
		$rt['errors'] = 0;
		
		$rt['permit'] = false;
		
		$rt['flag_last'] = false;
		
		$rt['rank'] = 0;
		
		if ($Post->pst_uid == $User->usr_id) {
			$rt['permit'] = true;
		} else {
			if ($User->usr_id != 1) {
				$rt['errors']++;
			} else {
				$rt['permit'] = true;
			}
		}
		
		$rt['pst_title_value'] = '';
		/* pst_title_error:
		0 => no error
		1 => empty
		2 => overflow
		999 => unspecific */
		$rt['pst_title_error'] = 0;
		$rt['pst_title_error_msg'] = array(1 => '你忘记写标题了', 2 => '你写的标题太长了');
		
		$rt['pst_content_value'] = '';
		/* pst_content_error:
		0 => no error
		1 => empty
		2 => overflow
		999 => unspecific */
		$rt['pst_content_error'] = 0;
		$rt['pst_content_error_msg'] = array(1 => '你忘记写内容了', 2 => '你写的内容太长了');
		
		if (isset($_POST['pst_title'])) {
			$rt['pst_title_value'] = make_single_safe($_POST['pst_title']);
			if (strlen($rt['pst_title_value']) > 0) {
				if (mb_strlen($rt['pst_title_value'], 'UTF-8') > 80) {
					$rt['pst_title_error'] = 2;
					$rt['errors']++;
				}
			} else {
				$rt['pst_title_error'] = 1;
				$rt['errors']++;
			}
		} else {
			$rt['pst_title_error'] = 1;
			$rt['errors']++;
		}
		
		
		if (isset($_POST['pst_content'])) {
			$rt['pst_content_value'] = make_multi_safe($_POST['pst_content']);
			if (strlen($rt['pst_content_value']) > 0) {
				if (mb_strlen($rt['pst_content_value'], 'utf-8') > (10240)) {
					$rt['pst_content_error'] = 2;
					$rt['errors']++;
				}
			} else {
				$rt['pst_content_error'] = 1;
				$rt['errors']++;
			}
		} else {
			$rt['pst_content_error'] = 1;
			$rt['errors']++;
		}
		
		if ($rt['errors'] == 0) {
			$sql = "SELECT pst_id FROM babel_post WHERE pst_tid = {$Post->pst_tid} ORDER BY pst_id ASC";
			$rs = mysql_query($sql);
			$i = 0;
			$count = mysql_num_rows($rs);
			while ($_p = mysql_fetch_array($rs)) {
				$i++;
				if (($_p['pst_id'] == $Post->pst_id) && ($i == $count)) {
					$rt['permit'] = true;
					$rt['flag_last'] = true;
				}
				if ($_p['pst_id'] == $Post->pst_id) {
					$rt['rank'] = $i;
				}
				unset($_p);
			}
			mysql_free_result($rs);
			if (!$rt['flag_last']) {
				if ($this->User->usr_id != 1) {
					$rt['permit'] = false;
					$rt['errors']++;
				}
			}
		}
		
		return $rt;
	}
	
	/* E module: Post Update Check logic */
	
	/* S module: Post Update Update logic */
	
	public function vxPostUpdateUpdate($post_id, $pst_title, $pst_content) {
		if (get_magic_quotes_gpc()) {
			$pst_title = stripslashes($pst_title);
			$pst_title = mysql_real_escape_string($pst_title);
			
			$pst_content = stripslashes($pst_content);
			$pst_content = mysql_real_escape_string($pst_content);
		} else {
			$pst_title = mysql_real_escape_string($pst_title);
			$pst_content = mysql_real_escape_string($pst_content);
		}
		$_now = time();
		$sql = "UPDATE babel_post SET pst_title = '{$pst_title}', pst_content = '{$pst_content}', pst_lastupdated = {$_now} WHERE pst_id = {$post_id} LIMIT 1";
		mysql_query($sql, $this->db);
		if (mysql_affected_rows($this->db) == 1) {
			return true;
		} else {
			return false;
		}
	}
	
	/* E module: Post Update Update logic */
	
	/* S module: Dry Create Check logic */
	
	public function vxDryItemCreateCheck() {
		/**
		 *
		 * method: POST
		 * elements: itm_name, itm_title, itm_substance
		 *
		 */

		$rt = array();
		$rt['errors'] = 0;
		
		$rt['itm_name_value'] = '';
		$rt['itm_name_error'] = 0;
		$rt['itm_name_error_msg'] = array(
			1 => '你没有输入 DRY 项目的名称',
			2 => '你输入的 DRY 项目的名称长度不能超过 100 个字符',
			3 => '你输入的 DRY 项目的名称中含有不被允许的字符',
			4 => '这个项目的名称和已经存在的项目的名称有冲突');
			
		if (isset($_POST['itm_name'])) {
			$rt['itm_name_value'] = fetch_single($_POST['itm_name']);
			if ($rt['itm_name_value'] == '') {
				$rt['errors']++;
				$rt['itm_name_error'] = 1;
			} else {
				if (mb_strlen($rt['itm_name_value'], 'UTF-8') > 100) {
					$rt['errors']++;
					$rt['itm_name_error'] = 2;
				} else {
					if (!preg_match('/^([a-zA-Z0-9\-\_]+)$/', $rt['itm_name_value'])) {
						$rt['errors']++;
						$rt['itm_name_error'] = 3;
					}
				}
			}
		} else {
			$rt['errors']++;
			$rt['itm_name_error'] = 1;
		}
		
		if ($rt['itm_name_error'] == 0) {
			$real_itm_name = mysql_real_escape_string($rt['itm_name_value']);
			$sql = "SELECT itm_name FROM babel_dry_item WHERE itm_name = '{$real_itm_name}' AND itm_uid = {$this->User->usr_id}";
			$rs = mysql_query($sql);
			if (mysql_num_rows($rs) == 1) {
				$rt['errors']++;
				$rt['itm_name_error'] = 4;
			}
			mysql_free_result($rs);
		}
		
		$rt['itm_title_value'] = '';
		$rt['itm_title_error'] = 0;
		$rt['itm_title_error_msg'] = array(
			1 => '你没有输入 DRY 项目的标题',
			2 => '你输入的 DRY 项目的标题长度不能超过 100 个字符');
			
		if (isset($_POST['itm_title'])) {
			$rt['itm_title_value'] = fetch_single($_POST['itm_title']);
			if ($rt['itm_title_value'] == '') {
				$rt['errors']++;
				$rt['itm_title_error'] = 1;
			} else {
				if (mb_strlen($rt['itm_title_value'], 'UTF-8') > 100) {
					$rt['errors']++;
					$rt['itm_title_error'] = 2;
				}
			}
		} else {
			$rt['errors']++;
			$rt['itm_title_error'] = 1;
		}
		
		$rt['itm_substance_value'] = '';
		$rt['itm_substance_error'] = 0;
		$rt['itm_substance_error_msg'] = array();
		
		if (isset($_POST['itm_substance'])) {
			$rt['itm_substance_value'] = fetch_multi($_POST['itm_substance']);
		}
		
		if (isset($_POST['itm_permission'])) {
			$rt['itm_permission_value'] = intval($_POST['itm_permission']);
			if (!in_array($rt['itm_permission_value'], array(0, 1))) {
				$rt['itm_permission_value'] = 1;
			}
		} else {
			$rt['itm_permission_value'] = 1;
		}
		
		return $rt;
	}
	
	/* E module: Dry Create Check logic */
	
	/* S module: Blog Create Check logic */
	
	public function vxBlogCreateCheck($user_money) {
		$rt = array();
		
		$rt['errors'] = 0;
		$rt['out_of_money'] = 0;
		
		/* blg_name (max: 20) */
		$rt['blg_name_value'] = '';
		$rt['blg_name_maxlength'] = 20;
		$rt['blg_name_error'] = 0;
		$rt['blg_name_error_msg'] = array(1 => '你没有写博客的访问地址', 2 => '你输入的博客的访问地址过长', 3 => '你使用了不被允许的字符', 4 => '这个访问地址已经被别人注册了');
		
		if (isset($_POST['blg_name'])) {
			$rt['blg_name_value'] = strtolower(fetch_single($_POST['blg_name']));
			if ($rt['blg_name_value'] == '') {
				$rt['errors']++;
				$rt['blg_name_error'] = 1;
			} else {
				if (mb_strlen($rt['blg_name_value'], 'UTF-8') > $rt['blg_name_maxlength']) {
					$rt['errors']++;
					$rt['blg_name_error'] = 2;
				} else {
					if (is_valid_blog_name($rt['blg_name_value'])) {
						$sql = "SELECT blg_id FROM babel_weblog WHERE blg_name = '" . $rt['blg_name_value'] . "'";
						$rs = mysql_query($sql);
						if (mysql_num_rows($rs) > 0) {
							$rt['errors']++;
							$rt['blg_name_error'] = 4;
						}
						mysql_free_result($rs);
					} else {
						$rt['errors']++;
						$rt['blg_name_error'] = 3;
					}
				}
			}
		} else {
			$rt['errors']++;
			$rt['blg_name_error'] = 1;
		}
		
		/* blg_title (max: 50) */
		
		$rt['blg_title_value'] = '';
		$rt['blg_title_maxlength'] = 50;
		$rt['blg_title_error'] = 0;
		$rt['blg_title_error_msg'] = array(1 => '你没有写博客的标题', 2 => '你输入的博客的标题过长');
		
		if (isset($_POST['blg_title'])) {
			$rt['blg_title_value'] = fetch_single($_POST['blg_title']);
			if ($rt['blg_title_value'] == '') {
				$rt['errors']++;
				$rt['blg_title_error'] = 1;
			} else {
				if (mb_strlen($rt['blg_title_value'], 'UTF-8') > $rt['blg_title_maxlength']) {
					$rt['errors']++;
					$rt['blg_title_error'] = 2;
				}
			}
		} else {
			$rt['errors']++;
			$rt['blg_title_error'] = 1;
		}
		
		/* blg_description (null) (text) */
		
		$rt['blg_description_value'] = '';
		$rt['blg_description_maxlength'] = 2000;
		$rt['blg_description_error'] = 0;
		$rt['blg_description_error_msg'] = array(2 => '你输入的博客的简介过长');
		
		if (isset($_POST['blg_description'])) {
			$rt['blg_description_value'] = fetch_multi($_POST['blg_description']);
			if (mb_strlen($rt['blg_description_value'], 'UTF-8') > $rt['blg_description_maxlength']) {
				$rt['errors']++;
				$rt['blg_description_error'] = 2;
			}
		}
		
		/* blg_years */
		
		require(BABEL_PREFIX . '/res/weblog_economy.php');
		
		$rt['blg_years_value'] = 1;
		
		if (isset($_POST['blg_years'])) {
			$rt['blg_years_value'] = intval($_POST['blg_years']);
			if (!in_array($rt['blg_years_value'], array_keys($_payment))) {
				$rt['blg_years_value'] = 1;
			}
		}
		
		$rt['blg_cost'] = intval($_cost[$rt['blg_years_value']]);
		
		if ($user_money < $rt['blg_cost']) {
			$rt['errors']++;
			$rt['out_of_money'] = 1;
		}
		
		return $rt;
	}
	
	/* E module: Blog Create Check logic */
	
	/* S module: Blog Create Insert logic */
	
	public function vxBlogCreateInsert($uid, $name, $title, $description, $years) {
		$name = mysql_real_escape_string($name);
		$title = mysql_real_escape_string($title);
		$description = mysql_real_escape_string($description);
		$time = time();
		$expire = time() + ((86400 * 365) * $years);
		$sql = "INSERT INTO babel_weblog(blg_uid, blg_name, blg_title, blg_description, blg_created, blg_lastupdated, blg_expire) VALUES({$uid}, '{$name}', '{$title}', '{$description}', {$time}, {$time}, {$expire})";
		mysql_query($sql, $this->db);
		if (mysql_affected_rows($this->db) == 1) {
			require(BABEL_PREFIX . '/res/weblog_economy.php');
			$exp_amount = $_cost[$years];
			$exp_memo = $title . ' / ' . $_payment[$years];
			return $this->User->vxPay($this->User->usr_id, -$exp_amount, 899, $exp_memo);
		} else {
			return false;
		}
	}
	
	/* E module: Blog Create Insert logic */
	
	/* S module: Blog Config Check logic */
	
	public function vxBlogConfigCheck($user_money, $weblog_id) {
		$rt = array();
		
		$rt['weblog_id'] = $weblog_id;
		
		$rt['errors'] = 0;
		$rt['out_of_money'] = 0;
		
		/* blg_title (max: 50) */
		
		$rt['blg_title_value'] = '';
		$rt['blg_title_maxlength'] = 50;
		$rt['blg_title_error'] = 0;
		$rt['blg_title_error_msg'] = array(1 => '你没有写博客的标题', 2 => '你输入的博客的标题过长');
		
		if (isset($_POST['blg_title'])) {
			$rt['blg_title_value'] = fetch_single($_POST['blg_title']);
			if ($rt['blg_title_value'] == '') {
				$rt['errors']++;
				$rt['blg_title_error'] = 1;
			} else {
				if (mb_strlen($rt['blg_title_value'], 'UTF-8') > $rt['blg_title_maxlength']) {
					$rt['errors']++;
					$rt['blg_title_error'] = 2;
				}
			}
		} else {
			$rt['errors']++;
			$rt['blg_title_error'] = 1;
		}
		
		/* blg_description (null) (text) */
		
		$rt['blg_description_value'] = '';
		$rt['blg_description_maxlength'] = 2000;
		$rt['blg_description_error'] = 0;
		$rt['blg_description_error_msg'] = array(2 => '你输入的博客的简介过长');
		
		if (isset($_POST['blg_description'])) {
			$rt['blg_description_value'] = fetch_multi($_POST['blg_description']);
			if (mb_strlen($rt['blg_description_value'], 'UTF-8') > $rt['blg_description_maxlength']) {
				$rt['errors']++;
				$rt['blg_description_error'] = 2;
			}
		}
		
		/* blg_mode */
		$_modes = Weblog::vxGetEditorModes();
		$mode_default = Weblog::vxGetDefaultEditorMode();
		
		$rt['blg_mode_value'] = $mode_default;
		
		if (isset($_POST['blg_mode'])) {
			$rt['blg_mode_value'] = intval($_POST['blg_mode']);
			if (!in_array($rt['blg_mode_value'], array_keys($_modes))) {
				$rt['blg_mode_value'] = $mode_default;
			}
		}
		
		/* blg_comment_permission */
		
		$_comment_permissions = Weblog::vxGetCommentPermissions();
		$comment_permission_default = Weblog::vxGetDefaultCommentPermission();
		
		$rt['blg_comment_permission_value'] = $comment_permission_default;
		
		if (isset($_POST['blg_comment_permission'])) {
			$rt['blg_comment_permission_value'] = intval($_POST['blg_comment_permission']);
			if (!in_array($rt['blg_comment_permission_value'], array_keys($_comment_permissions))) {
				$rt['blg_comment_permission_value'] = $comment_permission_default;
			}
		}
		
		/* blg_license */
		
		$_licenses = Weblog::vxGetLicenses();
		$license_default = Weblog::vxGetDefaultLicense();
		
		$rt['blg_license_value'] = $license_default;
		
		if (isset($_POST['blg_license'])) {
			$rt['blg_license_value'] = fetch_single($_POST['blg_license']);
			if (!array_key_exists($rt['blg_license_value'], $_licenses)) {
				$rt['blg_license_value'] = $license_default;
			}
		}
		
		/* blg_ing */
		
		$rt['blg_ing_value'] = 0;
		
		if (isset($_POST['blg_ing'])) {
			$rt['blg_ing_value'] = intval($_POST['blg_ing']);
			if ($rt['blg_ing_value'] != 0 && $rt['blg_ing_value'] != 1) {
				$rt['blg_ing_value'] = 0;
			}
		}
		
		/* blg_license_show */
		
		$rt['blg_license_show_value'] = 0;
		
		if (isset($_POST['blg_license_show'])) {
			$rt['blg_license_show_value'] = intval($_POST['blg_license_show']);
			if ($rt['blg_license_show_value'] != 0 && $rt['blg_license_show_value'] != 1) {
				$rt['blg_license_show_value'] = 0;
			}
		}
		
		return $rt;
	}
	
	/* E module: Blog Config Check logic */
	
	/* S module: Blog Config Update logic */
	
	public function vxBlogConfigUpdate($weblog_id, $title, $description, $mode, $comment_permission, $license, $license_show, $ing) {
		$title = mysql_real_escape_string($title);
		$description = mysql_real_escape_string($description);
		$license = mysql_real_escape_string($license);
		$time = time();
		$sql = "UPDATE babel_weblog SET blg_title = '{$title}', blg_description = '{$description}', blg_mode = {$mode}, blg_comment_permission = {$comment_permission}, blg_license = '{$license}', blg_license_show = {$license_show}, blg_lastupdated = {$time}, blg_dirty = 1, blg_ing = {$ing} WHERE blg_id = {$weblog_id}";
		mysql_query($sql, $this->db) or die(mysql_error());
		if (mysql_affected_rows($this->db) == 1) {
			return true;
		} else {
			return false;
		}
	}
	
	/* E module: Blog Config Update logic */
	
	/* S module: Blog Compose Check logic */
	
	public function vxBlogComposeCheck() {
		$rt = array();
		
		$rt['errors'] = 0;

		/* bge_title (max: 50) */
		
		$rt['bge_title_value'] = '';
		$rt['bge_title_maxlength'] = 50;
		$rt['bge_title_error'] = 0;
		$rt['bge_title_error_msg'] = array(1 => '你没有写文章的标题', 2 => '你输入的文章的标题过长');
		
		if (isset($_POST['bge_title'])) {
			$rt['bge_title_value'] = fetch_single($_POST['bge_title']);
			if ($rt['bge_title_value'] == '') {
				$rt['errors']++;
				$rt['bge_title_error'] = 1;
			} else {
				if (mb_strlen($rt['bge_title_value'], 'UTF-8') > $rt['bge_title_maxlength']) {
					$rt['errors']++;
					$rt['bge_title_error'] = 2;
				}
			}
		} else {
			$rt['errors']++;
			$rt['bge_title_error'] = 1;
		}
		
		/* bge_body (null) (text) */
		
		$rt['bge_body_value'] = '';
		$rt['bge_body_maxlength'] = 1024 * 1024 * 2;
		$rt['bge_body_error'] = 0;
		$rt['bge_body_error_msg'] = array(2 => '你输入的文章内容过长');
		
		if (isset($_POST['bge_body'])) {
			$rt['bge_body_value'] = fetch_multi($_POST['bge_body']);
			if (mb_strlen($rt['bge_body_value'], 'UTF-8') > $rt['bge_body_maxlength']) {
				$rt['errors']++;
				$rt['bge_body_error'] = 2;
			}
		}
		
		/* bge_mode */
		$_modes = Weblog::vxGetEditorModes();
		$mode_default = Weblog::vxGetDefaultEditorMode();
		
		$rt['bge_mode_value'] = $mode_default;
		
		if (isset($_POST['bge_mode'])) {
			$rt['bge_mode_value'] = intval($_POST['bge_mode']);
			if (!in_array($rt['bge_mode_value'], array_keys($_modes))) {
				$rt['bge_mode_value'] = $mode_default;
			}
		}
		
		/* bge_comment_permission */
		
		$_comment_permissions = Weblog::vxGetCommentPermissions();
		$comment_permission_default = Weblog::vxGetDefaultCommentPermission();
		
		$rt['bge_comment_permission_value'] = $comment_permission_default;
		
		if (isset($_POST['bge_comment_permission'])) {
			$rt['bge_comment_permission_value'] = intval($_POST['bge_comment_permission']);
			if (!in_array($rt['bge_comment_permission_value'], array_keys($_comment_permissions))) {
				$rt['bge_comment_permission_value'] = $comment_permission_default;
			}
		}
		
		/* bge_status (0 => draft, 1 => publish) */
		
		$rt['bge_status_value'] = 0;
		
		if (isset($_POST['bge_status'])) {
			$rt['bge_status_value'] = intval($_POST['bge_status']);
			if (!in_array($rt['bge_status_value'], array(0, 1))) {
				$rt['bge_status_value'] = 0;
			}
		}
		
		/* bge_tags */

		if (isset($_POST['bge_tags'])) {
			$rt['bge_tags_value'] = fetch_single($_POST['bge_tags']);
			if ($rt['bge_tags_value'] != '') {
				$tags = filter_tags(strtolower(fetch_single($_POST['bge_tags'])));
				$tags = explode(' ', $tags);
				$tags = array_unique($tags);
				$rt['bge_tags_value'] = $tags;
			} else {
				$rt['bge_tags_value'] = array();
			}
		}
		
		/* bge_published_date & bge_published_time */
		
		if (isset($_POST['bge_published_date']) && isset($_POST['bge_published_time'])) {
			$rt['bge_published_date_value'] = fetch_single($_POST['bge_published_date']);
			$rt['bge_published_time_value'] = fetch_single($_POST['bge_published_time']);
			$rt['published'] = strtotime($rt['bge_published_date_value'] . ' ' . $rt['bge_published_time_value']);
			if (($rt['published'] - mktime(0, 0, 0, 5, 31, 1985, 0)) < 3600) {
				$rt['published'] = time();
			}
		} else {
			$rt['published'] = time();
		}
		
		return $rt;
	}
	
	/* E module: Blog Compose Check logic */
	
	/* S module: Blog Compose Insert logic */
	
	public function vxBlogComposeInsert($user_id, $weblog_id, $title, $body, $mode, $comment_permission, $status, $published, $tags = array()) {
		$title = mysql_real_escape_string($title);
		$hash = md5($title . "\n\n" . $body);
		$body = mysql_real_escape_string($body);
		$time = time();
		if ($status == 0) {
			$published = 0;
		}
		$tags_sql = mysql_real_escape_string(implode(' ', $tags));
		$sql = "INSERT INTO babel_weblog_entry(bge_pid, bge_uid, bge_title, bge_body, bge_mode, bge_comment_permission, bge_tags, bge_status, bge_revisions, bge_hash, bge_created, bge_lastupdated, bge_published) VALUES({$weblog_id}, {$user_id}, '{$title}', '{$body}', {$mode}, {$comment_permission}, '{$tags_sql}', {$status}, 1, '{$hash}', {$time}, {$time}, {$published})";
		mysql_query($sql, $this->db);
		if (mysql_affected_rows($this->db) == 1) {
			if (count($tags) > 0) {
				$entry_id = mysql_insert_id();
				$sql = "DELETE FROM babel_weblog_entry_tag WHERE bet_eid = {$entry_id}";
				mysql_unbuffered_query($sql);
				foreach ($tags as $tag) {
					$tag = mysql_real_escape_string($tag);
					$sql = "INSERT INTO babel_weblog_entry_tag(bet_uid, bet_eid, bet_tag, bet_created) VALUES({$user_id}, {$entry_id}, '{$tag}', {$time})";
					mysql_unbuffered_query($sql);
				}
			} else {
				$sql = "DELETE FROM babel_weblog_entry_tag WHERE bet_eid = {$entry_id}";
				mysql_unbuffered_query($sql);
			}
			return true;
		} else {
			return false;
		}
	}
	
	/* E module: Blog Compose Insert logic */
	
	/* S module: Blog Edit Update logic */
	
	public function vxBlogEditUpdate($entry_id, $user_id, $title, $body, $mode, $comment_permission, $status, $published, $status_old, $tags) {
		$title = mysql_real_escape_string($title);
		$hash = md5($title . "\n\n" . $body);
		$body = mysql_real_escape_string($body);
		$time = time();
		if ($status_old == 0) {
			if ($status == 0) {
				$published = 0;
			}
		} else {
			if ($status == 0) {
				$published = 0;
			}
		}
		$tags_sql = mysql_real_escape_string(implode(' ', $tags));
		if ($published != 0) {
			$sql = "UPDATE babel_weblog_entry SET bge_title = '{$title}', bge_body = '{$body}', bge_tags = '{$tags_sql}', bge_revisions = bge_revisions + 1, bge_mode = {$mode}, bge_comment_permission = {$comment_permission}, bge_status = {$status}, bge_lastupdated = {$time}, bge_published = {$published} WHERE bge_id = {$entry_id}";
		} else {
			$sql = "UPDATE babel_weblog_entry SET bge_title = '{$title}', bge_body = '{$body}', bge_tags = '{$tags_sql}', bge_revisions = bge_revisions + 1, bge_mode = {$mode}, bge_comment_permission = {$comment_permission}, bge_status = {$status}, bge_lastupdated = {$time} WHERE bge_id = {$entry_id}";
		}
		mysql_query($sql, $this->db);
		if (mysql_affected_rows($this->db) == 1) {
			if (count($tags) > 0) {
				$sql = "DELETE FROM babel_weblog_entry_tag WHERE bet_eid = {$entry_id}";
				mysql_unbuffered_query($sql);
				foreach ($tags as $tag) {
					$tag = mysql_real_escape_string($tag);
					$sql = "INSERT INTO babel_weblog_entry_tag(bet_uid, bet_eid, bet_tag, bet_created) VALUES({$user_id}, {$entry_id}, '{$tag}', {$time})";
					mysql_unbuffered_query($sql);
				}
			} else {
				$sql = "DELETE FROM babel_weblog_entry_tag WHERE bet_eid = {$entry_id}";
				mysql_unbuffered_query($sql);
			}
			return true;
		} else {
			return false;
		}
	}
	
	/* E module: Blog Edit Update logic */
	
	/* S module: Blog Comment Check logic */
	
	public function vxBlogCommentCheck() {
		$rt = array();
		
		$rt['errors'] = 0;
		
		/* bec_nick (max: 20) */
		
		$rt['bec_nick_value'] = '';
		$rt['bec_nick_maxlength'] = 20;
		$rt['bec_nick_error'] = 0;
		$rt['bec_nick_error_msg'] = array(1 => 'You forget to write something', 2 => "It's too lengthy");
		
		if (isset($_POST['bec_nick'])) {
			$rt['bec_nick_value'] = fetch_single($_POST['bec_nick']);
			if ($rt['bec_nick_value'] == '') {
				$rt['errors']++;
				$rt['bec_nick_error'] = 1;
			} else {
				if (mb_strlen($rt['bec_nick_value'], 'UTF-8') > $rt['bec_nick_maxlength']) {
					$rt['errors']++;
					$rt['bec_nick_error'] = 2;
				}
			}
		} else {
			$rt['errors']++;
			$rt['bec_nick_error'] = 1;
		}
		
		/* bec_email (max: 100) */
		
		$rt['bec_email_value'] = '';
		$rt['bec_email_maxlength'] = 100;
		$rt['bec_email_error'] = 0;
		$rt['bec_email_error_msg'] = array(1 => 'You forget to leave your E-mail', 2 => "It's too lengthy", "Your E-mail address format is incorrect");
		
		if (isset($_POST['bec_email'])) {
			$rt['bec_email_value'] = fetch_single($_POST['bec_email']);
			if ($rt['bec_email_value'] == '') {
				$rt['errors']++;
				$rt['bec_email_error'] = 1;
			} else {
				if (mb_strlen($rt['bec_email_value'], 'UTF-8') > $rt['bec_email_maxlength']) {
					$rt['errors']++;
					$rt['bec_email_error'] = 2;
				} else {
					if (!is_valid_email($rt['bec_email_value'])) {
						$rt['errors']++;
						$rt['bec_email_error'] = 3;
					}
				}
			}
		} else {
			$rt['errors']++;
			$rt['bec_email_error'] = 1;
		}
		
		/* bec_url (max: 200) */
		
		$rt['bec_url_value'] = '';
		$rt['bec_url_maxlength'] = 200;
		$rt['bec_url_error'] = 0;
		$rt['bec_url_error_msg'] = array(2 => "It's too lengthy", "Your URL format is incorrect");
		
		if (isset($_POST['bec_url'])) {
			$rt['bec_url_value'] = fetch_single($_POST['bec_url']);
			if ($rt['bec_url_value'] != '') {
				if (mb_strlen($rt['bec_url_value'], 'UTF-8') > $rt['bec_url_maxlength']) {
					$rt['errors']++;
					$rt['bec_url_error'] = 2;
				} else {
					if (!is_valid_url($rt['bec_url_value'])) {
						$rt['errors']++;
						$rt['bec_url_error'] = 3;
					}
				}
			}
		}
		
		/* bec_body (max: 2000) */
		
		$rt['bec_body_value'] = '';
		$rt['bec_body_maxlength'] = 2000;
		$rt['bec_body_error'] = 0;
		$rt['bec_body_error_msg'] = array(1 => 'You forget to write something', 2 => "It's too lengthy");
		
		if (isset($_POST['bec_body'])) {
			$rt['bec_body_value'] = Weblog::vxFilterComment(fetch_multi($_POST['bec_body']));
			if ($rt['bec_body_value'] == '') {
				$rt['errors']++;
				$rt['bec_body_error'] = 1;
			} else {
				if (mb_strlen($rt['bec_body_value'], 'UTF-8') > $rt['bec_body_maxlength']) {
					$rt['errors']++;
					$rt['bec_body_error'] = 2;
				}
			}
		} else {
			$rt['errors']++;
			$rt['bec_body_error'] = 1;
		}
		
		return $rt;
	}
	
	/* E module: Blog Comment Check logic */
	
	/* S module: Blog Comment Insert logic */
	
	public function vxBlogCommentInsert($user_id, $entry_id, $nick, $email, $url, $body, $status) {
		$nick = mysql_real_escape_string($nick);
		$email = mysql_real_escape_string($email);
		$url = mysql_real_escape_string($url);
		$body = mysql_real_escape_string($body);
		$time = time();
		if ($status == 1) {
			$approved = time();
		} else {
			$approved = 0;
		}
		$ip = $_SERVER['REMOTE_ADDR'];
		$sql = "INSERT INTO babel_weblog_entry_comment(bec_eid, bec_uid, bec_nick, bec_email, bec_url, bec_body, bec_status, bec_ip, bec_created, bec_approved) VALUES({$entry_id}, {$user_id}, '{$nick}', '{$email}', '{$url}', '{$body}', {$status}, '{$ip}', {$time}, {$approved})";
		mysql_query($sql, $this->db) or die(mysql_error());
		if (mysql_affected_rows($this->db) == 1) {
			return true;
		} else {
			return false;
		}
	}
	
	/* E module: Blog Comment Insert logic */
	
	/* S module: Send Money Check logic */
	
	public function vxSendMoneyCheck() {
		$rt = array();
		
		$rt['errors'] = 0;
		
		$rt['who_value'] = '';
		$rt['who_object'] = null;
		$rt['who_error'] = 0;
		$rt['who_error_msg'] = array(1 => '你没有输入收款人的名字', 2 => '不能汇款给自己', 3 => '汇款人不存在');
		
		if (isset($_POST['who'])) {
			$rt['who_value'] = fetch_single($_POST['who']);
			if ($rt['who_value'] != '') {
				$sql = "SELECT usr_id, usr_nick, usr_email, usr_money FROM babel_user WHERE usr_nick = '" . mysql_real_escape_string($rt['who_value']) . "'";
				$rs = mysql_query($sql);
				if ($Object = mysql_fetch_object($rs)) {
					if ($Object->usr_id != $this->User->usr_id) {
						$rt['who_object'] = $Object;
					} else {
						$rt['errors']++;
						$rt['who_error'] = 2;
					}
				} else {
					$rt['errors']++;
					$rt['who_error'] = 3;
				}
				mysql_free_result($rs);
			} else {
				$rt['errors']++;
				$rt['who_error'] = 1;
			}
		} else {
			$rt['errors']++;
			$rt['who_error'] = 1;
		}
		
		$rt['amount_value'] = -1;
		$rt['amount_error'] = 0;
		$rt['amount_error_msg'] = array(1 => '你没有输入汇款数额', 2 => '每次汇款数额至少为 100 铜币', 3 => '汇款数额超出了你持有的铜币数量');
		
		if (isset($_POST['amount'])) {
			$rt['amount_value'] = abs(intval($_POST['amount']));
			if ($rt['amount_value'] != 0) {
				if ($rt['amount_value'] >= 100) {
					$rate = Validator::vxSendMoneyRate($this->User->usr_created, $this->User->usr_money);
					if (($rt['amount_value'] * (1 + $rate)) > $this->User->usr_money) {
						$rt['fee_value'] = 0;
						$rt['errors']++;
						$rt['amount_error'] = 3;
					} else {
						$rt['fee_value'] = $rt['amount_value'] * $rate;
					}
				} else {
					$rt['errors']++;
					$rt['amount_error'] = 2;
				}
			} else {
				$rt['errors']++;
				$rt['amount_error'] = 1;
			}
		} else {
			$rt['errors']++;
			$rt['amount_error'] = 1;
		}
		
		$rt['confirm'] = 0;
		
		if (isset($_POST['confirm'])) {
			$confirm = intval($_POST['confirm']);
			if ($confirm == 1) {
				$rt['confirm'] = 1;
			}
		}
		
		return $rt;
	}
	
	public static function vxSendMoneyRate($created, $money) {
		$rate = -1;
		$now = time();
		$duration = round(($now - $created) / 86400);
		if ($money > 10000 && ($duration > 200)) {
			return $rate = 0;
		}
		if ($money > 5000 && ($duration > 100)) {
			return $rate = 0.02;
		}
		if ($money > 2000 && ($duration > 30)) {
			return $rate = 0.05;
		}
		if ($money > 1800) {
			return $rate = 0.08;
		}
		return $rate;
	}
	
	/* E module: Send Money Check logic */
}

/* E Validator class */

?>