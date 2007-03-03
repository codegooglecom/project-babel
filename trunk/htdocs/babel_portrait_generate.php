<?php
/* Project Babel
 *
 * Author: Livid Liu <v2ex.livid@mac.com>
 * File: /htdocs/babel_portrait_generate.php
 * Usage: Generate static portrait images
 * Format: 1 tab indent(4 spaces), LF, UTF-8, no-BOM
 *  
 * Subversion Keywords:
 *
 * $Id: babel_portrait_generate.php 71 2007-02-05 14:04:47Z livid $
 * $Date: 2007-02-05 22:04:47 +0800 (Mon, 05 Feb 2007) $
 * $Revision: 71 $
 * $Author: livid $
 * $URL: http://svn.cn.v2ex.com/svn/babel/trunk/htdocs/babel_portrait_generate.php $
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
require_once('core/Settings.php');

$db = mysql_connect(BABEL_DB_HOSTNAME . ':' . BABEL_DB_PORT, BABEL_DB_USERNAME, BABEL_DB_PASSWORD);

$dir_output = BABEL_PREFIX . '/htdocs/img/p_static';

if (file_exists($dir_output)) {
	if (!is_writable($dir_output)) {
		die('[WARNING] - ' . $dir_output . ' is not writable!' . "\n");
	}
} else {
	die('[WARNING] - ' . $dir_output . " did not exist!\n");
}

mysql_select_db(BABEL_DB_SCHEMATA, $db);
mysql_query("SET NAMES utf8");
mysql_query("SET CHARACTER SET utf8");
mysql_query("SET COLLATION_CONNECTION='utf8_general_ci'");

$sql = "SELECT urp_id, urp_filename, urp_content FROM babel_user_portrait";
$rs = mysql_query($sql);

echo('<pre>');

while ($_urp = mysql_fetch_array($rs)) {
	$fn = $dir_output . '/' . $_urp['urp_filename'] . '.' . BABEL_PORTRAIT_EXT;
	$f = $_urp['urp_content'];
	$r = file_put_contents($fn, $f);
	echo '[OK] - ' . $fn . " is written: {$r} bytes.\n";
}

echo('</pre>');

mysql_free_result($rs);
mysql_close($db);
?>