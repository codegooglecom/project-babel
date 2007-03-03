<?php
/* Project Babel
*  Author: Livid Torvalds
*  File: /htdocs/babel_api.php
*  Usage: API Controller
*  Format: 1 tab indent(4 spaces), LF, UTF-8, no-BOM
*
*  Subversion Keywords:
*
*  $Id: babel_api.php 61 2007-02-05 08:07:06Z livid $
*  $LastChangedDate: 2007-02-05 16:07:06 +0800 (Mon, 05 Feb 2007) $
*  $LastChangedRevision: 61 $
*  $LastChangedBy: livid $
*  $URL: http://svn.cn.v2ex.com/svn/babel/trunk/htdocs/babel_api.php $
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