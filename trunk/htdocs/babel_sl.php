<?php
/* Project Babel
 *
 * Author: Livid Liu <v2ex.livid@mac.com>
 * File: /htdocs/babel_sl.php
 * Usage: Standalone Logic
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

require('core/StandaloneCore.php');

if (isset($_GET['m'])) {
	$m = strtolower(trim($_GET['m']));
} else {
	$m = 'home';
}

$s = &new Standalone;

switch ($m) {
	default:
	case 'home':
		$s->vxGoHome();
		break;
		
	case 'recv_portrait':
		$s->vxRecvPortrait();
		break;
	
	case 'recv_savepoint':
		$s->vxRecvSavepoint();
		break;
		
	case 'savepoint_erase':
		$s->vxSavepointErase();
		break;
		
	case 'recv_zen_project':
		$s->vxRecvZENProject();
		break;
		
	case 'erase_zen_project':
		$s->vxEraseZENProject();
		break;
		
	case 'recv_zen_task':
		$s->vxRecvZENTask();
		break;
	
	case 'change_zen_task_done':
		$s->vxChangeZENTaskDone();
		break;
		
	case 'change_zen_project_permission':
		$s->vxChangeZENProjectPermission();
		break;
		
	case 'erase_zen_task':
		$s->vxEraseZENTask();
		break;
		
	case 'undone_zen_task':
		$s->vxUndoneZENTask();
		break;
		
	case 'disable_uid':
		$s->vxDisableUserID();
		break;
		
	case 'erase_zero_topic_uid':
		$s->vxEraseZeroTopicUserID();
		break;
		
	case 'remove_message':
		$s->vxRemoveMessage();
		break;
		
	case 'topic_move_to':
		$s->vxTopicMoveTo();
		break;
		
	case 'json_home_tab_latest':
		$s->vxJSONHomeTabLatest();
		break;
		
	case 'json_home_tab_section':
		$s->vxJSONHomeTabSection();
		break;
		
	case 'api_zen2_delete_project':
		$s->z2 = new Zen_API($s->db, $s->User, $s->Validator);
		$s->z2->vxDeleteProject();
		break;
		
	case 'api_zen2_done_project':
		$s->z2 = new Zen_API($s->db, $s->User, $s->Validator);
		$s->z2->vxDoneProject();
		break;
		
	case 'api_zen2_undone_project':
		$s->z2 = new Zen_API($s->db, $s->User, $s->Validator);
		$s->z2->vxUndoneProject();
		break;
		
	case 'api_zen2_load_projects_active':
		$s->z2 = new Zen_API($s->db, $s->User, $s->Validator);
		echo $s->z2->vxLoadProjectsActive();
		break;
		
	case 'api_zen2_load_projects_done':
		$s->z2 = new Zen_API($s->db, $s->User, $s->Validator);
		echo $s->z2->vxLoadProjectsDone();
		break;
		
	case 'user_settle':
		$s->vxUserSettle();
		break;
		
	case 'recv_ing':
		$s->vxRecvIng();
		break;
		
	case 'erase_ing':
		$s->vxEraseIng();
		break;
		
	case 'js_ing_personal':
		$s->vxJavaScriptIngPersonal();
		break;
}
?>
