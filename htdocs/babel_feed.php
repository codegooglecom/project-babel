<?php
/* Project Babel
 *
 * Author: Livid Liu <v2ex.livid@mac.com>
 * File: /htdocs/babel_feed.php
 * Usage: Feed controller
 * Format: 1 tab indent(4 spaces), LF, UTF-8, no-BOM
 *  
 * Subversion Keywords:
 *
 * $Id: babel_feed.php 84 2007-02-11 22:48:31Z livid $
 * $Date: 2007-02-12 06:48:31 +0800 (Mon, 12 Feb 2007) $
 * $Revision: 84 $
 * $Author: livid $
 * $URL: http://svn.cn.v2ex.com/svn/babel/trunk/htdocs/babel_feed.php $
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

require('core/FeedCore.php');

if (isset($_GET['m'])) {
	$m = strtolower(trim($_GET['m']));
} else {
	$m = 'home';
}

$f = new Feed;

switch ($m) {
	default:
	case 'home':
		$f->vxFeed();
		break;
		
	case 'board':
		if (isset($_GET['board_name'])) {
			$board_name = strtolower(fetch_single($_GET['board_name']));
			$board_name_real = mysql_real_escape_string($board_name);
			$sql = "SELECT nod_id, nod_level, nod_name, nod_title, nod_topics FROM babel_node WHERE nod_name = '{$board_name_real}' AND nod_level > 0";
			$rs = mysql_query($sql);
			if ($Node = mysql_fetch_object($rs)) {
				mysql_free_result($rs);
				$f->vxFeedBoard($Node);
			} else {
				mysql_free_result($rs);
				$f->vxFeed();
			}
		} else {
			$f->vxFeed();
		}
		break;
		
	case 'user':
		if (isset($_GET['user_nick'])) {
			$user_nick = fetch_single($_GET['user_nick']);
			$user_nick_real = mysql_real_escape_string($user_nick);
			$sql = "SELECT usr_id, usr_nick, usr_gender, usr_portrait FROM babel_user WHERE usr_nick = '{$user_nick_real}'";
			$rs = mysql_query($sql);
			if ($User = mysql_fetch_object($rs)) {
				mysql_free_result($rs);
				$f->vxFeedUser($User);
			} else {
				mysql_free_result($rs);
				$f->vxFeed();
			}
		} else {
			$f->vxFeed();
		}
		break;
		
	case 'geo':
		if (isset($_GET['geo'])) {
			$geo = fetch_single($_GET['geo']);
			$Geo = new Geo($geo);
			if ($Geo->geo->geo) {
				$f->vxFeedGeo($Geo);
			} else {
				$f->vxFeed();
			}
		} else {
			$f->vxFeed();
		}
		break;
		
	case 'topic':
		if (isset($_GET['topic_id'])) {
			$topic_id = intval($_GET['topic_id']);
			if ($f->Validator->vxExistTopic($topic_id)) {
				$f->vxFeedTopic($topic_id);
			} else {
				$f->vxFeed();
			}
		} else {
			$f->vxFeed();
		}
		break;
}
?>
