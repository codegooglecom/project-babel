<?php
/* Project Zen
 *
 * Author: Livid Liu <v2ex.livid@mac.com>
 * File: /htdocs/core/ZenCore.php
 * Usage: Zen Core Classes
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

if (@V2EX_BABEL != 1) {
	die('<strong>Project Babel</strong><br /><br />Made by <a href="http://www.v2ex.com/">V2EX</a> | software for internet');
}

class Zen {
	public static function vxDeleteButton($project_id, $area) {
		return '&nbsp;<input type="image" onclick="z2DeleteProject(' . $project_id . ',' . "'{$area}'" . ');" alt="删除" src="/img/icons/silk/delete.png" align="absmiddle"></input>&nbsp;';
	}
	
	public static function vxDoneButton($project_id) {
		return '&nbsp;<input type="image" onclick="z2DoneProject(' . $project_id . ', ' . rand(1111, 9999) . ');" alt="完成" src="/img/icons/silk/accept.png" align="absmiddle"></input>&nbsp;';
	}
	
	public static function vxUndoneButton($project_id) {
		return '&nbsp;<input type="image" onclick="z2UndoneProject(' . $project_id . ', ' . rand(1111, 9999) . ');" alt="重来" src="/img/icons/silk/arrow_redo.png" align="absmiddle"></input>&nbsp;';
	}
	
	public static function vxIconTask($tasks) {
		return ' ' . $tasks . ' tasks';
	}
	
	public static function vxIconNote($notes) {
		return ' ' . $notes . ' notes';
	}
	
	public static function vxIconDB($dbs) {
		return ' ' . $dbs . ' dbs';
	}
	
	public static function vxTip($c) {
		if ($tips = $c->get('babel_zen2_tips')) {
			$tips = unserialize($tips);
			$_count = count($tips);
			$_tip = $tips[rand(0, ($_count - 1))];
		} else {
			require_once(BABEL_PREFIX . '/res/zen2_tips.php');
			$_count = count($tips);
			$_tip = $tips[rand(0, ($_count - 1))];
			$c->save(serialize($tips), 'babel_zen2_tips');
		}
		return $_tip;
	}
}

class Zen_API {
	public $User;
	public $db;
	
	private $r_failed = '通讯故障 ...';
	private $r_exception = '意外 ...';
	
	public function __construct($db, $User, $Validator, $header = true) {
		$this->db =& $db;
		$this->User =& $User;
		$this->Validator =& $Validator;
		if ($header) {
			header('Cache-control: no-cache, must-revalidate');
			header('Content-type: text/plain; charset=UTF-8');
		}
	}
	
	public function vxIsExistProject($project_id) {
		$sql = "SELECT zpr_id, zpr_uid FROM babel_zen_project WHERE zpr_id = {$project_id}";
		$rs = mysql_query($sql, $this->db);
		if ($Project = mysql_fetch_object($rs)) {
			mysql_free_result($rs);
			return $Project;
		} else {
			mysql_free_result($rs);
			return false;
		}
	}
	
	public function vxDeleteProject() {
		if (isset($_GET['project_id'])) {
			$project_id = intval($_GET['project_id']);
			$Project = $this->vxIsExistProject($project_id);
			if ($Project) {
				if ($Project->zpr_uid == $this->User->usr_id) {
					$sql = "DELETE FROM babel_zen_task WHERE zta_pid = {$project_id}";
					mysql_unbuffered_query($sql, $this->db);
					$sql = "DELETE FROM babel_zen_project WHERE zpr_id = {$project_id}";
					mysql_query($sql, $this->db);
					if (mysql_affected_rows($this->db) == 1) {
						if (isset($_GET['area'])) {
							$area = strtolower(fetch_single($_GET['area']));
							if ($area == 'active') {
								$o = $this->vxLoadProjectsActive();
								echo $o;
							} elseif ($area == 'done') {
								$o = $this->vxLoadProjectsDone();
								echo $o;
							} else {
								echo($this->r_failed);
							}
						} else {
							echo($this->r_failed);
						}
					} else {
						echo($this->r_failed);
					}
				} else {
					echo($this->r_failed);
				}
			} else {
				echo($this->r_failed);
			}
		} else {
			echo($this->r_failed);
		}
	}
	
	public function vxDoneProject() {
		if (isset($_GET['project_id'])) {
			$project_id = intval($_GET['project_id']);
			$Project = $this->vxIsExistProject($project_id);
			if ($Project) {
				if ($Project->zpr_uid == $this->User->usr_id) {
					$now = time();
					$sql = "UPDATE babel_zen_task SET zta_progress = 1, zta_completed = {$now} WHERE zta_pid = {$project_id}";
					mysql_unbuffered_query($sql);
					$sql = "UPDATE babel_zen_project SET zpr_progress = 1, zpr_completed = {$now} WHERE zpr_id = {$project_id}";
					mysql_query($sql, $this->db);
					if (mysql_affected_rows($this->db) == 1) {
						$o = $this->vxLoadProjectsActive();
						echo $o;
					} else {
						echo($this->r_failed);
					}
				} else {
					echo($this->r_failed);
				}
			} else {
				echo($this->r_failed);
			}
		} else {
			echo($this->r_failed);
		}
	}
	
	public function vxUndoneProject() {
		if (isset($_GET['project_id'])) {
			$project_id = intval($_GET['project_id']);
			$Project = $this->vxIsExistProject($project_id);
			if ($Project) {
				if ($Project->zpr_uid == $this->User->usr_id) {
					$now = time();
					$sql = "UPDATE babel_zen_project SET zpr_progress = 0, zpr_lasttouched = {$now} WHERE zpr_id = {$project_id}";
					mysql_query($sql, $this->db);
					if (mysql_affected_rows($this->db) == 1) {
						$o = $this->vxLoadProjectsDone();
						echo $o;
					} else {
						echo($this->r_failed);
					}
				} else {
					echo($this->r_failed);
				}
			} else {
				echo($this->r_failed);
			}
		} else {
			echo($this->r_failed);
		}
	}
	
	public function vxLoadProjectsActive() {
		$user_id = 0;
		if (isset($_GET['user_id'])) {
			$user_id = intval($_GET['user_id']);
			if (!$this->Validator->vxExistUser($user_id)) {
				$user_id = 0;
			}
		} else {
			if ($this->User->usr_id != 0) {
				$user_id = $this->User->usr_id;
			}
		}
		if ($user_id == 0) {
			echo $this->r_failed;
		} else {
			$sql = "SELECT zpr_id, zpr_uid, zpr_title, zpr_private, zpr_type, zpr_tasks, zpr_notes, zpr_dbs, zpr_created FROM babel_zen_project WHERE zpr_progress = 0 AND zpr_uid = {$user_id} ORDER BY zpr_created ASC";
			$rs = mysql_query($sql);
			$i = 0;
			$tasks = 0;
			$notes = 0;
			$dbs = 0;
			$o = '';
			while ($_p = mysql_fetch_array($rs)) {
				$i++;
				$tasks += $_p['zpr_tasks'];
				$notes += $_p['zpr_notes'];
				$dbs += $_p['zpr_dbs'];
				$_p['zpr_type_grid'] = 'zen2_grid_' . $_p['zpr_type'] . '_s';
				$o .= '<div class="zen2_project"><div class="' . $_p['zpr_type_grid'] . '"></div><div class="zen2_project_toolbar"><span class="tip_i"><small>' . Zen::vxIconTask($_p['zpr_tasks']) . ' / ' . Zen::vxIconNote($_p['zpr_notes']) . ' / ' . Zen::vxIconDB($_p['zpr_dbs']) . ' &nbsp;</small></span>';
				if ($_p['zpr_uid'] == $this->User->usr_id) {
					$o .= Zen::vxDoneButton($_p['zpr_id']);
					$o .= Zen::vxDeleteButton($_p['zpr_id'], 'active');
				}
				$o .= '</div><div class="zen2_project_main">&nbsp; <a href="/project/view/' . $_p['zpr_id'] . '.html">' . make_plaintext($_p['zpr_title']) . '</a><span class="tip_i">&nbsp;...&nbsp;<small>created ' . make_desc_time($_p['zpr_created']) . ' ago</small></span></div></div>';
				unset($_p);
			}
			mysql_free_result($rs);
			$o .= '<div class="conclude">' . $i . ' 个进行中的项目 - ' . $tasks . ' 项任务 - ' . $notes . ' 则笔记 - ' . $dbs . ' 个数据库</div>';
			return $o;
		}
	}
	
	public function vxLoadProjectsDone() {
		$user_id = 0;
		if (isset($_GET['user_id'])) {
			$user_id = intval($_GET['user_id']);
			if (!$this->Validator->vxExistUser($user_id)) {
				$user_id = 0;
			}
		} else {
			if ($this->User->usr_id != 0) {
				$user_id = $this->User->usr_id;
			}
		}
		if ($user_id == 0) {
			echo $this->r_failed;
		} else {
			$sql = "SELECT zpr_id, zpr_uid, zpr_title, zpr_private, zpr_type, zpr_tasks, zpr_notes, zpr_dbs, zpr_created FROM babel_zen_project WHERE zpr_progress = 1 AND zpr_uid = {$user_id} ORDER BY zpr_completed DESC";
			$rs = mysql_query($sql);
			$i = 0;
			$tasks = 0;
			$notes = 0;
			$dbs = 0;
			$o = '';
			while ($_p = mysql_fetch_array($rs)) {
				$i++;
				$tasks += $_p['zpr_tasks'];
				$notes += $_p['zpr_notes'];
				$dbs += $_p['zpr_dbs'];
				$_p['zpr_type_grid'] = 'zen2_grid_' . $_p['zpr_type'] . '_s';
				$o .= '<div class="zen2_project"><div class="' . $_p['zpr_type_grid'] . '"></div><div class="zen2_project_toolbar"><span class="tip_i"><small>' . Zen::vxIconTask($_p['zpr_tasks']) . ' / ' . Zen::vxIconNote($_p['zpr_notes']) . ' / ' . Zen::vxIconDB($_p['zpr_dbs']) . ' &nbsp;</small></span>';
				if ($_p['zpr_uid'] == $this->User->usr_id) {
					$o .= Zen::vxUndoneButton($_p['zpr_id']);
					$o .= Zen::vxDeleteButton($_p['zpr_id'], 'done');
				}
				$o .= '</div><div class="zen2_project_main">&nbsp; <a href="/project/view/' . $_p['zpr_id'] . '.html">' . make_plaintext($_p['zpr_title']) . '</a><span class="tip_i">&nbsp;...&nbsp;<small>created ' . make_desc_time($_p['zpr_created']) . ' ago</small></span></div></div>';
				unset($_p);
			}
			mysql_free_result($rs);
			$o .= '<div class="conclude">' . $i . ' 个完成了的项目 - ' . $tasks . ' 项任务 - ' . $notes . ' 则笔记 - ' . $dbs . ' 个数据库</div>';
			return $o;
		}
	}
}
?>
