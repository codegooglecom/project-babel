<?php
/* Project Babel
*  Author: Livid Torvalds
*  File: /htdocs/babel_dry.php
*  Usage: Project Dry
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

require('core/DryCore.php');

if (isset($_GET['m'])) {
	$m = strtolower(trim($_GET['m']));
} else {
	$m = 'home';
}

$d =& new Dry();

switch ($m) {
	default:
	case 'home':
		$d->vxDry();
		break;

}
?>