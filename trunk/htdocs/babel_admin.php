<?php
/* Project Babel
 *
 * Author: Livid Liu <v2ex.livid@mac.com>
 * File: /htdocs/babel_admin.php
 * Usage: Loader for Administrator Console
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

define('V2EX_BABEL', 1);
require_once('core/AdminCore.php');

if (isset($_GET['m'])) {
	$m = fetch_single($_GET['m']);
	if ($m == '') {
		$m = 'home';
	}
} else {
	$m = 'home';
}

define('__PAGE__', $m);

$a = &new Admin();

switch ($m) {
	default:
	case 'home':
		$a->vxHome();
		break;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="zh-CN">
<head>
<meta http-equiv="content-type" content="text/html;charset=utf-8" />
<meta http-equiv="cache-control" content="no-cache" />
<meta name="keywords" content="V2EX, Babel, Livid, PHP, " />
<title>V2EX Administrator Console</title>
<link href="/favicon.ico" rel="shortcut icon" />
<link href="/a/css/style.css" rel="stylesheet" type="text/css" />
<?php
if (MINT_LOCATION != '') {
	echo('<script src="' . MINT_LOCATION . '" type="text/javascript"></script>');
}
?>
</head>
<body>
<div id="top">
</div>
<div id="container">
<div id="left"><img src="/a/img/v2ex_logo_uranium_admin.png" alt="V2EX" />
</div>
<div id="right">
</div>
<div id="main">
</div>
</div>
</body>
</html>