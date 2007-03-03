<?php
/* Project Babel
 *
 * Author: Livid Torvalds
 * File: /htdocs/babel_ajax.php
 * Usage: AJAX Server
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

require('core/AJAXCore.php');

if (isset($_GET['m'])) {
	$m = strtolower(trim($_GET['m']));
} else {
	$m = 'null';
}

$a = new AJAXServer();

switch ($m) {
	default:
	case 'null':
		$a->vxNull();
		break;
		
	case 'fav_topic_add':
		if ($a->User->vxIsLogin()) {
			if (isset($_GET['topic_id'])) {
				$topic_id = intval($_GET['topic_id']);
				if ($a->Validator->vxExistTopic($topic_id)) {
					$sql = "SELECT fav_res FROM babel_favorite WHERE fav_res = {$topic_id} AND fav_type = 0 AND fav_uid = {$a->User->usr_id}";
					$rs = mysql_query($sql);
					if (mysql_num_rows($rs) == 1) {
						mysql_free_result($rs);
						$a->vxDuplicated();
						break;
					} else {
						mysql_free_result($rs);
						$Topic = new Topic($topic_id, $a->db);
						$a->vxFavTopicAdd($Topic);
						break;
					}
				} else {
					$a->vxMismatched();
					break;
				}
			} else {
				$a->vxMismatched();
				break;
			}
		} else {
			$a->vxDenied();
			break;
		}
	
	case 'fav_node_add':
		if ($a->User->vxIsLogin()) {
			if (isset($_GET['node_id'])) {
				$node_id = intval($_GET['node_id']);
				if ($a->Validator->vxExistNode($node_id)) {
					$sql = "SELECT fav_res FROM babel_favorite WHERE fav_res = {$node_id} AND fav_type = 1 AND fav_uid = {$a->User->usr_id}";
					$rs = mysql_query($sql);
					if (mysql_num_rows($rs) == 1) {
						mysql_free_result($rs);
						$a->vxDuplicated();
						break;
					} else {
						mysql_free_result($rs);
						$Node = new Node($node_id, $a->db);
						$Section = $Node->vxGetNodeInfo($Node->nod_sid);
						if ($Node->nod_level > 1) {
							$a->vxFavNodeAdd($Node, $Section);
							break;
						} else {
							$a->vxMismatched();
							break;
						}
					}
				} else {
					$a->vxMismatched();
					break;
				}
			} else {
				$a->vxMismatched();
				break;
			}
		} else {
			$a->vxDenied();
			break;
		}
		
	case 'fav_channel_add':
		if ($a->User->vxIsLogin()) {
			if (isset($_GET['channel_id'])) {
				$channel_id = intval($_GET['channel_id']);
				if ($a->Validator->vxExistChannel($channel_id)) {
					$sql = "SELECT fav_res FROM babel_favorite WHERE fav_res = {$channel_id} AND fav_type = 2 AND fav_uid = {$a->User->usr_id}";
					$rs = mysql_query($sql);
					if (mysql_num_rows($rs) == 1) {
						mysql_free_result($rs);
						$a->vxDuplicated();
						break;
					} else {
						mysql_free_result($rs);
						$Channel = new Channel($channel_id, $a->db);
						$a->vxFavChannelAdd($Channel);
						break;
					}
				} else {
					$a->vxMismatched();
					break;
				}
			} else {
				$a->vxMismatched();
				break;
			}
		} else {
			$a->vxDenied();
			break;
		}
	
	case 'fav_remove':
		if ($a->User->vxIsLogin()) {
			if (isset($_GET['fav_id'])) {
				$fav_id = intval($_GET['fav_id']);
				$sql = "SELECT fav_id FROM babel_favorite WHERE fav_id = {$fav_id} AND fav_uid = {$a->User->usr_id}";
				$rs = mysql_query($sql);
				if (mysql_num_rows($rs) == 1) {
					$Favorite = new Favorite(mysql_result($rs, 0, 0), $a->db);
					mysql_free_result($rs);
					$a->vxFavRemove($Favorite);
					break;
				} else {
					mysql_free_result($rs);
					$a->vxMismatched();
					break;
				}
			} else {
				$a->vxMismatched();
				break;
			}
		} else {
			$a->vxDenied();
			break;
		}
}
?>
