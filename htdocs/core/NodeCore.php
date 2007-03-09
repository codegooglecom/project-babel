<?php
/* Project Babel
*  Author: Livid Torvalds
*  File: /htdocs/core/NodeCore.php
*  Usage: Node Class
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

/* S Node class */

class Node {
	var $db;

	var $nod_id;
	var $nod_pid;
	var $nod_uid;
	var $nod_sid;
	var $nod_level;
	var $nod_name;
	var $nod_title;
	var $nod_description;
	var $nod_header;
	var $nod_footer;
	var $nod_topics;
	var $nod_favs;
	var $nod_created;
	var $nod_lastupdated;
	
	var $usr_id;
	var $usr_nick;
	
	public function __construct($node_id, $db) {
		$this->db =& $db;
		$sql = "SELECT nod_id, nod_pid, nod_uid, nod_sid, nod_level, nod_name, nod_title, nod_description, nod_header, nod_footer, nod_topics, nod_favs, nod_created, nod_lastupdated, usr_id, usr_nick FROM babel_node, babel_user WHERE nod_uid = usr_id AND nod_id = {$node_id}";
		$rs = mysql_query($sql, $this->db);
		$O = mysql_fetch_object($rs);
		mysql_free_result($rs);
		$this->nod_id = $O->nod_id;
		$this->nod_pid = $O->nod_pid;
		$this->nod_uid = $O->nod_uid;
		$this->nod_sid = $O->nod_sid;
		$this->nod_level = $O->nod_level;
		$this->nod_name = $O->nod_name;
		$this->nod_title = $O->nod_title;
		$this->nod_description = $O->nod_description;
		$this->nod_header = $O->nod_header;
		$this->nod_footer = $O->nod_footer;
		$this->nod_topics = $O->nod_topics;
		$this->nod_favs = $O->nod_favs;
		$this->nod_created = $O->nod_created;
		$this->nod_lastupdated = $O->nod_lastupdated;
		$this->usr_id = $O->usr_id;
		$this->usr_nick = $O->usr_nick;
		$O = null;
	}
	
	public function __destruct() {
	}
	
	public function vxGetNodeInfo($node_id) {
		$sql = "SELECT nod_id, nod_name, nod_title FROM babel_node WHERE nod_id = {$node_id}";
		$rs = mysql_query($sql, $this->db);
		$Node = mysql_fetch_object($rs);
		mysql_free_result($rs);
		return $Node;
	}
	
	public function vxUpdateTopics($board_id = '') {
		if ($board_id == '') {
			$board_id = $this->nod_id;
		}
		$_t = time();
		$sql = "SELECT COUNT(tpc_id) FROM babel_topic WHERE tpc_pid = {$board_id}";
		$rs = mysql_query($sql, $this->db);
		$nod_topics = mysql_result($rs, 0, 0);
		mysql_free_result($rs);
		$sql = "UPDATE babel_node SET nod_topics = {$nod_topics}, nod_lastupdated = {$_t} WHERE nod_id = {$board_id} LIMIT 1";
		mysql_query($sql, $this->db);
		if (mysql_affected_rows($this->db) == 1) {
			return true;
		} else {
			return false;
		}
	}
	
	public function vxUpdateFavs($board_id = '') {
		if ($board_id == '') {
			$board_id = $this->nod_id;
		}
		
		$sql = "SELECT COUNT(fav_id) FROM babel_favorite WHERE fav_res = '{$board_id}' AND fav_type = 1";
		
		$rs = mysql_query($sql, $this->db);
		$nod_favs = mysql_result($rs, 0, 0);
		mysql_free_result($rs);
		
		$sql = "UPDATE babel_node SET nod_favs = {$nod_favs} WHERE nod_id = {$board_id} LIMIT 1";
		
		mysql_query($sql, $this->db);
		if (mysql_affected_rows($this->db) == 1) {
			return true;
		} else {
			return false;
		}
	}
	
	public function vxGetNodeChildren($section_id = '') {
		if ($section_id == '') {
			$section_id = $this->nod_id;
		}
		
		$sql = "SELECT nod_id, nod_name, nod_title FROM babel_node WHERE nod_pid = {$section_id} ORDER BY nod_topics DESC";
		$rs = mysql_query($sql, $this->db);
		if (mysql_num_rows($rs) > 0) {
			return $rs;
		} else {
			return false;
		}
	}
	
	public function vxDrawChannels($board_id = '', $exclude = 0) {
		echo ('<span class="chl">');
		if ($board_id == '') {
			$board_id = $this->nod_id;
		}
		
		$sql = "SELECT chl_id, chl_title, chl_url FROM babel_channel WHERE chl_pid = {$board_id} ORDER BY chl_title";
		
		$rs = mysql_query($sql, $this->db);
		$count = mysql_num_rows($rs);
		if ($count > 0) {
			echo($exclude != 0 ? '，' . $count . ' 个其他相关频道<br />':'，' . $count . ' 个相关频道</span>');
			_v_hr();
			echo('<div class="channels">');
			while ($Channel = mysql_fetch_object($rs)) {
				if (trim($Channel->chl_title) == '') {
					$Channel->chl_title = $Channel->chl_url;
				}
				if ($Channel->chl_id == $exclude) {
					echo('<strong class="p_cur"><img src="' . CDN_UI . 'img/icons/silk/bullet_feed.png" align="absmiddle" />' . make_plaintext($Channel->chl_title) . '</strong> ');
				} else {
					$css_color = rand_color();
					echo('<img src="' . CDN_UI . 'img/icons/silk/bullet_feed.png" align="absmiddle" /><a href="/channel/view/' . $Channel->chl_id . '.html" class="var" style="color: ' . $css_color . '">' . make_plaintext($Channel->chl_title) . '</a> ');
				}
			}
			mysql_free_result($rs);
			echo('</div>');
			return true;
		} else {
			echo('</span>');
			mysql_free_result($rs);
			return false;
		}
	}
	
	private function vxTrimKijijiTitle($title) {
		if (mb_ereg_match('最新的客齐集广告', $title)) {
			mb_ereg('最新的客齐集广告 所在地：(.+) 分类：(.+)', $title, $m);
			return $m[2];
		} else {
			return $title;
		}
	}
}

/* E Node class */
?>
