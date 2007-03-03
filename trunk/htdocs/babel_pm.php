<?php
/* Project Babel
*  Author: Livid Torvalds
*  File: /htdocs/babel_pm.php
*  Usage: Loader for Private Message System
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

DEFINE('V2EX_BABEL', 1);

require('core/PrivateMessageCore.php');

if (isset($_GET['m'])) {
	$m = strtolower(trim($_GET['m']));
} else {
	$m = 'home';
}

$p = new PrivateMessage;

switch ($m) {
	default:
	case 'home':
		$p->vxHome();
		break;
	case 'compose':
		$p->vxCompose();
		break;
	case 'create':
		$p->vxCreate();
		break;
	case 'inbox':
		$p->vxInbox();
		break;
	case 'sent':
		$p->vxSent();
		break;
	case 'view':
		$p->vxView();
		break;
	case 'draft':
		$p->vxDraft();
		break;
}
?>