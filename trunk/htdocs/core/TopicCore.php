<?php
/* Project Babel
*  Author: Livid Torvalds
*  File: /htdocs/core/TopicCore.php
*  Usage: Topic Class
*  Format: 1 tab indent(4 spaces), LF, UTF-8, no-BOM
*
*  Subversion Keywords:
*
*  $Id: TopicCore.php 60 2007-02-05 08:00:48Z livid $
*  $LastChangedDate: 2007-02-05 16:00:48 +0800 (Mon, 05 Feb 2007) $
*  $LastChangedRevision: 60 $
*  $LastChangedBy: livid $
*  $URL: http://svn.cn.v2ex.com/svn/babel/trunk/htdocs/core/TopicCore.php $
*/

if (@V2EX_BABEL != 1) {
	die('<strong>Project Babel</strong><br /><br />Made by <a href="http://www.v2ex.com/">V2EX</a> | software for internet');
}

/* S Topic class */

class Topic {
	public $db;
	
	public $tpc_id;
	public $tpc_pid;
	public $tpc_uid;
	public $tpc_title;
	public $tpc_description;
	public $tpc_content;
	public $tpc_created;
	public $tpc_lastupdated;
	public $tpc_lasttouched;
	public $tpc_hits;
	public $tpc_refs;
	public $tpc_posts;
	public $tpc_favs;
	
	public $tpc_followers;
	
	public $tpc_reply_count;

	public $usr_id;
	public $usr_geo;
	public $usr_email;
	public $usr_email_notify;
	public $usr_nick;
	public $usr_gender;
	public $usr_portrait;
	public $usr_sw_notify_reply;

	public function __construct($topic_id, $db, $flag_format = 1, $flag_addhit = 0) {
		$this->db =& $db;
		
		$sql = "SELECT tpc_id, tpc_pid, tpc_uid, tpc_title, tpc_description, tpc_content, tpc_hits, tpc_refs, tpc_posts, tpc_favs, tpc_followers, tpc_created, tpc_lastupdated, tpc_lasttouched, usr_id, usr_geo, usr_email, usr_email_notify, usr_nick, usr_gender, usr_portrait, usr_sw_notify_reply FROM babel_topic, babel_user WHERE tpc_id = {$topic_id} AND tpc_uid = usr_id";
		$rs = mysql_query($sql, $this->db);
		if (mysql_num_rows($rs) == 1) {
			$O = mysql_fetch_object($rs);
			mysql_free_result($rs);
			$this->tpc_id = $O->tpc_id;
			$this->tpc_pid = $O->tpc_pid;
			$this->tpc_uid = $O->tpc_uid;
			$this->tpc_title = $O->tpc_title;
			if ($flag_format == 1) {
				$this->tpc_description = format_ubb($O->tpc_description);
				$this->tpc_content = format_ubb($O->tpc_content);
			} else {
				$this->tpc_description = $O->tpc_description;
				$this->tpc_content = $O->tpc_content;
			}
			$this->tpc_hits = intval($O->tpc_hits) + 1;
			$this->tpc_refs = $O->tpc_refs;
			$this->tpc_posts = $O->tpc_posts;
			$this->tpc_favs = $O->tpc_favs;
			if ($O->tpc_followers != '') {
				$this->tpc_followers = unserialize($O->tpc_followers);
			} else {
				$this->tpc_followers = array();
			}
			$this->tpc_created = $O->tpc_created;
			$this->tpc_lastupdated = $O->tpc_lastupdated;
			$this->tpc_lasttouched = $O->tpc_lasttouched;
			$this->tpc_reply_count = 0;
			$this->usr_id = $O->usr_id;
			$this->usr_geo = $O->usr_geo;
			$this->usr_email = $O->usr_email;
			$this->usr_email_notify = $O->usr_email_notify;
			$this->usr_nick = $O->usr_nick;
			$this->usr_gender = $O->usr_gender;
			$this->usr_portrait = $O->usr_portrait;
			$this->usr_sw_notify_reply = $O->usr_sw_notify_reply;
			if ($flag_addhit == 1) {
				if ($this->tpc_hits > 99) {
					if (($this->tpc_hits % 100) == 0) {
						$_return = $this->vxUpdateFavs();
						if (BABEL_DEBUG) {
							if (isset($_SESSION['babel_debug_log'])) {
								$_SESSION['babel_debug_log'][time()] = 'Topic::vxUpdateFavs() executed: ' . strval($_return);
							} else {
								$_SESSION['babel_debug_log'] = array();
								$_SESSION['babel_debug_log'][time()] = 'Topic::vxUpdateFavs() executed: ' . strval($_return);
							}
						}
					}
				}
				$sql = "SELECT COUNT(*) FROM babel_online WHERE onl_ip = '" . $_SERVER['REMOTE_ADDR'] . "'";
				if (mysql_result(mysql_query($sql), 0, 0) < 3) {
					$sql = "UPDATE babel_topic SET tpc_hits = tpc_hits + 1 WHERE tpc_id = {$this->tpc_id}";
					mysql_query($sql, $this->db);
				}
			}
			$O = null;
		} else {
			mysql_free_result($rs);
			$this->tpc_id = 0;
			$this->tpc_pid = 0;
			$this->tpc_uid = 0;
			$this->tpc_title = '';
			$this->tpc_description = '';
			$this->tpc_hits = 0;
			$this->tpc_refs = 0;
			$this->tpc_posts = 0;
			$this->tpc_favs = 0;
			$this->tpc_followers = array();
			$this->tpc_content = '';
			$this->tpc_created = 0;
			$this->tpc_lastupdated = 0;
			$this->tpc_lasttouched = 0;
			$this->tpc_reply_count = 0;
			$this->usr_id = 0;
			$this->usr_geo = 'earth';
			$this->usr_email = '';
			$this->usr_email_notify = '';
			$this->usr_nick = '';
			$this->usr_gender = 0;
			$this->usr_portrait = '';
			$this->usr_sw_notify_reply = 0;
		}
	}

	public function __destruct() {
	}
	
	public function vxFormatUBB($text) {
		$text = format_ubb($text);
		return $text;
	}
	
	public function vxGetAllReply($p, $topic_id = '') {
		if ($topic_id == '') {
			$topic_id = $this->tpc_id;
		}
		$sql = "SELECT pst_id, pst_title, pst_content, pst_created, pst_lastupdated, usr_id, usr_nick, usr_geo, usr_gender, usr_portrait FROM babel_post, babel_user WHERE pst_uid = usr_id AND pst_tid = {$topic_id} ORDER BY pst_created ASC LIMIT {$p['sql']},{$p['size']}";
		$rs = mysql_query($sql, $this->db);
		$this->tpc_reply_count = mysql_num_rows($rs);
		return $rs;
	}
	
	public function vxTouch($topic_id = '') {
		if ($topic_id == '') {
			$topic_id = $this->tpc_id;
		}
		$sql = 'UPDATE babel_topic SET tpc_lasttouched = ' . time() . ' WHERE tpc_id  = ' . $topic_id . ' LIMIT 1';
		mysql_query($sql, $this->db);
		if (mysql_affected_rows($this->db) == 1) {
			return true;
		} else {
			return false;
		}
	}
	
	public function vxEraseTopic($topic_id = '') {
		if ($topic_id == '') {
			$topic_id = $this->pst_id;
		}
		$sql = "DELETE FROM babel_topic WHERE tpc_id = '{$topic_id}' LIMIT  1";
		mysql_query($sql);
		if (mysql_affected_rows($this->db) == 1) {
			$sql = "DELETE FROM babel_post WHERE pst_tid = '{$topic_id}'";
			mysql_query($sql);
			$sql = "DELETE FROM babel_favorite WHERE fav_res = '{$topic_id}' AND fav_type = 0";
			mysql_query($sql);
			return true;
		} else {
			return false;
		}
	}
	
	public function vxUpdateTopics($board_id = '') {
		if ($board_id == '') {
			$board_id = $this->tpc_pid;
		}
		$sql = "SELECT COUNT(tpc_id) FROM babel_topic WHERE tpc_pid = {$board_id}";
		$rs = mysql_query($sql, $this->db);
		$nod_topics = mysql_result($rs, 0, 0);
		mysql_free_result($rs);
		$sql = "UPDATE babel_node SET nod_topics = {$nod_topics} WHERE nod_id = {$board_id} LIMIT 1";
		mysql_query($sql, $this->db);
		if (mysql_affected_rows($this->db) == 1) {
			return true;
		} else {
			return false;
		}
	}
	
	public function vxUpdatePosts($topic_id = '') {
		if ($topic_id == '') {
			$topic_id = $this->tpc_id;
		}
		$sql = "SELECT COUNT(pst_id) FROM babel_post WHERE pst_tid = {$topic_id}";
		$rs = mysql_query($sql, $this->db);
		$tpc_posts = mysql_result($rs, 0, 0);
		mysql_free_result($rs);
		$sql = "UPDATE babel_topic SET tpc_posts = {$tpc_posts} WHERE tpc_id = {$topic_id} LIMIT 1";
		mysql_query($sql, $this->db);
		if (mysql_affected_rows($this->db) == 1) {
			return $tpc_posts;
		} else {
			return false;
		}
	}
	
	public function vxUpdateFavs($topic_id = '') {
		if ($topic_id == '') {
			$topic_id = $this->tpc_id;
		}
		$sql = "SELECT COUNT(fav_id) FROM babel_favorite WHERE fav_res = {$topic_id} AND fav_type = 0";
		$rs = mysql_query($sql, $this->db);
		$tpc_favs = mysql_result($rs, 0, 0);
		mysql_free_result($rs);
		$sql = "UPDATE babel_topic SET tpc_favs = {$tpc_favs} WHERE tpc_id = {$topic_id} LIMIT 1";
		mysql_query($sql, $this->db);
		if (mysql_affected_rows($this->db) == 1) {
			return $tpc_favs;
		} else {
			return 0;
		}
	}
	
	public function vxUpdateFollowers() {
		$sql = "SELECT DISTINCT pst_uid, usr_nick FROM babel_post, babel_user WHERE usr_id = pst_uid AND pst_tid = {$this->tpc_id} AND pst_uid != {$this->tpc_uid}";
		$rs = mysql_query($sql, $this->db);
		if (mysql_num_rows($rs) > 0) {
			$_a = array();
			while ($_follower = mysql_fetch_object($rs)) {
				$_a[$_follower->pst_uid] = $_follower->usr_nick;
			}
			mysql_free_result($rs);
			if (count($_a) > 0) {
				$_a_s = serialize($_a);
				$_a_s = mysql_real_escape_string($_a_s, $this->db);
			} else {
				$_a_s = '';
			}
			$sql = "UPDATE babel_topic SET tpc_followers = '{$_a_s}' WHERE tpc_id = {$this->tpc_id}";
			mysql_unbuffered_query($sql);
			return $_a;
		} else {
			mysql_free_result($rs);
			$sql = "UPDATE babel_topic SET tpc_followers = NULL WHERE tpc_id = {$this->tpc_id}";
			mysql_unbuffered_query($sql);
			return array();
		}
	}
}

/* E Topic class */

/* S Post class */

class Post {
	public $pst_id;
	public $pst_tid;
	public $pst_uid;
	public $pst_title;
	public $pst_content;
	protected $db;

	public function __construct($post_id, $db) {
		$this->db = $db;
		$sql = "SELECT pst_id, pst_tid, pst_uid, pst_title, pst_content FROM babel_post WHERE pst_id = {$post_id}";
		$rs = mysql_query($sql, $this->db);
		if (mysql_num_rows($rs) == 1) {
			$O = mysql_fetch_object($rs);
			mysql_free_result($rs);
			$this->pst_id = $O->pst_id;
			$this->pst_tid = $O->pst_tid;
			$this->pst_uid = $O->pst_uid;
			$this->pst_title = $O->pst_title;
			$this->pst_content = $O->pst_content;
			$O = null;
		} else {
			$this->pst_id = 0;
			$this->pst_tid = 0;
			$this->pst_uid = 0;
			$this->pst_title = '';
			$this->pst_content = '';
		}
	}
	
	public function vxErasePost($post_id = '') {
		if ($post_id == '') {
			$post_id = $this->pst_id;
		}
		$sql = "DELETE FROM babel_post WHERE pst_id = '{$post_id}' LIMIT  1";
		mysql_query($sql);
		if (mysql_affected_rows($this->db) == 1) {
			return true;
		} else {
			return false;
		}
	}
	
	public function vxUpdatePosts($topic_id = '') {
		if ($topic_id == '') {
			$topic_id = $this->pst_tid;
		}
		$sql = "SELECT COUNT(pst_id) FROM babel_post WHERE pst_tid = {$topic_id}";
		$rs = mysql_query($sql, $this->db);
		$tpc_posts = mysql_result($rs, 0, 0);
		mysql_free_result($rs);
		$sql = "UPDATE babel_topic SET tpc_posts = {$tpc_posts} WHERE tpc_id = {$topic_id} LIMIT 1";
		mysql_query($sql, $this->db);
		if (mysql_affected_rows($this->db) == 1) {
			return true;
		} else {
			return false;
		}
	}
}

/* E Post class */
?>
