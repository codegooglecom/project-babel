<?php
/* Project Babel
*  Author: Livid Torvalds
*  File: /htdocs/core/URLCore.php
*  Usage: URL Class
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

/* S URL class */

class URL {
	public static function vxGetLogin($return) {
		$url = "/login/{$return}";
		return $url;
	}
	
	public static function vxGetBoardView($board_id) {
		$url = "/board/view/{$board_id}.html";
		return $url;
	}
	
	public static function vxGetPostModify($post_id) {
		$url = "/post/modify/{$post_id}.vx";
		return $url;
	}
	
	public static function vxGetPostErase($post_id) {
		$url = "/post/erase/{$post_id}.vx";
		return $url;
	}
	
	public static function vxGetTopicErase($topipc_id) {
		$url = "/topic/erase/{$topic_id}.vx";
		return $url;
	}
	
	public static function vxGetTopicView($topic_id, $page = 1, $anchor = '') {
		if (!isset($_SESSION['babel_page_topic'])) {
			$_SESSION['babel_page_topic'] = 1;
		}
		if ($page > 1) {
			if ($anchor != '') {
				$url = "/topic/view/{$topic_id}/" . $page . ".html#" . $anchor;
			} else {
				$url = "/topic/view/{$topic_id}/" . $page . ".html";
			}
		} else {
			if ($anchor != '') {
				$url = "/topic/view/{$topic_id}/" . $_SESSION['babel_page_topic'] . ".html#" . $anchor;
			} else {
				$url = "/topic/view/{$topic_id}/" . $_SESSION['babel_page_topic'] . ".html";
			}
		}
		return $url;
	}
	
	public static function vxGetTopicViewMobile($topic_id, $page = 1, $anchor = '') {
		if (!isset($_SESSION['babel_page_topic_last_mobile'])) {
			$_SESSION['babel_page_topic_last_mobile'] = 1;
		}
		if ($page > 1) {
			if ($anchor != '') {
				$url = "/t/{$topic_id}/" . $page . "#" . $anchor;
			} else {
				$url = "/t/{$topic_id}/" . $page;
			}
		} else {
			if ($anchor != '') {
				$url = "/t/{$topic_id}/" . $_SESSION['babel_page_topic'] . "#" . $anchor;
			} else {
				$url = "/t/{$topic_id}/" . $_SESSION['babel_page_topic'];
			}
		}
		return $url;
	}
	
	public static function vxGetTopicNew($board_id) {
		$url = "/topic/new/{$board_id}.vx";
		return $url;
	}
	
	public static function vxGetTopicModify($topic_id) {
		$url = "/topic/modify/{$topic_id}.vx";
		return $url;
	}
	
	public static function vxToRedirect($addr) {
		header('Location: ' . $addr);
	}
	
	public static function vxGetExpenseView() {
		$url = '/expense/view.vx';
		return $url;
	}
	
	public static function vxGetOnlineView() {
		$url = '/online/view.vx';
		return $url;
	}
	
	public static function vxGetTopicFavorite() {
		$url = '/topic/favorite.vx';
		return $url;
	}
	
	public static function vxGetUserModify() {
		$url = '/user/modify.vx';
		return $url;
	}
	
	public static function vxGetUserMove() {
		$url = '/user/move.vx';
		return $url;
	}
	
	public static function vxGetUserHome($user_nick) {
		$url = '/u/' . $user_nick;
		return $url;
	}
	
	public static function vxGetUserOwnHome($message = '') {
		if ($message == '') {
			$url = '/me';
		} else {
			$url = '/me/' . $message;
		}
		return $url;
	}
	
	public static function vxGetZEN($anchor = '') {
		if ($anchor != '') {
			$url = '/zen#' . $anchor;
		} else {
			$url = '/zen';
		}
		return $url;
	}
	
	public static function vxGetEraseZENProject($zen_project_id = 0) {
		$url = '/erase/zen/project/' . $zen_project_id . '.vx';
		return $url;
	}
	
	public static function vxGetHome() {
		$url = '/';
		return $url;
	}
	
	public static function vxGetMessageHome() {
		$url = '/message/home.vx';
		return $url;
	}
	
	public static function vxGetIngPersonal($user_nick) {
		$url = '/ing/' . urlencode($user_nick);
		return $url;
	}
	
	public static function vxGetDryNew() {
		$url = '/dry/new.vx';
		return $url;
	}
}

/* E URL class */
?>