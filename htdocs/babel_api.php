<?php
/* Project Babel
*  Author: Livid Torvalds
*  File: /htdocs/babel_api.php
*  Usage: API Controller
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

require('core/APICore.php');

if (isset($_GET['m'])) {
	$m = strtolower(trim($_GET['m']));
} else {
	$m = 'home';
}

$a =& new API;

switch ($m) {
	default:
	case 'topic_create':
		$a->vxTopicCreate();
		break;
}
?>